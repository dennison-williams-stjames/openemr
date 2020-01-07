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
include_once(dirname(__FILE__) .'/../../globals.php');

function include_them() {
        global $srcdir;
	include_once("$srcdir/api.inc");
	include_once("$srcdir/forms.inc");
}
include_them();

/* 
 * name of the database table associated with this form
 */
$formdir = "sji_intake_core_variables";
$table_name = "form_".$formdir;

if (!isset($encounter) || $encounter == "") {
    $encounter = date("Ymd");
}

if (!$pid && isset($_SESSION['pid'])) {
    $pid = $_SESSION['pid'];
}

function get_cv_form_obj($pid, $id = 0) {
   $table_name = 'form_sji_intake_core_variables';

   // Look up the id if it is not sent in
   if (!isset($id) || $id === 0) {
	   $query = 'select form_id from forms where pid = ? and formdir="sji_intake_core_variables" order by date desc limit 0,1';
	   $result = sqlQuery($query, array($pid));
	   $id = $result['form_id'];
   }

   $obj = formFetch($table_name, $id);

   // Add on partners genders
   $query = "select partners_gender from form_sji_intake_core_variables_partners_gender where pid = ?";
   $res = sqlStatement($query, array($id));
   $partners = array();
   while ($row = sqlFetchArray($res)) {
      if (strlen($row['partners_gender'])) {
         $partners[] = $row['partners_gender'];
      }
   }
   if (sizeof($partners)) {
      $obj['partners_gender'] = $partners;
   }

   // Add on dob and zip
   $query = "select sex,race,ethnicity,DOB,postal_code from patient_data where pid = ? order by id desc limit 0,1";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      if (strlen($row['DOB'])) {
         $obj['dob'] = $row['DOB'];
      }

      if (strlen($row['postal_code'])) {
         $obj['zip'] = $row['postal_code'];
      }

      if (strlen($row['sex'])) {
         $obj['sex'] = $row['sex'];
      }

      if (strlen($row['race'])) {
         $obj['race'] = $row['race'];
      }

      if (strlen($row['ethnicity'])) {
         $obj['ethnicity'] = $row['ethnicity'];
      }
   }
   return $obj;
}

function sji_extendedIntakeCoreVariables($formid, $submission) {
    global $pid;

    sqlStatement("delete from form_sji_intake_core_variables_partners_gender where pid=?", array($formid));
    if (isset($submission['partners_gender'])) {
        // TODO: audit this
	foreach ($submission['partners_gender'] as $person) {
            sqlInsert("insert into form_sji_intake_core_variables_partners_gender(partners_gender, pid) values(?, ?)", array($person, $formid));
        }
    }

    if (isset($submission['sex'])) {
        $sql = 'update patient_data set sex = ? where pid = ?';
        sqlQuery($sql, array($submission['sex'], $pid));
    }

    if (isset($submission['dob'])) {
        $sql = 'update patient_data set DOB = ? where pid = ?';
        sqlQuery($sql, array($submission['dob'], $pid));
    }

    if (isset($submission['zip'])) {
        $sql = 'update patient_data set postal_code = ? where pid = ?';
        sqlQuery($sql, array($submission['zip'], $pid));
    }

    if (isset($submission['ethnicity'])) {
        $sql = 'update patient_data set ethnicity = ? where pid = ?';
        sqlQuery($sql, array($submission['ethnicity'], $pid));
    }

    if (isset($submission['race'])) {
        $sql = 'update patient_data set race = ? where pid = ?';
        sqlQuery($sql, array($submission['race'], $pid));
    }
}

// TODO: housing_situation is different than the homeless field in the 
// patient_data column.  This functionality should probably be
// merged
$intake_core_variable_columns = array(
   'housing_situation',
   'amab_4_amab', 'pronouns',
   'sexual_identity', 'sex_without_condom', 
   'injected_without_perscription', 'shared_needle',
   'active_drug_user', 'aliases'
);

