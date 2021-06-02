CREATE TABLE IF NOT EXISTS `form_sji_counseling_counseling` (
	id bigint(20) NOT NULL auto_increment,
	`counseling` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_counseling_counseling_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_counseling` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_counseling` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	counseling_type varchar(255),
	counseling_time varchar(255),
	progress_notes varchar(500),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of counseling services --
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_type', 'Intial Intake', 'Initial Intake');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_type', 'Peer Counseling', 'Peer Counseling');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_type', 'STI Screening / Results', 'STI Screening / Results');

-- generate default list of counseling services --
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Test results', 'Test results');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Substance use counseling', 'Substance use counseling');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'HIV test', 'HIV test');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Goal setting', 'Goal Setting');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Referring partners', 'Referring partners');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Motivational interview', 'Motivational interview');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Risk reduction plan', 'Risk reduction plan');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Health education', 'Health education');

-- There is no durault duration list in OpenEMR.  Even though we will use these fields --
-- in other forms, we will assign them their own context --
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '15 minutes', '15 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '30 minutes', '30 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '45 minutes', '45 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '60 minutes', '60 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '90 minutes', '90 minutes');
