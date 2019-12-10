CREATE TABLE IF NOT EXISTS `form_sji_progress_note` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	treatment_location varchar(255),
	service_type varchar(255),
	sji_progress_notes_duration varchar(255),
	documentation varchar(255),
	progress_note varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

/* contact preferences list */
INSERT INTO list_options(list_id, option_id, title) values('treatment_location', 'office', 'office');
INSERT INTO list_options(list_id, option_id, title) values('treatment_location', 'housing site', 'housing site');
INSERT INTO list_options(list_id, option_id, title) values('treatment_location', 'phone', 'phone');
INSERT INTO list_options(list_id, option_id, title) values('treatment_location', 'other community setting', 'other community setting');

INSERT INTO list_options(list_id, option_id, title) values('service_type', 'individual therapy', 'individual therapy');
INSERT INTO list_options(list_id, option_id, title) values('service_type', 'support group', 'support group');
INSERT INTO list_options(list_id, option_id, title) values('service_type', 'case management', 'case management');
INSERT INTO list_options(list_id, option_id, title) values('service_type', 'crisis intervention', 'crisis intervention');
INSERT INTO list_options(list_id, option_id, title) values('service_type', 'administrative note', 'administrative note');
INSERT INTO list_options(list_id, option_id, title) values('service_type', 'no show', 'no show');

INSERT INTO list_options(list_id, option_id, title) values('sji_progress_notes_duration', '15 minutes', '15 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_progress_notes_duration', '30 minutes', '30 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_progress_notes_duration', '45 minutes', '45 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_progress_notes_duration', '60 minutes', '60 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_progress_notes_duration', '90 minutes', '90 minutes');

