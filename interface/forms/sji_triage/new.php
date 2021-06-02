<?php
/*
 *    This program is free software; you can redistribute it and/or
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
formHeader("Form: Triage encounter");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_triage";

// get the record from the database
$obj = array();
if (!empty($_GET['id'])) {
    $obj = formFetch("form_".$form_name, $_GET["id"]);
} else {
    // if we don't get passed an id then we do not have a triage row for this 
    // encounter.  Let's look for a previous triage to see if we can 
    // pre-populate the pharmacy and contact preferences columns
    $sql =
       "SELECT contact_preferences,pharmacy FROM form_sji_triage ".
       "WHERE pid=? ".
       "ORDER BY id DESC LIMIT 1";
    $last_triage = sqlQuery($sql, array($pid));
    if (!empty($last_triage)) {
       $obj = $last_triage;
    }
}
$obj = array_merge($obj,
   sji_extendedTriage_formFetch());

/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "";
    $found = 0;

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

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>

<body class="body_top">
<div class="container">
<?php echo date("F d, Y", time()); ?>

<form method=post action="<?php 
   if (isset($_GET['id'])) {
      echo $rootdir."/forms/".$form_name."/save.php?mode=update&id=".attr($_GET["id"]);
   } else {
      echo $rootdir."/forms/".$form_name."/save.php?mode=new";
   }
?>" name="my_form" id="my_form">

<div class="row bg-primary">
<div class="col-sm-12">
<span class="title"><?php echo xlt('Triage'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- Name, alias -->
<div class="form-group row">
<label for="name" class="col-sm-2 control-label"><?php echo xlt('Name:'); ?></label>
<div class="col-sm-4"><?php
/*
   if (!empty($obj['fname'])) {
      echo $obj['fname'];
   }

   if (!empty($obj['lname'])) {
      if (!empty($obj['fname'])) {
         echo " ";
      }
      echo $obj['lname'];
   }
*/
   if (!empty($obj['name'])) {
      echo $obj['name'];
   }

   if (!empty($obj['aliases'])) {
      if (!empty($obj['fname']) || !empty($obj['lname']) || !empty($obj['name'])) {
         echo " ";
      }
      echo "AKA: ". $obj['aliases'];
   }
?></div>
<div class="col-sm-6"></div>
</div>

<!-- pronouns -->
<div class="form-group row">
<label for="pronouns" class="col-sm-2 control-label"><?php echo xlt('Pronouns:'); ?></label>
<div class="col-sm-4"><?php
   if (!empty($obj['pronouns'])) {
      echo $obj['pronouns'];
   }
?></div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

<!-- temperature -->
<div class="form-group row">
<label for="temperature " class="control-label col-sm-2">Temperature:</label>
<div class="col-sm-4">
<input id="temperature" type=text name="temperature" <?php
   if (!empty($obj['temperature'])) {
      echo "value='". $obj['temperature'] ."'";
   }
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Blood pressure -->
<div class="form-group row">
<label for="blood_pressure" class="control-label col-sm-2">Blood pressure (sistolic/distolic):</label>
<div class="col-sm-4">
<input id="blood_pressure" type=text name="blood_pressure" <?php 
if ( !empty($obj['blood_pressure']) ) { 
   echo "value='". $obj['blood_pressure'] ."'"; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Chief complaint 
   TODO: Is this the same as the visit reason field?
-->
<div class="form-group row">
<label for="chief_complaint" class="control-label col-sm-2">Chief Complaint:</label>
<div class="col-sm-10">
<input id="chief_complaint" type=text name="chief_complaint" size=125 <?php 
if ( !empty($obj['chief_complaint']) ) { 
   echo "value='". $obj['chief_complaint'] ."'"; 
} 
?>>
</div>
</div>

<!-- Notes -->
<div class="form-group row">
<label for="notes" class="control-label col-sm-2"><?php 
   echo xlt('Onset / Location / Duration / Characteristics / Aggravating Factors / Relieving / Timing');
?>:</label>
<div class="col-sm-10">
<textarea id="notes" rows=4 name="notes" class="col-sm-10"><?php 
if ( !empty($obj['notes']) ) { 
   echo $obj['notes']; 
} 
?>
</textarea>
</div>
</div>

<!-- Concerns -->
<div class="form-group row">
<label for="concerns" class="control-label col-sm-2">Other Concerns / Perception of Health:</label>
<div class="col-sm-10">
<textarea id="concerns" rows=4 name="concerns" class="col-sm-10">
<?php 
if ( !empty($obj['concerns']) ) { 
   echo $obj['concerns']; 
} 
?>
</textarea>
</div>
</div>

<!-- Other services 
   TODO: how is this different than the information that's selected on the visit form?
-->
<div class="form-group row">
<label for="services" class="control-label col-sm-2">Other services tonight:</label>
<div class="col-sm-10">
<textarea id="services" rows=4 name="services" class="col-sm-10">
<?php 
if ( !empty($obj['services']) ) { 
   echo $obj['services']; 
} 
?>
</textarea>
</div>
</div>

<!-- Pharmacy
   The pharmacy association for a participant is done through by associating
   a pharmacy record (entered in the practice administration page) via a select
   list in the demographics edit page.  This is an issue because we might not 
   know all of the pharmacies we might want to work with a head of time and
   looking up this information may slow down clinic workflow.

   Clinical staff suggest this process is currently using a paper perscription
   and calls from providers to specific pharmacies

-->
<div class="form-group row">
<label for="pharmacy" class="control-label col-sm-2">Pharmacy:</label>
<div class="col-sm-10">
<input id="pharmacy" name="pharmacy" type="text" size=125 <?php 
if ( !empty($obj['pharmacy']) ) { 
   echo "value='". $obj['pharmacy'] ."'"; 
} 
?>>
</div>
</div>

<!-- Contact preferences
   We can contact participants by Voice, text, or email
-->
<div class="form-group row">
<label for="contact_preferences" class="control-label col-sm-2">Contact preferences:</label>
<div class="col-sm-4">
<select name="contact_preferences" id="contact_preferences" class="select2 form-control"

<?php if (isset($obj['contact_preferences'])) {
   echo 'data-placeholder="'. $obj['contact_preferences'] .'"';
} else {
   echo 'data-placeholder="What are you contact preferences?"';
}
?>

>
<option></option>
<?php echo getListOptions('contact_preferences'); ?>
</select>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<div id="email" class="form-group row ">
<label for="email" class="control-label col-sm-2">Email:</label>
<div class="col-sm-4">
<input id="email" name="email" type="text" size=50 <?php 
if ( !empty($obj['email']) ) { 
   echo "value='". $obj['email'] ."'"; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<div id="phone_home" class="form-group row ">
<label for="phone_home" class="control-label col-sm-2">Other phone:</label>
<div class="col-sm-4">
<input id="phone_home" name="phone_home" type="text" <?php 
if ( !empty($obj['phone_home']) ) { 
   echo "value='". $obj['phone_home'] ."'"; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<div id="phone_cell" class="form-group row ">
<label for="phone_cell" class="control-label col-sm-2">Cell phone:</label>
<div class="col-sm-4">
<input id="phone_cell" name="phone_cell" type="text" <?php 
if ( !empty($obj['phone_cell']) ) { 
   echo "value='". $obj['phone_cell'] ."'"; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

</div> <!-- container -->
</div> <!-- container -->

<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<input type="checkbox" name="private" id="private"><label for="private">This note is private</label>
<br>
-->

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); $('#my_form').submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });

    // selectize the two lists to allow selecting a predefined value from 
    // a staff managed list and allo for adding custum entered values since
    // we are storing it all as a vahchar(255) in the db anyhow
    $('.select2').select2({
       tags: true,
    });

    // utility to display different forms based on the contact preferences 
    // selected
    var contact_preferences = $('select#contact_preferences option:selected').text();
    if ( contact_preferences === 'text' ) {
       $('div#phone_cell').show();
       $('div#phone_home').hide();
       $('div#email').hide();
    } else if ( contact_preferences === 'email' ) {
       $('div#phone_cell').hide();
       $('div#phone_home').hide();
       $('div#email').show();
    } else if ( contact_preferences === 'phone call' ) {
       $('div#phone_cell').hide();
       $('div#phone_home').show();
       $('div#email').hide();
    } else {
       $('div#phone_cell').hide();
       $('div#phone_home').hide();
       $('div#email').hide();
    }

    $('select#contact_preferences').change(function() {
	    var contact_preferences = $('select#contact_preferences option:selected').text();
	    if ( contact_preferences === 'text' ) {
	       $('div#phone_cell').show();
	       $('div#phone_home').hide();
	       $('div#email').hide();
	    } else if ( contact_preferences === 'email' ) {
	       $('div#phone_cell').hide();
	       $('div#phone_home').hide();
	       $('div#email').show();
	    } else if ( contact_preferences === 'phone call' ) {
	       $('div#phone_cell').hide();
	       $('div#phone_home').show();
	       $('div#email').hide();
	    }
    });
    
});

//# sourceURL=sji_triage.js
</script>

</html>
