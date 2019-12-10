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
formHeader("Form: Mental Health Assessment");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_mental_health_assessment";

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
<span class="title"><?php echo xlt('Mental Health Assessment'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

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

<!-- strengths -->
<div class="form-group row">
<label for="strengths" class="control-label col-sm-2"><?php 
   echo xlt('Strengths');
?>:</label>
<div class="col-sm-10">
<textarea id="strengths" rows=4 name="strengths" class="col-sm-10"><?php 
if ( !empty($obj['strengths']) ) { 
   echo $obj['strengths']; 
} 
?>
</textarea>
</div>
</div>

<!-- risk_factors -->
<div class="form-group row">
<label for="risk_factors" class="control-label col-sm-2"><?php 
   echo xlt('Risk factors');
?>:</label>
<div class="col-sm-10">
<textarea id="risk_factors" rows=4 name="risk_factors" class="col-sm-10"><?php 
if ( !empty($obj['risk_factors']) ) { 
   echo $obj['risk_factors']; 
} 
?>
</textarea>
</div>
</div>

<!-- psychiatric_history -->
<div class="form-group row">
<label for="psychiatric_history" class="control-label col-sm-2"><?php 
   echo xlt('Psychiatric history');
?>:</label>
<div class="col-sm-10">
<textarea id="psychiatric_history" rows=4 name="psychiatric_history" class="col-sm-10"><?php 
if ( !empty($obj['psychiatric_history']) ) { 
   echo $obj['psychiatric_history']; 
} 
?>
</textarea>
</div>
</div>

<!-- psychosocial_history -->
<div class="form-group row">
<label for="psychosocial_history" class="control-label col-sm-2"><?php 
   echo xlt('Psychosocial history');
?>:</label>
<div class="col-sm-10">
<textarea id="psychosocial_history" rows=4 name="psychosocial_history" class="col-sm-10"><?php 
if ( !empty($obj['psychosocial_history']) ) { 
   echo $obj['psychosocial_history']; 
} 
?>
</textarea>
</div>
</div>

<!-- substance_history -->
<div class="form-group row">
<label for="substance_history" class="control-label col-sm-2"><?php 
   echo xlt('Substance use history');
?>:</label>
<div class="col-sm-10">
<textarea id="substance_history" rows=4 name="substance_history" class="col-sm-10"><?php 
if ( !empty($obj['substance_history']) ) { 
   echo $obj['substance_history']; 
} 
?>
</textarea>
</div>
</div>

<!-- medical_history -->
<div class="form-group row">
<label for="medical_history" class="control-label col-sm-2"><?php 
   echo xlt('Medical history');
?>:</label>
<div class="col-sm-10">
<textarea id="medical_history" rows=4 name="medical_history" class="col-sm-10"><?php 
if ( !empty($obj['medical_history']) ) { 
   echo $obj['medical_history']; 
} 
?>
</textarea>
</div>
</div>

<!-- mental_status_exam -->
<div class="form-group row">
<label for="mental_status_exam" class="control-label col-sm-2"><?php 
   echo xlt('Mental status exam');
?>:</label>
<div class="col-sm-10">
<textarea id="mental_status_exam" rows=4 name="mental_status_exam" class="col-sm-10"><?php 
if ( !empty($obj['mental_status_exam']) ) { 
   echo $obj['mental_status_exam']; 
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
