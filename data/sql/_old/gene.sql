
CREATE TABLE "gene" (
  "id" INTEGER PRIMARY KEY,
  "ncbi_gene_id" INTEGER NOT NULL,
  "ncbi_tax_id" INTEGER NOT NULL,
  "symbol" VARCHAR DEFAULT NULL,
  "locus_tag" VARCHAR DEFAULT NULL
);
CREATE INDEX gene_symbol ON gene (symbol);
CREATE INDEX gene_locus_tag ON gene (locus_tag);



CREATE TABLE "gene_synonym" (
  "gene_id" INTEGER NOT NULL,
  "synonym" VARCHAR NOT NULL
);
CREATE INDEX gene_synonym_gene_id ON gene_synonym (gene_id);
CREATE INDEX gene_synonym_synonym ON gene_synonym (synonym);



CREATE TABLE "gene_dbxref" (
  "gene_id" INTEGER NOT NULL,
  "dbxref" VARCHAR
);
CREATE INDEX gene_dbxref_gene_id ON gene_dbxref (gene_id);
CREATE INDEX gene_dbxref_dbxref ON gene_dbxref (dbxref);



ATTACH DATABASE "ncbi_gene_yeast.db" AS ncbi;
INSERT INTO gene SELECT * FROM ncbi.gene;
INSERT INTO gene_synonym SELECT gene_id, synonym FROM ncbi.gene_synonym;
INSERT INTO gene_dbxref SELECT gene_id, dbxref FROM ncbi.gene_dbxref;

VACUUM;
