<?php
require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$triage_columns = array(
   'chief_complaint',
   'notes',
   'concerns',
   'services',
   'pharmacy',
   'contact_preferences'
);

function sji_extendedTriage_formFetch() {
    global $pid;
    $encounter = $_SESSION['encounter'];
    $return = array();

    // Get options from form_vitals for user: temperature, sistolic, distolic
    $res = sqlStatement(
       "select temperature,bps,bpd ".
       "from form_vitals ".
       "left join forms on (forms.form_id = form_vitals.id) ".
       "where form_vitals.pid=? and forms.deleted=0 ".
       "order by form_vitals.id desc limit 1", array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['temperature'] = $row['temperature'];
       $return['blood_pressure'] = $row['bps'] .'/'. $row['bpd'];
    }

    // get participant information
    $res = sqlStatement(
       "select pid, fname, lname, email, phone_home, phone_cell, hipaa_allowemail, hipaa_allowsms, hipaa_message, hipaa_voice ".
       "from patient_data ".
       "where pid=?", array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['name'] = $row['fname'] ." ". $row['lname'];
       $return['email'] = $row['email'];
       $return['phone_home'] = $row['phone_home'];
       $return['phone_cell'] = $row['phone_cell'];
       $return['hipaa_allowemail'] = $row['hipaa_allowemail'];
       $return['hipaa_allowsms'] = $row['hipaa_allowsms'];
       $return['hipaa_message'] = $row['hipaa_message'];
       $return['hipaa_voice'] = $row['hipaa_voice'];
       $return['pid'] = $row['pid'];
    }

    // set contact preferences based on the hipaa setting
    if ($return['hipaa_allowemail'] == 'YES') {
       $return['Preferred contact (email)'] = $return['email'];
    }

    if ($return['hipaa_allowsms'] == 'YES') {
       $return['Preferred contact (text)'] = $return['phone_cell'];
    }

    if ($return['hipaa_voice'] == 'YES') {
       $return['Preferred contact (phone call)'] = $return['phone_home'];
    }

    // get a few items from our core variables
    $sql =
       "select gender, pronouns, aliases ".
       "from form_sji_intake_core_variables ".
       "where pid=? order by id desc limit 1";

    $res = sqlStatement($sql, array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['gender'] = $row['gender'];
       $return['pronouns'] = $row['pronouns'];
       $return['aliases'] = $row['aliases'];
    }

    return $return;
}

function sji_extendedTriage($formid, $submission) {
    global $pid;
    $encounter = $_SESSION['encounter'];

    // If we were passed in vitals we need to calculate a few values and either
    // update an existing vitals record, or add a new one
    if (!empty($submission['blood_pressure'])) {
       // parse distollic and sistolic values from the submission
       // TODO: how do we throw an error?
       preg_match(':(\d+)/(\d+):', $submission['blood_pressure'], $matches, PREG_OFFSET_CAPTURE);
       $submission['bps'] = $matches[1][0];
       $submission['bpd'] = $matches[2][0];
    }

    if (!empty($submission['temperature']) || (!empty($submission['bps']) && !empty($submission['bpd']))) {
       $vitals = [
          'temperature' => $submission['temperature'],
          'bps' => $submission['bps'],
          'bpd' => $submission['bpd'],
       ];

       $sql = "select form_id from forms where pid=? and encounter=? and form_name='Vitals' and deleted=0 order by id desc limit 1";
       $row = sqlFetchArray(sqlStatement($sql, array($pid, $encounter)));
       if (!empty($row)) {
          // TODO: error checking
          formUpdate('form_vitals', $vitals, $row['form_id'], $_SESSION['userauthorized']);
       } else {
          $newid = formSubmit('form_vitals', $vitals, $encounter, $_SESSION['userauthorized']);
          $id = addForm($encounter, 'Vitals', $newid, 'vitals', $pid, $_SESSION['userauthorized']);
       }
    }


}

