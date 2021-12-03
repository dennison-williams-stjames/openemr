<?php
/**
 * Common script for the encounter form (new and view) scripts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("shared.php");

require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;
use OpenEMR\OeUI\OemrUI;
use OpenEMR\Services\UserService;

$facilityService = new FacilityService();

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';

    // get the encounter id from the forms table
    $forms = sqlQuery("SELECT * FROM forms WHERE form_id = ?", array($id));
    $encounter = $forms['encounter'];

    // using the encounter id get the associated data from the forms_encounter table
    $result = sqlQuery("SELECT * FROM form_encounter WHERE encounter = ?", array($encounter));

    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        echo "<body>\n<html>\n";
        echo "<p>" . xlt('You are not authorized to see this encounter.') . "</p>\n";
        echo "</body>\n</html>\n";
        exit();
    }

    // TODO FIXME: if there are more than 1 visit in a day this may be an issue
    $result2 = sqlQuery('select * from form_sji_visit where encounter = ? order by date desc limit 0,1', array($encounter));
    foreach ($visit_columns as $column) {
	$result[$column] = $result2[$column];
    }

    foreach ($visit_time_columns as $column) {
        // TODO: make sure we are not loosing anything between military and 12hr time here
        if (
           preg_match('/ (\d\d:\d\d)/', $result2[$column], $matches) &&
           $matches[1] != '00:00'
        ) { 
	   $result[$column] = $matches[1];
        }
    }

    // Add on medical_services list
    $query = "select medical_service from form_sji_visit_medical_services where pid=?";
    $res = sqlStatement($query, array($result2['id']));
    $medical_services = array();
    while ($row = sqlFetchArray($res)) {
       $medical_services[] = $row['medical_service'];
    }
    if (sizeof($medical_services)) {
       $result['medical_services'] = $medical_services;
    }

    // Add on initial_test_for_sti list
    $query = "select initial_test_for_sti from form_sji_visit_initial_test_for_sti where pid=?";
    $res = sqlStatement($query, array($result2['id']));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['initial_test_for_sti'];
    }
    if (sizeof($test_results)) {
       $result['initial_test_for_sti'] = $test_results;
    }

    // Add on test_results_for_sti list
    $query = "select test_results_for_sti from form_sji_visit_test_results_for_sti where pid=?";
    $res = sqlStatement($query, array($result2['id']));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['test_results_for_sti'];
    }
    if (sizeof($test_results)) {
       $result['test_results_for_sti'] = $test_results;
    }

    // Add on counseling_services list
    $query = "select counseling_services from form_sji_visit_counseling_services where pid=?";
    $res = sqlStatement($query, array($result2['id']));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['counseling_services'];
    }
    if (sizeof($test_results)) {
       $result['counseling_services'] = $test_results;
    }
}

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

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = ? AND enddate IS NULL " .
  "ORDER BY type, begdate", array($pid));
?>
<!DOCTYPE html>
<html>
<head>
<?php Header::setupHeader(['datetime-picker', 'common', 'jquery-ui', 'jquery-ui-darkness']);?>
<title><?php echo xlt('Patient Encounter'); ?></title>


<!-- validation library -->
<?php
//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">


    var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

    // Process click on issue title.
    function newissue() {
        dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 700, 535, '', '', {
            buttons: [
            {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
            ]
        });
        return false;
    }

     // callback from add_edit_issue.php:
     function refreshIssue(issue, title) {
      var s = document.forms[0]['issues[]'];
      s.options[s.options.length] = new Option(title, issue, true, true);
     }

    <?php
    //Gets validation rules from Page Validation list.
    //Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
    $collectthis = collectValidationPageRules("/interface/forms/newpatient/common.php");
    if (empty($collectthis)) {
         $collectthis = "undefined";
    } else {
         $collectthis = json_sanitize($collectthis["new_encounter"]["rules"]);
    }
    ?>
    var collectvalidation = <?php echo $collectthis; ?>;
    $(function(){
        window.saveClicked = function(event) {
            var submit = submitme(1, event, 'new-encounter-form', collectvalidation);
            if (submit) {
            top.restoreSession();
            $('#new-encounter-form').submit();
            }
        }

        $(".enc_issue").on('click', function(e) {
           e.preventDefault();e.stopPropagation();
           dlgopen('', '', 700, 650, '', '', {

               buttons: [{text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}],

               allowResize: true,
               allowDrag: true,
               dialogId: '',
               type: 'iframe',
               url: $(this).attr('href')
           });
        });

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = true; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });

    const isPosEnabled = "" + <?php echo js_escape($GLOBALS['set_pos_code_encounter']); ?>;

    function getPOS() {
        if (!isPosEnabled) {
            return false;
        }
        let facility = document.forms[0].facility_id.value;
        $.ajax({
            url: "./../../../library/ajax/facility_ajax_code.php",
            method: "GET",
            data: {
                mode: "get_pos",
                facility_id: facility,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        }).done(function (fid) {
            document.forms[0].pos_code.value = JSON.parse(fid);
        }).fail(function (xhr) {
            console.log('error', xhr);
        });
    }

    function newUserSelected() {
        let provider = document.getElementById('provider_id').value;
        $.ajax({
            url: "./../../../library/ajax/facility_ajax_code.php",
            method: "GET",
            data: {
                mode: "get_user_data",
                provider_id: provider,
                csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
            }
        }).done(function (data) {
            let rtn = JSON.parse(data);
            document.forms[0].facility_id.value = rtn[0];
            if (isPosEnabled) {
                document.forms[0].pos_code.value = rtn[1];
            }
            if (Number(rtn[2]) === 1) {
                document.forms[0]['billing_facility'].value = rtn[0];
            }
        }).fail(function (xhr) {
            console.log('error', xhr);
        });
    }

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
if ($viewmode) {
    $body_javascript = '';
    $heading_caption = xl('Participant Encounter Form');
} else {
    $body_javascript = 'onload="javascript:document.new_encounter.reason.focus();"';
    $heading_caption = xl('New Encounter Form');
}


if ($GLOBALS['enable_help'] == 1) {
    $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 2) {
    $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 0) {
     $help_icon = '';
}
?>
<?php
$arrOeUiSettings = array(
    'heading_title' => $heading_caption,
    'include_patient_name' => true,// use only in appropriate pages
    'expandable' => false,
    'expandable_files' => array(""),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "common_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);

$provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
if (!$viewmode) {
    $now = date('Y-m-d');
    $encnow = date('Y-m-d 00:00:00');
    $time = date("H:i:00");
    $q = "SELECT pc_aid, pc_facility, pc_billing_location, pc_catid, pc_startTime" .
        " FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=?" .
        " ORDER BY pc_startTime ASC";
    $q_events = sqlStatement($q, array($pid, $now));
    while ($override = sqlFetchArray($q_events)) {
        $q = "SELECT encounter FROM form_encounter" .
            " WHERE pid=? AND date=? AND provider_id=?";
        $q_enc = sqlQuery($q, array($pid, $encnow, $override['pc_aid']));
        if (is_array($override) && !$q_enc['encounter']) {
            $provider_id = $override['pc_aid'];
            $default_bill_fac_override = $override['pc_billing_location'];
            $default_fac_override = $override['pc_facility'];
            $default_catid_override = $override['pc_catid'];
        }
    }
}
?>
</head>
<body class="body_top" <?php echo $body_javascript;?>>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <!-- Required for the popup date selectors -->
                <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
                <div class="page-header clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'>
                    <?php if ($viewmode) { ?>
                        <input type=hidden name='mode' value='update'>
                        <input type=hidden name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>'>
                    <?php } else { ?>
                        <input type='hidden' name='mode' value='new'>
                    <?php } ?>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>

		    <!-- This is where the SJI new visit form additions get added -->

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
                            <button type="button" class="btn btn-default btn-save" onclick="top.restoreSession(); saveClicked(undefined);"><?php echo xlt('Save');?></button>
                            <?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
                                <button type="button" class="btn btn-link btn-cancel btn-separate-left" onClick="return cancelClickedOld()"><?php echo xlt('Cancel');?></button>
                            <?php } else { // not $viewmode ?>
                            <button class="btn btn-link btn-cancel btn-separate-left link_submit" onClick="return cancelClickedNew()">
                                    <?php echo xlt('Cancel'); ?></button>
                            <?php } // end not $viewmode ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div><!--End of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
<script language="javascript">
<?php
if ($GLOBALS['enable_group_therapy']) { ?>
/* hide / show group name input */
    var groupCategories = <?php echo json_encode($therapyGroupCategories); ?>;
    $('#pc_catid').on('change', function () {
        if(groupCategories.indexOf($(this).val()) > -1){
            $('#therapy_group_name').show();
        } else {
            $('#therapy_group_name').hide();
        }
    })

    function sel_group() {
      top.restoreSession();
      var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_group_popup.php';
      dlgopen(url, '_blank', 500, 400, '', '', {
          buttons: [
              {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
          ]
      });
    }
    // This is for callback by the find-group popup.
    function setgroup(gid, name) {
        var f = document.forms[0];
        f.form_group.value = name;
        f.form_gid.value = gid;
    }

    <?php
    if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
        $('#therapy_group_name').show();
        <?php
    } ?>
    <?php
} ?>

$(function (){
    $('#billing_facility').addClass('col-sm-9');
    //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
    // the relevant css file needs to be called i.e. jquery-ui-darkness - to get a black tooltip
    $('#sensitivity-tooltip').attr( "title", <?php echo xlj('If set as high will restrict visibility of encounter to users belonging to certain groups (AROs). By default - Physicians and Administrators'); ?> );
    $('#sensitivity-tooltip').tooltip();
    $('#onset-tooltip').attr( "title", <?php echo xlj('Hospital date needed for successful billing of hospital encounters'); ?> );
    $('#onset-tooltip').tooltip();
});
</script>
</html>
