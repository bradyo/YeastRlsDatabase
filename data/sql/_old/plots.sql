
CREATE TABLE "set" (
  "id" INTEGER PRIMARY KEY,
	"filename" VARCHAR NOT NULL,
  "data" BLOB NOT NULL
);
CREATE INDEX set_filename ON "set" (filename);


CREATE TABLE "result" (
  "id" INTEGER PRIMARY KEY,
	"filename" VARCHAR NOT NULL,
  "data" BLOB NOT NULL
);
CREATE INDEX result_filename ON "result" (filename);


CREATE TABLE "cross_mating_type" (
  "id" INTEGER PRIMARY KEY,
	"filename" VARCHAR NOT NULL,
  "data" BLOB NOT NULL
);
CREATE INDEX cross_mating_type_filename ON "cross_mating_type" (filename);


CREATE TABLE "cross_media" (
  "id" INTEGER PRIMARY KEY,
	"filename" VARCHAR NOT NULL,
  "data" BLOB NOT NULL
);
CREATE INDEX cross_media_filename ON "cross_media" (filename);
