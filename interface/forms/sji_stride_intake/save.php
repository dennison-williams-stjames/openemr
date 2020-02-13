<?php
/**  Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30
 *   Copyright (C) Open Source Medical Software
 *
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


include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");
include_once("../../forms/sji_intake/common.php");
include_once("../../forms/sji_intake_core_variables/common.php");

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_stride_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

function sji_extendedStrideIntake($formid, $submission) {
    global $pid, $userauthorized;

    // Look for the external values which will likely have a different $encounter id */
    $query = 
       'SELECT id FROM form_sji_intake '.
       'WHERE pid = ? '.
       'ORDER BY date DESC '.
       'LIMIT 1';

    $res = sqlStatement($query, array($pid));

    $row = sqlFetchArray($res);

    $intake_id = $row['id'];

    error_log(__FUNCTION__.'() intake_id: '. $intake_id);

    $query = 
       'SELECT id FROM form_sji_intake_core_variables '.
       'WHERE pid = ? '.
       'ORDER BY date DESC '.
       'LIMIT 1';

    $res = sqlStatement($query, array($pid));

    $row = sqlFetchArray($res);

    $cv_id = $row['id'];

    error_log(__FUNCTION__.'() core variables id: '. $cv_id);

    $encounter = '';
    if (!empty($_SESSION['encounter'])) {
       $encounter = $_SESSION['encounter'];
    } else {
       $encounter = date("Ymd");
    }

    if (isset($intake_id)) {
       sqlStatement("delete from form_sji_intake_supportive_people where pid=?", array($intake_id));
    }

    if (
       isset($submission['supportive_people']) || 
       isset($submission['taken_hormones']) 
    ){

       if (!isset($intake_id)) {

          global $intake_columns;
          $submission2 = array();
          foreach ($intake_columns as $column) {
             if (isset($_POST[$column])) {
                $submission2[$column] = $_POST[$column];
             }
          }

          // create a new intake and save intake_id
          $newid = formSubmit('form_sji_intake', $submission2, '', $userauthorized);
          $intake_id = addForm($encounter, "St. James Infirmary Intake", $newid, "sji_intake", $pid, $userauthorized);
          error_log(__FUNCTION__.'() creating a new intake: '. $intake_id);
          sji_extendedIntake($newid, $_POST);
       } else {

          if (isset($submission['supportive_people'])) {
          foreach ($submission['supportive_people'] as $person) {
             $sql = "insert into form_sji_intake_supportive_people(supportive_people, pid) values(?, ?)";
             sqlInsert($sql, array($person, $intake_id));
          } // foreach
          } // if

          // Update taken hormones if changed
          if (isset($submission['taken_hormones']) && isset($intake_id)) {
             $sql = 'UPDATE form_sji_intake SET taken_hormones = ? where id = ?';
             $res = sqlStatement($sql, array($submission['taken_hormones'], $intake_id));
          } 
       } // else
    } 

    // Update pronouns if they have changed
    if (isset($submission['pronouns'])) {
       if (!isset($cv_id)) {

          $submission3 = array();
          global $intake_core_variable_columns;
          foreach ($intake_core_variable_columns as $column) {
             if (isset($_POST[$column])) {
                $submission3[$column] = $_POST[$column];
             }
          }

          // make sure to set cv_id
          $newid = formSubmit('form_sji_intake_core_variables', $submission3, '', $userauthorized);
          $cv_id = addForm($encounter, "St. James Infirmary Intake - Core Variables", $newid, "sji_intake_core_variables", $pid, $userauthorized);
          error_log(__FUNCTION__.'() created a new core variables intake: '. $cv_id);
          sji_extendedIntakeCoreVariables($newid, $_POST);
       } else {
          $sql = 'UPDATE form_sji_intake_core_variables SET pronouns = ? where id = ?';
          $res = sqlStatement($sql, array($submission['pronouns'], $cv_id));
       }
    } 

}

$stride_intake_columns = array(
   'why_are_you_here', 'hormone_duration', 'hormone_form_dosage',
   'hormone_program', 'why_stopped', 'why_continue', 
   'affect_expectations', 'effect_hopes', 'hormone_concerns',
   'who_out_to', 'financial_situation', 'safety_concerns',
   'useful_support', 'clinician_narrative'
);

$submission = array();
foreach ($stride_intake_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    error_log(__FILE__.' new STRIDE intake submission: '. "formSubmit($table_name, ". print_r($submission, 1) .", '', $userauthorized)");
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    error_log(__FILE__.' newid: '. $newid);
    error_log(__FILE__.' new STRIDE intake submission: '. " addForm(" . $_SESSION["encounter"] .', "St. James Infirmary STRIDE Intake", '. $newid .", \"sji_stride_intake\", $pid, $userauthorized)");
    addForm($_SESSION["encounter"], "St. James Infirmary STRIDE Intake", $newid, "sji_stride_intake", $pid, $userauthorized);
    error_log(__FILE__.' sji_extendedStrideIntake('. $newid .', '. print_r($_POST, 1));
    sji_extendedStrideIntake($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedStrideIntake($_GET["id"], $_POST);
}

formHeader("Redirecting....");
formJump();
formFooter();
