CREATE TABLE IF NOT EXISTS `form_sji_off_hour_contact` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	`assesment_plan` varchar(255) DEFAULT NULL,
	`follow_up_date` datetime DEFAULT NULL,

	PRIMARY KEY (id)
) ENGINE=InnoDB;

