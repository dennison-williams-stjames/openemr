CREATE TABLE IF NOT EXISTS `form_sji_triage` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	chief_complaint varchar(255),
	notes varchar(255),
	concerns varchar(255),
	services varchar(255),
	pharmacy varchar(255),
	contact_preferences varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

/* contact preferences list */
INSERT INTO list_options(list_id, option_id, title) values('contact_preferences', 'email', 'email');
INSERT INTO list_options(list_id, option_id, title) values('contact_preferences', 'text', 'text');
INSERT INTO list_options(list_id, option_id, title) values('contact_preferences', 'phone call', 'phone call');

