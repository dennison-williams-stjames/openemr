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
formHeader("Form: Mental Health Treatment Plan");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_mental_health_treatment_plan";

// get the record from the database
$obj = array();
if (!empty($_GET['id'])) {
    $obj = formFetch("form_".$form_name, $_GET["id"]);
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
<span class="title"><?php echo xlt('Mental Health Treatment Plan'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- diagnosis -->
<div class="form-group row">
<label for="diagnosis" class="control-label col-sm-2"><?php 
   echo xlt('Diagnosis');
?>:</label>
<div class="col-sm-10">
<textarea id="diagnosis" rows=4 name="diagnosis" class="col-sm-10"><?php 
if ( !empty($obj['diagnosis']) ) { 
   echo $obj['diagnosis']; 
} 
?>
</textarea>
</div>
</div>

<!-- presenting problem-->
<div class="form-group row">
<label for="presenting_problem" class="control-label col-sm-2"><?php 
   echo xlt('Presenting problem');
?>:</label>
<div class="col-sm-10">
<textarea id="presenting_problem" rows=4 name="presenting_problem" class="col-sm-10"><?php 
if ( !empty($obj['presenting_problem']) ) { 
   echo $obj['presenting_problem']; 
} 
?>
</textarea>
</div>
</div>

<!-- clients_goals -->
<div class="form-group row">
<label for="clients_goals" class="control-label col-sm-2"><?php 
   echo xlt('Client\'s goals');
?>:</label>
<div class="col-sm-10">
<textarea id="clients_goals" rows=4 name="clients_goals" class="col-sm-10"><?php 
if ( !empty($obj['clients_goals']) ) { 
   echo $obj['clients_goals']; 
} 
?>
</textarea>
</div>
</div>

<!-- clinical_objectives -->
<div class="form-group row">
<label for="clinical_objectives" class="control-label col-sm-2"><?php 
   echo xlt('Clinical objectives');
?>:</label>
<div class="col-sm-10">
<textarea id="clinical_objectives" rows=4 name="clinical_objectives" class="col-sm-10"><?php 
if ( !empty($obj['clinical_objectives']) ) { 
   echo $obj['clinical_objectives']; 
} 
?>
</textarea>
</div>
</div>

<!-- treatment_frequency -->
<div class="form-group row">
<label for="treatment_frequency" class="control-label col-sm-2"><?php 
   echo xlt('Treatment frequency');
?>:</label>
<div class="col-sm-10">
<textarea id="treatment_frequency" rows=4 name="treatment_frequency" class="col-sm-10"><?php 
if ( !empty($obj['treatment_frequency']) ) { 
   echo $obj['treatment_frequency']; 
} 
?>
</textarea>
</div>
</div>

<!-- interventions_plan -->
<div class="form-group row">
<label for="interventions_plan" class="control-label col-sm-2"><?php 
   echo xlt('Interventions plan');
?>:</label>
<div class="col-sm-10">
<textarea id="interventions_plan" rows=4 name="interventions_plan" class="col-sm-10"><?php 
if ( !empty($obj['interventions_plan']) ) { 
   echo $obj['interventions_plan']; 
} 
?>
</textarea>
</div>
</div>

<!-- discharge_plan -->
<div class="form-group row">
<label for="discharge_plan" class="control-label col-sm-2"><?php 
   echo xlt('Discharge plan');
?>:</label>
<div class="col-sm-10">
<textarea id="discharge_plan" rows=4 name="discharge_plan" class="col-sm-10"><?php 
if ( !empty($obj['discharge_plan']) ) { 
   echo $obj['discharge_plan']; 
} 
?>
</textarea>
</div>
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
    
});

//# sourceURL=sji_triage.js
</script>

</html>
