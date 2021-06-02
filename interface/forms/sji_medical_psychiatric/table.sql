CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_icd9_primary` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `icd_primary` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_icd9_secondary` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `icd_secondary` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_cpt_codes` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `cpt_codes` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_method_codes` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `method_codes` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_range_codes` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `range_codes` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_contraception_method` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `contraception_method` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_medical_psychiatric_provider_type` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `provider_type` varchar(255) NOT NULL,
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

	provider_type varchar(255),
	evaluate_manage_new tinyint(1),
	evaluate_manage_established tinyint(1),
	duration varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of medical\psychiatric provider types --
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'Counselor', 'Counselor');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'MD', 'MD');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'MD Pysch', 'MD Pysch');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'MFT/LCSW/PYSCH', 'MFT/LCSM/PYSCH');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'PA', 'PA');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_provider_type', 'RN', 'RN');

-- There is no durault duration list in OpenEMR.  Even though we will use these fields --
-- in other forms, we will assign them their own context --
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_duration', '15 minutes', '15 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_duration', '30 minutes', '30 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_duration', '45 minutes', '45 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_duration', '60 minutes', '60 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_duration', '90 minutes', '90 minutes');

-- Import the different contraception methods --
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Abstinence', 'Abstinence');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Diaphragm/Cervical Cap', 'Diaphragm/Cervical Cap');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Female Condom', 'Female Condom');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Female Sterilization', 'Female Sterilization');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Fertility Awareness', 'Fertility Awareness');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Hormonal Implant', 'Hormonal Implant');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Hormonal Injections 3-Month', 'Hormonal Injections 3-Month');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'IUD', 'IUD');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Male Condoms', 'Male Condoms');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Oral Contraception', 'Oral Contraception');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Pregnant/Partner Pregnant', 'Pregnant/Partner Pregnant');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Relay on Female', 'Relay on Female');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Seeking Pregnancy', 'Seeking Pregnancy');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Spermicide Alone', 'Spermicide Alone');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Sponge', 'Sponge');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Vaginal Ring', 'Vaginal Ring');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Vasectomy', 'Vasectomy');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'Other Method (Withdrawal)', 'Other Method (Withdrawal)');
INSERT INTO list_options(list_id, option_id, title) values('sji_medical_psychiatric_contraception_method', 'No Method - Other Reason - Same Sex Partner', 'No Method - Other Reason - Same Sex Partner');

