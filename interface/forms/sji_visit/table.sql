CREATE TABLE IF NOT EXISTS `form_sji_visit` (
 id bigint(20) NOT NULL auto_increment,
 date datetime default NULL,
 pid bigint(20) default NULL,
 user varchar(255) default NULL,
 groupname varchar(255) default NULL,
 authorized tinyint(4) default NULL,
 activity tinyint(4) default NULL,

 `symptoms` varchar(255) default NULL,
 `initial_test_for_hiv` tinyint(1) default NULL,
 `test_results_for_hiv` tinyint(1) default NULL,
 `last_tested_for_hiv` date default NULL,
 `last_tested_for_sti` date default NULL,
 `counselor_name` varchar(255) default NULL,
 `massage` tinyint(1) default NULL,
 `massage_apt_time` datetime default NULL,
 `ear_accupuncture` tinyint(1) default NULL,
 `full_body_accupuncture` tinyint(1) default NULL,
 `full_body_accupuncture_apt_time` datetime default NULL,
 `reiki` tinyint(1) default NULL,
 `reiki_apt_time` datetime default NULL,
 `phone_visit` tinyint(1) default NULL,
 `phone_visit_specify` varchar(255) default NULL,
 `talent_testing` tinyint(1) default NULL,
 `food` tinyint(1) default NULL,
 `clothing` tinyint(1) default NULL,
 `condoms` tinyint(1) default NULL,
 `nex_syringes` tinyint(1) default NULL,
 `hygiene_supplies` tinyint(1) default NULL,
 `referrals_to_other_services` tinyint(1) default NULL,
 `referrals_to_other_services_specify` varchar(255) default NULL,
 `other_harm_reduction_supplies` tinyint(1) default NULL,
 `other_harm_reduction_supplies_specify` varchar(255) default NULL,
 `support_group` tinyint(1) default NULL,
 `encounter` bigint(20) DEFAULT NULL,
 PRIMARY KEY (id),
 KEY `form_sji_visit_pid_idx` (`pid`) USING BTREE,
 KEY `form_sji_visit_encounter_idx` (`encounter`) USING BTREE,
 CONSTRAINT `form_sji_visit_pid_patient_data_pid_fk` FOREIGN KEY (`pid`) REFERENCES `patient_data` (`pid`) ON DELETE CASCADE ON UPDATE CASCADE,
 CONSTRAINT `form_sji_visit_pid_patient_data_pid_fk` FOREIGN KEY (`encounter`) REFERENCES `form_encounter` (`encounter`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB

CREATE TABLE IF NOT EXISTS `form_sji_visit_medical_services` (
 id bigint(20) NOT NULL auto_increment,
 pid bigint(20) default NULL,
 `medical_service` varchar(255) default NULL,
 PRIMARY KEY (id),
 KEY `form_sji_visit_medical_services_pid_idx` (`pid`) USING BTREE,
 CONSTRAINT `form_sji_visit_medical_services_pid_form_sji_visit_id_fk` 
    FOREIGN KEY (`pid`) REFERENCES `form_sji_visit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB

CREATE TABLE IF NOT EXISTS `form_sji_visit_initial_test_for_sti` (
 id bigint(20) NOT NULL auto_increment,
 pid bigint(20) default NULL,
 `initial_test_for_sti` varchar(255) default NULL,
 PRIMARY KEY (id),
 KEY `form_sji_visit_initial_test_for_sti_pid_idx` (`pid`) USING BTREE,
 CONSTRAINT `form_sji_visit_initial_test_for_sti_pid_form_sji_visit_id_fk` 
    FOREIGN KEY (`pid`) REFERENCES `form_sji_visit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB

CREATE TABLE IF NOT EXISTS `form_sji_visit_test_results_for_sti` (
 id bigint(20) NOT NULL auto_increment,
 pid bigint(20) default NULL,
 `test_results_for_sti` varchar(255) default NULL,
 PRIMARY KEY (id),
 KEY `form_sji_visit_test_results_for_sti_pid_idx` (`pid`) USING BTREE,
 CONSTRAINT `form_sji_visit_test_results_for_sti_pid_form_sji_visit_id_fk` 
    FOREIGN KEY (`pid`) REFERENCES `form_sji_visit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB

CREATE TABLE IF NOT EXISTS `form_sji_visit_counseling_services` (
 id bigint(20) NOT NULL auto_increment,
 pid bigint(20) default NULL,
 `counseling_services` varchar(255) default NULL,
 PRIMARY KEY (id),
 KEY `form_sji_visit_counseling_services_pid_idx` (`pid`) USING BTREE,
 CONSTRAINT `form_sji_visit_counseling_services_pid_form_sji_visit_id_fk` 
    FOREIGN KEY (`pid`) REFERENCES `form_sji_visit` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB
