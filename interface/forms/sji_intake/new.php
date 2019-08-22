<?php
 /*    This program is free software; you can redistribute it and/or
 *    modify it under the terms of the GNU General Public License
 *    as published by the Free Software Foundation; either version 2
 *    of the License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. -->
 */

use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once('common.php');

formHeader("Form: SJI Intake");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
if (isset($_GET['id']) && $_GET['id'] != "") {
	$obj = array_merge(
		formFetch("form_".$form_name, $_GET["id"]),
		sji_intake_formFetch($_GET["id"]));

	/* remove the time-of-day from the date fields */
	if (isset($obj['date_of_signature']) && $obj['date_of_signature'] != "") {
		$dateparts = explode(" ", $obj['date_of_signature']);
		$obj['date_of_signature'] = $dateparts[0];
	}
}

// TODO: figure out how to get values from the object selected
/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;

    $output = "";
    $selected = array();
    if (isset($obj[$list_id])) {
           $selected = $obj[$list_id];
    }
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
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

function getListOptionsJson($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "[";
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '{ value: "'. $list_options['option_id'] .'", name: "'. $list_options['option_id'] .'" },'. "\n";
    }

    return $output . "]";
}

function getExternalProvidersOptions() {
    global $obj;
    $output = "";
    $query = sqlStatement("SELECT id,organization FROM users where facility_id = 0 AND active = 1 AND organization is not null");
    while ($list_options = sqlFetchArray($query)) {
        $output .= '<option value="'. $list_options['organization'] .'" ';
        if (isset($obj['received_healthcare_from'])) {
           if (is_array($obj['received_healthcare_from']) && in_array($list_options['organization'], $obj['received_healthcare_from'])) {
              $output .= "selected ";
           } else if ($obj['received_healthcare_from'] === $list_options['organization']) {
              $output .= "selected ";
           }
        }
        $output .= '>'. $list_options['organization'] .'</option>';
    }

    return $output;
}
?>

<html><head>
<?php Header::setupHeader(['bootstrap', 'datetime-picker', 'select2']); ?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<script language="JavaScript">
// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<div class="container">

<div class="row bg-primary">
<div class="col-sm-12">
<h2 class="text-center"><?php echo xlt('St. James Infirmary Intake'); ?></h2>
</div>
</div>

<form class="form-horoizontal" role="form" method=post action="<?php 
echo $rootdir."/forms/".$form_name."/save.php?mode=";
if (isset($_GET['id'])) {
   echo "update&id=".attr($_GET["id"]);
} else {
   echo "new";
}
?>" name="my_form" id="my_form">

<div class="form-group row">

<div class="col-sm-3 padding form-group">
<label for="declined_intake" class="control-label text-center">Declined intake</label>
<input id="declined_intake" type=checkbox name="declined_intake" <?php 
if (
   isset($obj) && 
   isset($obj['declined_intake']) && 
   $obj['declined_intake']
   ) { 
      echo "checked"; 
   } 
?>>
</div>

<div class="col-sm-6 text-center">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

<div class="col-sm-3 text-center">
<?php echo date("F d, Y", time()); ?>
</div>

</div> <!-- class="form-group row" -->

<hr>

<div id="did_not_decline">

<!-- Referrer -->
<div class="form-group row">
<label for="referrer" class="col-sm-6 control-label">How did you first hear about the St. James Infirmary clinic?</label>
<div class="col-sm-6">
<select name="referrer" id="referrer" class="select2 form-control" 

<?php if (isset($obj['referrer'])) {
   echo 'data-placeholder="'. $obj['referrer'] .'"';
} else {
   echo 'data-placeholder="How were you referred to the clinic?"';
}
?>

>
<option></option>
<?php echo getListOptions('referrer'); ?>
</select>
</div>
</div>
<!-- Referrer -->


<div class="form-group row">
<label class="col-sm-6 control-label" for="interviewer_name">Interviewer name</label>
<div class="col-sm-6">
<input id="interviewer_name" name="interviewer_name" class="form-control" type=text
<?php 
if (isset($obj['interviewer_name'])) {
   echo "value='". $obj['interviewer_name'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="country_of_origin">What is your country of origin?</label>
<div class="col-sm-6">
<input id="country_of_origin" type=text data-placeholder="Enter your country of origin" name="country_of_origin"
<?php 
if (isset($obj['country_of_origin'])) {
   echo "value='". $obj['country_of_origin'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<h3 class="">Social Support</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="supportive_people">Who are your supportive people?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="supportive_people" multiple=multiple name="supportive_people[]" data-placeholder="Select or enter your supportive people">
<option></option>
<?php echo getListOptions('supportive_people'); ?>
</select>
</div>
</div>

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<h3 class="">Work History</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="sex_indusrtry_connection">Sex industry connection</label>
<div class="col-sm-6">
<select class="select2 form-control" id="sex_industry_connection" name="sex_industry_connection" data-placeholder="Select or enter your connection to the sex work industry if you are not a sex worker">
<option></option>
<?php echo getListOptions('sex_industry_connection'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="work_done">What sex work have you done before?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="work_done" multiple=multiple name="work_done[]" data-placeholder="Select or enter the sex work you have done">
<?php echo getListOptions('work_done'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="work_doing">What sex work are you doing now?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="work_doing" multiple=multiple name="work_doing[]" data-placeholder="Select or enter the sex work you are doing now">
<?php echo getListOptions('work_doing'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" class="col-sm-3 control-label" for="work_with">Who have you been sexualy active with?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="work_with" multiple=multiple name="work_with[]" data-placeholder="Select or enter who you have been sexualy active with">
<?php echo getListOptions('work_with'); ?>
</select>
</div>
</div>

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<h3 class="">Health Care</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="identified_as_sex_worker">Have you identified as a sex worker to medical providers in the past?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="identified_as_sex_worker" name="identified_as_sex_worker" data-placeholder="Have you identified as a sex worker to medical providers in the past?">
<option></option>
<?php echo getListOptions('identified_as_sex_worker'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="identified_as_sex_worker_reaction">What was the reaction you received when you identified as a sex worker?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="identified_as_sex_worker_reaction" name="identified_as_sex_worker_reaction[]" data-placeholder="What was the reaction you received when you identified as a sex worker?" multiple=multiple>
<?php echo getListOptions('identified_as_sex_worker_reaction'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="not_identified_sex_worker_reason">What was the reason you did not identify as a sex worker?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="not_identified_sex_worker_reason" multiple=multiple name="not_identified_sex_worker_reason[]" data-placeholder="What was the reason you did not identify as a sex worker?">
<?php echo getListOptions('not_identified_sex_worker_reason'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="received_healthcare_from">Where else have you gone for health care in the past two years?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="received_healthcare_from" multiple=multiple name="received_healthcare_from[]" data-placeholder="Where else have you received health care in the past two years?">
<?php 
// TODO: figure out how to pre-selected the saved values
// Get a list of all of the external providersAA
echo getExternalProvidersOptions(); 
?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_physical">When was your last physical?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="last_physical" name="last_physical" data-placeholder="When was your last physical?">
<option></option>
<?php echo getListOptions('last_physical'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="tested_for">Have you ever been tested for Hepatitis?</label>
<div class="col-sm-6">
<select multiple=multiple class="select2 form-control" id="tested_for" name="tested_for[]" data-placeholder="Select each test you have received?">
<?php echo getListOptions('tested_for'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="diagnosed_with">Have you ever been diagnosed with Hepatitis?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="diagnosed_with" name="diagnosed_with[]" multiple=multiple data-placeholder="Select each diagnosis">
<option></option>
<?php echo getListOptions('diagnosed_with'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hepatitis_follow_up">Have you ever received a hepatitis follow up or treatment?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="hepatitis_follow_up" name="hepatitis_follow_up" data-placeholder="Have you ever received a hepatitis follow up or treatment?">
<option></option>
<?php echo getListOptions('hepatitis_follow_up'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="vaccinated_for">Have you received any of the following vaccinations?</label>
<div class="col-sm-6">
<select multiple=multiple class="select2 form-control" id="vaccinated_for" name="vaccinated_for[]" data-placeholder="Select or enter all of the vaccinations you have received?">
<?php echo getListOptions('vaccinated_for'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_hepatitis_vaccination">Do you want the hepatitis vaccination?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="want_hepatitis_vaccination" name="want_hepatitis_vaccination" data-placeholder="Do you want the hepatitis vaccination?">
<option></option>
<?php echo getListOptions('want_hepatitis_vaccination'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hiv_tested">Have you been tested for HIV?</label>
<div class="col-sm-6">
<select class="select2 form-control" id="hiv_tested" name="hiv_tested" data-placeholder="Have you been tested for HIV?">
<option></option>
<?php echo getListOptions('hiv_tested'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_hiv_test_date">When was your last test for HIV?</label>
<div class="col-sm-6">
<!-- lets use the jquery datepicker ui for this -->
<input type=text id="last_hiv_test_date" class="datepicker" name="last_hiv_test_date" data-placeholder="When was your last HIV test?"
<?php
if (isset($obj['last_hiv_test_date']) && !preg_match('/^0000/', $obj['last_hiv_test_date'])) {
   echo 'value="'. $obj['last_hiv_test_date'] .'"';
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_hiv_test_result">What was your last HIV test result?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="last_hiv_test_result" name="last_hiv_test_result" data-placeholder="What was your last HIV test result?">
<option></option>
<?php echo getListOptions('last_hiv_test_result'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hiv_positive_receiving_care">If you are HIV positive, are you currently getting health care for your HIV?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="hiv_positive_receiving_care" name="hiv_positive_receiving_care" data-placeholder="If you are HIV positive, are you currently receiving health care for your HIV?">
<option></option>
<?php echo getListOptions('hiv_positive_receiving_care'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_hiv_test">Are you interested in getting HIV testing or counseling today?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_hiv_test" name="want_hiv_test" data-placeholder="Are you interested in getiing HIV testing or counseling today?">
<option></option>
<?php echo getListOptions('want_hiv_test'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="diagnosed_std_positive">Have you ever been diagnosed with a sexually transmitted disease (STD/VD)?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="diagnosed_std_positive" name="diagnosed_std_positive" data-placeholder="Have you ever been diagnosed with a sexually transmitted disease (STD/VD)?">
<option></option>
<?php echo getListOptions('diagnosed_std_positive'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="std_past">What STD(s) have you had in the past?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="std_past" multiple=multiple name="std_past[]" data-placeholder="What STD(s) have you had in the past?">
<option></option>
<?php echo getListOptions('std_past'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_std_test">Are you interested in getting STD testing or couseling today?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_std_test" name="want_std_test" data-placeholder="Are you interested in getting STD testing or counseling today?">
<option></option>
<?php echo getListOptions('want_std_test'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="tb_tested">Have you ever had a skin test for tuberculosis (TB)?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="tb_tested" name="tb_tested" data-placeholder="Have you ever had a skin test for tuberculosis (TB)?">
<option></option>
<?php echo getListOptions('tb_tested'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_tb_test_date">When was your last test for tuberculosis (TB)?</label>
<div class="col-sm-6">
<input type=text id="last_tb_test_date" class="datepicker" name="last_tb_test_date" data-placeholder="When was your last TB test?"
<?php
if (isset($obj['last_tb_test_date']) && !preg_match('/^0000/', $obj['last_tb_test_date'])) {
   echo "value='". $obj['last_tb_test_date'] ."'";
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_tb_test_result">What was the result of your last tuberculosis (TB) test?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="last_tb_test_result" name="last_tb_test_result" data-placeholder="What was the result of your last tuberculosis (TB) test?">
<option></option>
<?php echo getListOptions('last_tb_test_result'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="tb_follow_up">If you tested positive for TB, did you follow up with a chest x-ray or medication?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="tb_follow_up" name="tb_follow_up" data-placeholder="If you tested positive for TB, did you follow up with a chest x-ray or medication?">
<option></option>
<?php echo getListOptions('tb_follow_up'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_tb_test">Are you interested in being tested for TB today?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_tb_test" name="want_tb_test" data-placeholder="Are you interested in being tested for TB today?">
<option></option>
<?php echo getListOptions('want_tb_test'); ?>
</select>
</div>
</div>

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<h3>If female or FTM TG</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_pap_smear">When was your last pap smear?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="last_pap_smear" name="last_pap_smear" data-placeholder="When was your last pap smear?">
<option></option>
<?php echo getListOptions('last_pap_smear'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_pap_smear">If a year ago or more, or never, would you like to have a pap smear today?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_pap_smear" name="want_pap_smear" data-placeholder="If a year ago or more, or never, would you like to have a pap smear today?">
<option></option>
<?php echo getListOptions('want_pap_smear'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="abnormal_pap_smear">Have you ever had an abnormal pap smear?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="abnormal_pap_smear" name="abnormal_pap_smear" data-placeholder="Have you ever had an abnormal pap smear?">
<option></option>
<?php echo getListOptions('abnormal_pap_smear'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="abnormal_pap_smear_date">When was your last abnormal pap smear?</label>
<div class="col-sm-6">
<input type=text id="abnormal_pap_smear_date" class="datepicker" name="abnormal_pap_smear_date" data-placeholder="When was your last abnormal pap smear?"
<?php
if (isset($obj['abnormal_pap_smear_date']) && !preg_match('/^0000/', $obj['abnormal_pap_smear_date'])) {
   echo "value='". $obj['abnormal_pap_smear_date'] ."'";
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="abnormal_pap_smear_follow_up">Did you get a follow up for the abnormal pap smear?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="abnormal_pap_smear_follow_up" name="abnormal_pap_smear_follow_up" data-placeholder="Did you get a follow up for the abnormal pap smear?">
<option></option>
<?php echo getListOptions('abnormal_pap_smear_follow_up'); ?>
</select>
</div>
</div>

<!-- TODO: automate the collapse of this if there age does not make thiss eligible -->
<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="if_female_or_ftm_and_40_years_or_older" >
<h3>If female or FTM TG and 40 years or older</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_mamomgram">When was your last mammogram?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="last_mammogram" name="last_mammogram" data-placeholder="When was your last mammogram?">
<option></option>
<?php echo getListOptions('last_mammogram'); ?>
</select>
</div>
</div>

</div> <!-- id="iif_female_or_ftm_and_40_years_or_older" -->

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="if_male_or_mtf_tg" >
<h3>If male or MTF TG</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="last_testicular_exam_">When was your last testicular/genital/STD exam?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="last_testicular_exam" name="last_testicular_exam" data-placeholder="When was your last testicular/genital/STD exam?">
<option></option>
<?php echo getListOptions('last_testicular_exam'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_testicular_exam">If a year ago or more, or never, would you like to have an exam today?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_testicular_exam" name="want_testicular_exam" data-placeholder="If a year ago or more, or never, would you like to have an exam today?">
<option></option>
<?php echo getListOptions('want_testicular_exam'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="taking_hormones_now">Do you take hormones now?</label>
<div class="col-sm-6">

<select class="select2 form-control" type=text id="taking_hormones_now" name="taking_hormones_now" data-placeholder="Are you taking hormones now?">
<option></option>
<?php echo getListOptions('taking_hormones_now'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormones_types">What type of hormones are you taking now?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="hormones_types" name="hormones_types[]" multiple=multiple data-placeholder="What type of hormones are you taking now?">
<option></option>
<?php echo getListOptions('hormones_types'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="hormones_source">Where do you get your hormones?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="hormones_source" name="hormones_source" data-placeholder="Where do you get your hormones?">
<option></option>
<?php echo getListOptions('hormones_source'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="taken_hormones">Have you taken hormones?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="taken_hormones" name="taken_hormones" data-placeholder="Have you taken hormones?">
<option></option>
<?php echo getListOptions('taken_hormones'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_hormones">Are you interested in getting hormones here at St. James Infirmary?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_hormones" name="want_hormones" data-placeholder="Are you interested in getting hormones here at St. James Infirmary?">
<option></option>
<?php echo getListOptions('want_hormones'); ?>
</select>
</div> <!-- id="if_mtf_or_ftm_tg" -->
</div>
</div>

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="legal" class="bg-primary">
<h3>Legal</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="prostitution_case_pending">Do you currently have a prostitution-related case pending?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="prostitution_case_pending" name="prostitution_case_pending" data-placeholder="Do you currently have a prostitution-related case pending?">
<option></option>
<?php echo getListOptions('prostitution_case_pending'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_legal">Do you need legal assistance?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_legal" name="want_legal" data-placeholder="Do you need legal assistance?">
<option></option>
<?php echo getListOptions('want_legal'); ?>
</select>
</div>
</div>
</div> <!-- id="legal" -->

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="violence" >
<h3 id="violence">Violence</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="experienced_violence">Have you experienced violence related to your sex work?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="experienced_violence" name="experienced_violence" data-placeholder="Have you experienced violence related to your sex work?">
<option></option>
<?php echo getListOptions('experienced_violence'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="experienced_violence_from">Who have you experienced violence from?</label>
<div class="col-sm-6">
<select multiple=multiple class="select2 form-control" type=text id="experienced_violence_from" name="experienced_violence_from[]" data-placeholder="Who have you experienced violence from?">
<option></option>
<?php echo getListOptions('experienced_violence_from'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_to_report_bad_date">Would you like to report a bad date to the Bad Date List?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_to_report_bad_date" name="want_to_report_bad_date" data-placeholder="Would you like to report a bad date to the Bad Date List?">
<option></option>
<?php echo getListOptions('want_to_report_bad_date'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="experienced_violence_from_partner">Have you ever experienced violence by your partner, someone you live with, or anyone else in your personal life?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="experienced_violence_from_partner" name="experienced_violence_from_partner" data-placeholder="Have you ever experienced violence by your partner, someone you live with, or anyone else in your personal life?">
<option></option>
<?php echo getListOptions('experienced_violence_from_partner'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="currently_experiencing_violence">Are you experiencing this violence currently?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="currently_experiencing_violence" name="currently_experiencing_violence" data-placeholder="Are you experiencing this violence currently?">
<option></option>
<?php echo getListOptions('currently_experiencing_violence'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="want_dv_referral">Would you like a referral to a domestic violence program?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="want_dv_referral" name="want_dv_referral" data-placeholder="Would you like a referral to a domestic violence program?">
<option></option>
<?php echo getListOptions('want_dv_referral'); ?>
</select>
</div>
</div>
</div> <!-- id="violence" -->

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="mental_health" class="bg-primary">
<h3 id="mental_health">Mental Health</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="diagnosed_mental_health_condition">Have you ever been diagnosed with a mental health condition?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="diagnosed_mental_health_condition" name="diagnosed_mental_health_condition" data-placeholder="Have you ever been diagnosed with a mental health condition?">
<option></option>
<?php echo getListOptions('diagnosed_mental_health_condition'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="mental_health_condition">What have you been diagnosed with?</label>
<div class="col-sm-6">
<select class="select2 form-control" multiple="multiple" type=text id="mental_health_condition" name="mental_health_condition[]" data-placeholder="What have you been diagnosed with?">
<?php echo getListOptions('mental_health_condition'); ?>
</select>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="mental_health_conditioni_meds">Are you currently taking medication for this condition?</label>
<div class="col-sm-6">
<select class="select2 form-control" type=text id="mental_health_condition_meds" name="mental_health_condition_meds" data-placeholder="Are you currently taking medication for this condition?">
<option></option>
<?php echo getListOptions('mental_health_condition_meds'); ?>
</select>
</div>
</div>

</div> <!-- id="mental_health" -->

<div class="form-group row">
<div class="col-sm-12 bg-primary">
<div id="">
<h3 class="">Do you have any other questions or concerns that you want to discuss with me?</h3>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="comments">Intake worker notes, referral needs, ideas for other services SJI could offer, etc</label>
<div class="col-sm-6">
<textarea rows=3 type=text class="sm-textarea form-control" id="comments" name="comments" data-placeholder="Intake worker notes, referral needs, ideas for other services SJI could offer, etc)">
<?php
if (isset($obj['comments'])) {
   echo $obj['comments'];
}
?>
</textarea>
</div> <!-- id="comments" -->
</div>
</div>

</div> <!-- id=did_not_decline -->
<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<label class="col-sm-6 control-label" for="private">This note is private</label>
<input type="checkbox" name="private" id="private">
<br>

<br>
<b><?php echo xlt('Signature:'); ?></b>
<br>
-->

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>
</div> <!-- id="container" -->

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); $('#my_form').submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });
    //$("#printform").click(function() { PrintForm(); });

    // If the participant declined the intake lets hide the intake form
    if ( $('#declined_intake').is(":checked") ) {
       $('#did_not_decline').fadeOut('slow');
    }

    // A UI helper function that allows us to hide the form if the participant refused it
    $('#declined_intake').change(function() {
       if (this.checked) {
          $('#did_not_decline').fadeOut('slow');
       } else {
          $('#did_not_decline').fadeIn('slow');
       }
    });

    // Set the class to datepicker and let this do the rest
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

    $('.select2').select2({
       tags: true,
    });

});

</script>

</html>
