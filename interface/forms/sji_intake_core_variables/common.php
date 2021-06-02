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
	   $query = 'select form_id from forms '.
              'where pid = ? '.
              'and formdir="sji_intake_core_variables" '.
              'order by date desc limit 0,1';
	   $result = sqlQuery($query, array($pid));
	   $id = $result['form_id'];
   }

   $obj = formFetch($table_name, $id);

   // Add on columns from patient_data
   $query = "select sex,race,ethnicity,DOB as dob,street,".
      "city,state,postal_code as zip,email,phone_cell,".
      "phone_home,contact_relationship,phone_contact,".
      "hipaa_message,monthly_income,emergency_relationsh ".
      "FROM patient_data WHERE pid = ? ".
      "ORDER BY id DESC LIMIT 1";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      $obj = array_merge(is_array($obj) ? $obj : array(), $row);
   }

   // Add on insurance info
   $query = "select type,insurance_companies.name,policy_number,subscriber_lname,subscriber_fname ".
      "FROM insurance_data ".
      "LEFT JOIN insurance_companies ON (insurance_companies.id = insurance_data.provider)".
      "WHERE pid = ? ".
      "AND name IS NOT NULL";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      $key = $row['type'] .' insurance';
      $value = $row['name'] .
         ', policy number: '. $row['policy_number']. 
         ', subscriber name: '. $row['subscriber_fname'] .' '. $row['subscriber_lname'];
      $obj[$key] = $value;
   }
   return $obj;
}

function sji_extendedIntakeCoreVariables($formid, $submission) {
    global $pid;

    if (isset($submission['sex'])) {
        $sql = 'update patient_data set sex = ? where pid = ?';
        sqlQuery($sql, array($submission['sex'], $pid));
    }

    if (isset($submission['dob'])) {
        $sql = 'update patient_data set DOB = ? where pid = ?';
        sqlQuery($sql, array($submission['dob'], $pid));
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

    if (isset($submission['phone_contact'])) {
        $sql = 'update patient_data set phone_contact = ? where pid = ?';
        sqlQuery($sql, array($submission['phone_contact'], $pid));
    }

    if (isset($submission['contact_relationship'])) {
        $sql = 'update patient_data set contact_relationship = ? where pid = ?';
        sqlQuery($sql, array($submission['contact_relationship'], $pid));
    }

    if (isset($submission['hipaa_message'])) {
        $sql = 'update patient_data set hipaa_message = ? where pid = ?';
        sqlQuery($sql, array($submission['hipaa_message'], $pid));
    }

    if (isset($submission['emergency_relationsh'])) {
        $sql = 'update patient_data set emergency_relationsh = ? where pid = ?';
        sqlQuery($sql, array($submission['emergency_relationsh'], $pid));
    }

    // delete all of the associated insurance data and then add the data that
    // was sent in

    // Get a list of all insurance for the user and compare it against what
    // we were passed, if there is a match then update the insurance data
    // otherwise add it
    $sql = 'SELECT provider,type from insurance_data WHERE pid=? and provider != \'\'';
    $res = sqlStatement($sql, array($pid));
    $insurances = array();
    while ($insurance = sqlFetchArray($res)) {
       $insurances[$insurance['provider']] = $insurance['type'];
    }

    // TODO: delete all of the existing providers that were not re-submitted
    foreach ($insurances as $insurance => $type) {
       error_log("Checking to see if $insurance($type) was deleted from: ". print_r($submission['insurance'], 1));
       if (!isset($submission['insurance']) || array_search($insurance, $submission['insurance']) === FALSE) {
          $sql = 'DELETE from insurance_data WHERE pid=? and provider=?';
          error_log("deleting insurance_data for pid=$pid and provider=$insurance");
          sqlStatement($sql, array($pid, $insurance));
       }
    }

    if (isset($submission['insurance'])) {
    foreach ($submission['insurance'] as $insurance) {
       $name_key = 'iid'. $insurance .'_subscriber_name';
       $num_key = 'iid'. $insurance .'_subscriber_id';

       // Get the name associated with the insurance company if sent in
       $fname = '';
       $lname = '';
       if (isset($submission[$name_key])) {
          $names = array();
          preg_match('/^(\w+).*\b(\w+).*?$/', $submission[$name_key], $names);
          $fname = $names[1];
          $lname = $names[2];
       } 

       $number = '';
       if (isset($submission[$num_key])) {
          $number = $submission[$num_key];
       }

       if (array_search($insurance, array_keys($insurances)) === FALSE) {
          // Its new insurance data
          $type = 'tertiary';
          if (array_search('primary', array_values($insurances)) === FALSE) {
             $type = 'primary';
          } else if (array_search('secondary', array_values($insurances)) === FALSE) {
             $type = 'secondary';
          }
          error_log("inserting new insurance_data for pid=$pid and provider=$insurance");
          newInsuranceData($pid, $type, $insurance, '', '', $lname, '', $fname);
       } else {
          // update the insurance data
          if (isset($fname)) {
             error_log("updating insurance_data for pid=$pid and fname=$fname");
	     updateInsuranceData($insurance, array( 'subscriber_fname' => $fname));
          }

          if (isset($lname)) {
             error_log("updating insurance_data for pid=$pid and lname=$lname");
             updateInsuranceData($insurance, array( 'subscriber_lname' => $lname));
          }

          if (isset($number)) {
             error_log("updating insurance_data for pid=$pid and policy_number=$number");
             updateInsuranceData($insurance, array( 'policy_number' => $number));
          }

       } // else
    } // foreach submitted insurance provider

    } // insurance
}

// TODO: housing_situation is different than the homeless field in the 
// patient_data column.  This functionality should probably be
// merged
$intake_core_variable_columns = array(
   'housing_situation',
   'amab_4_amab', 'pronouns', 'disabled', 'mailing_list',
   'sexual_identity', 'aliases', 'dependents', 'hipaa_call_from_sji'
);

