<?php
require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$medical_columns = array(
   'duration',
);

function sji_extendedMedical_formFetch($formid = 0) {
    global $pid;
    $return = array();

    // get the values from the SOAP form
    $res = sqlStatement(
       "select subjective,objective,assessment,plan ".
       "from form_soap ".
       "left join forms on (forms.form_id = form_soap.id) ".
       "where form_soap.pid=? and forms.deleted=0 and forms.encounter=? ".
       "order by form_soap.id desc limit 1", array($pid, $encounter));

    while ($row = sqlFetchArray($res)) {
       $return = $row;
    }

    // get participant information
    $res = sqlStatement(
       "select pid, fname, lname ".
       "from patient_data ".
       "where pid=?", array($pid));

    while ($row = sqlFetchArray($res)) {
       $return['name'] = $row['fname'] ." ". $row['lname'];
       $return['pid'] = $row['pid'];
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

    $res = sqlStatement(
       "select icd_primary ".
       "from form_sji_medical_icd10_primary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd10_primary'][] = $row['icd_primary'];
    }

    $res = sqlStatement(
       "select icd_secondary ".
       "from form_sji_medical_icd10_secondary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd10_secondary'][] = $row['icd_secondary'];
    }

    $res = sqlStatement(
       "select cpt_codes ".
       "from form_sji_medical_cpt_codes ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['cpt_codes'][] = $row['cpt_codes'];
    }

    return $return;
}

function sji_extendedMedical($formid, $submission) {
    global $pid;
    $encounter = $_SESSION['encounter'];

    // add the soap record
    if (
       !empty($submission['subjective']) || 
       !empty($submission['objective']) || 
       !empty($submission['assessment']) || 
       !empty($submission['plan']) 
    ) {
       $soap = [
          'subjective' => $submission['subjective'],
          'objective' => $submission['objective'],
          'assessment' => $submission['assessment'],
          'plan' => $submission['plan'],
       ];

       $sql = "select form_id from forms where pid=? and encounter=? and ".
          "form_name='SOAP' and deleted=0 order by id desc limit 1";
       $row = sqlFetchArray(sqlStatement($sql, array($pid, $encounter)));
       if (!empty($row)) {
          // TODO: error checking
          formUpdate('form_soap', $soap, $row['form_id'], $_SESSION['userauthorized']);
       } else {
          $newid = formSubmit('form_vitals', $soap, $encounter, $_SESSION['userauthorized']);
          $id = addForm($encounter, 'SOAP', $newid, 'vitals', $pid, $_SESSION['userauthorized']);
       }
    }

    sqlStatement("delete from form_sji_medical_icd9_primary where pid=?", array($formid));
    if (isset($submission['icd9_primary'])) {
	foreach ($submission['icd9_primary'] as $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            $sql = "insert into form_sji_medical_icd9_primary(icd_primary, pid) values(?, ?)";
            sqlInsert($sql, array($icd9, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_icd9_secondary where pid=?", array($formid));
    if (isset($submission['icd9_secondary'])) {
	foreach ($submission['icd9_secondary'] as $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            sqlInsert("insert into form_sji_medical_icd9_secondary(icd_secondary, pid) values(?, ?)", array($icd9, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_cpt_codes where pid=?", array($formid));
    if (isset($submission['cpt_codes'])) {
        // TODO: audit this
	foreach ($submission['cpt_codes'] as $cpt) {
            sqlInsert("insert into form_sji_medical_cpt_codes(cpt_codes, pid) values(?, ?)", 
               array($cpt, $formid));
        }
    }

}

function getICD10PrimaryOptions() {
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

function getICD10SecondaryOptions() {
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
