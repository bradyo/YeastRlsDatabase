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
  lifespans1 = lifespanArrays[['MATa']];
  lifespans2 = lifespanArrays[['MATalpha']];
  lifespans3 = lifespanArrays[['Diploid']];

  allLifespans = c(lifespans1, lifespans2, lifespans3);
  if (is.null(allLifespans)) {
    print(name);
    cat("skipping, lifespans = null\n");
    dev.off();
    return();
  }
  maxX = max(allLifespans);

  legends = c(name);
  colors = c("white"); # invisible (no line)
  colorLookup = c("red", "blue", "purple");

  iColor = 1;
  hasPlot = FALSE;
  for (matingType in c('MATalpha', 'MATa', 'Diploid')) {
    color = colorLookup[iColor];
    iColor = iColor + 1;

    lifespans = lifespanArrays[[matingType]];
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
      legend = sprintf("%s (%i)", matingType, length(lifespans));
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
    labels = append(labels, "MATa");
    lifespansArrays[["MATa"]] = lifespans;
  }

  lifespans = sapply(strsplit(result[["r2_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "MATalpha");
    lifespansArrays[["MATalpha"]] = lifespans;
  }

  lifespans = sapply(strsplit(result[["r3_lifespans"]], ","), as.numeric);
  if (! is.null(dim(lifespans))) {
    labels = append(labels, "HomoDiploid");
    lifespansArrays[["Diploid"]] = lifespans;
  }

  # make survival plot
  filename = sprintf("%s/plots/cross_mating_type/survival%i.png", outputDir, id);
  makeMultiComparisonPlot(filename, name, labels, lifespansArrays);
}


# connect to the database and process each row
outputDir = commandArgs(trailingOnly=TRUE);
dbFilename = sprintf("%s/%s", outputDir, "rls.db");
conn = dbConnect(dbDriver("SQLite"), dbname=dbFilename);

rows = dbGetQuery(conn, "
  select c.id, c.genotype,
    r1.set_lifespans as \"r1_lifespans\",
    r2.set_lifespans as \"r2_lifespans\",
    r3.set_lifespans as \"r3_lifespans\"
  from cross_mating_type c
  left join result r1 on r1.id = c.a_result_id
  left join result r2 on r2.id = c.alpha_result_id
  left join result r3 on r3.id = c.homodip_result_id
  ");
apply(rows, 1, processRow, conn=conn, outputDir=outputDir);
dbDisconnect(conn);


