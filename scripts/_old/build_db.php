<?php
require_once('lib/functions.php');
require_once('lib/Experiment.php');
require_once('lib/ExperimentFile.php');
require_once('lib/Set.php');
require_once('lib/Result.php');
require_once('lib/YeastStrain.php');
require_once('lib/Gene.php');
require_once('lib/YeastStrainTable.php');
require_once('lib/BuildLog.php');
require_once('lib/PublishedGenotypeTable.php');

// bump up the max memory to infinity
ini_set('memory_limit', '-1');

// check command line flag for building public genes only
$isPublic = false;
if (count($argv) > 1) {
  if ($argv[1] == 'public') {
    $isPublic = true;
  }
}

// get the output director from the command line
$outputDir = "output";
if ($isPublic) {
  $outputDir = "output-public";
}

// clear output folder and copy over ncbi preloaded database
echo "=== PREPARING OUTPUT FOLDER ===\n\n";
rmdirr($outputDir);
mkdir($outputDir);
mkdir($outputDir . '/plots');
mkdir($outputDir . '/plots/result');
mkdir($outputDir . '/plots/set');
mkdir($outputDir . '/plots/cross_media');
mkdir($outputDir . '/plots/cross_mating_type');
copy('db/rls.db', $outputDir . '/rls.db');
chmod($outputDir . '/rls.db', 0777);

$fh = fopen($outputDir . '/updating', 'w');
fputs($fh, 'place this file in data directory to enable updating message');
fclose($fh);

// connect to output rls database
echo "connecting to output/rls.db database\n";
$dbh = new PDO('sqlite:' . $outputDir . '/rls.db');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "\n";

// copy yeast_strain table from intput/core.db to output/rls.db
echo "copying yeast_strains from input/core.db\n";
$dbh->exec('ATTACH "input/yeast_strain.db" as core');
$dbh->exec('INSERT INTO yeast_strain SELECT * FROM core.yeast_strain');
$dbh->exec('UPDATE yeast_strain SET genotype_unique = REPLACE(genotype_unique, "-", "")');
$dbh->exec('VACUUM');
echo "\n";

echo "saving meta data\n";
$dbh->exec('INSERT INTO meta (name, value) VALUES ("built_at", DATETIME("now", "localtime"))');

// gene db required for getGenotypeClean()
$geneDbh = new PDO('sqlite:db/ncbi_gene_yeast.db');
$geneDbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//echo "\n=== POPULATING PUBLICATION RELATIONS ===\n\n";
//$dbh->beginTransaction();
//buildGenotypePublications($dbh, 'input/published_genes.csv');
//$dbh->commit();

// build combined results (pooling = file) from experiment input files
echo "\n=== BUILDING FILE-COMBINED RESULTS ===\n\n";
$dbh->beginTransaction();
buildUnpooledResults($dbh, 'input/experiments', $isPublic);
$dbh->commit();

// build strain pooled results from unpooled results
echo "\n== BUILDING STRAIN POOLED RESULTS ===\n\n";
$dbh->beginTransaction();
buildStrainPooledResults($dbh);
$dbh->commit();

// build genotype pooled sets from strain pooled results
echo "\n=== BUILDING GENOTYPE POOLED RESULTS ===\n\n";
$dbh->beginTransaction();
buildGenotypePooledResults($dbh);
$dbh->commit();

// update cross media table
echo "\n=== BUILDING CROSS MEDIA TABLE ===\n";
$dbh->beginTransaction();
buildCrossMedia($dbh);
$dbh->commit();

// update cross mating type table
echo "\n=== BUILDING CROSS MATING TYPE TABLE ===\n";
$dbh->beginTransaction();
buildCrossMatingType($dbh);
$dbh->commit();

// update result data cache fileds (experiments, set_lifespans, ref_lifespans)
echo "\n=== UPDATING CACHE FIELDS ===\n\n";
$dbh->beginTransaction();
updateResultCacheFields($dbh);
$dbh->commit();

// vacuuming database (optimizes keys)
echo "=== OPTIMIZING DATABASE ===\n\n";
$dbh->exec('VACUUM');



// ===========================================================================
// FUNCTIONS BELOW
// ===========================================================================

/**
function buildGenotypePublications(PDO $dbh, $filename)
{
  ini_set('auto_detect_line_endings', true);

  if (!file_exists($filename)) {
    echo "file does not exist\n";
    return;
  }

  $fin = fopen($filename, "r");
  $data = array();
  while (($lineData = fgetcsv($fin)) !== false) {
    $data[] = $lineData;
  }

  for ($i = 1; $i < count($data); $i++) { // skip header
    $rowData = $data[$i];

    $genotype = strtolower($rowData[0]);
    $genotype = getCleanGenotype($genotype, $dbh);
    $pubmedId = $rowData[2];

    // skip insert if already exists
    $stmt = $dbh->prepare('
      SELECT * FROM genotype_pubmed_id WHERE genotype = ? AND pubmed_id = ? LIMIT 1
    ');
    $stmt->execute(array($genotype, $pubmedId));
    if ($row = $stmt->fetch()) {
      continue;
    }

    // insert into database
    $stmt = $dbh->prepare('
      INSERT INTO genotype_pubmed_id (genotype, pubmed_id) VALUES (?, ?)
    ');
    $stmt->execute(array($genotype, $pubmedId));
  }
}
*/

function buildUnpooledResults($dbh, $inputDir, $isPublic = false)
{
  $yeastStrainTable = new YeastStrainTable($dbh);
  $publishedGenotypeTable = new PublishedGenotypeTable($dbh);

  // for each file, extract data and insert into database
  foreach (getFilenames($inputDir) as $filename) {
    echo "processing $filename\n";

    // insert experiment into db
    $experimentName = basename($filename);

    // remove extension and leading "expt" from experiment names
    if (preg_match('/^(expt)?(.+)\.csv$/', $experimentName, $matches)) {
      $experimentName = $matches[2];
    }

    // add a null entry so experiment shows up at least in log page
    $buildLog = new BuildLog($dbh, $experimentName, null);
    $buildLog->save();

    // load the experiment file and get the combined rows
    try {
      $experimentFile = new ExperimentFile($filename);
      $combinedRows = $experimentFile->getCombinedRows();
    }
    catch (Exception $e) {
      $message = 'file skipped: '.$e->getMessage();
      $buildLog = new BuildLog($dbh, $experimentName, $message);
      $buildLog->save();
      echo $message, "\n\n";
      continue;
    }

    // save sets
    $sets = array();
    $setsByRowId = array();
    foreach ($combinedRows as $key => $combinedRow) {
      // prepare set values and create new set object
      $setName = $combinedRow['name'];
      $lifespans = $combinedRow['lifespans'];

      $setValues = array(
        'name' => $setName,
        'experiment' => $experimentName,
        'lifespans' => $lifespans,
        'lifespan_start_count' => $combinedRow['lifespan_start_count'],
      );

      // add optional values if they are not empty
      if ( ! empty($combinedRow['media'])) {
        $setValues['media'] = $combinedRow['media'];
      }
      if ( ! empty($combinedRow['temperature'])) {
        $setValues['temperature'] = $combinedRow['temperature'];
      }
      if (!empty($combinedRow['strain'])) {
        // TODO: look up strain from separate database (or get from web
        // service). Store strains into local table with cleaned pooling
        // genotype. No need to store all strains in table when only a
        // subset appears in lifespan experiment files.
        $strainName = strtoupper($combinedRow['strain']);
        $strain = $yeastStrainTable->findByName($strainName);
        if ($strain) {
          $setValues['strain'] = $strain;
        }
        else {
          $message = "strain '$strainName' not found in core.db yeast_strain table";
          $buildLog = new BuildLog($dbh, $experimentName, $message);
          $buildLog->save();
          echo $message, "\n";
        }
      }

      // if we are loading public set, check if genotype is in published list.
      // if so we want to save the set, otherwise skip it.
      $addRow = true;
      if ($isPublic) {
        if ($strain == null) {
          $addRow = false;
        } else {
          $genotype = $strain->poolingGenotype;

          $wtGenotypes = array('BY4741', 'BY4742', 'BY4743');
          if (!in_array($genotype, $wtGenotypes)) {
            $pubmedIds = $publishedGenotypeTable->findPubmedIdsByGenotype($genotype);
            if (count($pubmedIds) == 0) {
              $addRow = false;
            }
          }
        }
      }

      if ($addRow) {
        // save the set and save for result building
        $set = new Set($dbh, $setValues);
        $set->save();
        $sets[$key] = $set;

        foreach ($combinedRow['row_ids'] as $id) {
          $setsByRowId[$id] = $set;
        }
      }
    }

    // build results
    foreach ($combinedRows as $key => $combinedRow) {
      $setName = $combinedRow['name'];
      $set = $sets[$key];

      if ($set == null) {
        continue;
      }

      // set up base values (set_* fields only)
      $baseValues = array(
        'experiments' => array($set->experimentName),
        'set_name' => $set->name,
        'set_media' => $set->media,
        'set_temperature' => $set->temperature,
        'set_ids' => array($set->id),
        'set_lifespans' => $set->lifespans,
        'set_lifespan_start_count' => $set->lifespanStartCount,
      );
      if (($strain = $set->strain) !== null) {
        $baseValues['set_strain'] = $strain->name;
        $baseValues['set_background'] = $strain->background;
        $baseValues['set_mating_type'] = $strain->matingType;
        $messages = array();
        $genotype = $strain->poolingGenotype;
        foreach ($messages as $message) {
          $buildLog = new BuildLog($dbh, $experimentName, $message);
          $buildLog->save();
        }
        if (! empty($genotype)) {
          $baseValues['set_genotype'] = $genotype;
        }
      }

      // get actual references since some reference names may not even exist
      $references = array();
      foreach ($combinedRow['references'] as $input) {
        $ref = null;
        if (intval($input) == $input) {
          // input is a number, so look for set by id
          $ref = $setsByRowId[$input];
        }
        if ($ref === null) {
          // look for reference by name
          $ref = $sets[$input];
        }
        if ($ref !== null) {
          $references[] = $ref;
        }
      }

      // remove duplicate references in reference ids
      $cleanReferences = array();
      foreach ($combinedRow['references'] as $refId) {
        $refSet = $setsByRowId[$refId];
        if (!in_array($refSet, $cleanReferences)) {
          $cleanReferences[] = $refSet;
        }
      }
      $references = $cleanReferences;

      if (count($references) == 0) {
        continue;
      }

      // save results to database
      $resultId = null;
      if (count($references) == 0) {
        // save result without reference
        $result = new Result($dbh, $baseValues);
        $result->save();
        $resultId = $result->id;
      }
      else {
        // save a separate result for each reference
        foreach ($references as $reference) {
          // prepare and save result object with reference
          $values = $baseValues;
          $values['ref_name'] = $reference->name;
          $values['ref_media'] = $reference->media;
          $values['ref_temperature'] = $reference->temperature;
          $values['ref_ids'] = array($reference->id);
          $values['ref_lifespans'] = $reference->lifespans;
          $values['ref_lifespan_start_count'] = $reference->lifespanStartCount;
          if (($strain = $reference->strain) !== null) {
            $values['ref_strain'] = $strain->name;
            $values['ref_background'] = $strain->background;
            $values['ref_mating_type'] = $strain->matingType;
            $genotype = $strain->poolingGenotype;
            if (! empty($genotype)) {
              $values['ref_genotype'] = $genotype;
            }
          }
          try {
            $result = new Result($dbh, $values);
            $result->pooledBy = 'file';
            $result->save();
            $resultId = $result->id;
          }
          catch (Exception $e) {
            echo "exception: failed to save result object to db:\n";
            print_r($result);
          }
        }
      }
    }

    echo "\n";
  }
}



function buildStrainPooledResults($dbh)
{
  $sth = $dbh->prepare('
    SELECT
      r.id as "id",
      r.set_name as "set_name",
      ys.name as "set_strain_name",
      r.set_strain as "set_strain",
      r.set_background as "set_background",
      r.set_mating_type as "set_mating_type",
      r.set_genotype as "set_genotype",
      r.set_media as "set_media",
      r.set_temperature as "set_temperature",
      r.ref_name as "ref_name",
      r.ref_strain as "ref_strain",
      r.ref_background as "ref_background",
      r.ref_mating_type as "ref_mating_type",
      r.ref_genotype as "ref_genotype",
      r.ref_media as "ref_media",
      r.ref_temperature as "ref_temperature"
    FROM result r
    LEFT JOIN yeast_strain ys ON ys.id = r.set_strain
    LEFT JOIN yeast_strain yr ON yr.id = r.ref_strain
    WHERE r.pooled_by = "file"
    AND r.set_strain IS NOT NULL
    AND (r.ref_strain IS NOT NULL OR r.ref_name IS NULL)
    ');
  $sth->execute();
  $resultRows = $sth->fetchAll(PDO::FETCH_ASSOC);

  $sthSelectResultSet = $dbh->prepare('
    SELECT rs.set_id FROM result_set rs
    WHERE rs.result_id = ? ');

  $sthSelectResultRef = $dbh->prepare('
    SELECT rr.set_id FROM result_ref rr
    WHERE rr.result_id = ? ');

  $pooledResults = array(); // array of Result, indexed by pooling key
  foreach ($resultRows as $resultRow) {
    $poolingKey = join('/', array(
      $resultRow['set_strain'],
      $resultRow['set_media'],
      $resultRow['set_temperature'],
      $resultRow['ref_strain'],
      strtolower(preg_replace('/\s+/', '', $resultRow['ref_media'])), // case insensitive, ignore whitespace
      $resultRow['ref_temperature'],
      ));

    $pooledResult = $pooledResults[$poolingKey];
    if ( ! isset($pooledResult)) {
      // create a new pooled result object from previous data
      $pooledResult = new Result($dbh, $resultRow);
      $pooledResult->id = null;
      $pooledResult->setName = $resultRow['set_strain'];
      $pooledResult->refName = $resultRow['ref_strain'];
      $pooledResult->pooledBy = 'strain';
    }

    // fetch set ids and append them to result object
    $setIds = array();
    $sthSelectResultSet->execute(array($resultRow['id']));
    while ($resultSetRow = $sthSelectResultSet->fetch(PDO::FETCH_ASSOC)) {
      $setId = $resultSetRow['set_id'];
      $setIds[$setId] = $setId;
    }
    $pooledResult->setIds = array_merge($pooledResult->setIds, array_keys($setIds));

    // fetch ref set ids and append them to result object
    $refIds = array();
    $sthSelectResultRef->execute(array($resultRow['id']));
    while ($resultRefRow = $sthSelectResultRef->fetch(PDO::FETCH_ASSOC)) {
      $setId = $resultRefRow['set_id'];
      $refIds[$setId] = $setId;
    }
    $pooledResult->refIds = array_merge($pooledResult->refIds, array_keys($refIds));

    // copy into pooled sets array
    $pooledResults[$poolingKey] = $pooledResult;
  }

  // finally, save the pooled result objects
  foreach ($pooledResults as $pooledResult) {
    $pooledResult->save();
  }
}

function buildGenotypePooledResults($dbh)
{
  $sth = $dbh->prepare('
    SELECT
      r.id as "id",
      r.set_name as "set_name",
      ys.name as "set_strain_name",
      r.set_strain as "set_strain",
      r.set_background as "set_background",
      r.set_mating_type as "set_mating_type",
      r.set_genotype as "set_genotype",
      r.set_media as "set_media",
      r.set_temperature as "set_temperature",
      r.ref_name as "ref_name",
      r.ref_strain as "ref_strain",
      r.ref_background as "ref_background",
      r.ref_mating_type as "ref_mating_type",
      r.ref_genotype as "ref_genotype",
      r.ref_media as "ref_media",
      r.ref_temperature as "ref_temperature"
    FROM result r
    LEFT JOIN yeast_strain ys ON ys.id = r.set_strain
    LEFT JOIN yeast_strain yr ON yr.id = r.ref_strain
    WHERE r.pooled_by = "strain"
    AND r.set_genotype IS NOT NULL
    AND (r.ref_genotype IS NOT NULL OR r.ref_name IS NULL)
    ');
  $sth->execute();
  $resultRows = $sth->fetchAll(PDO::FETCH_ASSOC);

  $sthSelectResultSet = $dbh->prepare('
    SELECT set_id FROM result_set WHERE result_id = ? ');
  $sthSelectResultRef = $dbh->prepare('
    SELECT set_id FROM result_ref WHERE result_id = ? ');

  $pooledResults = array(); // array of Result, indexed by pooling key
  foreach ($resultRows as $resultRow) {
    $setGenotypeClean = $resultRow['set_genotype'];
    $refGenotypeClean = $resultRow['ref_genotype'];

    $poolingKey = join('/', array(
      $setGenotypeClean,
      $resultRow['set_background'],
      $resultRow['set_mating_type'],
      strtolower(preg_replace('/\s+/', '', $resultRow['set_media'])), // case insensitive, ignore whitespace
      $resultRow['set_temperature'],
      $refGenotypeClean,
      $resultRow['ref_background'],
      $resultRow['ref_mating_type'],
      strtolower(preg_replace('/\s+/', '', $resultRow['ref_media'])), // case insensitive, ignore whitespace
      $resultRow['ref_temperature'],
      ));

    $pooledResult = $pooledResults[$poolingKey];
    if ( ! isset($pooledResult)) {
      // create a new pooled result object from previous data
      $pooledResult = new Result($dbh, $resultRow);
      $pooledResult->id = null;
      $pooledResult->setName = $setGenotypeClean;
      $pooledResult->setStrain = null;
      $pooledResult->refName = $refGenotypeClean;
      $pooledResult->refStrain = null;
      $pooledResult->pooledBy = 'genotype';
    }

    // fetch set ids and append them to result object
    $setIds = array();
    $sthSelectResultSet->execute(array($resultRow['id']));
    while ($resultSetRow = $sthSelectResultSet->fetch(PDO::FETCH_ASSOC)) {
      $setId = $resultSetRow['set_id'];
      $setIds[$setId] = $setId;
    }
    $pooledResult->setIds = array_merge($pooledResult->setIds, array_keys($setIds));

    // fetch ref set ids and append them to result object
    $refIds = array();
    $sthSelectResultRef->execute(array($resultRow['id']));
    while ($resultRefRow = $sthSelectResultRef->fetch(PDO::FETCH_ASSOC)) {
      $setId = $resultRefRow['set_id'];
      $refIds[$setId] = $setId;
    }
    $pooledResult->refIds = array_merge($pooledResult->refIds, array_keys($refIds));

    // copy into pooled sets array
    $pooledResults[$poolingKey] = $pooledResult;
  }

  // finally, save the pooled result objects
  foreach ($pooledResults as $pooledResult) {
    $pooledResult->save();
  }
}

function buildCrossMedia($db)
{
  // fetch unique keys. Make sure set background, mating type, media, and
  // temperature match the reference
  $query = '
    SELECT id, set_genotype, set_background, set_mating_type,
      set_temperature, set_media
    FROM result
    WHERE pooled_by = "genotype"
    AND LENGTH(set_background) > 0 AND set_background = ref_background
        AND ref_background = ref_genotype
    AND LENGTH(set_mating_type) > 0 AND set_mating_type = ref_mating_type
    AND LENGTH(set_temperature) > 0 AND set_temperature = ref_temperature
    AND set_media IN ("YPD", "0.5% D", "0.05% D", "3% Gly")
    AND ref_media = "YPD"
    ';
  $stmt = $db->prepare($query);
  $stmt->execute();

  // build data set
  $rows = array();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = join('/', array(
      $row['set_genotype'],
      $row['set_background'],
      $row['set_mating_type'],
      $row['set_temperature'],
      ));
    $media = $row['set_media'];

    if (!isset($rows[$key])) {
      $rows[$key] = array(
        'genotype' => $row['set_genotype'],
        'background' => $row['set_background'],
        'mating_type' => $row['set_mating_type'],
        'temperature' => $row['set_temperature'],
        'results' => array(),
        );
    }
    $rows[$key]['results'][$media] = $row['id'];
  }

  // save the rows to database
  $stmtSave = $db->prepare('
    INSERT INTO cross_media (
      genotype, background, mating_type, temperature,
      ypd_result_id, d05_result_id, d005_result_id, gly3_result_id
    ) VALUES (?, ?, ?, ?,  ?, ?, ?, ?)
    ');

  foreach ($rows as $row) {
    $params = array(
      $row['genotype'],
      $row['background'],
      $row['mating_type'],
      $row['temperature'],
      $row['results']["YPD"],
      $row['results']["0.5% D"],
      $row['results']["0.05% D"],
      $row['results']["3% Gly"],
      );
    $stmtSave->execute($params);
  }
}

function buildCrossMatingType($db)
{
    // fetch unique keys. Make sure set background, mating type, media, and
  // temperature match the reference
  $query = '
    SELECT id, set_genotype, set_mating_type, set_temperature, set_media
    FROM result
    WHERE pooled_by = "genotype"
    AND (
      set_background = "BY4741"
      OR set_background = "BY4742"
      OR set_background = "BY4743"
    )
    AND set_background = ref_background
    AND ref_background = ref_name
    AND LENGTH(set_temperature) > 0 AND set_temperature = ref_temperature
    AND LENGTH(set_media) > 0 AND set_media = ref_media
    AND LENGTH(set_mating_type) > 0 AND LENGTH(ref_mating_type) > 0
    ';
  $stmt = $db->prepare($query);
  $stmt->execute();

  // build data set
  $rows = array();
  foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = join('/', array(
      $row['set_genotype'],
      strtolower(preg_replace('/\s+/', '', $row['set_media'])), // case insensitive, ignore whitespace
      $row['set_temperature'],
      ));
    $matingType = $row['set_mating_type'];

    if (!isset($rows[$key])) {
      $rows[$key] = array(
        'genotype'    => $row['set_genotype'],
        'media'       => $row['set_media'],
        'temperature' => $row['set_temperature'],
        'results'     => array(),
        );
    }
    $rows[$key]['results'][$matingType] = $row['id'];
  }

  // save the rows to database
  $stmtSave = $db->prepare('
    INSERT INTO cross_mating_type
    (genotype, background, media, temperature, a_result_id, alpha_result_id, homodip_result_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ');

  foreach ($rows as $row) {
    if (count($row['results']) < 1) {
      continue; // skip blanks
    }
    $params = array(
      $row['genotype'],
      "BY474*",
      $row['media'],
      $row['temperature'],
      $row['results']['MATa'],
      $row['results']['MATalpha'],
      $row['results']['Diploid']
      );
    $stmtSave->execute($params);
  }
}



function updateResultCacheFields($dbh)
{
  $sthSelect = $dbh->prepare('
    SELECT
      r.id as result_id,
      se.experiments as experiments,
      sl.set_lifespans as set_lifespans,
      sl.set_lifespan_start_count as set_lifespan_start_count,
      rl.ref_lifespans as ref_lifespans,
      rl.ref_lifespan_start_count as ref_lifespan_start_count
    FROM result r

    LEFT JOIN (
      SELECT result_id, GROUP_CONCAT(lifespans) AS set_lifespans, SUM(lifespan_start_count) AS set_lifespan_start_count
      FROM (
        SELECT rs.result_id as "result_id", s.lifespans as "lifespans", s.lifespan_start_count as "lifespan_start_count" 
        FROM result_set rs
        LEFT JOIN "set" s ON rs.set_id = s.id
        )
      GROUP BY result_id
    ) sl ON sl.result_id = r.id

    LEFT JOIN (
      SELECT result_id, GROUP_CONCAT(lifespans) AS ref_lifespans, SUM(lifespan_start_count) AS ref_lifespan_start_count
      FROM (
        SELECT rr.result_id, s.lifespans, s.lifespan_start_count as "lifespan_start_count" 
        FROM result_ref rr
        LEFT JOIN "set" s ON rr.set_id = s.id
        )
      GROUP BY result_id
    ) rl ON rl.result_id = r.id

    LEFT JOIN (
      SELECT result_id, GROUP_CONCAT(experiment) AS experiments
      FROM (
        SELECT DISTINCT result_id, experiment
        FROM (
          SELECT rs.result_id, s.experiment FROM result_set rs
          LEFT JOIN "set" s ON rs.set_id = s.id
          UNION
          SELECT rr.result_id, s.experiment FROM result_ref rr
          LEFT JOIN "set" s ON rr.set_id = s.id
        )
      )
      GROUP BY result_id
    ) se ON se.result_id = r.id
    ');
  $sthSelect->execute();


  $sthUpdate = $dbh->prepare('
    UPDATE result
    SET experiments = ?, set_lifespans = ?, set_lifespan_start_count = ?, ref_lifespans = ?, ref_lifespan_start_count = ?
    WHERE id = ?
    ');

  $sthInsertExp = $dbh->prepare('
    INSERT INTO result_experiment (result_id, experiment)
    VALUES (?, ?)');


  while ($row = $sthSelect->fetch(PDO::FETCH_ASSOC)) {
    $sthUpdate->execute(array(
      $row['experiments'],
      $row['set_lifespans'],
      $row['set_lifespan_start_count'],
      $row['ref_lifespans'],
      $row['ref_lifespan_start_count'],
      $row['result_id'],
      ));

    // add experiments to result_experiment
    if (isset($row['experiments'])) {
      $experiments = explode(',', $row['experiments']);
      foreach ($experiments as $experiment) {
        $sthInsertExp->execute(array(
          $row['result_id'],
          $experiment,
          ));
      }
    }
  }
}


function getCleanGenotype($genotype, $dbh, &$messages = array())
{
  global $geneDbh;
  $sth = $geneDbh->prepare('
      SELECT g.id, g.symbol FROM gene g
      LEFT JOIN gene_synonym s ON s.gene_id = g.id
      WHERE LOWER(REPLACE(g.symbol, "-", "")) = ?
        OR LOWER(REPLACE(g.locus_tag, "-", "")) = ?
      ');

  $genotype = trim($genotype);
  $values = str_replace('-', '', $genotype);
  $values = preg_split("/\s+/", $genotype);

  // using database, convert values making up genotype to a gene symbol
  foreach ($values as &$value) {
    // check case, if it is mixed just skip it
    $isUpper = false;
    if (strtoupper($value) == $value) {
      $isUpper = true;
    } else if (strtolower($value) == $value) {
      $isUpper = false;
    } else {
      continue;
    }
    $value = strtolower($value);

    // convert locus tag,
    $sth->execute(array($value, $value));
    $geneSymbols = array();
    while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
      $geneSymbol = $row['symbol'];
      $geneSymbols[$geneSymbol] = $geneSymbol;
    }
    $geneSymbols = array_keys($geneSymbols);

    if (count($geneSymbols) == 1) {
      $geneSymbol = $geneSymbols[0];
      $value = $geneSymbol;
    }
    else if (count($geneSymbols) > 1) {
      $message = "genotype segement '$value' resolves to multiple gene symbols: "
        .join(', ', $geneSymbols);
      $messages[] = $message;
      echo $message, "\n";
    }

    if ($isUpper) {
      $value = strtoupper($value);
    } else {
      $value = strtolower($value);
    }
  }
  sort($values);

  $genotype = join(' ', array_values($values));
  return $genotype;
}
