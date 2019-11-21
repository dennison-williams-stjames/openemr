-- TODO: figure out if we should be collecting multiple icd10 & CPT values for
-- these fields or if there should only be one per encounter

CREATE TABLE IF NOT EXISTS `form_sji_medical_icd10_primary` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `icd_primary` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_icd10_secondary` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `icd_secondary` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_cpt_codes` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `cpt_codes` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	duration varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

-- There is no durault duration list in OpenEMR.  Even though we will use these fields --
-- in other forms, we will assign them their own context --
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_duration', '15 minutes', '15 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_duration', '30 minutes', '30 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_duration', '45 minutes', '45 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_duration', '60 minutes', '60 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_duration', '90 minutes', '90 minutes');
