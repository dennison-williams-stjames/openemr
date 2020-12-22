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


// FIXME: do not include an absolute path!
include_once("../../forms/sji_intake/common.php");
include_once("../../forms/sji_intake_core_variables/common.php");
include_once(dirname(__FILE__) .'/../../globals.php');

global $srcdir;
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

/* 
 * name of the database table associated with this form
 */
$formdir = "sji_oth_intake";
$table_name = "form_".$formdir;

if (!isset($encounter) || $encounter == "") {
    $encounter = date("Ymd");
}

if (!$pid && isset($_SESSION['pid'])) {
    $pid = $_SESSION['pid'];
}

function sji_extendedOTH_formFetch($id = 0) {
   global $pid, $table_name;

   // Look up the id if it is not sent in
   if (!isset($id) || $id === 0) {
	   $query = 'select form_id from forms '.
              'where pid = ? '.
              'and formdir="sji_oth_intake" '.
              'order by date desc limit 0,1';
	   $result = sqlQuery($query, array($pid));
	   $id = $result['form_id'];
   }

   $obj = formFetch($table_name, $id);

   // Add on columns from patient_data
   $query = "select sex,race,ethnicity,DOB,street,".
      "city,state,postal_code,email,phone_cell, concat(fname, ' ', lname) as Name,".
      "monthly_income ".
      "FROM patient_data WHERE pid = ? ".
      "ORDER BY id DESC LIMIT 1";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      $obj = array_merge($obj, $row);
   }

   // Add on columns from core_variables
   $query = "select pronouns,aliases,housing_situation,gender,sexual_identity ".
      "FROM form_sji_intake_core_variables WHERE pid = ? ".
      "ORDER BY date DESC LIMIT 1";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      $obj = array_merge($obj, $row);
   }

   // Add on income sources
   $query = "select income_source from form_sji_oth_intake_income_sources where pid=?";
   $res = sqlStatement($query, array($id));
   while ($row = sqlFetchArray($res)) {
      $obj['income_sources'][] = $row['income_source'];
   }

   // Add on income verification
   $query = "select income_verification from form_sji_oth_intake_income_verification where pid=?";
   $res = sqlStatement($query, array($id));
   while ($row = sqlFetchArray($res)) {
      $obj['income_verification'][] = $row['income_verification'];
   }

   // Add on income verification
   $query = "select noncash_assistance from form_sji_oth_intake_noncash_assistance where pid=?";
   $res = sqlStatement($query, array($id));
   while ($row = sqlFetchArray($res)) {
      $obj['noncash_assistance'][] = $row['noncash_assistance'];
   }

   // Add on priorities
   $query = "select priorities from form_sji_oth_intake_priorities where pid=?";
   $res = sqlStatement($query, array($id));
   while ($row = sqlFetchArray($res)) {
      $obj['priorities'][] = $row['priorities'];
   }

   return $obj;
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

    // Add income sources
    $sql = 'delete from form_sji_oth_intake_income_sources where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    if (isset($submission['income_sources'])) {
	    foreach ($submission['income_sources'] as $income_source) {
	       $sql = 'insert into form_sji_oth_intake_income_sources(pid, income_source) values(?, ?)';
	       $res = sqlStatement($sql, array($intake_id, $income_source));
	    }
    }

    // add income verification
    $sql = 'delete from form_sji_oth_intake_income_verification where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    if (isset($submission['income_verification'])) {
	    foreach ($submission['income_verification'] as $income_verification) {
	       $sql = 'insert into form_sji_oth_intake_income_verification(pid, income_verification) values(?, ?)';
	       $res = sqlStatement($sql, array($intake_id, $income_verification));
	    }
    }

    // add noncash assistance
    $sql = 'delete from form_sji_oth_intake_noncash_assistance where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    if (isset($submission['noncash_assistance'])) {
	    foreach ($submission['noncash_assistance'] as $noncash_assistance) {
	       $sql = 'insert into form_sji_oth_intake_noncash_assistance(pid, noncash_assistance) values(?, ?)';
	       $res = sqlStatement($sql, array($intake_id, $noncash_assistance));
	    }
    }

    // add priorities
    $sql = 'delete from form_sji_oth_intake_priorities where pid=?';
    $res = sqlStatement($sql, array($intake_id));
    if (isset($submission['priorities'])) {
	    foreach ($submission['priorities'] as $priorities) {
	       $sql = 'insert into form_sji_oth_intake_priorities(pid, priorities) values(?, ?)';
	       $res = sqlStatement($sql, array($intake_id, $priorities));
	    }
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
    if (isset($submission['postal_code'])) {
        $sql = 'update patient_data set postal_code = ? where pid = ?';
        sqlQuery($sql, array($submission['postal_code'], $pid));
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

    // TODO: add/update ethnicity & race
    // TODO: add/update gender
    // TODO: add/update sexual identity

    // Update pronouns if they have changed
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
       if (isset($submission['pronouns'])) {
          $sql = 'UPDATE form_sji_intake_core_variables SET pronouns = ? where id = ?';
          $res = sqlStatement($sql, array($submission['pronouns'], $cv_id));
       }

       if (isset($submission['aliases'])) {
          $sql = 'UPDATE form_sji_intake_core_variables SET aliases = ? where id = ?';
          $res = sqlStatement($sql, array($submission['aliases'], $cv_id));
       }

       if (isset($submission['housing_situation'])) {
          $sql = 'UPDATE form_sji_intake_core_variables SET housing_situation = ? where id = ?';
          $res = sqlStatement($sql, array($submission['housing_situation'], $cv_id));
       }

       if (isset($submission['gender'])) {
          $sql = 'UPDATE form_sji_intake_core_variables SET gender = ? where id = ?';
          $res = sqlStatement($sql, array($submission['gender'], $cv_id));
       }

       if (isset($submission['sexual_identity'])) {
          $sql = 'UPDATE form_sji_intake_core_variables SET sexual_identity = ? where id = ?';
          $res = sqlStatement($sql, array($submission['sexual_identity'], $cv_id));
       }
    }

}

$oth_intake_columns = array(
   'landlord_name', 'landlord_phone', 'landlord_address',
   'landlord_email', 'base_rent', 'split_rent', 'your_rent',
   'is_trans', 'requesting', 'eviction_risk', 'eviction_risk_description',
   'veteran', 'interested_in_sji', 'rental_agreement_name'
);

$oth_intake_binary_columns = array(
   'is_trans', 'eviction_risk', 'veteran', 'interested_in_sji'
);
