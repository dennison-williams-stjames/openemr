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
formHeader("Form: Progress Note");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_progress_note";

// get the record from the database
$obj = array();
if (!empty($_GET['id'])) {
    $obj = formFetch("form_".$form_name, $_GET["id"]);
} 

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

<!-- treatment_location -->
<div class="form-group row">
<label for="treatment_location " class="control-label col-sm-2">Teatment location:</label>
<div class="col-sm-4">
<select id="treatment_location" name="treatment_location" <?php
if (isset($obj['treatment_location'])) {
   echo 'data-placeholder="'. $obj['treatment_location'] .'"';
} else {
   echo 'data-placeholder="Where was the treatment location?"';
}
?>

>
<option></option>
<?php echo getListOptions('treatment_location'); ?>
</select>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- service_type -->
<div class="form-group row">
<label for="service_type " class="control-label col-sm-2">Service type:</label>
<div class="col-sm-4">
<select id="service_type" name="service_type" <?php
if (isset($obj['service_type'])) {
   echo 'data-placeholder="'. $obj['service_type'] .'"';
} else {
   echo 'data-placeholder="What type of service was provided?"';
}
?>

>
<option></option>
<?php echo getListOptions('service_type'); ?>
</select>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- sji_progress_notes_duration -->
<div class="form-group row">
<label for="sji_progress_notes_duration " class="control-label col-sm-2">Service duration:</label>
<div class="col-sm-4">
<select id="sji_progress_notes_duration" name="sji_progress_notes_duration" <?php
if (isset($obj['sji_progress_notes_duration'])) {
   echo 'data-placeholder="'. $obj['sji_progress_notes_duration'] .'"';
} else {
   echo 'data-placeholder="What service duration?"';
}
?>

>
<option></option>
<?php echo getListOptions('sji_progress_notes_duration'); ?>
</select>
</div>
<div class="col-sm-6 text-center"></div>
</div>

<!-- documentation -->
<div class="form-group row">
<label for="documentation" class="control-label col-sm-2"><?php 
   echo xlt('Documentation and travel time');
?>:</label>
<div class="col-sm-10">
<textarea id="documentation" rows=4 name="documentation" class="col-sm-10"><?php 
if ( !empty($obj['documentation']) ) { 
   echo $obj['documentation']; 
} 
?>
</textarea>
</div>
</div>

<!-- progress note -->
<div class="form-group row">
<label for="progress_note" class="control-label col-sm-2"><?php 
   echo xlt('Progress note');
?>:</label>
<div class="col-sm-10">
<textarea id="progress_note" rows=4 name="progress_note" class="col-sm-10"><?php 
if ( !empty($obj['progress_note']) ) { 
   echo $obj['progress_note']; 
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
