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
include_once("common.php");
include_once("$srcdir/api.inc");

formHeader("Form: SJI Intake - Core variables");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_intake_core_variables";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
if (isset($_GET['id'])) {
   $obj = get_cv_form_obj($pid, $_GET["id"]);
} 

// else get the most recent copy of the data from the database
else {
   $sql = "SELECT id from form_sji_intake_core_variables where pid = ? order by date DESC LIMIT 1";
   $res = sqlStatement($sql, array($pid));
   $intake = sqlFetchArray($res);
   if (isset($intake['id'])) {
      $obj = get_cv_form_obj($pid, $intake["id"]);
   }
}

/* remove the time-of-day from the date fields */
if ($obj['date_of_signature'] != "") {
	$dateparts = explode(" ", $obj['date_of_signature']);
	$obj['date_of_signature'] = $dateparts[0];
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

        // This is a special case for ethnicity and race
        if (preg_match('/_/', $list_options['option_id']) && !preg_match('/_/', $list_options['title'])) {
           $list_options['option_id'] = $list_options['title'];
        }

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

?>

<html><head>
<?php Header::setupHeader(['bootstrap', 'datetime-picker', 'select2']); ?>

<script language="JavaScript">
// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<div class="container">

<div class="row bg-primary">
<div class="col-sm-12">
<h2 class="text-center"><?php echo xlt('St. James Infirmary Intake - Core Variables'); ?></h2>
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

<div class="col-sm-2 padding form-group"></div>

<div class="col-sm-6 text-center">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

<div class="col-sm-4 text-center">
<?php
if (isset($obj['date'])) {
echo 'Last updated: ' .date("F d, Y", strtotime($obj['date'])); 
} else {
echo date("F d, Y", time()); 
}
?>
</div>

</div> <!-- class="form-group row" -->

<div class="row">
<div class="col-sm-12">
<p>Thank you for answering these questions. You have the right to skip any of the questions you don’t want to answer.</p>

<p>We at St. James recognize and embrace the variety and fluid nature of both sexual orientation and gender, and we are limited in the number of categories we can use. THANK YOU for your understanding and help!</p>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

<!-- dob -->
<div class="form-group row">
<label for="dob" class="col-sm-6 control-label">What is your date of birth?</label>
<div class="col-sm-6">
<input type="text" name="dob" id="dob" class="form-control datepicker" 
<?php
if (isset($obj['dob']) && !preg_match('/^0000/', $obj['dob'])) {
   echo 'value="'. $obj['dob'] .'"';
}
?>
>
</div>
</div>
<!-- dob -->

<!-- aliases -->
<div class="form-group row">
<label for="aliases" class="col-sm-6 control-label">Do you use any other names, nicknames, or aliases?</label>
<div class="col-sm-6">
<input type="text" name="aliases" id="aliases" class="form-control" 
<?php
if (isset($obj['aliases'])) {
   echo 'value="'. $obj['aliases'] .'"';
}
?>
>
</div>
</div>
<!-- aliases -->

<!-- zip -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="zip"><?php echo xlt('What is your zip code?'); ?></label>
<div class="col-sm-6">
<select id="zip" name="zip" class="select2 form-control">
<option></option>
<?php echo getListOptions('zip'); ?>
</select>
</div>
</div>
<!-- zip -->

<!-- shelter -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="housing_situation"><?php echo xlt('Are you currently living in a homeless shelter?'); ?></label>
<div class="col-sm-6">
<select id="housing_situation" name="housing_situation" class="select2 form-control">
<option></option>
<?php echo getListOptions('housing_situation'); ?>
</select>
</div>
</div>
<!-- zip -->

<!-- ethnicity -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="ethnicity"><?php echo xlt('What is your ethnicity?'); ?></label>
<div class="col-sm-6">
<select id="ethnicity" name="ethnicity" class="form-control select2">
<option></option>
<?php echo getListOptions('ethnicity'); ?>
</select>
</div>
</div>
<!-- ethnicity -->

<!-- race -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="race"><?php echo xlt('What is your race?'); ?></label>
<div class="col-sm-6">
<select id="race" type=text name="race" class="form-control select2">
<option></option>
<?php echo getListOptions('race'); ?>
</select>
</div>
</div>
<!-- race -->

<!-- sex -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="gender"><?php echo xlt('How do you define your gender?'); ?></label>
<div class="col-sm-6">
<select id="sex" type=text name="sex" class="form-control select2">
<option></option>
<?php echo getListOptions('sex'); ?>
</select>
</div>
</div>
<!-- sex -->

<!-- amab_4_amab -->
<div class="form-group row" id=amab_4_amab>
<label class="col-sm-6 control-label" for="amab_4_amab"><?php echo xlt('Have you EVER engaged in sexual activity (personal or professional) with someone else who was also assigned male at birth?'); ?></label>
<div class="col-sm-6">
<select id="amab_4_amab" name="amab_4_amab" class="form-control select2">
<option></option>
<?php echo getListOptions('amab_4_amab'); ?>
</select>
</div>
</div>
<!-- amab_4_amab -->

<!-- pronouns -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="pronouns"><?php echo xlt('What are your pronouns?'); ?></label>
<div class="col-sm-6">
<select id="pronouns" type=text name="pronouns" class="form-control select2">
<option></option>
<?php echo getListOptions('pronouns'); ?>
</select>
</div>
</div>
<!-- pronouns -->

<!-- Sexual_Identity -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="sexual_identity"><?php echo xlt('What sexual orientation do you identify with the most?'); ?></label>
<div class="col-sm-6">
<select id="sexual_identity" type=text name="sexual_identity" class="form-control select2">
<option></option>
<?php echo getListOptions('sexual_identity'); ?>
</select>
</div>
</div>
<!-- sexual identity-->

<!-- partners_gender -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="partners_gender"><?php echo xlt('What genders are the people you have sex with?'); ?></label>
<div class="col-sm-6">
<select id="partners_gender" name="partners_gender[]" class="form-control select2" multiple=multiple>
<?php echo getListOptions('partners_gender'); ?>
</select>
</div>
</div>
<!-- partners gender -->

<!-- sex_without_condom -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="sex_without_condom"><?php echo xlt('In the last 12 months have you had sex without a condom with anyone?'); ?></label>
<div class="col-sm-6">
<select id="sex_without_condom" name="sex_without_condom" class="form-control select2">
<option></option>
<?php echo getListOptions('sex_without_condom'); ?>
</select>
</div>
</div>
<!-- sex without a condom -->

<!-- injected_without_perscription -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="injected_without_perscription"><?php echo xlt('Have you ever injected anything without a doctor\'s perscription?'); ?></label>
<div class="col-sm-6">
<select id="injected_without_perscription" name="injected_without_perscription" class="form-control select2">
<option></option>
<?php echo getListOptions('injected_without_perscription'); ?>
</select>
</div>
</div>
<!-- Injected drugs without a doctors perscription -->

<!-- shared_needle -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="shared_needle"><?php echo xlt('Have you used a needle that was used by any other person, including a sex partner, in the last 12 months?'); ?></label>
<div class="col-sm-6">
<select id="shared_needle" name="shared_needle" class="form-control select2">
<option></option>
<?php echo getListOptions('shared_needle'); ?>
</select>
</div>
</div>

<!-- active drug user-->
<div class="form-group row">
<label class="col-sm-6 control-label" for="active_drug_user"><?php echo xlt('Do you smoke anything, drink alcohol or use any drugs without a perscription?'); ?></label>
<div class="col-sm-6">
<select id="active_drug_user" name="active_drug_user" class="form-control select2">
<option></option>
<?php echo getListOptions('active_drug_user'); ?>
</select>
</div>
</div>


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

    // If the participant was assigned male at birth show the optional question
    var id = $('#sex').find("option:selected").attr("value");
    if (id == 'Male') {
       $('div#amab_4_amab').show();
    } else {
       $('div#amab_4_amab').hide();
    }

    $("#sex").change(function(){
       var id = $(this).find("option:selected").attr("value");
       if (id == 'Male') {
          $('div#amab_4_amab').show();
       } else {
          $('div#amab_4_amab').hide();
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
