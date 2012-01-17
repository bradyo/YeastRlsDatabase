
CREATE TABLE "set" (
  "id" INTEGER PRIMARY KEY,
  "name" VARCHAR NULL,
  "media" VARCHAR NULL,
  "temperature" REAL NULL,
  "experiment" VARCHAR NULL,
  "strain" VARCHAR NULL,
  "lifespans" VARCHAR NULL,
  "lifespan_start_count" INTEGER NULL,
  "lifespan_count" INTEGER NULL,
  "lifespan_mean" REAL NULL,
  "lifespan_stdev" REAL NULL 
);
CREATE INDEX set_name ON "set" (name);
CREATE INDEX set_media ON "set" (media);
CREATE INDEX set_temperature ON "set" (temperature);
CREATE INDEX set_experiment ON "set" (experiment);
CREATE INDEX set_strain ON "set" (strain);


CREATE TABLE "result" (
  "id" INTEGER PRIMARY KEY,
  "experiments" VARCHAR NULL,
  "set_name" VARCHAR NULL,
  "set_strain" VARCHAR NULL,
  "set_background" VARCHAR NULL,
  "set_mating_type" VARCHAR NULL,
	"set_locus_tag" VARCHAR NULL,
  "set_genotype" VARCHAR NULL,
  "set_media" VARCHAR NULL,
  "set_temperature" REAL NULL,
  "set_lifespan_start_count" INTEGER NULL,
  "set_lifespan_count" INTEGER NULL,
  "set_lifespan_mean" REAL NULL,
  "set_lifespan_stdev" REAL NULL,
  "set_lifespans" VARCHAR NULL,
  "ref_name" VARCHAR NULL,
  "ref_strain" VARCHAR NULL,
  "ref_background" VARCHAR NULL,
  "ref_mating_type" VARCHAR NULL,
	"ref_locus_tag" VARCHAR NULL,
  "ref_genotype" VARCHAR NULL,
  "ref_media" VARCHAR NULL,
  "ref_temperature" REAL NULL,
  "ref_lifespan_start_count" INTEGER NULL,
  "ref_lifespan_count" INTEGER NULL,
  "ref_lifespan_mean" REAL NULL,
  "ref_lifespan_stdev" REAL NULL,
  "ref_lifespans" VARCHAR NULL,
  "percent_change" REAL NULL,
  "ranksum_u" REAL NULL,
  "ranksum_p" REAL NULL,
  "pooled_by" VARCHAR NULL
);
CREATE INDEX result_set_name ON "result" (set_name);
CREATE INDEX result_set_strain ON "result" (set_strain);
CREATE INDEX result_set_background ON "result" (set_background);
CREATE INDEX result_set_mating_type ON "result" (set_mating_type);
CREATE INDEX result_set_locus_tag ON "result" (set_locus_tag);
CREATE INDEX result_set_genotype ON "result" (set_genotype);
CREATE INDEX result_set_media ON "result" (set_media);
CREATE INDEX result_set_temperature ON "result" (set_temperature);
CREATE INDEX result_set_lifespan_mean ON "result" (set_lifespan_mean);
CREATE INDEX result_ref_name ON "result" (ref_name);
CREATE INDEX result_ref_strain ON "result" (ref_strain);
CREATE INDEX result_ref_background ON "result" (ref_background);
CREATE INDEX result_ref_mating_type ON "result" (ref_mating_type);
CREATE INDEX result_ref_locus_tag ON "result" (ref_locus_tag);
CREATE INDEX result_ref_genotype ON "result" (ref_genotype);
CREATE INDEX result_ref_media ON "result" (ref_media);
CREATE INDEX result_ref_temperature ON "result" (ref_temperature);
CREATE INDEX result_percent_change ON "result" (percent_change);
CREATE INDEX result_ranksum_p ON "result" (ranksum_p);
CREATE INDEX result_pooled_by ON "result" (pooled_by);


CREATE TABLE "result_experiment" (
  "result_id" INTEGER NOT NULL,
  "experiment" VARCHAR NOT NULL
);
CREATE INDEX result_experiment_result_id ON "result_experiment" (result_id);
CREATE INDEX result_experiment_experiment ON "result_experiment" (experiment);


CREATE TABLE "genotype_pubmed_id" (
  "genotype" VARCHAR NOT NULL,
  "pubmed_id" INTEGER NOT NULL
);
CREATE INDEX genotype_pubmed_id_genotype ON "genotype_pubmed_id" (genotype);
CREATE INDEX genotype_pubmed_id_pubmed_id ON "genotype_pubmed_id" (pubmed_id);




CREATE TABLE "result_set" (
  "result_id" INTEGER NOT NULL,
  "set_id" INTEGER NOT NULL
);
CREATE INDEX result_set_result_id ON "result_set" (result_id);
CREATE INDEX result_set_set_id ON "result_set" (set_id);


CREATE TABLE "result_ref" (
  "result_id" INTEGER NOT NULL,
  "set_id" INTEGER NOT NULL
);
CREATE INDEX result_ref_result_id ON "result_ref" (result_id);
CREATE INDEX result_ref_set_id ON "result_ref" (set_id);


CREATE TABLE "cross_media" (
	"id" INTEGER PRIMARY KEY,
	"locus_tag" VARCHAR NULL,
	"genotype" VARCHAR NOT NULL,
	"background" VARCHAR DEFAULT NULL,
	"mating_type" VARCHAR DEFAULT NULL,
  "temperature" REAL NULL,
	"ypd_result_id" INTEGER,
	"d05_result_id" INTEGER,
	"d005_result_id" INTEGER,
	"gly3_result_id" INTEGER
);
CREATE INDEX cross_media_locus_tag ON "cross_media" (locus_tag);
CREATE INDEX cross_media_genotype ON "cross_media" (genotype);
CREATE INDEX cross_media_background ON "cross_media" (background);
CREATE INDEX cross_media_mating_type ON "cross_media" (mating_type);
CREATE INDEX cross_media_temperature ON "cross_media" (temperature);


CREATE TABLE "cross_mating_type" (
	"id" INTEGER PRIMARY KEY,
	"locus_tag" VARCHAR NULL,
	"genotype" VARCHAR NOT NULL,
	"background" VARCHAR DEFAULT NULL,
	"media" VARCHAR DEFAULT NULL,
	"temperature" REAL NULL,
	"a_result_id" INTEGER NULL,
	"alpha_result_id" INTEGER NULL,
	"homodip_result_id" INTEGER NULL
);
CREATE INDEX cross_mating_type_locus_tag ON "cross_mating_type" (locus_tag);
CREATE INDEX cross_mating_type_genotype ON "cross_mating_type" (genotype);
CREATE INDEX cross_mating_type_background ON "cross_mating_type" (background);
CREATE INDEX cross_mating_type_media ON "cross_mating_type" (media);
CREATE INDEX cross_mating_type_temperature ON "cross_mating_type" (temperature);



CREATE TABLE "yeast_strain" (
  "id" INTEGER PRIMARY KEY,
  "name" VARCHAR UNIQUE NOT NULL,
  "owner" VARCHAR,
  "background" VARCHAR,
  "mating_type" VARCHAR,
  "genotype" VARCHAR,
	"genotype_short" VARCHAR,
	"genotype_unique" VARCHAR,
  "freezer_code" VARCHAR,
  "comment" VARCHAR,
  "created_at" TIMESTAMP,
  "updated_at" TIMESTAMP,
	"is_locked" INTEGER
);
CREATE INDEX yeast_strain_name  ON yeast_strain (name);
CREATE INDEX yeast_strain_owner ON yeast_strain (owner);
CREATE INDEX yeast_strain_background ON yeast_strain (background);
CREATE INDEX yeast_strain_genotype_short ON yeast_strain (genotype_short);
CREATE INDEX yeast_strain_genotype_unique ON yeast_strain (genotype_unique);
CREATE INDEX yeast_strain_mating_type ON yeast_strain (mating_type);


CREATE TABLE "meta" (
  "name" VARCHAR NOT NULL,
	"value" VARCHAR
);
CREATE INDEX meta_name ON meta (name);


CREATE TABLE "build_log" (
	"id" INTEGER PRIMARY KEY,
	"filename" VARCHAR NOT NULL,
	"message" VARCHAR
);
CREATE INDEX build_log_filename ON build_log (filename);

