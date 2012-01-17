# usage:
# R --vanilla --args output < analyze.R 

library(DBI);
library(RSQLite);
library(exactRankTests);

processResult = function(result, conn) {
  id = as.numeric(result["id"]);
  setValues = sapply(strsplit(result["set_lifespans"], ","), as.numeric);
  refValues = sapply(strsplit(result["ref_lifespans"], ","), as.numeric);

  # perform calculations on lifespan to update database with
  setMean = mean(setValues);
  setStdev = sd(setValues);
	if (is.na(setStdev)) {
		setStdev = 0;
	}

  if (! is.null(dim(refValues))) {
    refMean = mean(refValues);
    refStdev = sd(refValues);
		if (is.na(refStdev)) {
			refStdev = 0;
		}

    percentChange = (setMean - refMean) / refMean * 100;

    stats = wilcox.exact(setValues, refValues);
    ranksumU = stats$statistic;
    ranksumP = stats$p.value;

    # update database
    q = sprintf("
      UPDATE result SET
        set_lifespan_count = '%d',
        set_lifespan_mean = '%e',
        set_lifespan_stdev = '%e',
        ref_lifespan_count = '%d',
        ref_lifespan_mean = '%e',
        ref_lifespan_stdev = '%e',
        percent_change = '%e',
        ranksum_u = '%e',
        ranksum_p = '%.12e'
      WHERE id = '%d'
      ", length(setValues), setMean, setStdev, length(refValues), refMean, refStdev,
      percentChange, ranksumU, ranksumP, id );
    dbSendQuery(conn, q)
  }
  else {
    # update database
    q = sprintf("
      UPDATE result SET
        set_lifespan_count = %d,
        set_lifespan_mean = %e,
        set_lifespan_stdev = %e
      WHERE id = %d
      ", length(setValues), setMean, setStdev, id );
    dbSendQuery(conn, q);
  }
}

processSet = function(set, conn) {
  id = as.numeric(set[["id"]]);
  lifespans = sapply(strsplit(set["lifespans"], ","), as.numeric);

  # perform calculations on lifespan to update database with
  lifespanCount = length(lifespans);
  lifespanMean = mean(lifespans);
  lifespanStdev = sd(lifespans);
	if (is.na(lifespanStdev)) {
		lifespanStdev = 0;
	}

  # update database
  q = sprintf("
    UPDATE \"set\" SET
      lifespan_count = %s,
      lifespan_mean = %s,
      lifespan_stdev = %s
    WHERE id = %d
    ", lifespanCount, lifespanMean, lifespanStdev, id );
  dbSendQuery(conn, q);
}


# connect to the database and process each row
outputDir = commandArgs(trailingOnly=TRUE);
dbFilename = sprintf("%s/%s", outputDir, "rls.db");
conn = dbConnect(dbDriver("SQLite"), dbname=dbFilename);

dbBeginTransaction(conn);
sets = dbGetQuery(conn, "
	SELECT s.id as id, s.lifespans as lifespans 
	FROM \"set\" s 
	WHERE s.lifespans IS NOT NULL
	"
);
apply(sets, 1, processSet, conn=conn);

results = dbGetQuery(conn, "
  SELECT
    r.id as id,
    r.set_name as set_name,
    r.ref_name as ref_name,
    r.set_lifespans as set_lifespans,
    r.ref_lifespans as ref_lifespans
  FROM result r
  WHERE r.set_lifespans IS NOT NULL
  "
);
apply(results, 1, processResult, conn=conn);

dbCommit(conn);
dbDisconnect(conn);




