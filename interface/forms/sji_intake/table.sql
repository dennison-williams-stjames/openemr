CREATE TABLE IF NOT EXISTS `form_sji_intake_work_with` (
	id bigint(20) NOT NULL auto_increment,
	`work_with` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_work_doing` (
	id bigint(20) NOT NULL auto_increment,
	`work_doing` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_work_done` (
	id bigint(20) NOT NULL auto_increment,
	`work_done` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_supportive_people` (
	id bigint(20) NOT NULL auto_increment,
	`supportive_people` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_received_healthcare_from` (
	id bigint(20) NOT NULL auto_increment,
	`received_healthcare_from` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_identified_as_sex_worker_reaction` (
	id bigint(20) NOT NULL auto_increment,
	`identified_as_sex_worker_reaction` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_not_identified_sex_worker_reason` (
	id bigint(20) NOT NULL auto_increment,
	`not_identified_sex_worker_reason` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_tested_for` (
	id bigint(20) NOT NULL auto_increment,
	`tested_for` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_diagnosed_with` (
	id bigint(20) NOT NULL auto_increment,
	`diagnosed_with` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_hormones_types` (
	id bigint(20) NOT NULL auto_increment,
	`hormones_types` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_experienced_violence_from` (
	id bigint(20) NOT NULL auto_increment,
	`experienced_violence_from` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_std_past` (
	id bigint(20) NOT NULL auto_increment,
	`std_past` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake_mental_health_condition` (
	id bigint(20) NOT NULL auto_increment,
	`mental_health_condition` varchar(200) NOT NULL,
	pid bigint(20) default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_work_done_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `form_sji_intake` (`id`) ON DELETE CASCADE ON UPDATE CASCADE

) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_intake` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	declined_intake tinyint(1) default NULL,
	`interviewer_name` varchar(200) DEFAULT NULL,
        `referrer` varchar(50) DEFAULT NULL,
        `relationship_status` varchar(255) DEFAULT NULL,

	/* ethnicity is included as part of participant form */

	/* TODO: is there already a list for this? */
	`country_of_origin` varchar(50) DEFAULT NULL,

	/* language is included in the participant form */

        /* housing is handled by the housing_situation field in the 
           sji_intake_core_variables module */

	`sex_industry_connection` varchar(255) default null,

	/* sex industry work done is tracked with the join table above, form_sji_intake_work_done */
	/* sex industry work doing is tracked with the join table above, form_sji_intake_work_doing */
	/* sex industry work with is tracked with the join table above, form_sji_intake_work_with */

        `identified_as_sex_worker` varchar(20) DEFAULT NULL,

	`last_physical` varchar(255) default null,
        `hepatitis_follow_up` varchar(2) DEFAULT NULL,

        /* vaccinations will get added to the patient vaccination list */
       
        /* TODO: this should process a hepatius vaccine procedure for the participant if checked */ 
	`want_hepatitis_vaccination` varchar(20) DEFAULT NULL,
        `hiv_tested` varchar(20) DEFAULT NULL,
        `last_hiv_test_date` date not NULL,
        `last_hiv_test_result` varchar(255) not NULL,
        `hiv_positive_receiving_care` varchar(255) not NULL,

	/* TODO: this should add a hiv test procedure for the participant */
        `want_hiv_test` varchar(20) DEFAULT NULL,

        `diagnosed_std_positive` varchar(20) DEFAULT NULL,

	/* TODO: this should add a std panel test procedure for the participant */
        `want_std_test` varchar(20) DEFAULT NULL,

        `tb_tested` varchar(20) DEFAULT NULL,
        `last_tb_test_date` date not NULL,
        `last_tb_test_result` varchar(255) not NULL,
        `tb_follow_up` varchar(20) DEFAULT NULL,

	/* TODO: this should add a std panel test procedure for the participant */
        `want_tb_test` varchar(20) DEFAULT NULL,

        `last_pap_smear` varchar(255) not NULL,

	/* TODO: this should add a pap smear test procedure for the participant */
        `want_pap_smear` varchar(20) DEFAULT NULL,
        `abnormal_pap_smear` varchar(20) DEFAULT NULL,
        `abnormal_pap_smear_date` date DEFAULT NULL,
        `abnormal_pap_smear_follow_up` varchar(20) DEFAULT NULL,

	/* TODO: we can use the sji_last_physical list for this */
        `last_mammogram` varchar(255) not NULL,

        `last_testicular_exam` varchar(255) not NULL,

	/* TODO: this should add a testicular exam for the participant */
        `want_testicular_exam` varchar(20) not NULL,

        `taking_hormones_now` varchar(20) not NULL,
        `hormones_type` varchar(255) not NULL,
        `hormones_source` varchar(255) not NULL,
        `taken_hormones` varchar(20) not NULL,

        /* TODO: this should trigger hormone procedure */
        `want_hormones` varchar(20) not NULL,

        `prostitution_case_pending` varchar(20) not NULL,
        `want_legal` varchar(20) not NULL,

        `experienced_violence` varchar(20) not NULL,

	/* TODO: this should probably trigger something, but the bad date list is not integrated into the OpenEMR workflow */
        `want_to_report_bad_date` varchar(20) default  NULL,
        `experienced_violence_from_partner` varchar(20) default NULL,
        `currently_experiencing_violence` varchar(20) default NULL,
        `want_dv_referral` varchar(20) default NULL,
		
        `diagnosed_mental_health_condition` varchar(20) default NULL,
        `mental_health_condition_meds` varchar(20) default NULL,

        `comments` varchar(255) default NULL,

	`date_of_signature` datetime default NULL,
	PRIMARY KEY (id),
        KEY `pid` (`pid`),
        CONSTRAINT `form_sji_intake_ibfk_1` FOREIGN KEY (`pid`) REFERENCES `patient_data` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* referrer list */
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Friend/Relative/Co-worker', 'Friend/Relative/Co-worker');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Outreach worker', 'Outreach worker');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Non-Outreach SJI staff', 'Non-Outreach SJI staff');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Pre-trial diversion', 'Pre-trial diversion');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Media', 'Media');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'SJI Flyer', 'SJI Flyer');
INSERT INTO list_options(list_id, option_id, title) values('referrer', 'Research', 'Research');

/* sex worker connection list */
INSERT INTO list_options(list_id, option_id, title) values('sex_industry_connection', 'Partner of sex worker', 'Partner of sex worker');
INSERT INTO list_options(list_id, option_id, title) values('sex_industry_connection', 'Family member of a sex worker', 'Family member of a sex worker');
INSERT INTO list_options(list_id, option_id, title) values('sex_industry_connection', 'Refused', 'Refused');

/* supportive people list */
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Boss/Manager in sex work', 'Boss/Manager in sex work');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Brothers/Sisters', 'Brothers/Sisters');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Customers', 'Customers');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Friends (other sex workers)', 'Friends (other sex workers)');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Friends (not other sex workers)', 'Friends (not other sex workers)');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Intimate partner/s', 'Intimate partner/s');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Medical provider', 'Medical provider');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Parents', 'Parents');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Peer counselor', 'Peer counselor');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Therapist/Mental health professional', 'Therapist/Mental health professional');
INSERT INTO list_options(list_id, option_id, title) values('supportive_people', 'Other family', 'Other family');

/* work done list */
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Brothel', 'Brothel');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Independent massage', 'Independent massage');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Internet (webcam)', 'Internet (webcam)');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Escort Agency', 'Escort Agency');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Independent In-call/Out-call', 'Independent In-call/Out-call');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Madame', 'Madame');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Massage parlor', 'Massage parlor');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Modeling for clients', 'Modeling for clients');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Phone sex', 'Phone sex');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Porn', 'Porn');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Pro Dom', 'Pro Dom');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Sex trades/survival sex (eg. for food, drugs, shelter)', 'Sex trades/survival sex (eg. for food, drugs, shelter)');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Street', 'Street');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Stripping/dancing/peep shows', 'Stripping/dancing/peep shows');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('work_done', 'Refused', 'Refused');

/* work doing list */
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Brothel', 'Brothel');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Independent massage', 'Independent massage');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Internet (webcam)', 'Internet (webcam)');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Escort Agency', 'Escort Agency');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Independent In-call/Out-call', 'Independent In-call/Out-call');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Madame', 'Madame');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Massage parlor', 'Massage parlor');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Modeling for clients', 'Modeling for clients');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Phone sex', 'Phone sex');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Porn', 'Porn');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Pro Dom', 'Pro Dom');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Sex trades/survival sex (eg. for food, drugs, shelter)', 'Sex trades/survival sex (eg. for food, drugs, shelter)');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Street', 'Street');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Stripping/dancing/peep shows', 'Stripping/dancing/peep shows');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('work_doing', 'Refused', 'Refused');

/* work with list */
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Cisgender male', 'Cisgender male');
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Cisgender female', 'Cisgender female');
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Transgender male', 'Transgender male');
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Transgender female', 'Transgender female');
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Do not have sex with customers', 'Do not have sex with customers');
INSERT INTO list_options(list_id, option_id, title) values('work_with', 'Refused', 'Refused');

/* identified sex as worker list */
INSERT INTO list_options(list_id, option_id, title) values('identified_as_sex_worker', 'Always', 'Always');
INSERT INTO list_options(list_id, option_id, title) values('identified_as_sex_worker', 'Sometimes', 'Sometimes');
INSERT INTO list_options(list_id, option_id, title) values('identified_as_sex_worker', 'Never', 'Never');
INSERT INTO list_options(list_id, option_id, title) values('identified_as_sex_worker', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('identified_as_sex_worker', 'Refused', 'Refused');

/* identified sex worker reaction list */
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Positive response', 'Positive response');
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Neutral', 'Neutral');
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Negative response', 'Negative response');
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Mixed reactions', 'Mixed reactions');
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Refused to see me', 'Refused to see me');
INSERT INTO list_options(list_id, option_id, title) values('identified_sex_worker_reaction', 'Refused', 'Refused');

/* not identified as a sex worker reason list */
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Afraid of disapproval', 'Afraid of disapproval');
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Did not think it was relevant', 'Did not think it was relevant');
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Embarrrassed', 'Embarrassed');
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Negative past experience', 'Negative past experience');
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('not_identified_sex_worker_reason', 'Refused', 'Refused');

/* last physical list */
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'Less than one year ago', 'Less than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'One year ago', 'One year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'More than one year ago', 'More than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'Never', 'Never');
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('last_physical', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'Less than one year ago', 'Less than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'One year ago', 'One year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'More than one year ago', 'More than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'Never', 'Never');
INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('last_mammogram', 'Refused', 'Refused');

/* tested for list */
INSERT INTO list_options(list_id, option_id, title) values('tested_for', 'Hepatitis A', 'Hepatitis A');
INSERT INTO list_options(list_id, option_id, title) values('tested_for', 'Hepatitis B', 'Hepatitis B');
INSERT INTO list_options(list_id, option_id, title) values('tested_for', 'Hepatitis C', 'Hepatitis C');
INSERT INTO list_options(list_id, option_id, title) values('tested_for', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('tested_for', 'Refused', 'Refused');

/* diagnosed with list */
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Hepatitis A', 'Hepatitis A');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Hepatitis B', 'Hepatitis B');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Hepatitis C', 'Hepatitis C');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Do not know which type of hepatitis', 'Do not know which type of hepatitis');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Never tested', 'Never tested');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_with', 'Refused', 'Refused');

/* STD past list */
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Chlamydia', 'Chlamydia');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Genital herpes', 'Genital herpes');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Genital warts/HPV', 'Genital warts/HPV');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Gonorrhea (genital/oral/anal)', 'Gonorrhea (genital/oral/anal)');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Non-Gonococcal Urethritis (NGU)', 'Non-Gonococcal Urethritis (NGU)');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Pelvic Inflammatory Disease (PID)', 'Pelvic Inflammatory Disease (PID)');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Syphilis', 'Syphilis');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Trichomonas', 'Trichomonas');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('std_past', 'Refused', 'Refused');

/* last pap list */
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'Less than one year ago', 'Less than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'One year ago', 'One year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'More than one year ago', 'More than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'Have had a hysterectomy for non-cancer reason', 'Have had a hysterectomy for non-cancer reason');
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'Never', 'Never');
INSERT INTO list_options(list_id, option_id, title) values('last_pap_smear', 'Do not know', 'Do not know');

/* last testical exam list */
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'Less than one year ago', 'Less than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'One year ago', 'One year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'More than one year ago', 'More than one year ago');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'Have had my testicles removed', 'Have had my testicles removed');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'Never', 'Never');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('last_testicular_exam', 'Refused', 'Refused');

/* taking hormones now list */
INSERT INTO list_options(list_id, option_id, title) values('taking_hormones_now', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('taking_hormones_now', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('taking_hormones_now', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('mental_health_condition_meds', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('mental_health_condition_meds', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('mental_health_condition_meds', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_to_report_bad_date', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_to_report_bad_date', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_to_report_bad_date', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('currently_experiencing_violence', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('currently_experiencing_violence', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('currently_experiencing_violence', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('experienced_violence_from_partner', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('experienced_violence_from_partner', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('experienced_violence_from_partner', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('taken_hormones', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('taken_hormones', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('taken_hormones', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('experienced_violence', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('experienced_violence', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('experienced_violence', 'Refused', 'Refused');

/* taking hormones now list */
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_type', 'Cream', 'Cream');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_type', 'Injectable', 'Injectable');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_type', 'Patch', 'Patch');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_type', 'Pills', 'Pills');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_type', 'Refused', 'Refused');

/* hormones source list */
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Dimensions', 'Dimensions');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Friend', 'Friend');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Internet', 'Internet');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Lyon-Martin', 'Lyon-Martin');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Street', 'Street');
INSERT INTO list_options(list_id, option_id, title) values('sji_hormones_source', 'Tom Wadell', 'Tom Wadell');

/* experienced violence from list */
INSERT INTO list_options(list_id, option_id, title) values('sji_experienced_violence_from', 'Co-workers', 'Co-workers');
INSERT INTO list_options(list_id, option_id, title) values('sji_experienced_violence_from', 'Customers', 'Customers');
INSERT INTO list_options(list_id, option_id, title) values('sji_experienced_violence_from', 'Employer/manager/pimp', 'Employer/manager/pimp');
INSERT INTO list_options(list_id, option_id, title) values('sji_experienced_violence_from', 'Police', 'Police');
INSERT INTO list_options(list_id, option_id, title) values('sji_experienced_violence_from', 'Refused', 'Refused');

/* mental health condition list */
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Depression', 'Depression');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Manic-Depression/Bipolar', 'Manic-Depression/Bipolar');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Schizophrenia', 'Schizophrenia');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Personality Disorder', 'Personality Disorder');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Anxiety/Panic', 'Anxiety/Panic');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Eating disorder/anorexia/bulimia', 'Eating disorder/anorexia/bulimia');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('sji_mental_health_condition', 'Refused', 'Refused');

/* vaccinated for list */
INSERT INTO list_options(list_id, option_id, title) values('vaccinated_for', 'Hepatitis A (1 of 2 shots)', 'Hepatitis A (1 of 2 shots)');
INSERT INTO list_options(list_id, option_id, title) values('vaccinated_for', 'Hepatitis A (2 of 2 shots)', 'Hepatitis A (2 of 2 shots)');
INSERT INTO list_options(list_id, option_id, title) values('vaccinated_for', 'Hepatitis B (1 of 3 shots)', 'Hepatitis B (1 of 3 shots)');
INSERT INTO list_options(list_id, option_id, title) values('vaccinated_for', 'Hepatitis B (2 of 3 shots)', 'Hepatitis B (2 of 3 shots)');
INSERT INTO list_options(list_id, option_id, title) values('vaccinated_for', 'Hepatitis B (3 of 3 shots)', 'Hepatitis B (3 of 3 shots)');

/* want hepatitis vaccination options list */
INSERT INTO list_options(list_id, option_id, title) values('want_hepatitis_vaccination', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_hepatitis_vaccination', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_hepatitis_vaccination', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_hepatitis_vaccination', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_legal', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_legal', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_legal', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_legal', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_hormones', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_hormones', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_hormones', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_hormones', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_testicular_exam', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_testicular_exam', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_testicular_exam', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_testicular_exam', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('diagnosed_mental_health_condition', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_mental_health_condition', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_mental_health_condition', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_mental_health_condition', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_pap_smear', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_pap_smear', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_pap_smear', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_pap_smear', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_dv_referral', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_dv_referral', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_dv_referral', 'Maybe later', 'Maybe later');

INSERT INTO list_options(list_id, option_id, title) values('want_tb_test', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_tb_test', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_tb_test', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_tb_test', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('hiv_tested', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('hiv_tested', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('hiv_tested', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('hiv_tested', 'Refused', 'Refused');

/* HIV test result options list */
INSERT INTO list_options(list_id, option_id, title) values('last_hiv_test_result', 'Positive', 'Positive');
INSERT INTO list_options(list_id, option_id, title) values('last_hiv_test_result', 'Negative', 'Negative');
INSERT INTO list_options(list_id, option_id, title) values('last_hiv_test_result', 'Indeterminant', 'Indeterminant');
INSERT INTO list_options(list_id, option_id, title) values('last_hiv_test_result', 'Do not know/Never got the results', 'Do not know/Never got the results');
INSERT INTO list_options(list_id, option_id, title) values('last_hiv_test_result', 'Refused', 'Refused');

/* HIV test result options list */
INSERT INTO list_options(list_id, option_id, title) values('hiv_positive_receiving_care', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('hiv_positive_receiving_care', 'No, never', 'No, never');
INSERT INTO list_options(list_id, option_id, title) values('hiv_positive_receiving_care', 'In the past, but not now', 'In the past, but not now');
INSERT INTO list_options(list_id, option_id, title) values('hiv_positive_receiving_care', 'Refused', 'Refused');

/* Want HIV test options list */
INSERT INTO list_options(list_id, option_id, title) values('want_hiv_test', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_hiv_test', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_hiv_test', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_hiv_test', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('want_std_test', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('want_std_test', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('want_std_test', 'Maybe later', 'Maybe later');
INSERT INTO list_options(list_id, option_id, title) values('want_std_test', 'Refused', 'Refused');

/* Diagnosed STD positive options list */
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_std_positive', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_std_positive', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_std_positive', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('diagnosed_std_positive', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('prostitution_case_pending', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('prostitution_case_pending', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('prostitution_case_pending', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('prostitution_case_pending', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear_follow_up', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear_follow_up', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear_follow_up', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear_follow_up', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('abnormal_pap_smear', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('tb_follow_up', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('tb_follow_up', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('tb_follow_up', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('tb_follow_up', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('tb_tested', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('tb_tested', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('tb_tested', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('tb_tested', 'Refused', 'Refused');

INSERT INTO list_options(list_id, option_id, title) values('hepatitis_follow_up', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('hepatitis_follow_up', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('hepatitis_follow_up', 'Do not know', 'Do not know');
INSERT INTO list_options(list_id, option_id, title) values('hepatitis_follow_up', 'Refused', 'Refused');

/* Last TB test rresult options list */
INSERT INTO list_options(list_id, option_id, title) values('last_tb_test_result', 'Positive', 'Positive');
INSERT INTO list_options(list_id, option_id, title) values('last_tb_test_result', 'Negative', 'Negative');
INSERT INTO list_options(list_id, option_id, title) values('last_tb_test_result', 'Did not follow up for the results', 'Did not follow up for the results');
INSERT INTO list_options(list_id, option_id, title) values('last_tb_test_result', 'Do not remember', 'Do not remember');
INSERT INTO list_options(list_id, option_id, title) values('last_tb_test_result', 'Refused', 'Refused');
