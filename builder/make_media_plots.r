library(DBI);
library(RSQLite);
library(exactRankTests);


makeMultiComparisonPlot = function(filename, name, labels, lifespanArrays)
{
  png(filename = filename, width = 400, height = 350, units = "px",
    pointsize = 16, bg = "white" );
  par(mar = c(3,3,0.5,1))       # Trim margin around plot [b,l,t,r]
  par(tcl = 0.35)               # Switch tick marks
  par(mgp = c(1.5,0.2,0))       # Set margin lines; default c(3,1,0) [title,labels,line]
  par(xaxs = "r", yaxs = "r")   # Extend axis limits
  par(lwd=2)

  # prepare a multi line comparison plot
  lifespans1 = lifespanArrays[['YPD 2%']];
  lifespans2 = lifespanArrays[['0.5% D']];
  lifespans3 = lifespanArrays[['0.05% D']];
  lifespans4 = lifespanArrays[['3% Gly']];
  allLifespans = c(lifespans1, lifespans2, lifespans3, lifespans4);

  if (is.null(allLifespans)) {
    print(name);
    cat("no lifespans, skipping\n");
    dev.off();
    return();
  }
  maxX = max(allLifespans);

  legends = c(name);
  colors = c("white"); # invisible, no line for name
  colorLookup = c("black", "orange", "red", "green");

  iColor = 1;
  hasPlot = FALSE;
  for (media in c('YPD 2%', '0.5% D', '0.05% D', '3% Gly')) {
    color = colorLookup[iColor];
    iColor = iColor + 1;

    lifespans = lifespanArrays[[media]];
    if (!is.null(dim(lifespans))) {
      x = c(0, sort(lifespans));
      n = length(x);
      y = rep(1, n);
      for (i in 2:n) {
        y[i] = 1 - (i * 1/n);
      }

      if (hasPlot == FALSE) {
        plot(x, y, type="s", col=color, lty=1, lwd=2,
          xlab="age (cell divisions)", ylab="fraction alive",
          xlim = c(0, maxX + 1), ylim = c(0,1) );
        hasPlot = TRUE;
      } else {
        lines(x, y, type="s", col=color, lty=1, lwd=2);
      }

      colors = append(colors, color);
      legend = sprintf("%s (%i)", media, length(lifespans));
      legends = append(legends, legend);
    }
  }

  legend("topright", legends, lty=1, lwd=2, bty='n', col=colors);

  dev.off();
}


processRow = function(result, conn, outputDir) {
  id = as.numeric(result["id"]);
  name = result[["genotype"]];

  labels = c();
  lifespansArrays = c();

  lifespans = sapply(strsplit(result[["r1_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "YPD 2%");
    lifespansArrays[["YPD 2%"]] = lifespans;
  }

  lifespans = sapply(strsplit(result[["r2_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "0.5% D");
    lifespansArrays[["0.5% D"]] = lifespans;
  }

  lifespans = sapply(strsplit(result[["r3_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "0.05% D");
    lifespansArrays[["0.05% D"]] = lifespans;
  }

  lifespans = sapply(strsplit(result[["r4_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "3% Gly");
    lifespansArrays[["3% Gly"]] = lifespans;
  }

  # make survival plot
  filename = sprintf("%s/plots/cross_media/survival%i.png", outputDir, id);
  makeMultiComparisonPlot(filename, name, labels, lifespansArrays);
}


# connect to the database and process each row
outputDir = commandArgs(trailingOnly=TRUE);
dbFilename = sprintf("%s/%s", outputDir, "rls.db");
conn = dbConnect(dbDriver("SQLite"), dbname=dbFilename);

dbBeginTransaction(conn);
rows = dbGetQuery(conn, "
  select c.id, c.genotype,
    r1.set_lifespans as \"r1_lifespans\",
    r2.set_lifespans as \"r2_lifespans\",
    r3.set_lifespans as \"r3_lifespans\",
    r4.set_lifespans as \"r4_lifespans\"
  from cross_media c
  left join result r1 on r1.id = c.ypd_result_id
  left join result r2 on r2.id = c.d05_result_id
  left join result r3 on r3.id = c.d005_result_id
  left join result r4 on r4.id = c.gly3_result_id
  ");
apply(rows, 1, processRow, conn=conn, outputDir=outputDir);
dbCommit(conn);
dbDisconnect(conn);

