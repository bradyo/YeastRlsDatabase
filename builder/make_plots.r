# usage:
# R --vanilla --args output < analyze.R 

library(DBI);
library(RSQLite);

makeSurvivalPlot = function(filename, setName, setLifespans) 
{
  png(filename = filename, width=400, height=350, units="px", pointsize=16, bg="white" );
  par(mar=c(3,3,0.5,1))     # Trim margin around plot [b,l,t,r]
  par(tcl=0.35)             # Switch tick marks
  par(mgp=c(1.5,0.2,0))     # Set margin lines; default c(3,1,0) [title,labels,line]
  par(xaxs="r", yaxs="r")   # Extend axis limits
  par(lwd=2)

  # calculate x and y for survival curve
  maxX = max(setLifespans);
  n = length(setLifespans) + 1;
  x = c(0, sort(setLifespans));
  y = rep(1, n);
  for (i in 2:n) {
    y[i] = 1 - (i * 1/n);
  }

	# plot line
  plot(x, y, type="s", col="black", lty=1, lwd=2, xlim=c(0, maxX + 1), ylim=c(0,1),
    xlab="age (cell divisions)", ylab="fraction alive"
  );
  setLegend = sprintf("%s (%i)", setName, length(setLifespans));
  legend("topright", c(setLegend), lwd = 2, bty = 'n', col=c("black"), ncol=1);
  dev.off();
}

makeSurvivalComparisonPlot = function(filename, setName, setLifespans, refName, refLifespans,
  percentChange, pValue)
{
  png(filename=filename, width=400, height=350, units="px", pointsize=16, bg="white");
  par(mar=c(3,3,0.5,1))     # Trim margin around plot [b,l,t,r]
  par(tcl=0.35)             # Switch tick marks
  par(mgp=c(1.5,0.2,0))     # Set margin lines; default c(3,1,0) [title,labels,line]
  par(xaxs="r", yaxs="r")   # Extend axis limits
  par(lwd=2)

  # calculate x and y for survival curve
  maxX = max(c(setLifespans, refLifespans));
  n = length(refLifespans) + 1;
  x = c(0, sort(refLifespans));
  y = rep(1, n);
  for (i in 2:n) {
    y[i] = 1 - (i * 1/n);
  }
  plot(x, y, type="s", col="black", lty=1, lwd=2, xlim=c(0, maxX + 1), ylim=c(0, 1),
    xlab="age (cell divisions)", ylab="fraction alive"
  );

  # calculate x and y for survival reference curve
  n = length(setLifespans) + 1;
  x = c(0, sort(setLifespans));
  y = rep(1, n);
  for (i in 2:n) {
    y[i] = 1 - (i * 1/n);
  }
  lines(x, y, type = "s", col = "red", lty = 1, lwd = 2);

	# add legend
  setLegend = sprintf("%s (%i)", setName, length(setLifespans));
  refLegend = sprintf("%s (%i)", refName, length(refLifespans));
  legend("topright", c(setLegend, refLegend), lwd=2, bty='n', col=c('red', 'black'));

  # add percent change label
  s = sprintf("\u0394 mean: %.2f%% \np: %f", percentChange, pValue);
  mtext(s, side=1, adj=0.1, padj=-1);
  dev.off();
}


makeHistogram = function(filename, lifespans) 
{
  png(filename = filename, width=250, height=150, units="px", pointsize=16, bg="white" );
  par(mar=c(3,3,0.5,1))     # Trim margin around plot [b,l,t,r]
  par(tcl=0.35)             # Switch tick marks to insides of axes
  par(mgp=c(1.5,0.2,0))     # Set margin lines; default c(3,1,0) [title,labels,line]
  par(xaxs="r", yaxs="r")   # Extend axis limits by 4%
  par(lwd=2)

  hist(lifespans, breaks=10, lty=1, lwd=2, xlab="age (cell divisions)", ylab="count", main="");
  rug(lifespans, ticksize="0.1", lwd="1", side=1, col="red");
  par(new=T);
  boxplot(lifespans, horizontal=TRUE, axes=FALSE, col="red", xlim=c(1,6), at=3);
  par(new=F);
  dev.off();
}


processResult = function(result, conn, outputDir) 
{
  id = as.numeric(result["id"]);
  setValues = sapply(strsplit(result["set_lifespans"], ","), as.numeric);
  refValues = sapply(strsplit(result["ref_lifespans"], ","), as.numeric);
	percentChange = as.numeric(result["percent_change"]);
	ranksumP = as.numeric(result["ranksum_p"]);

  if (! is.null(dim(refValues))) {
    # make survival plot
    filename = sprintf("%s/plots/result/survival%i.png", outputDir, id);
    makeSurvivalComparisonPlot(filename, result['set_name'], setValues,
      result['ref_name'], refValues, percentChange, ranksumP
    );

    # make histograms
    filename = sprintf("%s/plots/result/histogram%i_set.png", outputDir, id);
    makeHistogram(filename, setValues);
    filename = sprintf("%s/plots/result/histogram%i_ref.png", outputDir, id);
    makeHistogram(filename, refValues);
  } 
  else {
    # make survival plot
    filename = sprintf("%s/plots/result/survival%i.png", outputDir, id);
    makeSurvivalPlot(filename, result['set_name'], setValues);

    # make histograms
    filename = sprintf("%s/plots/result/histogram%i_set.png", outputDir, id);
    makeHistogram(filename, setValues);
  }
}

processSet = function(set, conn, outputDir) {
  id = as.numeric(set[["id"]]);
  lifespans = sapply(strsplit(set["lifespans"], ","), as.numeric);

  # make histogram
  filename = sprintf("%s/plots/set/histogram%i.png", outputDir, id);
  makeHistogram(filename, lifespans);
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
apply(sets, 1, processSet, conn=conn, outputDir=outputDir);

results = dbGetQuery(conn, "
  SELECT
    r.id as id,
    r.set_name as set_name,
    r.ref_name as ref_name,
    r.set_lifespans as set_lifespans,
    r.ref_lifespans as ref_lifespans,
		r.percent_change,
		r.ranksum_p
  FROM result r
  WHERE r.set_lifespans IS NOT NULL
  "
);
apply(results, 1, processResult, conn=conn, outputDir=outputDir);

dbCommit(conn);
dbDisconnect(conn);




