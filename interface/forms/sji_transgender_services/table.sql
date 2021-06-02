CREATE TABLE IF NOT EXISTS `form_sji_transgender_services` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

sji_transgender_services varchar(255),
referral varchar(255),
progress_notes varchar(255),

PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of holistic services --
INSERT INTO list_options(list_id, option_id, title) values('sji_transgender_services', '1st Medical H&P', 'First Medical H&P');
INSERT INTO list_options(list_id, option_id, title) values('sji_transgender_services', 'F/O Medical', 'F/O Medical');
INSERT INTO list_options(list_id, option_id, title) values('sji_transgender_services', 'Injection', 'Injection');
INSERT INTO list_options(list_id, option_id, title) values('sji_transgender_services', 'Routine Lab Draw', 'Routine Lab Draw');
INSERT INTO list_options(list_id, option_id, title) values('sji_transgender_services', 'Trans Intake/Education', 'Trans Intake/Education');
