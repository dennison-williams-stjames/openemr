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
formHeader("Form: Medical encounter");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_medical";

// get the record from the database
$obj = array();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $obj = array_merge(
        formFetch("form_".$form_name, $_GET["id"]),
        sji_extendedMedical_formFetch($_GET["id"]));
} else {
    $obj = sji_extendedMedical_formFetch();
}
error_log(__FILE__ ." obj: ". print_r($obj, 1));

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
<span class="title"><?php echo xlt('Medical record'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- Name, alias -->
<div class="form-group row">
<label for="name" class="col-sm-2 control-label"><?php echo xlt('Name:'); ?></label>
<div class="col-sm-4"><?php
   if (!empty($obj['name'])) {
      echo $obj['name'];
   }

   if (!empty($obj['aliases'])) {
      if (!empty($obj['name'])) {
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

<!-- SOAP S -->
<div class="form-group row">
<label for="subjective" class="col-sm-2 control-label"><?php echo xlt('Subjective:'); ?></label>
<div class="col-sm-10">
<textarea id="subjective" rows=4 name="subjective" class="col-sm-10"><?php 
if ( !empty($obj['subjective']) ) { 
   echo $obj['subjective']; 
} 
?>
</textarea>
</div>
</div>

<!-- SOAP O -->
<div class="form-group row">
<label for="objective" class="col-sm-2 control-label"><?php echo xlt('Objective:'); ?></label>
<div class="col-sm-10">
<textarea id="objective" rows=4 name="objective" class="col-sm-10"><?php 
if ( !empty($obj['objective']) ) { 
   echo $obj['objective']; 
} 
?>
</textarea>
</div>
</div>

<!-- SOAP A -->
<div class="form-group row">
<label for="assessment" class="col-sm-2 control-label"><?php echo xlt('Assessment:'); ?></label>
<div class="col-sm-10">
<textarea id="assessment" rows=4 name="assessment" class="col-sm-10"><?php 
if ( !empty($obj['assessment']) ) { 
   echo $obj['assessment']; 
} 
?>
</textarea>
</div>
</div>

<!-- SOAP P -->
<div class="form-group row">
<label for="plan" class="col-sm-2 control-label"><?php echo xlt('Plan:'); ?></label>
<div class="col-sm-10">
<textarea id="plan" rows=4 name="plan" class="col-sm-10"><?php 
if ( !empty($obj['plan']) ) { 
   echo $obj['plan']; 
} 
?>
</textarea>
</div>
</div>

<!-- ICD10 primary -->
<div class="form-group row">
<label for="icd10_primary" class="col-sm-2 control-label"><?php echo xlt('ICD10 primary:'); ?></label>
<div class="col-sm-4">
<select name="icd10_primary[]" id="icd10_primary" multiple=multiple class="select2 form-control" data-placeholder="Select primary ICD10 codes">
<option></option>
<?php 
echo getICD10PrimaryOptions(); 
?>
</select>
</div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

<!-- ICD10 secondary -->
<div class="form-group row">
<label for="icd10_secondary" class="col-sm-2 control-label"><?php echo xlt('ICD10 secondary:'); ?></label>
<div class="col-sm-4">
<select name="icd10_secondary[]" id="icd10_secondary" multiple=multiple class="select2 form-control" data-placeholder="Select secondary ICD10 codes">
<option></option>
<?php 
echo getICD10SecondaryOptions(); 
?>
</select>
</div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

<!-- CPT -->
<div class="form-group row">
<label for="cpt_codes" class="col-sm-2 control-label"><?php echo xlt('CPT codes:'); ?></label>
<div class="col-sm-4">
<select name="cpt_codes[]" id="cpt_codes" multiple=multiple class="select2 form-control" data-placeholder="Select CPT codes">
<option></option>
<?php 
echo getCPTCodes(); 
?>
</select>
</div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

<!-- Duration -->
<div class="form-group row">
<label for="duration" class="col-sm-2 control-label"><?php echo xlt('Duration:'); ?></label>
<div class="col-sm-4">
<select name="duration" id="duration" class="select2 form-control" data-placeholder="Select or enter the time spent...">
<option></option>
<?php echo getListOptions('sji_medical_psychiatric_duration'); ?>
</select>
</div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

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
