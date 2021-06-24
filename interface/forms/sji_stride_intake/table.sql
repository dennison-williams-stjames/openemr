CREATE TABLE IF NOT EXISTS `form_sji_stride_intake` (
	id bigint(20) NOT NULL auto_increment,
	date datetime default NULL,
	pid bigint(20) default NULL,
	user varchar(255) default NULL,
	groupname varchar(255) default NULL,
	authorized tinyint(4) default NULL,
	activity tinyint(4) default NULL,

        /* pronouns are stored in the form_sji_intake_core_variables table */
        /* What brought you to the transgender care program today? */
        why_are_you_here varchar(255) default NULL,
        
        /* taken_hormones is tracked in the form_sji_intake table */

        /* If yes, for how long? */
        hormone_duration varchar(255) default NULL,

        /* If yes, what form/dosage */
        hormone_form_dosage varchar(255) default NULL,

        /* If yes, through which program */
        hormone_program varchar(255) default NULL,

        /* If yes, why did you stop? */
        why_stopped varchar(255) default null,

        /* why do you want to continue to take hormones? */
        why_continue varchar(255) default null,

        /* How do you think taking hormones will continue to affect you physically and mentally? */
        affect_expectations varchar(255) default null,

        /* What are some of the effects of taking hormones you feel particulary hopeful about? */
        effect_hopes varchar(255) default null,

        /* What concerns do you have about taking hormones? */
        hormone_concerns varchar(255) default null,

        /* Who in your life are you out to as trans? */
        who_out_to varchar(255) default null,

        /* supportive people is tracked in sji_intake */

        /* Tell me about your financial situation */
        financial_situation varchar(255) default null,

        /* What safety concerns do you have around being trans? */
        safety_concerns varchar(255) default null,

        /* What other types of support would be useful to you? */
        useful_support varchar(255) default null,

        clinician_narrative text,

	PRIMARY KEY (id)
) ENGINE=InnoDB;

