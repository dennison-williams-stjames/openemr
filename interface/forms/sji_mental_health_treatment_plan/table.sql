CREATE TABLE IF NOT EXISTS `form_sji_mental_health_treatment_plan` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	diagnosis varchar(255),
	presenting_problem varchar(255),
	clients_goals varchar(255),
	clinical_objectives varchar(255),
	treatment_frequency varchar(255),
	interventions_plan varchar(255),
	discharge_plan varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

