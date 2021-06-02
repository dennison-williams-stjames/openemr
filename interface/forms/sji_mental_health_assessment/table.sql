CREATE TABLE IF NOT EXISTS `form_sji_mental_health_assessment` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

	presenting_problem varchar(255),
	strengths varchar(255),
	risk_factors varchar(255),
	psychiatric_history varchar(255),
	psychosocial_history varchar(255),
	substance_history varchar(255),
	medical_history varchar(255),
	mental_status_exam varchar(255),

	PRIMARY KEY (id)
) ENGINE=InnoDB;

