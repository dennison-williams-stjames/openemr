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
require_once('common.php');

formHeader("Form: Our Trans Home Rental Subsidy Application");
$returnurl = 'encounter_top.php';
/* name of this form */
$form_name = "sji_oth_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
if (!empty($_GET['id'])) {
   $obj = array_merge(
      formFetch("form_".$form_name, $_GET["id"]),
      sji_extendedOTH_formFetch($_GET["id"])
   );
} else {
   // if none was supplied then we populate the obj from the most recent intake
   $sql = 'SELECT id FROM form_sji_oth_intake where pid = ? order by date desc limit 1';
   $res = sqlStatement($sql, array($pid));
   $row = sqlFetchArray($res);
   if (isset($row['id'])) {
      $obj = array_merge(
         formFetch("form_".$form_name, $row["id"]),
         sji_extendedOTH_formFetch($_GET["id"]));
   }
}

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

?>

<html><head>
<?php Header::setupHeader(['bootstrap', 'select2', 'datetime-picker']); ?>

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
<h2 class="text-center"><?php echo xlt('Our Trans Home SF Rental Subsidy Application'); ?></h2>
Our Trans Home SF’s Rental Subsidy Program is a financial assistance program for
transgender and non-binary individuals residing in the Bay Area, CA who are at risk of
homelessness. Eligibility is determined based on income, rent price, and availability of
program funds. If enrolled, income will be verified every three months to determine
continued eligibility. The amount of subsidy awarded depends on income and will
decrease over time to allow for the program to reach the largest number of those in
need.
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

<!-- Participant alias -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="alias">Aliases (name(s) you go by)</label>
<div class="col-sm-6" id="alias">
<input type="text" name="aliases" id="aliases" class="form-control"
<?php
if (isset($obj['aliases'])) {
   echo 'value="'. $obj['aliases'] .'"';
}
?>
>
</div>
</div>
<!-- participant Alias -->


<!-- TODO: is there any reason the name on the application would be different then the primary name we have for them in the EMR -->
<!-- Participant name -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="rental_agreement_name">Name (on the rental agreement)</label>
<div class="col-sm-6" id="rental_agreement_name">
<input type="text" name="rental_agreement_name" id="rental_agreement_name" class="form-control"
<?php
if (isset($obj['rental_agreement_name'])) {
   echo 'value="'. $obj['rental_agreement_name'] .'"';
} else if (isset($obj['Name'])) {
   echo 'value="'. $obj['Name'] .'"';
}
?>>
</div>
</div>
<!-- participant name -->

<!-- dob -->
<div class="form-group row">
<label for="DOB" class="col-sm-6 control-label">Date of Birth</label>
<div class="col-sm-6">
<input type="text" name="DOB" id="DOB" class="form-control datepicker"
<?php
if (isset($obj['DOB']) && !preg_match('/^0000/', $obj['DOB'])) {
   echo 'value="'. $obj['DOB'] .'"';
}
?>
>
</div>
</div>
<!-- dob -->

<!-- pronouns -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="pronouns"><?php echo xlt('Pronouns'); ?></label>
<div class="col-sm-6">
<select id="pronouns" type=text name="pronouns" class="form-control select2">
<option></option>
<?php echo getListOptions('pronouns'); ?>
</select>
</div>
</div>
<!-- pronouns -->

<!-- cell phone -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="phone_cell"><?php echo xlt('Cell Phone Number'); ?></label>
<div class="col-sm-6">
<input type="text" name="phone_cell" id="phone_cell" class="form-control"
<?php
if (isset($obj['phone_cell'])) {
   echo 'value="'. $obj['phone_cell'] .'"';
}
?>>
</div>
</div>
<!-- phone -->

<!-- email -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="email"><?php echo xlt('Email'); ?></label>
<div class="col-sm-6">
<input type="text" name="email" id="email" class="form-control"
<?php
if (isset($obj['email'])) {
   echo 'value="'. $obj['email'] .'"';
}
?>
>
</div>
</div>
<!-- email -->

<!-- Are you trans? -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="is_trans"><?php echo xlt('Are you trans, non-binary, gender variant, or intersex?'); ?></label>
<div class="col-sm-1">
<input type="checkbox" <?php
if (isset($obj['is_trans']) && $obj['is_trans'] == 1) {
   echo 'checked ';
}
?>name="is_trans"></input>
</div>
<div class="col-sm-5"></div>
</div>

<!-- What are you requesting? -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="requesting"><?php echo xlt('What type of rental assistance are you requesting?'); ?></label>
<div class="col-sm-6">
<select id="requesting" name="requesting" class="select2 form-control">
<option></option>
<?php echo getListOptions('requesting'); ?>
</select>
</div>
</div>

<!-- shelter -->
<!-- TODO: get feedback on if this is an acceptable alternative to the 'Are you experiencing homelessness?' question -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="housing_situation"><?php echo xlt('What is your current housing situation?'); ?></label>
<div class="col-sm-6">
<select id="housing_situation" name="housing_situation" class="select2 form-control">
<option></option>
<?php echo getListOptions('housing_situation'); ?>
</select>
</div>
</div>
<!-- shelter -->

<div class="form-group row">
<label class="col-sm-12 control-label"><?php echo xlt('If applicable, please provide your landlord\'s information'); ?></label>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="landlord_name"><?php echo xlt('Landlord name'); ?></label>
<div class="col-sm-6">
<input class="form-control" id="landlord_name" name="landlord_name" <?php
if (isset($obj['landlord_name'])) {
   echo "value='". $obj['landlord_name'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="landlord_phone"><?php echo xlt('Landlord phone'); ?></label>
<div class="col-sm-6">
<input class="form-control" id="landlord_phone" name="landlord_phone" <?php
if (isset($obj['landlord_phone'])) {
   echo "value='". $obj['landlord_phone'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="landlord_email"><?php echo xlt('Landlord email'); ?></label>
<div class="col-sm-6">
<input class="form-control" id="landlord_email" name="landlord_email" <?php
if (isset($obj['landlord_email'])) {
   echo "value='". $obj['landlord_email'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="landlord_address"><?php echo xlt('Landlord mailing address for rent check (please verify to avoide delays in payment)'); ?></label>
<div class="col-sm-6">
<input class="form-control" id="landlord_address" name="landlord_address" <?php
if (isset($obj['landlord_address'])) {
   echo "value='". $obj['landlord_address'] ."'";
}
?>>
</div>
</div>

<div class="form-group row">
<label class="col-sm-12 control-label"><?php echo xlt('Your address of residence'); ?></label>
</div>

<!-- street-->
<div class="form-group row">
<label class="col-sm-6 control-label" for="street"><?php echo xlt('Address'); ?></label>
<div class="col-sm-6">
<input type="text" name="street" id="street" class="form-control"
<?php
if (isset($obj['street'])) {
   echo 'value="'. $obj['street'] .'"';
}
?>
>
</div>
</div>
<!-- street -->

<!-- city-->
<div class="form-group row">
<label class="col-sm-6 control-label" for="city"><?php echo xlt('City'); ?></label>
<div class="col-sm-6">
<input type="text" name="city" id="city" class="form-control"
<?php
if (isset($obj['city'])) {
   echo 'value="'. $obj['city'] .'"';
}
?>
>
</div>
</div>
<!-- city -->

<!-- state -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="state"><?php echo xlt('State'); ?></label>
<div class="col-sm-6">
<select id="state" name="state" class="select2 form-control">
<option></option>
<?php echo getListOptions('state'); ?>
</select>
</div>
</div>
<!-- state -->

<!-- zip -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="postal_code"><?php echo xlt('Zip'); ?></label>
<div class="col-sm-6">
<input type="text" name="postal_code" id="postal_code" class="form-control"
<?php
if (isset($obj['postal_code'])) {
   echo 'value="'. $obj['postal_code'] .'"';
}
?>
>
</div>
</div>
<!-- zip -->

<!-- base_rent -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="base_rent"><?php echo xlt('What is the total base rent for the entire space (as listed on the lease, NOT including any utilities or add on expenses)?'); ?></label>
<div class="col-sm-6">
<input type="text" name="base_rent" id="base_rent" class="form-control"
<?php
if (isset($obj['base_rent'])) {
   echo 'value="'. $obj['base_rent'] .'"';
}
?>
>
</div>
</div>
<!-- base_rent -->

<div class="form-group row">
<label class="col-sm-6 control-label" for="split_rent"><?php echo xlt('How many rommmates do you split the rent with?'); ?></label>
<div class="col-sm-6">
<input type="text" name="split_rent" id="base_rent" class="form-control"
<?php
if (isset($obj['split_rent'])) {
   echo 'value="'. $obj['split_rent'] .'"';
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="your_rent"><?php echo xlt('What do you pay for your monthly base rent? (For you only, not roommates or partners)'); ?></label>
<div class="col-sm-6">
<input type="text" name="your_rent" id="your_rent" class="form-control"
<?php
if (isset($obj['your_rent'])) {
   echo 'value="'. $obj['your_rent'] .'"';
}
?>
>
</div>
</div>
<!-- your_rent -->

<div class="form-group row">
<label class="col-sm-6 control-label" for="eviction_risk"><?php echo xlt('Are you at risk for eviction?'); ?></label>
<div class="col-sm-1">
<input type="checkbox" name="eviction_risk" id="eviction_risk"
<?php
if (isset($obj['eviction_risk']) && $obj['eviction_risk'] == 1) {
   echo 'checked ';
}
?>
>
</div>
<div class="col-sm-5"></div>
</div>
<!-- eviction_risk -->

<div class="form-group row" id="eviction-risk-questions">
<label class="col-sm-6 control-label" for="eviction_risk_description">If you are at risk for eviction, please provide more information about your circumstances</label>
<div class="col-sm-6">
<textarea class="form-control" rows=3 id="eviction_risk_description" name="eviction_risk_description">
<?php
if (isset($obj['eviction_risk_description'])) {
   echo $obj['eviction_risk_description'];
}
?>
</textarea>
</div>
</div>

<!-- TODO: evaluate if the housing q at the top handles this 
If you are currently homeless, where are you staying?
-->

<div class="form-group row">
<label class="col-sm-6 control-label" for="monthly_income"><?php echo xlt('What is your total monthly income (without subtracting expenses)?'); ?></label>
<div class="col-sm-6">
<input type="text" name="monthly_income" id="monthly_income" class="form-control"
<?php
if (isset($obj['monthly_income'])) {
   echo 'value="'. $obj['monthly_income'] .'"';
}
?>
>
</div>
</div>

<!-- income_sources -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="income_sources"><?php echo xlt('Please select all applicable income sources'); ?></label>
<div class="col-sm-6">
<select id="income_sources" type=text name="income_sources[]" class="form-control select2" multiple="multiple">
<option></option>
<?php 
echo getListOptions('income_sources'); 
?>
</select>
</div>
</div>
<!-- income_sources-->

<!-- income_verification -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="income_verification"><?php echo xlt('What type of income verification documents are you submitting with your application? {Please include these documents with your application)'); ?></label>
<div class="col-sm-6">
<select id="income_verification" type=text name="income_verification[]" class="form-control select2" multiple="multiple">
<option></option>
<?php echo getListOptions('income_verification'); ?>
</select>
</div>
</div>
<!-- income_verification-->

<!-- noncash_assistance -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="noncash_assistance"><?php echo xlt('Please specify any non-cash assistance programs you are enrolled in)'); ?></label>
<div class="col-sm-6">
<select id="noncash_assistance" type=text name="noncash_assistance[]" class="form-control select2" multiple="multiple">
<option></option>
<?php echo getListOptions('noncash_assistance'); ?>
</select>
</div>
</div>
<!-- income_verification-->

<!-- TODO: this should add a callback reminder for the registration administrator -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="interested_in_sji"><?php echo xlt('Our Trans Home SF is a program of St. James Infirmary, a clinic that offers services to current and former sex workers. Are you interested in receiving medical or mental health services through St. James?'); ?></label>
<div class="col-sm-1">
<input type="checkbox" name="interested_in_sji" id="interested_in_sji" 
<?php
if (isset($obj['interested_in_sji']) && $obj['interested_in_sji'] == 1) {
   echo 'checked ';
}
?>
>
</div>
<div class="col-sm-5"></div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="priorities"><?php echo xlt('We prioritize BIPOC (Black, Indigenous, and People of Color), those living with HIV/AIDS, current and former sex workers, people with diabilities, and those who were formerly incarcerated. Check all that apply (if comfortable)'); ?></label>
<div class="col-sm-6">
<select multiple="multiple" id="priorities" type=text name="priorities[]" class="form-control select2">
<option></option>
<?php echo getListOptions('priorities'); ?>
</select>
</div>
</div>
<!-- priorities-->

<!-- TODO: the OTH intake form has a much more robust race and ethnicity questionaire, lets confirm that ours is sufficient -->
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

<!-- gender -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="gender"><?php echo xlt('How do you define your gender?'); ?></label>
<div class="col-sm-6">
<select id="sex" type=text name="sex" class="form-control select2">
<option></option>
<?php echo getListOptions('sex'); ?>
</select>
</div>
</div>
<!-- gender -->

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

<div class="form-group row">
<label class="col-sm-6 control-label" for="veteran"><?php echo xlt('Are you a veteran?'); ?></label>
<div class="col-sm-1">
<input type="checkbox" name="veteran" id="veteran" 
<?php
if (isset($obj['veteran']) && $obj['veteran'] == 1) {
   echo 'checked ';
}
?>
>
</div>
<div class="col-sm-5"></div>
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

    $('#eviction_risk_questions').hide();
    var eviction_risk = $('#taken_hormones').val();
    if ( eviction_risk == 'Yes' ) {
       $('#eviction_risk_questions').fadeIn('slow');
    }

    $('.select2').select2({
       tags: true,
    });

});

</script>

</html>
