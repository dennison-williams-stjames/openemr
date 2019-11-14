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
if (!empty($_GET['id'])) {
    $obj = array_merge(
        formFetch("form_".$form_name, $_GET["id"]),
        sji_extendedTriage_formFetch($_GET["id"]));

}

/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "";
    $found = 0;

    $selected = array();
    preg_match('/sji_medical_psychiatric_(.*)/', $list_id, $matches);
    if (isset($matches[1]) && isset($obj[$matches[1]])) {
        $selected = $obj[$matches[1]];
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
   if (!empty($obj['fname'])) {
      echo $obj['fname'];
   }

   if (!empty($obj['lname'])) {
      if (!empty($obj['fname'])) {
         echo " ";
      }
      echo $obj['lname'];
   }

   if (!empty($obj['aliases'])) {
      if (!empty($obj['fname']) || !empty($obj['lname'])) {
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
<label for="blood_pressure" class="control-label col-sm-2">Blood pressure (sistolic/distolic)</label>
<div class="col-sm-4">
<input id="blood_pressure" type=text name="blood_pressure" <?php 
if ( !empty($obj['bps']) && !empty($obj['bpd']) ) { 
   echo $obj['bps'] .'/'. $obj['bpd']; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Chief complaint 
   TODO: Is this the same as the visit reason field?
-->
<div class="form-group row">
<label for="chief_complaint" class="control-label col-sm-2">Chief Complaint</label>
<div class="col-sm-4">
<input id="chief_complaint" type=text name="chief_complaint" <?php 
if ( !empty($obj['chief_complaint']) ) { 
   echo $obj['chief_complaint']; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Notes -->
<div class="form-group row">
<label for="notes" class="control-label col-sm-2">Onset/Location/Duration/Characteristics/Aggravating Factors/Relieving/Timing</label>
<div class="col-sm-4">
<textarea id="notes" rows=4 name="notes" <?php 
if ( !empty($obj['notes']) ) { 
   echo $obj['notes']; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Concerns -->
<div class="form-group row">
<label for="concerns" class="control-label col-sm-2">Other Concerns/Perception of Health</label>
<div class="col-sm-4">
<textarea id="concerns" rows=4 name="concerns" <?php 
if ( !empty($obj['concerns']) ) { 
   echo $obj['concerns']; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- Other services 
   TODO: how is this different than the information that's selected on the visit form?
-->
<div class="form-group row">
<label for="services" class="control-label col-sm-2">Other services tonight</label>
<div class="col-sm-4">
<textarea id="services" rows=4 name="services" <?php 
if ( !empty($obj['services']) ) { 
   echo $obj['services']; 
} 
?>>
</div>
<div class="col-sm-6 text-center"></div>
</div>

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
});

</script>

</html>
