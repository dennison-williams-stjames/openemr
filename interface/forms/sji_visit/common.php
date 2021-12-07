<?php
/**
 * Common script for the SJI visit form (new and view) scripts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

use OpenEMR\Core\Header;

include_once('../../globals.php');
require_once("shared.php");

if (!isset($srcdir)) {
	$srcdir = 'library';
}

if (!isset($rootdir)) {
	$rootdir = 'interface';
}

include_once("$srcdir/api.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

formHeader("Form: SJI Visit Intake");
$returnurl = 'encounter_top.php';
/* name of this form */
$form_name = "sji_visit";
$result = $obj = array();

if (!isset($pid)) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
if (isset($_REQUEST['id']) && $_REQUEST['id'] != "") {
   $result = $obj = array_merge(
      formFetch("form_".$form_name, $_REQUEST["id"]),
      sji_visit_formFetch($_REQUEST["id"]));

}

function sji_visit_formFetch($formid) {
	$return = array();

	if (!isset($formid)) {
		error_log(__FUNCTION__ .'($formid) : Did not receive $formid');
	}

	// Add on the existing form_sji_visit_counseling_services rows
	$query = "select counseling_services from form_sji_visit_counseling_services where pid=?";
	$res = sqlStatement($query, array($formid));
	$counseling_services = array();
	while ($row = sqlFetchArray($res)) {
	   $counseling_services[] = $row['counseling_services'];
	}
	if (sizeof($counseling_services)) {
	   $return['counseling_services'] = $counseling_services;
	}

	// Add on the existing form_sji_visit_initial_test_for_sti rows
	$query = "select initial_test_for_sti from form_sji_visit_initial_test_for_sti where pid=?";
	$res = sqlStatement($query, array($formid));
	$itfs = array();
	while ($row = sqlFetchArray($res)) {
	   $itfs [] = $row['initial_test_for_sti'];
	}
	if (sizeof($itfs)) {
	   $return['initial_test_for_sti'] = $itfs;
	}

	// Add on the existing form_sji_visit_medical_services rows
	$query = "select medical_services from form_sji_visit_medical_services where pid=?";
	$res = sqlStatement($query, array($formid));
	$ms = array();
	while ($row = sqlFetchArray($res)) {
	   $ms [] = $row['medical_services'];
	}
	if (sizeof($ms)) {
	   $return['medical_services'] = $ms;
	}

	// Add on the existing form_sji_visit_test_results_for_sti rows
	$query = "select test_results_for_sti from form_sji_visit_test_results_for_sti where pid=?";
	$res = sqlStatement($query, array($formid));
	$trfs = array();
	while ($row = sqlFetchArray($res)) {
	   $trfs[] = $row['test_results_for_sti'];
	}
	if (sizeof($trfs)) {
	   $return['test_results_for_sti'] = $trfs;
	}

	return $return;
}

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

function set_medical_services () {
   $services = array(
      'Routine exam',
      'Transgender care',
      'Vaccinations',
      'Wound absess',
   );
   $sql = 'INSERT INTO list_options(list_id, option_id, title) values("medical_services", ?, ?)';
   foreach ($services as $service) {
      sqlInsert($sql, array($service, $service));
   }
}

function set_initial_test_for_sti (){
   $services = array(
      'Chlamydia',
      'Gonorrhea',
      'Hepatitis C',
      'Syphilis',
   );
   $sql = 'INSERT INTO list_options(list_id, option_id, title) values("initial_test_for_sti", ?, ?)';
   foreach ($services as $service) {
      sqlInsert($sql, array($service, $service));
   }
}

function set_test_results_for_sti (){
   $services = array(
      'Chlamydia',
      'Gonorrhea',
      'Syphilis',
   );
   $sql = 'INSERT INTO list_options(list_id, option_id, title) values("test_results_for_sti", ?, ?)';
   foreach ($services as $service) {
      sqlInsert($sql, array($service, $service));
   }
}

function set_counseling_services (){
   $services = array(
      'Peer counseling',
      'Mental health services',
      'Case management',
      'Linkage to care',
      'Psychotherapy'
   );
   $sql = 'INSERT INTO list_options(list_id, option_id, title) values("counseling_services", ?, ?)';
   foreach ($services as $service) {
      sqlInsert($sql, array($service, $service));
   }
}

function setInternalList($list_id) {
   switch($list_id) {
      case 'medical_services':
         set_medical_services();
         break;
      case 'initial_test_for_sti':
         set_initial_test_for_sti();
         break;
      case 'test_results_for_sti':
         set_test_results_for_sti();
         break;
      case 'counseling_services':
         set_counseling_services();
         break;
      default:
         break;
   }
}

/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $result;

    $output = "";
    $selected = array();
    if (isset($result[$list_id])) {
           $selected = $result[$list_id];
    }
    $sql = "SELECT ".implode(',', $fieldnames).
       " FROM list_options where list_id = ? AND activity = 1 order by seq";
    $query = sqlStatement($sql, array($list_id));

    $list_options = sqlFetchArray($query);

    if (! $list_options ) {
       setInternalList($list_id);
       $query = sqlStatement($sql, array($list_id));
       $list_options = sqlFetchArray($query);
    }
    // Check to see if the custom list is populated and populate it if it is not
    while ($list_options) {
        $output .= '<option value="'. $list_options['option_id'] .'" ';

        if (isset($selected)) {

           if (is_array($selected) && in_array($list_options['option_id'], $selected)) {
              $output .= "selected ";
              $key = array_search($list_options['option_id'], $selected);
              if ($key >= 0) {
                 array_splice($selected, $key, 1);
              }
           } else if ($selected === $list_options['option_id']) {
              $output .= "selected ";
              unset($selected);
           }
        } 

        $output .= '>'. $list_options['title'] .'</option>';
        $list_options = sqlFetchArray($query);
    } 

    if (isset($selected)) {
       if (is_array($selected)) {
          foreach ($selected as $selection) {
             $output .= '<option value="'. $selection .'" selected>'. $selection .'</option>';
          }
       } else if (strlen($selected)) {
          $output .= '<option value="'. $selected .'" selected>'. $selected .'</option>';
       }
    }

    return $output;
}

?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker', 'common', 'jquery-ui', 'jquery-ui-darkness']);?>
<title><?php echo xlt('SJI Visit Intake Form'); ?></title>


<script language="JavaScript">


    $(function(){
        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    // Handler for Cancel clicked when creating a new encounter.
    // Show demographics or encounters list depending on what frame we're in.
    function cancelClickedNew() {
        if (top.tab_mode) {
            window.parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
        }
        var target = window;
        while (target != top) {
            if (target.name == 'RBot') {
                target.parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
                break;
            }
            else if (target.name == 'RTop') {
                target.parent.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php');
                break;
            }
            target = target.parent;
        }
        return false;
    }

    // Handler for cancel clicked when not creating a new encounter.
    // Just reload the view mode.
    function cancelClickedOld() {
        location.href = '<?php echo "$rootdir/patient_file/encounter/forms.php"; ?>';
        return false;
    }

</script>
<style>
@media only screen and (max-width: 1024px) {
    #visit-details [class*="col-"], #visit-issues [class*="col-"]{
    width: 100%;
    text-align: <?php echo ($_SESSION['language_direction'] == 'rtl') ? 'right ': 'left '?> !Important;
}
</style>
<?php
if (isset($viewmode)) {
    $body_javascript = '';
    $heading_caption = xl('Participant Visit Form');
} else {
    $body_javascript = 'onload="javascript:document.visit-form.symptoms.focus();"';
    $heading_caption = xl('New Participant Visit Form');
}


$help_icon = '';
?>
</head>
<body class="body_top" <?php echo $body_javascript;?>>
    <div id="container_div" class="container">

	<div class="row bg-primary">
		<div class="col-sm-12">
		<h2 class="text-center"><?php echo xlt('St. James Infirmary Visit Intake'); ?></h2>
		</div>
	</div>

        <div class="row">
            <div class="col-sm-12">
                <form id="visit-form" method='post' action="<?php echo $rootdir ?>/forms/sji_visit/save.php" name='new_visit'>
                    <?php if (isset($viewmode)) { ?>
                        <input type=hidden name='mode' value='update'>
                        <input type=hidden name='id' value='<?php echo (isset($_REQUEST["id"])) ? attr($_REQUEST["id"]) : '' ?>'>
                    <?php } else { ?>
                        <input type='hidden' name='mode' value='new'>
                    <?php } ?>

<fieldset>
<legend><?php echo xlt('Medical Care'); ?></legend>

<div id="medical-care" >

<div class="row form-group clear-fix">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="symptoms" class="control-label oe-text-to-right"><?php
echo xlt('Do you have a current illness or symptoms?');
?></label>
</div>
<div class="col-sm-4">
<input name="symptoms" id="symptoms" class="form-control" 

<?php if (isset($result['symptoms'])) {
   echo 'value="'. $result['symptoms'] .'"';
} else {
   echo 'data-placeholder="'. xlt('Describe the symptoms') .'"';
}
?>></div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="medical_service" class="control-label"><?php
echo xlt('What type of medical service are you looking for?');
?></label>
</div>
<div class="col-sm-4">
<select multiple=multiple name="medical_services[]" id="medical_services" class="form-control">
<?php 
// TODO: add this list
echo getListOptions('medical_services'); 
?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->


</div> <!-- medical-care -->
</fieldset>


<fieldset>
<legend><?php echo xlt('Harm Reduction Services'); ?></legend>

<div id="harm_reduction_services">

<div class="row form-group clear-fix">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="initial_test_for_hiv" class="control-label"><?php
echo xlt('Initial test for HIV?');
?></label>
</div>
<div class="col-sm-4">
<input type="hidden" name="initial_test_for_hiv" value=off></input>
<input type="checkbox" id="initial_test_for_hiv" name="initial_test_for_hiv" <?php 
if (
	isset($result['initial_test_for_hiv']) &&
	$result['initial_test_for_hiv']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="initial_test_for_sti" class="control-label"><?php
echo xlt('Initial test for STI?');
?></label>
</div>
<div class="col-sm-4">
<select multiple=multiple class="select2 form-control" id="initial_test_for_sti" name="initial_test_for_sti[]">
<?php echo getListOptions('initial_test_for_sti'); ?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="test_results_for_hiv" class="control-label"><?php
echo xlt('Are you expecting HIV test results?');
?></label>
</div>
<div class="col-sm-4">
<input type="hidden" name="test_resaults_for_hiv" value=off></input>
<input type="checkbox" id="test_results_for_hiv" name="test_results_for_hiv" <?php 
if (
	isset($result['test_results_for_hiv']) && 
	$result['test_results_for_hiv']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="last_tested_for_hiv" class="control-label"><?php
echo xlt('When were you last tested for HIV?');
?></label>
</div>
<div class="col-sm-4">
<input type="text" class="form-control col-sm-6 datepicker" id="last_tested_for_hiv" name="last_tested_for_hiv" <?php
if (
	isset($result['last_tested_for_hiv']) &&
	$result['last_tested_for_hiv']
) { echo 'value="'. $result['last_tested_for_hiv'] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="test_results_for_sti" class="control-label"><?php
echo xlt('Are you expecting STI test results?');
?></label>
</div>
<div class="col-sm-4">
<select class="select2 form-control" id="test_results_for_sti" name="test_results_for_sti[]" multiple=multiple>
<?php echo getListOptions('test_results_for_sti'); ?>
</select>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="last_tested_for_sti" class="col-sm4 control-label"><?php
echo xlt('When were you last tested for STIs?');
?></label></div>
<div class="col-sm-4">
<input type="text" class="datepicker form-control" id="last_tested_for_sti" name="last_tested_for_sti" <?php
if (
	isset($result['last_tested_for_sti']) &&
	$result['last_tested_for_sti']) { echo 'value="'. $result['last_tested_for_sti'] .'"'; }
?>></input>
</div> <!-- col-sm4 -->
</div> <!-- row -->

</div> <!-- harm_reduction_services -->
</fieldset>



<fieldset>
<legend><?php 
echo xlt('Counseling');
?></legend>
<div id="counseling">

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="counseling_services" class="control-label"><?php
echo xlt('What types of counseling services are you looking for?');
?></label></div>
<div class="col-sm-4">
<select multiple=multiple class="form-control col-sm4 select2" id="counseling_services" name="counseling_services[]">
<?php echo getListOptions('counseling_services'); ?>
</select>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="counselor_name" class="control-label"><?php
echo xlt('Name of mental health provider you have an appointment with?');
?></label></div>
<div class="col-sm-4">
<select class="form-control col-sm-4 select2" id="counselor_name" name="counselor_name"><?php

$returnval = array();
$sql = "select users.id as uid, fname, lname, list_options.title as title from users ".
	"left join list_options on users.physician_type = list_options.option_id ".
	"where users.authorized = 1 and " .
	"users.active = 1 and ".
	"users.username != '' and ".
	"list_options.list_id = 'physician_type' ".
	"order by fname,lname,title";

$output = '<option></option>';
$rez = sqlStatement($sql);
for ($iter=0; $row=sqlFetchArray($rez); $iter++) {
	$name = $row['fname'] .' '. $row['lname'];
	if (isset($row['title'])) {
		$name .= ' ('. $row['title'] .')';
	}
	$output .= '<option value="'. $row['uid'] .'" ';
	if (isset($result['counselor_name']) && $result['counselor_name'] === $row['uid']) {
		$output .= "selected ";
	}
        $output .= '>'. $name .'</option>';
}
print $output;

?></select>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

</div> <!-- counseling -->
</fieldset>


<fieldset>
<legend><?php 
echo xlt('Holistic');
?></legend>

<div id="holistic" >

<div class="row form-group">
<div class="col-sm-1"></div>

<div class="col-sm-4">
<label for="massage" class="control-label"><?php
echo xlt('Massage');
?></label></div>

<div class="col-sm-4">
<input type="hidden" name="massage" value=off></input>
<input type=checkbox id="massage" name="massage" <?php 
if (
	isset($result['massage']) &&
	$result['massage']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="massage_apt_time" class="control-label"><?php
echo xlt('Massage appointment time');
?></label></div>
<div class="col-sm-4">
<input type=text class="form-control col-sm-4 timepicker" id="massage" name="massage_apt_time" <?php
if (isset($result['massage_apt_time'])) { echo 'value="'. $result['massage_apt_time'] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="ear_accupuncture" class="control-label"><?php
echo xlt('Ear accupuncture');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="ear_accupuncture" value=off></input>
<input type=checkbox id="ear_accupuncture" name="ear_accupuncture" <?php 
if (
	isset($result['ear_accupuncture']) && 
	$result['ear_accupuncture']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="full_body_accupuncture" class="control-label"><?php
echo xlt('Full body accupuncture');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="full_body_accupuncture" value=off></input>
<input type=checkbox id="full_body_accupuncture" name="full_body_accupuncture" <?php 
if (
	isset($result['full_body_accupuncture']) &&
	$result['full_body_accupuncture']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="full_body_accupuncture_apt_time" class="control-label"><?php
echo xlt('Full body accupuncture appointment time');
?></label>
</div>
<div class="col-sm-4">
<input type=text class="form-control col-sm-4 timepicker" id="full_body_accupuncture_apt_time" name="full_body_accupuncture_apt_time" <?php
if (isset($result['full_body_accupuncture_apt_time'])) { echo 'value="'. $result['full_body_accupuncture_apt_time'] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="reiki" class="control-label"><?php
echo xlt('Reiki');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="reiki" value=off></input>
<input type=checkbox id="reiki" name="reiki" <?php 
if (
	isset($result['reiki']) &&
	$result['reiki']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="reiki_apt_time" class="control-label"><?php
echo xlt('Reiki appointment time');
?></label>
</div>
<div class="col-sm-4">
<input type=text class="form-control col-sm-4 timepicker" id="reiki_apt_time" name="reiki_apt_time" <?php
if (isset($result['reiki_apt_time'])) { echo 'value="'. $result['reiki_apt_time'] .'"'; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

</div> <!-- holistic -->
</fieldset>


<fieldset>
<legend><?php 
echo xlt('Other');
?></legend>

<div id="other" class="col-sm-12">

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="phone_visit" class="control-label"><?php
echo xlt('Phone visit');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="phone_visit" value=off></input>
<input type=checkbox id="phone_visit" name="phone_visit" <?php 
if (
	isset($result['phone_visit']) &&
	$result['phone_visit']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="phone_visit_specify" class="control-label"><?php
echo xlt('Phone visit specify');
?></label>
</div>
<div class="col-sm-4">
<input type=text class="form-control col-sm-4" id="phone_visit_specify" name="phone_visit_specify" <?php
if (isset($result['phone_visit_specify'])) { echo 'value="'. $result['phone_visit_specify'] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="talent_testing" class="control-label"><?php
echo xlt('Talent testing');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="talent_testing" value=off></input>
<input type=checkbox id="talent_testing" name="talent_testing" <?php 
if (
	isset($result['talent_testing']) && 
	$result['talent_testing']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="food" class="control-label"><?php
echo xlt('Food');
?></label></div>
<div class="col-sm-4">
<input type=hidden name="food" value=off></input>
<input type=checkbox id="food" name="food" <?php 
if (
	isset($result['food']) &&
	$result['food']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="clothing" class="control-label"><?php
echo xlt('Clothing');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="clothing" value=off></input>
<input type=checkbox id="clothing" name="clothing" <?php 
if (
	isset($result['clothing']) &&
	$result['clothing']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="condoms" class="control-label"><?php
echo xlt('Condoms lube');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="condoms" value=off></input>
<input type=checkbox id="condoms" name="condoms" <?php 
if (
	isset($result['condoms']) &&
	$result['condoms']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="nex_syringes" class="control-label"><?php
echo xlt('NEX syringes');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="nex_syringes" value=off></input>
<input type=checkbox id="nex_syringes" name="nex_syringes" <?php 
if (
	isset($result['nex_syringes']) &&
	$result['nex_syringes']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="hygiene_supplies" class="control-label"><?php
echo xlt('Hygiene supplies');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="hygiene_supplies" value=off></input>
<input type=checkbox id="hygiene_supplies" name="hygiene_supplies" <?php 
if (
	isset($result['hygiene_supplies']) &&
	$result['hygiene_supplies']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="referrals_to_other_services" class="control-label"><?php
echo xlt('Referrals to other services');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="referrals_to_other_services" value=off></input>
<input type=checkbox id="referrals_to_other_services" name="referrals_to_other_services" <?php 
if (
	isset($result['referrals_to_other_services']) &&
	$result['referrals_to_other_services']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="referrals_to_other_services_specify" class="control-label"><?php
echo xlt('Specify the referrals you are looking for');
?></label>
</div>
<div class="col-sm-4">
<input type=text class="form-control" id="referrals_to_other_services_specify" name="referrals_to_other_services_specify" <?php
if (isset($result['referrals_to_other_services_specify'])) { echo 'value="'. $result["referrals_to_other_services_specify"] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="other_harm_reduction_supplies" class="control-label"><?php
echo xlt('Other harm reduction supplies');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="other_harm_reduction_supplies" value=off></input>
<input type=checkbox id="other_harm_reduction_supplies" name="other_harm_reduction_supplies" <?php 
if (
	isset($result['other_harm_reduction_supplies']) &&
	$result['other_harm_reduction_supplies']
) { 
echo "checked"; } 
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="other_harm_reduction_supplies_specify" class="control-label"><?php
echo xlt('Specify the other harm reduction supplies you are looking for');
?></label>
</div>
<div class="col-sm-4">
<input type=text class="form-control" id="other_harm_reduction_supplies_specify" name="other_harm_reduction_supplies_specify" <?php
if (isset($result["other_harm_reduction_supplies_specify"])) { echo 'value="'. $result["other_harm_reduction_supplies_specify"] .'"'; }
?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-1"></div>
<div class="col-sm-4">
<label for="support_group" class="control-label"><?php
echo xlt('Support group');
?></label>
</div>
<div class="col-sm-4">
<input type=hidden name="support_group" value=off></input>
<input type=checkbox id="support_group" name="support_group" <?php 
if (
	isset($result['support_group']) &&
	$result['support_group']
) { echo "checked"; } ?>></input>
</div> <!-- col-sm-4 -->
</div> <!-- row -->

</div> <!-- other -->
</fieldset>

                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
			    <input 
				type="button" 
				class="btn btn-default btn-save" 
				value="<?php echo xlt('Save');?>">
                            <?php if (isset($viewmode) || empty($_REQUEST["autoloaded"])) { // not creating new encounter ?>
				<input type="button" 
				class="btn btn-link btn-cancel btn-separate-left" 
				onClick="return cancelClickedOld()"
				value="<?php echo xlt('Cancel');?>">
                            <?php } else { // not $viewmode ?>
			    <input 
				type="button"
				class="btn btn-link btn-cancel btn-separate-left link_submit" 
				onClick="return cancelClickedNew()"
				value="<?php echo xlt('Cancel'); ?>">
                            <?php } // end not $viewmode ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div><!--End of container div-->
</body>
<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".btn-save").click(function() { top.restoreSession(); $('#visit-form').submit(); });
    $(".btn-cancel").click(function() { parent.closeTab(window.name, false); });
});
</script>
</html>
