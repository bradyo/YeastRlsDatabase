<?php
require_once('lib/functions.php');
ini_set('memory_limit', '-1'); // bump up the max memory

// check command line flag for building public genes only
$isPublic = false;
if (count($argv) > 1 && $argv[1] == 'public') {
	$isPublic = true;
}

// get the output director from the command line
$outputDir = "output";
if ($isPublic) {
	$outputDir = "output-public";
}

// copy db template to output folder
copy('db/plots.db', $outputDir . '/plots.db');
chmod($outputDir . '/plots.db', 0777);

$plotsPath = $outputDir . '/plots';

// connect to output plots database
echo "connecting to output/plots.db database\n";
$dbh = new PDO('sqlite:'.$outputDir.'/plots.db');
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "\n=== ADDING PLOTS TO DB ===\n\n";
importPlots($plotsPath, 'set');
importPlots($plotsPath, 'result');
importPlots($plotsPath, 'cross_mating_type');
importPlots($plotsPath, 'cross_media');

echo "\n=== OPTIMIZING DB ===\n\n";
$dbh->exec('VACUUM');



// ==================================================================================

function importPlots($plotsPath, $directory)
{
    global $dbh;
    $insertStmt = $dbh->prepare("
        INSERT INTO \"$directory\" (filename, data) VALUES (?, ?)
    ");

    $dbh->beginTransaction();
    $dir = opendir($plotsPath . '/' . $directory);
    if ($dir) {
        while (false !== ($filename = readdir($dir))) {
            if ($filename != '.' && $filename != '..') {
                $path = $plotsPath . '/' . $directory . '/' . $filename;

                $fh = fopen($path, "rb");
                $content = fread($fh, filesize($path));
                $content = addslashes($content);
                fclose($fh);

                $params = array($filename, $content);
                $insertStmt->execute($params);
            }
        }
    }
    $dbh->commit();
}
