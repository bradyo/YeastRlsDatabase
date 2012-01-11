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
