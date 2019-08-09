CREATE TABLE IF NOT EXISTS `form_sji_holistic` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

holistic_type varchar(255),

PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of holistic services --
INSERT INTO list_options(list_id, option_id, title) values('sji_holistic', 'Ear Acupuncture', 'Ear Acupuncture');
INSERT INTO list_options(list_id, option_id, title) values('sji_holistic', 'Full Body Acupuncture', 'Full Body Acupunture');
INSERT INTO list_options(list_id, option_id, title) values('sji_holistic', 'Massage', 'Massage');
INSERT INTO list_options(list_id, option_id, title) values('sji_holistic', 'Reiki', 'Reiki');
