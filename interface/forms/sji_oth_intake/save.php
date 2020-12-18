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
$table_name = "form_sji_oth_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

function sji_extendedOTHIntake($formid, $submission) {
    global $pid, $userauthorized;

    // Look for the external values which will likely have a different $encounter id */
    $query = 
       'SELECT id FROM form_sji_oth_intake '.
       'WHERE pid = ? '.
       'ORDER BY date DESC '.
       'LIMIT 1';

    $res = sqlStatement($query, array($pid));

    $row = sqlFetchArray($res);

    $intake_id = $row['id'];

    $query = 
       'SELECT id FROM form_sji_intake_core_variables '.
       'WHERE pid = ? '.
       'ORDER BY date DESC '.
       'LIMIT 1';

    $res = sqlStatement($query, array($pid));

    $row = sqlFetchArray($res);

    $cv_id = $row['id'];

    $encounter = '';
    if (!empty($_SESSION['encounter'])) {
       $encounter = $_SESSION['encounter'];
    } else {
       $encounter = date("Ymd");
    }

    // Update email
    $sql = 'UPDATE patient_data SET email = ? where pid = ?';
    $res = sqlStatement($sql, array($submission['email'], $pid));

    // TODO: update phone if changed
    // TODO: update address if changed

    // Add income sources
    $sql = 'delete from form_sji_oth_intake_income_sources where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    foreach ($submission['income_sources'] as $income_source) {
       $sql = 'insert into form_sji_oth_intake_income_sources(pid, income_source) values(?, ?)';
       $res = sqlStatement($sql, array($intake_id, $income_source));
    }

    // add income verification
    $sql = 'delete from form_sji_oth_intake_income_verification where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    foreach ($submission['income_verification'] as $income_verification) {
       $sql = 'insert into form_sji_oth_intake_income_verification(pid, income_verification) values(?, ?)';
       $res = sqlStatement($sql, array($intake_id, $income_verification));
    }

    // add noncash assistance
    $sql = 'delete from form_sji_oth_intake_noncash_assistance where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    foreach ($submission['noncash_assistance'] as $noncash_assistance) {
       $sql = 'insert into form_sji_oth_intake_noncash_assistance(pid, noncash_assistance) values(?, ?)';
       $res = sqlStatement($sql, array($intake_id, $noncash_assistance));
    }

    // add priorities
    $sql = 'delete from form_sji_oth_intake_priorities where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    foreach ($submission['priorities'] as $priorities) {
       $sql = 'insert into form_sji_oth_intake_priorities(pid, priorities) values(?, ?)';
       $res = sqlStatement($sql, array($intake_id, $priorities));
    }

    if (isset($submission['sex'])) {
        $sql = 'update patient_data set sex = ? where pid = ?';
        sqlQuery($sql, array($submission['sex'], $pid));
    }

    if (isset($submission['ethnicity'])) {
        $sql = 'update patient_data set ethnicity = ? where pid = ?';
        sqlQuery($sql, array($submission['ethnicity'], $pid));
    }

    if (isset($submission['race'])) {
        $sql = 'update patient_data set race = ? where pid = ?';
        sqlQuery($sql, array($submission['race'], $pid));
    }

    if (isset($submission['street'])) {
        $sql = 'update patient_data set street = ? where pid = ?';
        sqlQuery($sql, array($submission['street'], $pid));
    }

    if (isset($submission['city'])) {
        $sql = 'update patient_data set city = ? where pid = ?';
        sqlQuery($sql, array($submission['city'], $pid));
    }

    if (isset($submission['state'])) {
        $sql = 'update patient_data set state = ? where pid = ?';
        sqlQuery($sql, array($submission['state'], $pid));
    }
    if (isset($submission['zip'])) {
        $sql = 'update patient_data set postal_code = ? where pid = ?';
        sqlQuery($sql, array($submission['zip'], $pid));
    }

    if (isset($submission['email'])) {
        $sql = 'update patient_data set email = ? where pid = ?';
        sqlQuery($sql, array($submission['email'], $pid));
    }

    if (isset($submission['phone_cell'])) {
        $sql = 'update patient_data set phone_cell = ? where pid = ?';
        sqlQuery($sql, array($submission['phone_cell'], $pid));
    }

    if (isset($submission['monthly_income'])) {
        $sql = 'update patient_data set monthly_income = ? where pid = ?';
        sqlQuery($sql, array($submission['monthly_income'], $pid));
    }

    if (isset($submission['phone_home'])) {
        $sql = 'update patient_data set phone_home = ? where pid = ?';
        sqlQuery($sql, array($submission['phone_home'], $pid));
    }


    // TODO: add/update ethnicity & race
    // TODO: add/update gender
    // TODO: add/update sexual identity

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
          sji_extendedIntakeCoreVariables($newid, $_POST);
       } else {
          $sql = 'UPDATE form_sji_intake_core_variables SET pronouns = ? where id = ?';
          $res = sqlStatement($sql, array($submission['pronouns'], $cv_id));
       }
    } 

}

$oth_intake_columns = array(
   'landlord_name', 'landlord_phone', 'landlord_address',
   'landlord_email', 'base_rent', 'split_rent', 'your_rent',
   'is_trans', 'requesting', 'eviction_risk', 'eviction_risk_description',
   'veteran', 'interested_in_sji'
);

$submission = array();
foreach ($oth_intake_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($_SESSION["encounter"], "OTH Rental Subsidy Applicartion", $newid, "sji_oth_intake", $pid, $userauthorized);
    sji_extendedOTHIntake($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedOTHIntake($_GET["id"], $_POST);
}

formHeader("Redirecting....");
formJump();
formFooter();
