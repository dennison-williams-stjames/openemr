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
include_once("$srcdir/patient.inc");

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
   } else {
      $obj = get_cv_form_obj($pid);
   }
}


/* remove the time-of-day from the date fields */
if (isset($obj['date_of_signature'])) {
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

<div class="row">
<div class="col-sm-12 bg-primary">
<p>Thank you for answering these questions. You have the right to skip any of the questions you don’t want to answer.</p>

<p>We at St. James recognize and embrace the variety and fluid nature of both sexual orientation and gender, and we are limited in the number of categories we can use. THANK YOU for your understanding and help!</p>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

<div class="form-group row save">

<div class="col-sm-8 text-center">
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
<!-- shelter -->

<!-- disabled -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="disabled"><?php echo xlt('Do you consider yourself an individual with a disability?'); ?></label>
<div class="col-sm-6">
<select id="disabled" name="disabled" class="select2 form-control">
<option></option>
<?php echo getListOptions('disabled'); ?>
</select>
</div>
</div>
<!-- shelter -->

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
<label class="col-sm-6 control-label" for="zip"><?php echo xlt('Zip'); ?></label>
<div class="col-sm-6">
<input type="text" name="zip" id="zip" class="form-control" 
<?php
if (isset($obj['zip'])) {
   echo 'value="'. $obj['zip'] .'"';
}
?>
>
</div>
</div>
<!-- zip -->

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

<!-- mailing list -->
<!-- TODO: figure out how to automate the subscription here -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="mailing_list"><?php echo xlt('Would you like to be added to our mailing list?'); ?></label>
<div class="col-sm-6">
<select id="mailing_list" name="mailing_list" class="select2 form-control">
<option></option>
<?php echo getListOptions('mailing_list'); ?>
</select>
</div>
</div>
<!-- email -->

<!-- phone_cell -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="phone_cell"><?php echo xlt('Cell phone'); ?></label>
<div class="col-sm-6">
<input type="text" name="phone_cell" id="phone_cell" class="form-control" 
<?php
if (isset($obj['phone_cell'])) {
   echo 'value="'. $obj['phone_cell'] .'"';
}
?>
>
</div>
</div>
<!-- phone_cell -->

<!-- phone_home -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="phone_home"><?php echo xlt('Alternate phone'); ?></label>
<div class="col-sm-6">
<input type="text" name="phone_home" id="phone_home" class="form-control" 
<?php
if (isset($obj['phone_home'])) {
   echo 'value="'. $obj['phone_home'] .'"';
}
?>
>
</div>
</div>
<!-- phone_home -->

<!-- emergency contact -->
<div class="form-group row col-sm-12 header bg-primary"><?php
echo '<b>'. xlt('Emergency Contact') .'</b>: '. xlt('Who should we contact if there is an emergency while you are at SJI?');
?></div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="contact_relationship"><?php echo xlt('Name'); ?></label>
<div class="col-sm-6">
<input type="text" name="contact_relationship" id="contact_relationship" class="form-control" 
<?php
if (isset($obj['contact_relationship'])) {
   echo 'value="'. $obj['contact_relationship'] .'"';
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="emergency_relationsh"><?php echo xlt('Relationship to you?'); ?></label>
<div class="col-sm-6">
<input type="text" name="emergency_relationsh" id="emergency_relationsh" class="form-control" 
<?php
if (isset($obj['emergency_relationsh'])) {
   echo 'value="'. $obj['emergency_relationsh'] .'"';
}
?>
>
</div>
</div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="phone_contact"><?php echo xlt('Phone'); ?></label>
<div class="col-sm-6">
<input type="text" name="phone_contact" id="phone_contact" class="form-control" 
<?php
if (isset($obj['phone_contact'])) {
   echo 'value="'. $obj['phone_contact'] .'"';
}
?>
>
</div>
</div>

<!-- hipaa_call_from_sji -->
<div class="form-group row">
<label class="col-sm-6 control-label" for="hipaa_call_from_sji"><?php echo xlt('May we say we are calling from SJI?'); ?></label>
<div class="col-sm-6">
<select id="hipaa_call_from_sji" name="hipaa_call_from_sji" class="select2 form-control">
<option></option>
<?php echo getListOptions('hipaa_call_from_sji'); ?>
</select>
</div>
</div>
<!-- hipaa_call_from_sji -->

<!-- emergency contact -->

<div class="form-group row col-sm-12 bg-primary"><?php
echo xlt('State Reimbursement Eligibility: Your answers to the following questions DO NOT change wether or not you may recieve our services and DOES NOT effect whether or not you have to pay for services. As always, all our services are FREE.');
?></div>

<div class="form-group row">
<label class="col-sm-6 control-label" for="insurance"><?php echo xlt('Do you have any of the following insurances?'); ?></label>
<div class="col-sm-6">
<?php 
   // we want a list of all of the current providers along with the option 
   // to solicit the name of a new provider.  Its possible that participants
   // will have both FamilyPact and Medical so there should be a way to collect
   // a policy number for each one.  This looks like a possible solution
   // https://stackoverflow.com/questions/5808015/jquery-to-auto-create-textbox-when-choosing-other-from-drop-down

   // get a list of insurance providers that the participant has on record
   $sql = "SELECT provider,policy_number,subscriber_fname,subscriber_lname ".
      "from insurance_data ".
      "where pid = ? and provider is not null";
   $res = sqlStatement($sql, array($pid));
   $providers = array();

   while ($provider = sqlFetchArray($res)) { 
      $providers[$provider['provider']] = $provider;
   }

   $insurancei = getInsuranceProviders();

   $ins_divs ='';

   print "<select name='insurance[]' id='insurance' ".
      " multiple=multiple class='form-control select2'>";
   print '<option></option>';

   $name_divs = $num_divs = $ins_divs = '';
   foreach ($insurancei as $iid => $iname) {
      $name_divs ='<div class="form-group row name" id="iid'. $iid .'">'. 
         "<label class='col-sm-6 control-label' for='iid". 
         $iid ."_subscriber_name'>".
         xlt('What name is used on your'). " $iname ". 
         xlt('plan, if it differs from the name you provided?').
         '</label>'. 
         '<div class="col-sm-6">'.
         '<input type="text" name="iid'.
         $iid .'_subscriber_name" id="iid'. $iid .'_subscriber_name" '.
	 'class="form-control" ';

      $num_divs ='<div class="form-group row num" id="iid'. $iid .'">'. 
         "<label class='col-sm-6 control-label' for='iid". 
         $iid ."_subscriber_id'>".
         xlt('What is the policy number on your'). " $iname ". 
         xlt('plan?').
         '</label>'. 
         '<div class="col-sm-6">'.
         '<input type="text" name="iid'.
         $iid .'_subscriber_id" id="iid'. $iid .'_subscriber_id" '.
	 'class="form-control" ';

      if (array_search($iid, array_keys($providers)) !== FALSE) {
         $sel = 'selected="selected"';
         $name_divs .= 'value="'. $providers[$iid]['subscriber_fname'].
            ' '. $providers[$iid]['subscriber_lname'] .'"';
         $num_divs .= 'value="'. $providers[$iid]['policy_number'] .'"';
      } else {
         $sel = '';
      }
      $name_divs .= ">\n".  "</div>\n".  "</div>\n";
      $num_divs .= ">\n".  "</div>\n".  "</div>\n";
      $ins_divs .= $name_divs . $num_divs;

      print '<option value="' .$iid. '" '.$sel.' >' .
         $iname. '(' .$iid. ')</option>';
   }

   print '</select>';
?>
</div>
</div>

<?php

print $ins_divs;

?>

<div class="form-group row">
<label class="col-sm-6 control-label" for="monthly_income"><?php echo xlt('What is your currently legally reportable monthly income?'); ?></label>
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

<div class="form-group row">
<label class="col-sm-6 control-label" for="dependents"><?php echo xlt('How many people are supported by your income?'); ?></label>
<div class="col-sm-6">
<input type="text" name="dependents" id="dependents" class="form-control" 
<?php
if (isset($obj['dependents'])) {
   echo 'value="'. $obj['dependents'] .'"';
}
?>
>
</div>
</div>

<div style="margin: 10px;" class="form-group row">
<div class="col-sm-8 text-center">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>
<div class="col-sm-4"></div>
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

    // show and hide divs associated with the specified insurance companies
    // Loop across the selected values in select#insurance and show them
    $('select#insurance > option').each(function(){
       var selector1 = 'div.name#iid'+ $(this).val();
       var selector2 = 'div.num#iid'+ $(this).val();
       if ($(this).is(':selected')) {
       } else {
          $(selector1).hide();
          $(selector2).hide();
       }
    });

    // set up a listener on select#insurance for changes and then hide show
    // the name and policy number divs for the selected ins. providers
    $('select#insurance').on('select2:unselect', function(e) {

            var to_hide = 'select#insurance option[value="'+ 
               e.params.data.id +'"]';
            $(to_hide).removeAttr('selected');
	    $('select#insurance > option').each(function(){
               var selector1 = 'div.name#iid'+ $(this).val();
               var selector2 = 'div.num#iid'+ $(this).val();
               if ($(this).is(':selected')) {
	          $(selector1).show();
	          $(selector2).show();
               } else {
	          $(selector1).hide();
	          $(selector2).hide();
               }
	    });
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
