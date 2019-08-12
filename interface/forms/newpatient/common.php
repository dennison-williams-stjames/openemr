<?php
/**
 * Common script for the encounter form (new and view) scripts.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

require_once("shared.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

$result = array();
if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_encounter WHERE id = ?", array($id));
    $encounter = $result['encounter'];
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
    $res = sqlStatement($query, array($id));
    $medical_services = array();
    while ($row = sqlFetchArray($res)) {
       $medical_services[] = $row['medical_service'];
    }
    if (sizeof($medical_services)) {
       $result['medical_services'] = $medical_services;
    }

    // Add on initial_test_for_sti list
    $query = "select initial_test_for_sti from form_sji_visit_initial_test_for_sti where pid=?";
    $res = sqlStatement($query, array($id));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['initial_test_for_sti'];
    }
    if (sizeof($test_results)) {
       $result['initial_test_for_sti'] = $test_results;
    }

    // Add on test_results_for_sti list
    $query = "select test_results_for_sti from form_sji_visit_test_results_for_sti where pid=?";
    $res = sqlStatement($query, array($id));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['test_results_for_sti'];
    }
    if (sizeof($test_results)) {
       $result['test_results_for_sti'] = $test_results;
    }

    // Add on counseling_services list
    $query = "select counseling_services from form_sji_visit_counseling_services where pid=?";
    $res = sqlStatement($query, array($id));
    $test_results = array();
    while ($row = sqlFetchArray($res)) {
       $test_results[] = $row['counseling_services'];
    }
    if (sizeof($test_results)) {
       $result['counseling_services'] = $test_results;
    }
}

// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b)
{
    return ($a[2] < $b[2]) ? -1 : 1;
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
      'Hepatitis A',
      'Hepatitis B',
      'Hepatitis C',
      'Herpes',
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
      'Hepatitis A',
      'Hepatitis B',
      'Hepatitis C',
      'Herpes',
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
      'Mental health psychiatrist',
      'Case management',
      'Linkage to care',
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

<title><?php echo xlt('Participant Encounter'); ?></title>
    <?php Header::setupHeader(['jquery-ui', 'datetime-picker', 'bootstrap', 'select2']); ?>

<!-- validation library -->
<?php
//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 700, 535, '', '', {
      buttons: [
          {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
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
         $collectthis = $collectthis["new_encounter"]["rules"];
    }
    ?>
 var collectvalidation = <?php echo($collectthis); ?>;
 $(document).ready(function(){
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
           buttons: [
               {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
           ],
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

   $('.timepicker').datetimepicker({
        datepicker: false,
        format: 'H:i'
   });

   $("div#form").accordion({
      icons: { "header": "ui-icon-plus", "activeHeader": "ui-icon-minus" },
      header: "h3.header",
      heightStyle: "content",
   });


 });

function bill_loc(){
	var pid=<?php echo attr($pid);?>;
	var dte=document.getElementById('form_date').value;
	var facility=document.forms[0].facility_id.value;
	ajax_bill_loc(pid,dte,facility);
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
.ui-icon {
   z-index: 2;
   top: 26px;
}
</style>

</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.reason.focus();">
<?php } ?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<div class="container">
<form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php<?php
   if (isset($_GET['id'])) {
      echo "?mode=update&id=". attr($_GET["id"]);
   } else {
      echo "?mode=new";
   }
?>" name='new_encounter'>

<div class="row">
<div class="col-sm-3">
<?php if ($viewmode) { ?>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>'>
<span class=title><?php echo xlt('Participant Visit Form'); ?></span>
<?php } else { ?>
<input type='hidden' name='mode' value='new'>
<span class='title'><?php echo xlt('New Participant Visit Form'); ?></span>
<?php } ?>
</div> <!-- col-sm-3 -->

<div class="col-sm-2">
      <a href="javascript:saveClicked(undefined);" class="css_button link_submit"><span><?php echo xlt('Save'); ?></span></a>
<?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
      <a href="" class="css_button link_submit" onClick="return cancelClickedOld()">
      <span><?php echo xlt('Cancel'); ?></span></a>
    <?php } else { // not $viewmode ?>
      <a href="" class="css_button link_submit" onClick="return cancelClickedNew()">
      <span><?php echo xlt('Cancel'); ?></span></a>
    <?php } // end not $viewmode ?>

</div> <!-- col-sm-2 -->

<div class="col-sm-7"></div> <!-- col-sm-9 -->
</div> <!-- row -->

<div class="row form-group">

<div class="col-sm-3" id=comments>

<div class="row form-group">
<label for=reason class="col-sm-12 control-label bg-primary"><?php echo xlt('Reason for Visit')?></label>
</div>
<div class="row">
<div class="col-sm-12">
<textarea name='reason' class="sm-textarea form-control" rows=6 id=reason
    ><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
</div> <!-- col-sm-12 -->
</div> <!-- row -->
</div> <!-- comments -->

<div class="col-sm-6" id=form>

<h3 class="row header">
<div class="col-sm-12 bg-primary">
<span class="ui-icon-plus"></span><?php 
echo xlt('General');
?></div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id=general>
<div class="row form-group">

<div class="col-sm-6"><label for="form_date" class="control-label"><?php echo xlt('Date of Service:'); ?></label></div>
<div class="col-sm-6">
      <input type='text' class='datepicker input-sm form-control' name='form_date' id='form_date'
       value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : oeFormatShortDate(date('Y-m-d')); ?>'
       title='<?php echo xla('Date of service'); ?>' />
</div> <!-- col -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-6"><label for="pc_catid" class="control-label"><?php echo xlt('Visit Category:'); ?></label></div>
<div class="col-sm-6">
      <select class="form-control" name='pc_catid' id='pc_catid'>
          <option value='_blank'>-- <?php echo xlt('Select One'); ?> --</option>
            <?php
            //Bring only patient ang group categories
            $visitSQL = "SELECT pc_catid, pc_catname, pc_cattype 
                       FROM openemr_postcalendar_categories
                       WHERE pc_active = 1 and pc_cattype IN (0,3) and pc_constant_id  != 'no_show' ORDER BY pc_seq";

            $visitResult = sqlStatement($visitSQL);
            $therapyGroupCategories = [];

            while ($row = sqlFetchArray($visitResult)) {
                $catId = $row['pc_catid'];
                $name = $row['pc_catname'];

                if ($row['pc_cattype'] == 3) {
                    $therapyGroupCategories[] = $catId;
                }

                if ($catId === "_blank") {
                    continue;
                }

                if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                    continue;
                }

                // Fetch acl for category of given encounter. Only if has write auth for a category, then can create an encounter of that category.
                $postCalendarCategoryACO = fetchPostCalendarCategoryACO($catId);
                if ($postCalendarCategoryACO) {
                    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
                } else { // if no aco is set for category
                    $authPostCalendarCategoryWrite = true;
                }

                //if no permission for category write, don't show in drop-down
                if (!$authPostCalendarCategoryWrite) {
                    continue;
                }

                $optionStr = '<option value="%pc_catid%" %selected%>%pc_catname%</option>';
                $optionStr = str_replace("%pc_catid%", attr($catId), $optionStr);
                $optionStr = str_replace("%pc_catname%", text(xl_appt_category($name)), $optionStr);
                if ($viewmode) {
                    $selected = ($result['pc_catid'] == $catId) ? " selected" : "";
                } else {
                    $selected = ($GLOBALS['default_visit_category'] == $catId) ? " selected" : "";
                }

                  $optionStr = str_replace("%selected%", $selected, $optionStr);
                  echo $optionStr;
            }
            ?>
      </select>
</div> <!-- col -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-6"><label for='facility_id' class="control-label"><?php echo xlt('Facility'); ?>:</label></div>
<div class="col-sm-6">
<select name='facility_id' id='facility_id' class='form-control' onChange="bill_loc()">
    <?php
    if ($viewmode) {
	$def_facility = $result['facility_id'];
    } else {
        // TODO: is there a better way than hard coding this for SJI?
	$def_facility = 3;
    }
    $posCode = '';
    $facilities = $facilityService->getAllServiceLocations();
    if ($facilities) {
	foreach ($facilities as $iter) { ?>
    <option value="<?php echo attr($iter['id']); ?>"
	<?php
	if ($def_facility == $iter['id']) {
	    if (!$viewmode) {
		$posCode = $iter['pos_code'];
	    }
	    echo "selected";
	}?>><?php echo text($iter['name']); ?>
    </option>
    <?php
	}
    }
    ?>
</select>
</div> <!-- col -->
</div> <!-- row -->
</div> <!-- row#general -->

<h3 class="row header">
<div class="col-sm-12 bg-primary"><?php 
echo xlt('Medical Care');
?></div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id="medical-care" >

<div class="row form-group">
<div class="col-sm-6">
<label for="symptoms" class="control-label"><?php
echo xlt('Do you have a current illness or symptoms?');
?></label>
</div>
<div class="col-sm-6">
<input name="symptoms" id="symptoms" class="form-control" 

<?php if (isset($result['symptoms'])) {
   echo 'value="'. $result['symptoms'] .'"';
} else {
   echo 'data-placeholder="'. xlt('Describe the symptoms') .'"';
}
?>></div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-6">
<label for="medical_service" class="control-label"><?php
echo xlt('What type of medical service are you looking for?');
?></label>
</div>
<div class="col-sm-6">
<select multiple=multiple name="medical_services[]" id="medical_services" class="form-control">
<?php 
// TODO: add this list
echo getListOptions('medical_services'); 
?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->


</div> <!-- medical-care -->


<h3 class="row header">
<div class="col-sm-12 bg-primary"><?php 
echo xlt('Harm Reduction Services');
?></div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id="harm_reduction_services">

<div class="row form-group">
<div class="col-sm-6">
<label for="initial_test_for_hiv" class="control-label"><?php
echo xlt('Initial test for HIV?');
?></label>
</div>
<div class="col-sm-6">
<input type="hidden" name="initial_test_for_hiv" value=off></input>
<input type="checkbox" id="initial_test_for_hiv" name="initial_test_for_hiv" <?php 
if (
	isset($result['initial_test_for_hiv']) &&
	$result['initial_test_for_hiv']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-6">
<label for="initial_test_for_sti" class="control-label"><?php
echo xlt('Initial test for STI?');
?></label>
</div>
<div class="col-sm-6">
<select multiple=multiple class="col-sm-6 select2 form-control" id="initial_test_for_sti" name="initial_test_for_sti[]">
<?php echo getListOptions('initial_test_for_sti'); ?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="test_results_for_hiv" class="col-sm-6 control-label"><?php
echo xlt('Are you expecting HIV test results?');
?></label>
<div class="col-sm-6">
<input type="hidden" name="test_resaults_for_hiv" value=off></input>
<input type="checkbox" id="test_results_for_hiv" name="test_results_for_hiv" <?php 
if (
	isset($result['test_results_for_hiv']) && 
	$result['test_results_for_hiv']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="last_tested_for_hiv" class="col-sm-6 control-label"><?php
echo xlt('When were you last tested for HIV?');
?></label>
<div class="col-sm-6">
<input type="text" class="form-control col-sm-6 datepicker" id="last_tested_for_hiv" name="last_tested_for_hiv" <?php
if (
	isset($result['last_tested_for_hiv']) &&
	$result['last_tested_for_hiv']
) { echo 'value="'. $result['last_tested_for_hiv'] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="test_results_for_sti" class="col-sm-6 control-label"><?php
echo xlt('Are you expecting STI test results?');
?></label>
<div class="col-sm-6">
<select class="select2 form-control" id="test_results_for_sti" name="test_results_for_sti[]" multiple=multiple>
<?php echo getListOptions('test_results_for_sti'); ?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="last_tested_for_sti" class="col-sm-6 control-label"><?php
echo xlt('When were you last tested for STIs?');
?></label>
<div class="col-sm-6">
<input type="text" class="col-sm-6 datepicker form-control" id="last_tested_for_sti" name="last_tested_for_sti" <?php
if (
	isset($result['last_tested_for_sti']) &&
	$result['last_tested_for_sti']) { echo 'value="'. $result['last_tested_for_sti'] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

</div> <!-- harm_reduction_services -->



<h3 class="row header">
<div class="col-sm-12 bg-primary"><?php 
echo xlt('Counseling');
?></div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id="counseling">

<div class="row form-group">
<label for="counseling_services" class="col-sm-6 control-label"><?php
echo xlt('What types of counseling services are you looking for?');
?></label>
<div class="col-sm-6">
<select multiple=multiple class="form-control col-sm-6 select2" id="counseling_services" name="counseling_services[]">
<?php echo getListOptions('counseling_services'); ?>
</select>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="counselor_name" class="col-sm-6 control-label"><?php
echo xlt('Name of mental health psychiatrist you have an appointment with?');
?></label>
<div class="col-sm-6">
<input type=text class="col-sm-6 form-control" id="counselor_name" name="counselor_name" <?php 
if (
	isset($result['counselor_name']) &&
	$result['counselor_name']
) { echo 'value="'. $result['counselor_name'] .'"'; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

</div> <!-- counseling -->


<h3 class="row header">
<div class="col-sm-12 bg-primary"><?php 
echo xlt('Holistic');
?></div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id="holistic" >

<div class="row form-group">
<label for="massage" class="col-sm-6 control-label"><?php
echo xlt('Massage');
?></label>
<div class="col-sm-6">
<input type="hidden" name="massage" value=off></input>
<input type=checkbox id="massage" name="massage" <?php 
if (
	isset($result['massage']) &&
	$result['massage']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="massage_apt_time" class="col-sm-6 control-label"><?php
echo xlt('Massage appointment time');
?></label>
<div class="col-sm-6">
<input type=text class="form-control col-sm-6 timepicker" id="massage" name="massage_apt_time" <?php
if (isset($result['massage_apt_time'])) { echo 'value="'. $result['massage_apt_time'] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="ear_accupuncture" class="col-sm-6 control-label"><?php
echo xlt('Ear accupuncture');
?></label>
<div class="col-sm-6">
<input type=hidden name="ear_accupuncture" value=off></input>
<input type=checkbox id="ear_accupuncture" name="ear_accupuncture" <?php 
if (
	isset($result['ear_accupuncture']) && 
	$result['ear_accupuncture']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="full_body_accupuncture" class="col-sm-6 control-label"><?php
echo xlt('Full body accupuncture');
?></label>
<div class="col-sm-6">
<input type=hidden name="full_body_accupuncture" value=off></input>
<input type=checkbox id="full_body_accupuncture" name="full_body_accupuncture" <?php 
if (
	isset($result['full_body_accupuncture']) &&
	$result['full_body_accupuncture']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="full_body_accupuncture_apt_time" class="col-sm-6 control-label"><?php
echo xlt('Full body accupuncture appointment time');
?></label>
<div class="col-sm-6">
<input type=text class="form-control col-sm-6 timepicker" id="full_body_accupuncture_apt_time" name="full_body_accupuncture_apt_time" <?php
if (isset($result['full_body_accupuncture_apt_time'])) { echo 'value="'. $result['full_body_accupuncture_apt_time'] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="reiki" class="col-sm-6 control-label"><?php
echo xlt('Reiki');
?></label>
<div class="col-sm-6">
<input type=hidden name="reiki" value=off></input>
<input type=checkbox id="reiki" name="reiki" <?php 
if (
	isset($result['reiki']) &&
	$result['reiki']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="reiki_apt_time" class="col-sm-6 control-label"><?php
echo xlt('Reiki appointment time');
?></label>
<div class="col-sm-6">
<input type=text class="form-control col-sm-6 timepicker" id="reiki_apt_time" name="reiki_apt_time" <?php
if (isset($result['reiki_apt_time'])) { echo 'value="'. $result['reiki_apt_time'] .'"'; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

</div> <!-- holistic -->


<h3 class="row header">
<div class="col-sm-12 bg-primary"><?php 
echo xlt('Other');
?>
</div> <!-- col-sm-12-->
</h3> <!-- row -->

<div id="other" class="collapse">

<div class="row form-group">
<label for="phone_visit" class="col-sm-6 control-label"><?php
echo xlt('Phone visit');
?></label>
<div class="col-sm-6">
<input type=hidden name="phone_visit" value=off></input>
<input type=checkbox id="phone_visit" name="phone_visit" <?php 
if (
	isset($result['phone_visit']) &&
	$result['phone_visit']
) { echo "checked"; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="phone_visit_specify" class="col-sm-6 control-label"><?php
echo xlt('Phone visit specify');
?></label>
<div class="col-sm-6">
<input type=text class="form-control col-sm-6" id="phone_visit_specify" name="phone_visit_specify" <?php
if (isset($result['phone_visit_specify'])) { echo 'value="'. $result['phone_visit_specify'] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="talent_testing" class="col-sm-6 control-label"><?php
echo xlt('Talent testing');
?></label>
<div class="col-sm-6">
<input type=hidden name="talent_testing" value=off></input>
<input type=checkbox id="talent_testing" name="talent_testing" <?php 
if (
	isset($result['talent_testing']) && 
	$result['talent_testing']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="food" class="col-sm-6 control-label"><?php
echo xlt('Food');
?></label>
<div class="col-sm-6">
<input type=hidden name="food" value=off></input>
<input type=checkbox id="food" name="food" <?php 
if (
	isset($result['food']) &&
	$result['food']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="clothing" class="col-sm-6 control-label"><?php
echo xlt('Clothing');
?></label>
<div class="col-sm-6">
<input type=hidden name="clothing" value=off></input>
<input type=checkbox id="clothing" name="clothing" <?php 
if (
	isset($result['clothing']) &&
	$result['clothing']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="condoms" class="col-sm-6 control-label"><?php
echo xlt('Condoms lube');
?></label>
<div class="col-sm-6">
<input type=hidden name="condoms" value=off></input>
<input type=checkbox id="condoms" name="condoms" <?php 
if (
	isset($result['condoms']) &&
	$result['condoms']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="nex_syringes" class="col-sm-6 control-label"><?php
echo xlt('NEX syringes');
?></label>
<div class="col-sm-6">
<input type=hidden name="nex_syringes" value=off></input>
<input type=checkbox id="nex_syringes" name="nex_syringes" <?php 
if (
	isset($result['nex_syringes']) &&
	$result['nex_syringes']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="hygiene_supplies" class="col-sm-6 control-label"><?php
echo xlt('Hygiene supplies');
?></label>
<div class="col-sm-6">
<input type=hidden name="hygiene_supplies" value=off></input>
<input type=checkbox id="hygiene_supplies" name="hygiene_supplies" <?php 
if (
	isset($result['hygiene_supplies']) &&
	$result['hygiene_supplies']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="referrals_to_other_services" class="col-sm-6 control-label"><?php
echo xlt('Referrals to other services');
?></label>
<div class="col-sm-6">
<input type=hidden name="referrals_to_other_services" value=off></input>
<input type=checkbox id="referrals_to_other_services" name="referrals_to_other_services" <?php 
if (
	isset($result['referrals_to_other_services']) &&
	$result['referrals_to_other_services']) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="referrals_to_other_services_specify" class="col-sm-6 control-label"><?php
echo xlt('Specify the referrals you are looking for');
?></label>
<div class="col-sm-6">
<input type=text class="form-control" id="referrals_to_other_services_specify" name="referrals_to_other_services_specify" <?php
if (isset($result['referrals_to_other_services_specify'])) { echo 'value="'. $result["referrals_to_other_services_specify"] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="other_harm_reduction_supplies" class="col-sm-6 control-label"><?php
echo xlt('Other harm reduction supplies');
?></label>
<div class="col-sm-6">
<input type=hidden name="other_harm_reduction_supplies" value=off></input>
<input type=checkbox id="other_harm_reduction_supplies" name="other_harm_reduction_supplies" <?php 
if (
	isset($result['other_harm_reduction_supplies']) &&
	$result['other_harm_reduction_supplies']
) { 
echo "checked"; } 
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="other_harm_reduction_supplies_specify" class="col-sm-6 control-label"><?php
echo xlt('Specify the other harm reduction supplies you are looking for');
?></label>
<div class="col-sm-6">
<input type=text class="form-control" id="other_harm_reduction_supplies_specify" name="other_harm_reduction_supplies_specify" <?php
if (isset($result["other_harm_reduction_supplies_specify"])) { echo 'value="'. $result["other_harm_reduction_supplies_specify"] .'"'; }
?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

<div class="row form-group">
<label for="support_group" class="col-sm-6 control-label"><?php
echo xlt('Support group');
?></label>
<div class="col-sm-6">
<input type=hidden name="support_group" value=off></input>
<input type=checkbox id="support_group" name="support_group" <?php 
if (
	isset($result['support_group']) &&
	$result['support_group']
) { echo "checked"; } ?>></input>
</div> <!-- col-sm-6 -->
</div> <!-- row -->

</div> <!-- other -->
</div> <!-- form -->

<div class="col-sm-3" id=issues>
<div class="row">
<div class="col-sm-12">
<?php
  // To see issues stuff user needs write access to all issue types.
  $issuesauth = true;
foreach ($ISSUE_TYPES as $type => $dummy) {
    if (!acl_check_issue($type, '', 'write')) {
        $issuesauth = false;
        break;
    }
}

if ($issuesauth) {
?>
<?php echo xlt('Issues (Injuries/Medical/Allergy)'); ?>
    <?php if (acl_check('patients', 'med', '', 'write')) { ?>
       <a href="../../patient_file/summary/add_edit_issue.php" class="css_button_small link_submit enc_issue"
        onclick="top.restoreSession()"><span><?php echo xlt('Add'); ?></span></a>
        <?php } ?>
<?php } ?>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

<div class="row form-group">
<div class="col-sm-12">
    <select multiple name='issues[]' class='form-control'
	    title='<?php echo xla('Hold down [Ctrl] for multiple selections or to unselect'); ?>' size='6'>
	<?php
	while ($irow = sqlFetchArray($ires)) {
	    $list_id = $irow['id'];
	    $tcode = $irow['type'];
	    if ($ISSUE_TYPES[$tcode]) {
		$tcode = $ISSUE_TYPES[$tcode][2];
	    }
	    echo "    <option value='" . attr($list_id) . "'";
	    if ($viewmode) {
		$perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
		"pid = ? AND encounter = ? AND list_id = ?", array($pid, $encounter, $list_id));
		if ($perow['count']) {
		    echo " selected";
		}
	    } else {
		// For new encounters the invoker may pass an issue ID.
		if (!empty($_REQUEST['issue']) && $_REQUEST['issue'] == $list_id) {
		    echo " selected";
		}
	    }
	    echo ">" . text($tcode) . ": " . text($irow['begdate']) . " " .
	    text(substr($irow['title'], 0, 40)) . "</option>\n";
	}
	?>
    </select>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

<div class="row">
<div class="col-sm-12">
    <p><i><?php echo xlt('To link this encounter/consult to an existing issue, click the '
    . 'desired issue above to highlight it and then click [Save]. '
    . 'Hold down [Ctrl] button to select multiple issues.'); ?></i></p>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

</div> <!-- col-sm-3 issues -->
</div> <!-- row -->

</form>

</div> <!-- container -->

</body>

<script language="javascript">
<?php
if (!$viewmode) { ?>
 function duplicateVisit(enc, datestr) {
    if (!confirm('<?php echo xls("A visit already exists for this patient today. Click Cancel to open it, or OK to proceed with creating a new one.") ?>')) {
            // User pressed the cancel button, so re-direct to today's encounter
            top.restoreSession();
            parent.left_nav.setEncounter(datestr, enc, window.name);
            parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
            return;
        }
        // otherwise just continue normally
    }
<?php

  // Search for an encounter from today
  $erow = sqlQuery("SELECT fe.encounter, fe.date " .
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? " .
    " AND fe.date >= ? " .
    " AND fe.date <= ? " .
    " AND " .
    "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
    "ORDER BY fe.encounter DESC LIMIT 1", array($pid,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')));

if (!empty($erow['encounter'])) {
    // If there is an encounter from today then present the duplicate visit dialog
    echo "duplicateVisit('" . $erow['encounter'] . "', '" .
        attr(oeFormatShortDate(substr($erow['date'], 0, 10))) . "');\n";
}
}
?>

<?php if ($GLOBALS['enable_group_therapy']) { ?>
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
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
          ]
      });
  }
  // This is for callback by the find-group popup.
  function setgroup(gid, name) {
     var f = document.forms[0];
     f.form_group.value = name;
     f.form_gid.value = gid;
  }

    <?php if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
    $('#therapy_group_name').show();
    <?php } ?>
<?php } ?>
</script>

</html>
