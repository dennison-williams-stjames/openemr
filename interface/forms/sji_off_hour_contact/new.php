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

formHeader("Form: SJI Off-Hour Contact");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_off_hour_contact";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

// get the record from the database
$obj = get_oh_form_obj($pid, $_GET["id"]);

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
<?php Header::setupHeader(['bootstrap', 'datetime-picker']); ?>

<script language="JavaScript">
// required for textbox date verification
var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

</head>

<body class="body_top">
<div class="container">

<div class="row bg-primary">
<div class="col-sm-12">
<h2 class="text-center"><?php echo xlt('St. James Infirmary Off-Hour Contact'); ?></h2>
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

<div class="col-sm-3 padding form-group"></div>

<div class="col-sm-6 text-center">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

<div class="col-sm-3 text-center">
<?php echo date("F d, Y", time()); ?>
</div>

</div> <!-- class="form-group row" -->

<div class="row">
<div class="col-sm-12">
<p><ul><li><?php
echo xlt('Remind the participant the clinic isn\'t open and a clinician will return the call within 24 hours');
?></li>

<li><?php
echo xlt('Send an email reminder with the participants medical record number to Chuck <chuck@stjamesinfirmary.org> for STRIDE participants and to Brianna <brianna@stjamesinfirmary.org>, <brianna.singleton@ucsf.edu> for all other participants');
?></li></ul>
</p>

<p>
</div> <!-- col-sm-12 -->
</div> <!-- row -->

<!-- aliases -->
<div class="form-group row">
<label for="aliases" class="col-sm-6 control-label text-right"><?php echo xlt("Participant aliases"); ?>:</label>
<div class="col-sm-6 float-left"><?php echo $obj['aliases']; ?></div>
</div>

<!-- gender -->
<div class="form-group row">
<label for="gender" class="col-sm-6 control-label text-right"><?php echo xlt("Participant's gender"); ?>:</label>
<div class="col-sm-6"><?php echo $obj['sex']; ?></div>
</div>

<!-- pronouns -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="pronouns"><?php echo xlt("Participant's pronouns"); ?>:</label>
<div class="col-sm-6"><?php echo $obj['pronouns']; ?></div>
</div>

<!-- reason -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="reason"><?php echo xlt('Reason for call'); ?>:</label>
<div class="col-sm-6"><?php
if (! empty($obj['reason']) ) {
   echo $obj['reason'];
}
?></div>
</div>

<!-- work number -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="phone_biz"><?php echo xlt('Business number'); ?>:</label>
<div class="col-sm-6">
<input id="phone_biz" name="phone_biz" class="form-control" type=text
<?php 
if (isset($obj['phone_biz'])) {
   echo "value='". $obj['phone_biz'] ."'";
}
?>>
</div>
</div>

<!-- home number -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="phone_home"><?php echo xlt('Home number'); ?>:</label>
<div class="col-sm-6">
<input id="phone_home" name="phone_home" class="form-control" type=text
<?php 
if (isset($obj['phone_home'])) {
   echo "value='". $obj['phone_home'] ."'";
}
?>>
</div>
</div>

<!-- cell number -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="phone_cell"><?php echo xlt('Cell number'); ?>:</label>
<div class="col-sm-6">
<input id="phone_cell" name="phone_cell" class="form-control" type=text
<?php 
if (isset($obj['phone_cell'])) {
   echo "value='". $obj['phone_cell'] ."'";
}
?>>
</div>
</div>

<!-- email -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="email"><?php echo xlt('Email address'); ?>:</label>
<div class="col-sm-6">
<input id="email" name="email" class="form-control" type=text
<?php 
if (isset($obj['email'])) {
   echo "value='". $obj['email'] ."'";
}
?>>
</div>
</div>

<!-- hipaa_voice -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="hipaa_voice"><?php echo xlt('Can we leave a message'); ?>?</label>
<div class="col-sm-6">
<input id="hipaa_voice" type=checkbox name="hipaa_voice" <?php 
if (! empty($obj['hipaa_voice']) && preg_match('/(?:YES|on)/', $obj['hipaa_voice'])) {
   echo "checked";
}
?>>
</div>
</div>

<!-- hipaa_allowsms -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="hipaa_allowsms"><?php echo xlt('Can we send a text message'); ?>?</label>
<div class="col-sm-6">
<input id="hipaa_allowsms" type=checkbox name="hipaa_allowsms" <?php 
if (! empty($obj['hipaa_allowsms']) && preg_match('/(?:YES|on)/', $obj['hipaa_allowsms']) ) {
   echo "checked";
}
?>>
</div>
</div>

<!-- hipaa_allowemail -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="hipaa_allowemail"><?php echo xlt('Can we send an email message'); ?>?</label>
<div class="col-sm-6">
<input id="hipaa_allowemail" type=checkbox name="hipaa_allowemail" <?php 
if (! empty($obj['hipaa_allowemail']) && preg_match('/(?:YES|on)/', $obj['hipaa_allowemail']) ) {
   echo "checked";
}
?>>
</div>
</div>

<!-- date -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="follow_up_date"><?php echo xlt('When was the participant contacted'); ?>?</label>
<div class="col-sm-6">
<input id="follow_up_date" type=text name="follow_up_date" class="form-control datepicker" data-placeholder="<?php echo xlt('When was the participant contacted'); ?>"
<?php 
if (! empty($obj['follow_up_date']) ) {
   echo 'value="'. $obj['follow_up_date'] .'"';
}
?>>
</div>
</div>

<!-- assesment_plan -->
<div class="form-group row">
<label class="col-sm-6 control-label text-right" for="assesment_plan"><?php echo xlt('Assesment and plan'); ?>:</label>
<div class="col-sm-6">
<textarea rows=3 id="assesment_plan" type=text name="assesment_plan" class="form-control sm-textarea" 
data-placeholder="<?php echo xlt('Assesment and plan'); ?>">
<?php
if (! empty($obj['assesment_plan']) ) {
   echo $obj['assesment_plan'];
}
?>
</textarea>
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

    // Set the class to datepicker and let this do the rest
    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = false; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

});

</script>

</html>
