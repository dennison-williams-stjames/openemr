<?php
/*
 *   This program is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU General Public License
 *   as published by the Free Software Foundation; either version 2
 *   of the License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_intake";

$intake_columns = array('declined_intake', 'interviewer_name', 'referrer', 
   'relationship_status', 'country_of_origin', 'sex_industry_connection',
   'identified_as_sex_worker',
   'last_physical', 'hepatitis_follow_up', 'want_hepatitis_vaccination',
   'hiv_tested', 'last_hiv_test_date', 'last_hiv_test_result', 
   'hiv_positive_receiving_care', 'want_hiv_test', 'diagnosed_std_positive',
   'want_std_test', 'tb_tested', 'last_tb_test_date', 'last_tb_test_result',
   'tb_follow_up', 'want_tb_test', 'last_pap_smear', 'want_pap_smear',
   'abnormal_pap_smear', 'abnormal_pap_smear_date', 
   'abnormal_pap_smear_follow_up', 'last_mammogram', 'last_testicular_exam',
   'want_testicular_exam', 'taking_hormones_now', 'hormones_type',
   'hormones_source', 'taken_hormones', 'want_hormones', 
   'prostitution_case_pending', 'want_legal', 'experienced_violence',
   'want_to_report_bad_date', 'experienced_violence_from_partner',
   'currently_experiencing_violence', 'want_dv_referral', 
   'diagnosed_mental_health_condition', 'mental_health_condition_meds',
   'comments'
);

function sji_intake_formFetch($formid) {
	$return = array();

	// Add on work_with list
	$query = "select work_with from form_sji_intake_work_with where pid=?";
	$res = sqlStatement($query, array($formid));
	$work_with = array();
	while ($row = sqlFetchArray($res)) {
	   $work_with[] = $row['work_with'];
	}
	if (sizeof($work_with)) {
	   $return['work_with'] = $work_with;
	}

	// Add on work_doing list
	$query = "select work_doing from form_sji_intake_work_doing where pid=?";
	$res = sqlStatement($query, array($formid));
	$work_doing = array();
	while ($row = sqlFetchArray($res)) {
	   $work_doing[] = $row['work_doing'];
	}
	if (sizeof($work_doing)) {
	   $return['work_doing'] = $work_doing;
	}

	// Add on work_done list
	$query = "select work_done from form_sji_intake_work_done where pid=?";
	$res = sqlStatement($query, array($formid));
	$work_done = array();
	while ($row = sqlFetchArray($res)) {
	   $work_done[] = $row['work_done'];
	}
	if (sizeof($work_done)) {
	   $return['work_done'] = $work_done;
	}

	// Add on supportive_people list
	$query = "select supportive_people from form_sji_intake_supportive_people where pid=?";
	$res = sqlStatement($query, array($formid));
	$supportive_people = array();
	while ($row = sqlFetchArray($res)) {
	   $supportive_people[] = $row['supportive_people'];
	}
	if (sizeof($supportive_people)) {
	   $return['supportive_people'] = $supportive_people;
	}

	// Add on received_healthcare_from list
	$query = "select received_healthcare_from from form_sji_intake_received_healthcare_from where pid=?";
	$res = sqlStatement($query, array($formid));
	$received_healthcare_from = array();
	while ($row = sqlFetchArray($res)) {
	   $received_healthcare_from[] = $row['received_healthcare_from'];
	}
	if (sizeof($received_healthcare_from)) {
	   $return['received_healthcare_from'] = $received_healthcare_from;
	   $return['received_health_care_from'] = $received_healthcare_from;
	}

	$query = "select identified_as_sex_worker_reaction from form_sji_intake_identified_as_sex_worker_reaction where pid=?";
	$res = sqlStatement($query, array($formid));
	$reason = array();
	while ($row = sqlFetchArray($res)) {
	   $reason[] = $row['identified_as_sex_worker_reaction'];
	}
	if (sizeof($reason)) {
	   $return['identified_as_sex_worker_reaction'] = $reason;
	}

	$query = "select not_identified_sex_worker_reason from form_sji_intake_not_identified_sex_worker_reason where pid=?";
	$res = sqlStatement($query, array($formid));
	$reason = array();
	while ($row = sqlFetchArray($res)) {
	   $reason[] = $row['not_identified_sex_worker_reason'];
	}
	if (sizeof($reason)) {
	   $return['not_identified_sex_worker_reason'] = $reason;
	}

	// Add on tested_for list
	$query = "select tested_for from form_sji_intake_tested_for where pid=?";
	$res = sqlStatement($query, array($formid));
	$tested_for = array();
	while ($row = sqlFetchArray($res)) {
	   $tested_for[] = $row['tested_for'];
	}
	if (sizeof($tested_for)) {
	   $return['tested_for'] = $tested_for;
	}

	// Add on diagnosed_with list
	$query = "select diagnosed_with from form_sji_intake_diagnosed_with where pid=?";
	$res = sqlStatement($query, array($formid));
	$diagnosed_with = array();
	while ($row = sqlFetchArray($res)) {
	   $diagnosed_with[] = $row['diagnosed_with'];
	}
	if (sizeof($diagnosed_with)) {
	   $return['diagnosed_with'] = $diagnosed_with;
	}

	// Add on hormones_types list
	$query = "select hormones_types from form_sji_intake_hormones_types where pid=?";
	$res = sqlStatement($query, array($formid));
	$hormones_types = array();
	while ($row = sqlFetchArray($res)) {
	   $hormones_types[] = $row['hormones_types'];
	}
	if (sizeof($hormones_types)) {
	   $return['hormones_types'] = $hormones_types;
	}

	// Add on experienced_violence_from list
	$query = "select experienced_violence_from from form_sji_intake_experienced_violence_from where pid=?";
	$res = sqlStatement($query, array($formid));
	$experienced_violence_from = array();
	while ($row = sqlFetchArray($res)) {
	   $experienced_violence_from[] = $row['experienced_violence_from'];
	}
	if (sizeof($experienced_violence_from)) {
	   $return['experienced_violence_from'] = $experienced_violence_from;
	}

	// Add on std_past list
	$query = "select std_past from form_sji_intake_std_past where pid=?";
	$res = sqlStatement($query, array($formid));
	$std_past = array();
	while ($row = sqlFetchArray($res)) {
	   $std_past[] = $row['std_past'];
	}
	if (sizeof($std_past)) {
	   $return['std_past'] = $std_past;
	}

	// Add on mental_health_condition list
	$query = "select mental_health_condition from form_sji_intake_mental_health_condition where pid=?";
	$res = sqlStatement($query, array($formid));
	$mental_health_condition = array();
	while ($row = sqlFetchArray($res)) {
	   $mental_health_condition[] = $row['mental_health_condition'];
	}
	if (sizeof($mental_health_condition)) {
	   $return['mental_health_condition'] = $mental_health_condition;
	}

	// Add the Hep immunizations that we ask for in our intake
	$query = "select cvx_code, note from immunizations where patient_id=? and note like '%sji_intake%'";
	$res = sqlStatement($query, array($formid));
	$immunizations = array();
	while ($row = sqlFetchArray($res)) {
	   preg_match('/(\(.*\))/', $row['note'], $matches);
	   if ($row['cvx_code'] == 85) {
	      $immunizations[] = 'Hepatitis A '. $matches[1];
	   } else if ($row['cvx_code'] == 45) {
	      $immunizations[] = 'Hepatitis B '. $matches[1];
	   }
	}
	if (sizeof($immunizations)) {
	   $return['vaccinated_for'] = $immunizations;
	}

	return $return;
}

function sji_extendedIntake($formid, $submission) {
    global $pid;

    sqlStatement("delete from form_sji_intake_supportive_people where pid=?", array($formid));
    if (isset($submission['supportive_people'])) {
        // TODO: audit this
	foreach ($submission['supportive_people'] as $person) {
            sqlInsert("insert into form_sji_intake_supportive_people(supportive_people, pid) values(?, ?)", array($person, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_work_done where pid=?", array($formid));
    if (isset($submission['work_done'])) {
        // TODO: audit this
	foreach ($submission['work_done'] as $work) {
            sqlInsert("insert into form_sji_intake_work_done(work_done, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_work_doing where pid=?", array($formid));
    if (isset($submission['work_doing'])) {
        // TODO: audit this
	foreach ($submission['work_doing'] as $work) {
            sqlInsert("insert into form_sji_intake_work_doing(work_doing, pid) values(?, ?)", array($work, $formid));
        }
    }

    // Aka active work, lists genders of others our participants are working 
    // with now, aka "Who have you been sexualy active with?"
    sqlInsert("delete from form_sji_intake_work_with where pid=?", array($formid));
    if (isset($submission['work_with'])) {
	foreach ($submission['work_with'] as $work) {
            sqlInsert("insert into form_sji_intake_work_with(work_with, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_received_healthcare_from where pid=?", array($formid));
    if (isset($submission['received_healthcare_from'])) {
	foreach ($submission['received_healthcare_from'] as $work) {
            sqlInsert("insert into form_sji_intake_received_healthcare_from(received_healthcare_from, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_identified_as_sex_worker_reaction where pid=?", array($formid));
    if (isset($submission['identified_as_sex_worker_reaction'])) {
	foreach ($submission['identified_as_sex_worker_reaction'] as $work) {
            //sqlInsert("insert into form_sji_intake_identified_as_sex_worker_reaction(identified_as_sex_worker_reaction, pid) values('$work', $formid)");
            sqlInsert("insert into form_sji_intake_identified_as_sex_worker_reaction(identified_as_sex_worker_reaction, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_not_identified_sex_worker_reason where pid=?", array($formid));
    if (isset($submission['not_identified_sex_worker_reason'])) {
	foreach ($submission['not_identified_sex_worker_reason'] as $reason) {
            sqlInsert("insert into form_sji_intake_not_identified_sex_worker_reason(not_identified_sex_worker_reason, pid) values(?, ?)", array($reason, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_tested_for where pid=?", array($formid));
    if (isset($submission['tested_for'])) {
	foreach ($submission['tested_for'] as $work) {
            sqlInsert("insert into form_sji_intake_tested_for(tested_for, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_diagnosed_with where pid=?", array($formid));
    if (isset($submission['diagnosed_with'])) {
	foreach ($submission['diagnosed_with'] as $work) {
            sqlInsert("insert into form_sji_intake_diagnosed_with(diagnosed_with, pid) values(?, ?)", array($work, $formid));
        }
    }

    // Add additional vaccinations, in the notes field mention the import and import date
    // vcaccinated for
    sqlInsert("delete from immunizations where patient_id=? and note like '%sji_intake%'", array($pid));
    if (isset($submission['vaccinated_for'])) {
        $man = 'AB';
	foreach ($submission['vaccinated_for'] as $vaccine) {
            $note = 'Imported from sji_intake';
	    // TODO: Create a lookup for the vaccines in our system to the cvx_code and associated manufacturer

            if (preg_match('/1 of 2/', $vaccine)) {
                $note .= ' (1 of 2 shots)'; 
            } else if (preg_match('/2 of 2/', $vaccine)){
                $note .= ' (2 of 2 shots)'; 
            } else if (preg_match('/1 of 3/', $vaccine)){
                $note .= ' (1 of 3 shots)'; 
            } else if (preg_match('/2 of 3/', $vaccine)){
                $note .= ' (2 of 3 shots)'; 
            } else if (preg_match('/3 of 3/', $vaccine)){
                $note .= ' (3 of 3 shots)'; 
            }

            if (preg_match('/Hepatitis A/', $vaccine)) {
               $cvx = 85;
            } else if (preg_match('/Hepatitis B/', $vaccine)) {
               $cvx = 45;
            } else {
               return;
            }
            sqlInsert("insert into immunizations(patient_id, cvx_code, manufacturer, note) values(?,?,?,?)", array($pid, $cvx, $man, $note));
        }
    }

    sqlInsert("delete from form_sji_intake_std_past where pid=?", array($formid));
    if (isset($submission['std_past'])) {
	foreach ($submission['std_past'] as $work) {
            sqlInsert("insert into form_sji_intake_std_past(std_past, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_hormones_types where pid=?", array($formid));
    if (isset($submission['hormones_types'])) {
	foreach ($submission['hormones_types'] as $type) {
            sqlInsert("insert into form_sji_intake_hormones_types(hormones_types, pid) values(?, ?)", array($type, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_experienced_violence_from where pid=?", array($formid));
    if (isset($submission['experienced_violence_from'])) {
	foreach ($submission['experienced_violence_from'] as $work) {
            sqlInsert("insert into form_sji_intake_experienced_violence_from(experienced_violence_from, pid) values(?, ?)", array($work, $formid));
        }
    }

    sqlInsert("delete from form_sji_intake_mental_health_condition where pid=?", array($formid));
    if (isset($submission['mental_health_condition'])) {
	foreach ($submission['mental_health_condition'] as $work) {
            sqlInsert("insert into form_sji_intake_mental_health_condition(mental_health_condition, pid) values(?, ?)", array($work, $formid));
        }
    }
}

// New intakes have to be associated with an existing encounter
function new_intake($data, $pid) {
   global $userauthorized;
   $table_name = "form_sji_intake";
   $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;

   $submission = sji_get_intake_submission_from_data($data);

   $newid = formSubmit($table_name, $submission, '', $userauthorized);
   addForm($data['encounter'], "St. James Infirmary Intake", $newid, "sji_intake", $pid, $userauthorized);
   sji_extendedIntake($newid, $data);

   return $newid;
}

function sji_get_intake_submission_from_data($data) {
	global $intake_columns;

	$submission = array();
	foreach ($intake_columns as $column) {
	   if (isset($data[$column])) {

	      // Don't submit blank dates
	      if (preg_match('/0000-00-00/', $data[$column]) || !strlen($data[$column])) {
		 continue;
	      }

	      $submission[$column] = $data[$column];

	      // Checkboxes get submitted as on or off but saved in the DB as 1 or 0
	      if ($submission[$column] == 'on') {
		 $submission[$column] = 1;
	      } else if ($submission[$column] == 'off') {
		 $submission[$column] = 0;
	      }


	   }
	}
	return $submission;
}

