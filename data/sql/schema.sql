SET storage_engine=INNODB;

CREATE TABLE experiment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    namespace VARCHAR(32),
    status VARCHAR(32),
    created_at DATETIME,
    contact_email VARCHAR(128),
    name VARCHAR(128),
    strains_data TEXT,
    experiment_data TEXT,
    INDEX (namespace),
    INDEX (status),
    INDEX (created_at),
    INDEX (name),
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE strain (
    id INT AUTO_INCREMENT PRIMARY_KEY, 
    namesapce VARCHAR(32),
    name VARCHAR(128) NOT NULL, 
    contact_email VARCHAR(128), 
    background VARCHAR(255), 
    mating_type VARCHAR(255),
    genotype VARCHAR(255), 
    genotype_short VARCHAR(255), 
    genotype_unique VARCHAR(255), 
    freezer_code VARCHAR(255), 
    comment text, 
    is_locked VARCHAR(1),
    UNIQUE (namespace, name),
    INDEX (namespace),
    INDEX (name),
    INDEX (background),
    INDEX (mating_type),
    INDEX (genotype_short),
    INDEX (genotype_unique)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;


CREATE TABLE "gene" (
    "id" INTEGER PRIMARY KEY,
    "ncbi_gene_id" INTEGER NOT NULL,
    "ncbi_tax_id" INTEGER NOT NULL,
    "symbol" VARCHAR DEFAULT NULL,
    "locus_tag" VARCHAR DEFAULT NULL,
    INDEX (ncbi_gene_id),
    INDEX (ncbi_tax_id),
    INDEX (symbol),
    INDEX (locus_tag)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE "gene_synonym" (
    "gene_id" INTEGER NOT NULL,
    "synonym" VARCHAR NOT NULL,
    INDEX (gene_Id),
    INDEX (synonym)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

CREATE TABLE "gene_dbxref" (
    "gene_id" INTEGER NOT NULL,
    "dbxref" VARCHAR(32),
    INDEX (gene_id),
    INDEX (dbxref)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;