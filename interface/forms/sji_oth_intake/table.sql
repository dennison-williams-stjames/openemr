CREATE TABLE IF NOT EXISTS `form_sji_oth_intake_income_sources` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `income_source` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_oth_intake_income_verification` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `income_verification` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_oth_intake_noncash_assistance` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `noncash_assistance` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_oth_intake_priorities` (
        id bigint(20) NOT NULL auto_increment,
        pid bigint(20) default NULL,
        `priorities` varchar(200) NOT NULL,
        PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `form_sji_oth_intake` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

        /* pronouns are stored in the form_sji_intake_core_variables table */

        landlord_name varchar(255) default NULL,
        
        landlord_phone varchar(255) default NULL,

        landlord_address varchar(255) default NULL,

        landlord_email varchar(255) default NULL,

        base_rent varchar(255) default null,

        split_rent varchar(255) default null,

        your_rent varchar(255) default null,

        is_trans tinyint(1) default 0,

        requesting varchar(255) default null,

        eviction_risk tinyint(1) default 0,

        eviction_risk_description varchar(255) default null,

        veteran tinyint(1) default 0,

        interested_in_sji tinyint(1) default 0,

	PRIMARY KEY (id)
) ENGINE=InnoDB;


INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Employment', 'Employment');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Cash Economy Work', 'Cash Economy Work');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Unemployment', 'Unemployment');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Supplemental Security Income (SSI)', 'Supplemental Security Income (SSI)');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Social State Disability Insurance (SSDI)', 'Social State Disability Insurance (SSDI)');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'CalWORKS', 'CalWORKS');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'County Adult Assistance Program (CAAP)', 'County Adult Assistance Program (CAAP)');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Cash Assistance Linked to Medi-Cal (CALM)', 'Cash Assistance Linked to Medi-Cal (CALM)');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Cash Assistance Program for Immigrants (CAPI)', 'Cash Assistance Program for Immigrants (CAPI)');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Refugee Cash Assistance', 'Refugee Cash Assistance');
INSERT INTO list_options(list_id, option_id, title) values('income_sources', 'Financial Aid (student)', 'Financial Aid (student)');

INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'Pay Stubs', 'Pay Stubs');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'W2', 'W2');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'Self-Attestation letter', 'Self-Attestation letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'Bank Statements', 'Bank Statements');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'Unemployment Award Letter', 'Unemployment Award Letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'SSI Award Letter', 'SSI Award Letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'SSDI Award Letter', 'SSDI Award Letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'CalWORKS Award Letter', 'CalWORKS Award Letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'CAAP/CALM/CAPI award letter', 'CAAP/CALM/CAPI award letter');
INSERT INTO list_options(list_id, option_id, title) values('income_verification', 'Financial Aid award letter', 'Financial Aid award letter');

INSERT INTO list_options(list_id, option_id, title) values('noncash_assistance', 'Medi-Cal', 'Medi-Cal');
INSERT INTO list_options(list_id, option_id, title) values('noncash_assistance', 'Medicare', 'Medicare');
INSERT INTO list_options(list_id, option_id, title) values('noncash_assistance', 'CalFresh (food stamps)', 'CalFresh (food stamps)');

INSERT INTO list_options(list_id, option_id, title) values('priorities', 'BIPOC', 'BIPOC');
INSERT INTO list_options(list_id, option_id, title) values('priorities', 'Living with HIV/AIDS', 'Living with HIV/AIDS');
INSERT INTO list_options(list_id, option_id, title) values('priorities', 'Current Sex Worker', 'Current Sex Worker');
INSERT INTO list_options(list_id, option_id, title) values('priorities', 'Former Sex Worker', 'Former Sex Worker');
INSERT INTO list_options(list_id, option_id, title) values('priorities', 'Disabled', 'Disabled');
INSERT INTO list_options(list_id, option_id, title) values('priorities', 'Formerly Incarcerated', 'Formerly Incarcerated');

INSERT INTO list_options(list_id, option_id, title) values('requesting', 'Emergency: Overdue rent', 'Emergency: Overdue rent');
INSERT INTO list_options(list_id, option_id, title) values('requesting', 'Emergency: Next month\'s rent', 'Emergency: Next month\s rent');
INSERT INTO list_options(list_id, option_id, title) values('requesting', 'Emergency: Security deposit for move in', 'Emergency: Security deposit for move in');
INSERT INTO list_options(list_id, option_id, title) values('requesting', 'Ongoing monthly assistance', 'Ongoing monthly assistance');
