CREATE TABLE IF NOT EXISTS `form_sji_intake_core_variables` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	`housing_situation` varchar(50) DEFAULT NULL,
	`amab_4_amab` varchar(50) DEFAULT NULL,
	`pronouns` varchar(255) DEFAULT NULL,
	`sexual_identity` varchar(255) DEFAULT NULL,

	`aliases` varchar(255) DEFAULT NULL,
	`disabled` varchar(255) DEFAULT NULL,
	`mailing_list` varchar(255) DEFAULT NULL,
	`dependents` varchar(255) DEFAULT NULL,
	`hipaa_call_from_sji` varchar(255) DEFAULT NULL,

	PRIMARY KEY (id)
) ENGINE=InnoDB;

--  zip list 
-- INSERT INTO list_options(list_id, option_id, title) values('zip', 'Not applicable', 'Not applicable');
-- INSERT INTO list_options(list_id, option_id, title) values('zip', 'Don\'t know', 'Don\'t know');
-- 
-- 
--  shelter list 
-- INSERT INTO list_options(list_id, option_id, title) values('shelter', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('shelter', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('shelter', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('shelter', 'Don\'t know', 'Don\'t know');
-- 
-- 
--  housing situation list 
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Friend\'s House/Apartment', 'Friend\'s House/Apartment');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Hotel/Rooming House/SRO', 'Hotel/Rooming House/SRO');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'On the streets', 'On the streets');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Own/Rent House/Apartment', 'Own/Rent House/Apartment');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Parent\'s House/Apartment', 'Parent\'s House/Apartment');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Shelter', 'Shelter');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Squatting', 'Squatting');
-- INSERT INTO list_options(list_id, option_id, title) values('housing_situation', 'Treatment program', 'Treatment program');
-- 
-- 
--  sexual identity 
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Gay', 'Gay');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Lesbian', 'Lesbian');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Bisexual', 'Bisexual');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Hetero', 'Hetero');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Queer', 'Queer');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Same Gender-Loving', 'Same Gender-Loving');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Questioning', 'Questioning');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Missing', 'Missing');
-- INSERT INTO list_options(list_id, option_id, title) values('sexual_identity', 'Declined to state', 'Declined to state');
-- 
-- 
--  partners gender 
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Cis Women', 'Cis Women');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Cis Men', 'Cis Men');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Intersex', 'Intersex');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Trans Women (MTF)', 'Trans Women (MTF)');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Trans Men (FTM)', 'Trans Men (FTM)');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Other trans/non-binary people', 'Other trans/non-binary people');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('partners_gender', 'Don\'t know', 'Don\'t know');
-- 
-- 
--  sex without a condom 
-- INSERT INTO list_options(list_id, option_id, title) values('sex_without_condom', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('sex_without_condom', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('sex_without_condom', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('sex_without_condom', 'Don\'t know', 'Don\'t know');
-- 
-- 
--  assigned male at birth and has had sex with someone assigned male at birth 
-- INSERT INTO list_options(list_id, option_id, title) values('amab_4_amab', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('amab_4_amab', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('amab_4_amab', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('amab_4_amab', 'Don\'t know', 'Don\'t know');
-- 
-- 
--  Have you ever injected anything without a doctors perscription 
-- INSERT INTO list_options(list_id, option_id, title) values('injected_without_perscription', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('injected_without_perscription', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('injected_without_perscription', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('injected_without_perscription', 'Don\'t know', 'Don\'t know');
-- 
-- INSERT INTO list_options(list_id, option_id, title) values('shared_needle', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('shared_needle', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('shared_needle', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('shared_needle', 'Don\'t know', 'Don\'t know');
-- 
-- INSERT INTO list_options(list_id, option_id, title) values('active_drug_user', 'Yes', 'Yes');
-- INSERT INTO list_options(list_id, option_id, title) values('active_drug_user', 'No', 'No');
-- INSERT INTO list_options(list_id, option_id, title) values('active_drug_user', 'Declined to state', 'Declined to state');
-- INSERT INTO list_options(list_id, option_id, title) values('active_drug_user', 'Don\'t know', 'Don\'t know');
-- 
INSERT INTO list_options(list_id, option_id, title) values('disabled', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('disabled', 'No', 'No');
INSERT INTO list_options(list_id, option_id, title) values('disabled', 'Declined', 'Declined');

INSERT INTO list_options(list_id, option_id, title) values('mailing_list', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('mailing_list', 'No', 'No');

INSERT INTO list_options(list_id, option_id, title) values('hipaa_message', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('hipaa_message', 'No', 'No');

INSERT INTO list_options(list_id, option_id, title) values('hipaa_call_from_sji', 'Yes', 'Yes');
INSERT INTO list_options(list_id, option_id, title) values('hipaa_call_from_sji', 'No', 'No');
