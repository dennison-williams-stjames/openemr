<?php
require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

// Only clinicians should have access to this
// Clinicians at SJI are those allowed to see lab results
require_once("$srcdir/acl.inc");
if (!acl_check('patients','lab')) die("Access Denied.");

$medical_psychiatric_columns = array(
   'duration',
   'evaluate_manage_new',
   'evaluate_manage_established'
);

function sji_extendedMedicalPsychiatric_formFetch($formid) {
    $return = array();

    $res = sqlStatement(
       "select provider_type ".
       "from form_sji_medical_psychiatric_provider_type ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['provider_type'][] = $row['provider_type'];
    }

    $res = sqlStatement(
       "select icd_primary ".
       "from form_sji_medical_psychiatric_icd9_primary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd9_primary'][] = $row['icd_primary'];
    }

    $res = sqlStatement(
       "select icd_secondary ".
       "from form_sji_medical_psychiatric_icd9_secondary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd9_secondary'][] = $row['icd_secondary'];
    }

    $res = sqlStatement(
       "select icd_primary ".
       "from form_sji_medical_psychiatric_icd10_primary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd10_primary'][] = $row['icd_primary'];
    }

    $res = sqlStatement(
       "select icd_secondary ".
       "from form_sji_medical_psychiatric_icd10_secondary ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['icd10_secondary'][] = $row['icd_secondary'];
    }

    $res = sqlStatement(
       "select cpt_codes ".
       "from form_sji_medical_psychiatric_cpt_codes ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['cpt_codes'][] = $row['cpt_codes'];
    }

    $res = sqlStatement(
       "select range_codes ".
       "from form_sji_medical_psychiatric_range_codes ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['range_codes'][] = $row['range_codes'];
    }

    $res = sqlStatement(
       "select method_codes ".
       "from form_sji_medical_psychiatric_method_codes ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['methods_codes'][] = $row['method_codes'];
    }

    $res = sqlStatement(
       "select contraception_method ".
       "from form_sji_medical_psychiatric_contraception_method ".
       "where pid=?", array($formid));

    while ($row = sqlFetchArray($res)) {
       $return['contraception_method'][] = $row['contraception_method'];
    }

    return $return;
}

function sji_extendedMedicalPsychiatric($formid, $submission) {
    global $pid;

    sqlStatement("delete from form_sji_medical_psychiatric_provider_type where pid=?", array($formid));
    if (isset($submission['provider_type'])) {
        // TODO: audit this
	foreach ($submission['provider_type'] as $key => $icd9) {
            if (!strlen($icd9)) {
               continue;
            }
            sqlInsert("insert into form_sji_medical_psychiatric_provider_type(provider_type, pid) values(?, ?)", array($icd9, $formid));
        }
    }

    // There is no longer functionality for updating icd9 codes

    sqlStatement("delete from form_sji_medical_psychiatric_icd10_primary where pid=?", array($formid));
    if (isset($submission['icd10_primary'])) {
	foreach ($submission['icd10_primary'] as $key => $icd10) {
            if (!strlen($icd10)) {
               continue;
            }
            $sql = "insert into form_sji_medical_psychiatric_icd10_primary(icd_primary, pid) values(?, ?)";
            sqlInsert($sql, array($icd10, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_icd10_secondary where pid=?", array($formid));
    if (isset($submission['icd10_secondary'])) {
	foreach ($submission['icd10_secondary'] as $key => $icd10) {
            if (!strlen($icd10)) {
               continue;
            }
            sqlInsert("insert into form_sji_medical_psychiatric_icd10_secondary(icd_secondary, pid) values(?, ?)", array($icd10, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_cpt_codes where pid=?", array($formid));
    if (isset($submission['cpt_codes'])) {
        // TODO: audit this
	foreach ($submission['cpt_codes'] as $key => $cpt) {
            sqlInsert("insert into form_sji_medical_psychiatric_cpt_codes(cpt_codes, pid) values(?, ?)", 
               array($cpt, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_method_codes where pid=?", array($formid));
    if (isset($submission['methods_codes'])) {
        // TODO: audit this
	foreach ($submission['methods_codes'] as $key => $mc) {
            sqlInsert("insert into form_sji_medical_psychiatric_method_codes(method_codes, pid) values(?, ?)", 
               array($mc, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_range_codes where pid=?", array($formid));
    if (isset($submission['range_codes'])) {
        // TODO: audit this
	foreach ($submission['range_codes'] as $key => $mc) {
            sqlInsert("insert into form_sji_medical_psychiatric_range_codes(range_codes, pid) values(?, ?)", 
               array($mc, $formid));
        }
    }

    sqlStatement("delete from form_sji_medical_psychiatric_contraception_method where pid=?", array($formid));
    if (isset($submission['contraception_method'])) {
        // TODO: audit this
	foreach ($submission['contraception_method'] as $key => $mc) {
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

function getICD10PrimaryOptions() {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 102";
   $query = sqlStatement($sql);
   $debug = array();
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['icd10_primary']) &&
          array_search($icd9['code_text'], $obj['icd10_primary']) !== false
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
   $sql = "SELECT id,code_text,code FROM codes WHERE code_type = 102 limit 100";
   $query = sqlStatement($sql);
   while ($icd9 = sqlFetchArray($query)) {
      $output .= '<option value="'. $icd9['code_text'] .'" ';

      if (
          isset($obj['icd10_secondary']) &&
          array_search($icd9['code_text'], $obj['icd10_secondary']) !== false
      ) {
         $output .= 'selected="selected" ';
         $found = 1;
      }
      $output .= '>'. $icd9['code_text'] .'</option>';
   }
   return $output;
}

function getCPTCodes2() {
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

function getICD10Ajax($term) {
   global $obj;
   $output = "";
   $found = 0;
   $sql = "SELECT id,code_text,code ".
      "FROM codes ".
      "WHERE code_type = 102 ".
      "AND ( code like CONCAT('%', ?, '%') ".
      "OR code_text like CONCAT('%', ?, '%') ) ".
      "LIMIT 100";
   $query = sqlStatement($sql, array($term, $term));
   $return = array();
   while ($icd10 = sqlFetchArray($query)) {
      $ret['id'] = $icd10['code'] .': '. $icd10['code_text'];;
      $ret['text'] = $icd10['code'] .': '. $icd10['code_text'];
      $return[] = $ret;
   }

   return json_encode($return);
}

