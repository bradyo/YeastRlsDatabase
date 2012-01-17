CREATE TABLE experiment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(32),
    name VARCHAR(64),
    status VARCHAR(32),
    data TEXT,
    strain_data TEXT
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;

CREATE TABLE strain (
    id INT AUTO_INCREMENT, 
    namesapce VARCHAR(32),
    name VARCHAR(128) NOT NULL, 
    owner VARCHAR(128), 
    background VARCHAR(255), 
    mating_type VARCHAR(255),
    genotype VARCHAR(255), 
    genotype_short VARCHAR(255), 
    genotype_unique VARCHAR(255), 
    freezer_code VARCHAR(255), 
    comment text, 
    is_locked VARCHAR(1), 
    created_at DATETIME NOT NULL, 
    updated_at DATETIME NOT NULL, 
    INDEX owner_idx (owner), 
    PRIMARY KEY(id)
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;




CREATE TABLE yeastrls_experiment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  facility VARCHAR(64),
  number INT NULL,
  description TEXT NULL,
  key_data TEXT NOT NULL,
  requested_by VARCHAR(64) NOT NULL,
  requested_at TIMESTAMP,
	request_message TEXT NULL,
  reviewed_at TIMESTAMP NULL,
  status VARCHAR(64) NULL,
  review_message TEXT NULL,
  completed_at TIMESTAMP
) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = INNODB;
