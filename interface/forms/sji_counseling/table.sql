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
progress_notes varchar(255),

PRIMARY KEY (id)
) ENGINE=InnoDB;

-- generate default list of counseling services --
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Counseling/Prevent Gp', 'Counseling/Prevent Gp');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'General Prevention', 'General Prevention');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'HAP Sign-Up', 'HAP Sign-Up');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Initial Family Plan Meth', 'Initial Family Plan Meth');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Language Interpretation', 'Language Interpretation');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Peer Counseling', 'Peer Counseling');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Risk Reduction Coun', 'Risk Reduction Coun');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Smoking Cessation', 'Smoking Cessation');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Other Counseling', 'Other Counseling');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'New Participant Intake', 'New Participant Intake');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Group Counseling', 'Group Counseling');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Case Management', 'Case Management');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling', 'Linkage to care', 'Linkage to care');

-- There is no durault duration list in OpenEMR.  Even though we will use these fields --
-- in other forms, we will assign them their own context --
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '15 minutes', '15 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '30 minutes', '30 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '45 minutes', '45 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '60 minutes', '60 minutes');
INSERT INTO list_options(list_id, option_id, title) values('sji_counseling_time', '90 minutes', '90 minutes');
