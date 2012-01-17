#!/bin/sh

php build_db.php 
OUTPUT_DIR="/home/brady/projects/rlsdb_builder/data"
R --vanilla --args "$OUTPUT_DIR" < analyze.r
R --vanilla --args "$OUTPUT_DIR" < make_plots.r
R --vanilla --args "$OUTPUT_DIR" < make_mating_plots.r
R --vanilla --args "$OUTPUT_DIR" < make_media_plots.r
php build_plots_db.php
php make_csv.php


echo "\n=== COPYING TO DATA FOLDER ===\n\n"
DEST_DIR="/home/brady/projects/rlsdb_builder/data"

mkdir -p "$DEST_DATA_DIR/previous"
mv "$DEST_DIR/plots.db" "$DEST_DIR/previous"
mv "$DEST_DIR/rls.csv" "$DEST_DIR/previous"
mv "$DEST_DIR/rls.db" "$DEST_DIR/previous"

cp output/updating "$DEST_DIR"
cp output/plots.db "$DEST_DIR"
cp output/rls.csv "$DEST_DIR"
cp output/rls.db "$DEST_DIR"
rm "$DEST_DIR/updating"


