<?php
/**
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

include_once(dirname(__FILE__).'/../../globals.php');
require_once("$srcdir/api.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");
require_once("$srcdir/encounter.inc");
require_once("$srcdir/forms.inc");


$visit_columns = array('symptoms', 'initial_test_for_hiv', 'test_results_for_hiv', 
'last_tested_for_hiv', 'last_tested_for_sti', 'counselor_name', 'massage', 
'ear_accupuncture', 'full_body_accupuncture',
'reiki', 'phone_visit', 'phone_visit_specify',
'talent_testing', 'food', 'clothing', 'condoms', 'nex_syringes', 
'hygiene_supplies', 'referrals_to_other_services',
'referrals_to_other_services_specify', 'other_harm_reduction_supplies',
'other_harm_reduction_supplies_specify', 'support_group'
);

$visit_time_columns = array(
'reiki_apt_time', 'massage_apt_time', 'full_body_accupuncture_apt_time'
);

function sji_get_visit_submission_from_data($data) {
	global $visit_columns, $visit_time_columns;
        $visit_columns = array_merge($visit_columns, $visit_time_columns);

	$submission = array();
	foreach ($visit_columns as $column) {
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

	      if (
		 array_search($column, array('massage_apt_time', 'reiki_apt_time', 'full_body_accupuncture_apt_time')) !== false &&

		 // If there is a space then the date is already prepended
		 !preg_match('/ /', $submission[$column]) &&

		 strlen($submission[$column])
	      ) {
		 $submission[$column] = date('Y-m-d') .' '. $submission[$column];
	      }

	   }
	}
	return $submission;
}

function new_visit($data, $pid) {
   global $userauthorized;
   $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
   $encounter = generate_id();
   $submission = sji_get_visit_submission_from_data($data);
   $submission['encounter'] = $encounter;
   $data['date'] = isset($data['date']) ? $data['date'] : $data['form_date'];

   $id = addForm(
      $encounter,
      "New Patient Encounter",
      '', // we will correctly set this further down
      "newpatient",
      $pid,
      $userauthorized,
      $data['date']
   );

   // Add the encounter that we are associating the visit with
   $eid = sqlInsert("INSERT INTO form_encounter SET " .
      "date = '" . add_escape_custom($data['date']) . "', " .
      "onset_date = '" . add_escape_custom(isset($data['onset_date']) ? $data['onset_date'] : '') . "', " .
      "reason = '" . add_escape_custom(isset($data['reason']) ? $data['reason'] : '') . "', " .
      "facility = '" . add_escape_custom(isset($data['facility']) ? $data['facility'] : '') ."', " .
      "pc_catid = '" . add_escape_custom(isset($data['pc_catid']) ? $data['pc_catid'] : '') . "', " .
      "facility_id = '" . add_escape_custom(isset($data['facility_id']) ? $data['facility_id'] : '') . "', " .
      "billing_facility = '" . add_escape_custom(isset($data['billing_facility']) ? $data['billing_facility'] : '') . "', " .
      "sensitivity = '" . add_escape_custom(isset($data['sensitivity']) ? $data['sensitivity'] : '') . "', " .
      "referral_source = '" . add_escape_custom(isset($data['referral_source']) ? $data['referral_source'] : '') . "', " .
      "pid = '" . add_escape_custom($pid) . "', " .
      "encounter = '" . add_escape_custom($encounter) . "', " .
      "pos_code = '" . add_escape_custom(isset($data['pos_code']) ? $data['pos_code'] : '') . "', " .
      "external_id = '" . add_escape_custom(isset($data['external_id']) ? $data['external_id'] : '') . "', " .
      "provider_id = '" . add_escape_custom($provider_id) . "'");

   $newid = sji_extendedVisit($encounter, $data);

   // fix the associated dates
   $sql = "update form_sji_visit set date=? where encounter=?";
   sqlQuery($sql, array($data['date'], $encounter));
   $sql = "update form_encounter set date=? where encounter=?"; // $intake['encounter']
   sqlQuery($sql, array($data['date'], $encounter));
   $sql = "update forms set date=?,form_id=? where encounter=?"; // $intake['encounter']
   sqlQuery($sql, array($data['date'], $eid, $encounter)); 

   setencounter($encounter);

   return $encounter;
}

function sji_extendedVisit($eid, $submission) {
   global $userauthorized;

   $tuned = sji_get_visit_submission_from_data($submission);
   $tuned['encounter'] = $eid;
   $id = formSubmit('form_sji_visit', $tuned, $eid, $userauthorized);

   sqlStatement("delete from form_sji_visit_medical_services where pid=?", array($id));
   if (isset($submission['medical_services'])) {
      // TODO: audit this
      foreach ($submission['medical_services'] as $service) {
         sqlInsert("insert into form_sji_visit_medical_services (medical_service, pid) values(?, ?)", 
            array($service, $id));
      }
   }

   sqlStatement("delete from form_sji_visit_initial_test_for_sti where pid=?", array($id));
   if (isset($submission['initial_test_for_sti'])) {
      // TODO: audit this
      foreach ($submission['initial_test_for_sti'] as $service) {
         sqlInsert("insert into form_sji_visit_initial_test_for_sti(initial_test_for_sti, pid) values(?, ?)", 
            array($service, $id));
      }
   }

   sqlStatement("delete from form_sji_visit_test_results_for_sti where pid=?", array($id));
   if (isset($submission['test_results_for_sti'])) {
      // TODO: audit this
      foreach ($submission['test_results_for_sti'] as $service) {
         sqlInsert("insert into form_sji_visit_test_results_for_sti(test_results_for_sti, pid) values(?, ?)", 
            array($service, $id));
      }
   }

   sqlStatement("delete from form_sji_visit_counseling_services where pid=?", array($id));
   if (isset($submission['counseling_services'])) {
      // TODO: audit this
      foreach ($submission['counseling_services'] as $service) {
         sqlInsert("insert into form_sji_visit_counseling_services(counseling_services, pid) values(?, ?)", 
            array($service, $id));
      }
   }

   return $id;
}

function update_visit($data, $pid) {
    global $userauthorized;

    $data['date'] = isset($data['date']) ? $data['date'] : $data['form_date'];
    $id = $data["id"];
    $result = sqlQuery("SELECT encounter, sensitivity FROM form_encounter WHERE id = ?", array($id));
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        die(xlt("You are not authorized to see this encounter."));
    }

    $encounter = $result['encounter'];

    // See view.php to allow or disallow updates of the encounter date.
    $datepart = acl_check('encounters', 'date_a') ? "date = '" . add_escape_custom($data['date']) . "', " : "";
    sqlStatement("UPDATE form_encounter SET " .
    $datepart .
    "onset_date = '" . add_escape_custom($data['onset_date']) . "', " .
    "reason = '" . add_escape_custom($data['reason']) . "', " .
    "facility = '" . add_escape_custom($data['facility']) . "', " .
    "pc_catid = '" . add_escape_custom($data['pc_catid']) . "', " .
    "facility_id = '" . add_escape_custom($data['facility_id']) . "', " .
    "billing_facility = '" . add_escape_custom(isset($data['billing_facility']) ? $data['billing_facility'] : '') . "', " .
    "sensitivity = '" . add_escape_custom($data['sensitivity']) . "', " .
    "referral_source = '" . add_escape_custom($data['referral_source']) . "', " .
    "pid = '" . add_escape_custom($pid) . "', " .
    "encounter = '" . add_escape_custom($encounter) . "', " .
    "pos_code = '" . add_escape_custom(isset($data['pos_code']) ? $data['pos_code'] : '') . "' " .
    "WHERE id = '" . add_escape_custom($id) . "'");

    return sji_extendedVisit($encounter, $data);
}
