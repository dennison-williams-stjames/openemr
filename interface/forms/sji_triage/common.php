<?php
require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$triage_columns = array(
   'chief_complaint',
   'notes',
   'concerns',
   'services'
);

function sji_extendedTriage_formFetch($formid) {
    global $pid;
    $encounter = $_SESSION['encounter'];
    $return = array();

    // Get options from form_vitals for user: temperature, sistolic, distolic
    $res = sqlStatement(
       "select temperature,bps,bpd ".
       "from form_vitals ".
       "where pid=? ".
       "order by id asc limit 1", array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['temperature'] = $row['temperature'];
       $return['bps'] = $row['bps'];
       $return['bpd'] = $row['bpd'];
    }

    // get participant information
    $res = sqlStatement(
       "select fname, lname, email, hipaa_allowemail, hippa_allowsms, hippa_message, hippa_voice ".
       "from patient_data ".
       "where pid=?", array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['name'] = $row['fname'] ." ". $row['lname'];
       $return['email'] = $row['email'];
       $return['hipaa_allowemail'] = $row['hipaa_allowemail'];
       $return['hipaa_allowsms'] = $row['hipaa_allowsms'];
       $return['hipaa_message'] = $row['hipaa_message'];
       $return['hipaa_voice'] = $row['hipaa_voice'];
    }

    // get a few items from our core variables
    $res = sqlStatement(
       "select gender, pronouns, aliases ".
       "from form_sji_intake_core_variables ".
       "where pid=? order by id asc limit 1", array($pid));

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
    if (!empty($submission['temperature']) || !empty($submission['bps']) || !empty($submission['bpd'])) {
       $sql = "select form_id from forms where pid=? and encounter=? and form_name='Vitals' order by id desc limit 1";
       $row = sqlFetchArray(sqlStatement($sql, array($pid, $encounter)));
       if (!empty($row)) {
          $sql = "insert into form_vitals(temperature, bps, bpd, pid, temp_method) values(?, ?, ?, $pid, 'Oral')";
          // TODO: error checking
          sqlStatement($sql, array($submission['temperature'], $submission['bps'], $submission['bpd']));
       } else {
          // TODO: add new vital forms
       }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_provider_type where pid=?", array($formid));
    if (isset($submission['provider_type'])) {
        // TODO: audit this
	foreach ($submission['provider_type'] as $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            sqlInsert("insert into form_sji_medical_psychiatric_provider_type(provider_type, pid) values(?, ?)", array($icd9, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_icd9_primary where pid=?", array($formid));
    if (isset($submission['icd9_primary'])) {
	foreach ($submission['icd9_primary'] as $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            $sql = "insert into form_sji_medical_psychiatric_icd9_primary(icd_primary, pid) values(?, ?)";
            sqlInsert($sql, array($icd9, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_icd9_secondary where pid=?", array($formid));
    if (isset($submission['icd9_secondary'])) {
	foreach ($submission['icd9_secondary'] as $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            sqlInsert("insert into form_sji_medical_psychiatric_icd9_secondary(icd_secondary, pid) values(?, ?)", array($icd9, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_cpt_codes where pid=?", array($formid));
    if (isset($submission['cpt_codes'])) {
        // TODO: audit this
	foreach ($submission['cpt_codes'] as $cpt) {
            sqlInsert("insert into form_sji_medical_psychiatric_cpt_codes(cpt_codes, pid) values(?, ?)", 
               array($cpt, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_method_codes where pid=?", array($formid));
    if (isset($submission['methods_codes'])) {
        // TODO: audit this
	foreach ($submission['methods_codes'] as $mc) {
            sqlInsert("insert into form_sji_medical_psychiatric_method_codes(method_codes, pid) values(?, ?)", 
               array($mc, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_range_codes where pid=?", array($formid));
    if (isset($submission['range_codes'])) {
        // TODO: audit this
	foreach ($submission['range_codes'] as $mc) {
            sqlInsert("insert into form_sji_medical_psychiatric_range_codes(range_codes, pid) values(?, ?)", 
               array($mc, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_contraception_method where pid=?", array($formid));
    if (isset($submission['contraception_method'])) {
        // TODO: audit this
	foreach ($submission['contraception_method'] as $mc) {
            sqlInsert("insert into form_sji_medical_psychiatric_contraception_method(contraception_method, pid) values(?, ?)", 
               array($mc, $formid));
        }
    }

}

function getICD9PrimaryOptions() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 2";
   $query = sqlStatement($sql);
   $debug = array();
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      //$debug[] = $icd9['code_text'];

      if (
          isset($obj['icd9_primary']) &&
          array_search($icd9['code_text'], $obj['icd9_primary']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      } 
      $output .= '>'. $icd9['code_text'] .'</option>';
   }

   return $output;
}

function getICD9SecondaryOptions() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 2";
   $query = sqlStatement($sql);
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['icd9_secondary']) &&
          array_search($icd9['code_text'], $obj['icd9_secondary']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      } 
      $output .= '>'. $icd9['code_text'] .'</option>';
   }
   return $output;
}

function getCPTCodes() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 1";
   $query = sqlStatement($sql);
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['cpt_codes']) &&
          array_search($icd9['code_text'], $obj['cpt_codes']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      } 
      $output .= '>'. $icd9['code_text'] .'</option>';
   }
   return $output;
}

//TODO: make sure this works with multiple selected values
function getMethodsCodes() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 113";
   $query = sqlStatement($sql);
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['methods_codes']) &&
          array_search($icd9['code_text'], $obj['methods_codes']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      } 
      $output .= '>'. $icd9['code_text'] .'</option>';
   }
   return $output;
}

function getRangeCodes() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 114";
   $query = sqlStatement($sql);
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['range_codes']) &&
          array_search($icd9['code_text'], $obj['range_codes']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      } 
      $output .= '>'. $icd9['code_text'] .'</option>';
   }
   return $output;
}
