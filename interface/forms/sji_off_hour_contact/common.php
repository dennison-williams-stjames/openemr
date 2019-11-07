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

function get_oh_form_obj($pid, $id) {
   $table_name = 'form_sji_off_hour_contact';

   if (! empty($id) ) {
      $obj = formFetch($table_name, $id);
   } 

   $query = "select form_id from forms where encounter = ? order by id asc limit 1";
   $form = sqlQuery($query, array($_SESSION["encounter"]));
   $id = $form['form_id'];

   // Add on the visit reason
   $query = "select reason from form_encounter where id= ? order by id desc limit 1";
   $res = sqlStatement($query, array($id));
   $row = sqlFetchArray($res);
   if (isset($row)) {
      $obj['reason'] = $row['reason'];
   }

   // Add on participant gender, and pronouns
   $query = "select gender,pronouns,aliases from form_sji_intake_core_variables where pid = ? order by id DESC limit 1";
   $res = sqlStatement($query, array($pid));
   $partners = array();
   $row = sqlFetchArray($res);
   if (isset($row)) {
      $obj['gender'] = $row['gender'];
      $obj['pronouns'] = $row['pronouns'];
      $obj['aliases'] = $row['aliases'];
   }

   // Add on phone numbers and contact preferences
   $query = "select sex as 'Sex assigned at birth', phone_home, phone_biz, phone_cell, email, hipaa_voice, hipaa_message, hipaa_allowsms, hipaa_allowemail ".
      "from patient_data where pid = ? order by id desc limit 0,1";
   $res = sqlStatement($query, array($pid));
   if ($row = sqlFetchArray($res)) {
      $obj['phone_home'] = $row['phone_home'];      
      $obj['phone_biz'] = $row['phone_biz'];      
      $obj['phone_cell'] = $row['phone_cell'];      
      $obj['email'] = $row['email'];      
      $obj['hipaa_voice'] = $row['hipaa_voice'];      
      $obj['hipaa_message'] = $row['hipaa_message'];      
      $obj['hipaa_allowsms'] = $row['hipaa_allowsms'];      
      $obj['hipaa_allowemail'] = $row['hipaa_allowemail'];      
   }
   return $obj;
}

function sji_extendedOffHourContact($formid, $submission) {
    global $pid;

    sqlStatement("delete from form_sji_intake_core_variables_partners_gender where pid=?", array($formid));
    if (isset($submission['partners_gender'])) {
        // TODO: audit this
	foreach ($submission['partners_gender'] as $person) {
            sqlInsert("insert into form_sji_intake_core_variables_partners_gender(partners_gender, pid) values(?, ?)", array($person, $formid));
        }
    }

    // If we recieved a gender value we will need to update or insert a new core_variables row
    if (!empty($submission['pronouns'])) {
       $sql = "select count(*) as ct from form_sji_intake_core_variables where pid = ?";
       $query = sqlStatement($sql, array($pid));
       $row = sqlFetchArray($query);
       if (!empty($row['ct']) && $row['ct']>=1) {
          $sql = 'update patient_data set pronouns = ? where pid = ?';
          sqlQuery($sql, array($submission['pronouns'], $pid));
       } else {
          $newid = formSubmit("form_sji_intake_core_variables", $submission, '', $userauthorized);
          addForm($_SESSION['encounter'], "St. James Infirmary Intake - Core Variables", 
             $newid, "sji_intake_core_variables", $pid, $userauthorized);
       }
    }

    if (isset($submission['phone_biz'])) {
        $sql = 'update patient_data set phone_biz = ? where pid = ?';
        sqlQuery($sql, array($submission['phone_biz'], $pid));
    }

    if (isset($submission['phone_home'])) {
        $sql = 'update patient_data set phone_home= ? where pid = ?';
        sqlQuery($sql, array($submission['phone_home'], $pid));
    }

    if (isset($submission['phone_cell'])) {
        $sql = 'update patient_data set phone_cell = ? where pid = ?';
        sqlQuery($sql, array($submission['phone_cell'], $pid));
    }

    if (isset($submission['email'])) {
        $sql = 'update patient_data set email = ? where pid = ?';
        sqlQuery($sql, array($submission['email'], $pid));
    }

    if (isset($submission['hipaa_voice'])) {
        $sql = 'update patient_data set hipaa_voice = ? where pid = ?';
        sqlQuery($sql, array($submission['hipaa_voice'], $pid));
    }

    if (isset($submission['hipaa_message'])) {
        $sql = 'update patient_data set hipaa_message = ? where pid = ?';
        sqlQuery($sql, array($submission['hipaa_message'], $pid));
    }

    if (isset($submission['hipaa_allowsms'])) {
        $sql = 'update patient_data set hipaa_allowsms = ? where pid = ?';
        sqlQuery($sql, array($submission['hipaa_allowsms'], $pid));
    }

    if (isset($submission['hipaa_allowemail'])) {
        $sql = 'update patient_data set hipaa_allowemail = ? where pid = ?';
        sqlQuery($sql, array($submission['hipaa_allowemail'], $pid));
    }

    // TODO: add date time participant was contacted
}


$intake_core_variable_columns = array(
   'assesment_plan',
);

