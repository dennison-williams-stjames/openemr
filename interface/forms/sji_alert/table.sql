CREATE TABLE IF NOT EXISTS `form_sji_alert` (
id bigint(20) NOT NULL auto_increment,
date datetime default NULL,
pid bigint(20) default NULL,
user varchar(255) default NULL,
groupname varchar(255) default NULL,
authorized tinyint(4) default NULL,
activity tinyint(4) default NULL,

alert varchar(255),

PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of holistic services --
INSERT INTO list_options(list_id, option_id, title) values('sji_alert', 'The participant has been banned', 'The participant has been banned');
INSERT INTO list_options(list_id, option_id, title) values('sji_alert', 'The participant is under contract to stay away', 'The participant is under contract to stay away');
