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
require_once("common.php");
formHeader("Form: Counseling encounter");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_counseling";

// get the record from the database
if (isset($_GET['id'])) {
    $obj = array_merge(
	formFetch("form_".$form_name, $_GET["id"]),
	sji_counseling_formFetch($_GET["id"]));
}

function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;

    $output = "";
    $selected = array();
    $list_id = preg_replace('/^sji_(.*)/', '$1', $list_id);

    if (isset($obj[$list_id])) {
           $selected = $obj[$list_id];
    }
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array('sji_'. $list_id));
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
<?php 
if (isset($obj['date'])) {
   echo date("F d, Y", strtotime($obj['date']));
} else {
   echo date("F d, Y"); 
}
?>

<form method=post action="<?php 
   if (isset($_GET['id'])) {
      echo $rootdir."/forms/".$form_name."/save.php?mode=update&id=".attr($_GET["id"]);
   } else {
      echo $rootdir."/forms/".$form_name."/save.php?mode=new";
   }
?>" name="my_form" id="my_form">

<div class="row bg-primary">
<div class="col-sm-12">
<span class="title"><?php echo xlt('Harm Reduction Counseling'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- Counseling Type -->
<div class="form-group row">
<label for="counseling_type" class="col-sm-2 control-label"><?php echo xlt('Type of Session:'); ?></label>
<div class="col-sm-4">
<select name="counseling_type" id="counseling_type" class="select2 form-control" data-placeholder="Select or enter a counseling type...">
<option></option>
<?php echo getListOptions('sji_counseling_type'); ?>
</select>
</div>
<div class="col-sm-6"></div>
</div>
<!-- Counseling Type -->

<!-- Counseling-->
<div class="form-group row">
<label for="counseling" class="col-sm-2 control-label"><?php echo xlt('Counseling:'); ?></label>
<div class="col-sm-4">
<select name="counseling[]" id="counseling" class="select2 form-control" data-placeholder="Select or enter..." multiple=multiple>
<?php echo getListOptions('sji_counseling'); ?>
</select>
</div>
<div class="col-sm-6"></div>
</div>
<!-- Counseling Type -->

<div class="form-group row">
<label for="counseling_time" class="col-sm-2 control-label"><?php echo xlt('Duration:'); ?></label>
<div class="col-sm-4">
<select name="counseling_time" id="counseling_time" class="select2 form-control" data-placeholder="Select or enter the time spent...">
<option></option>
<?php echo getListOptions('sji_counseling_time'); ?>
</select>
</div> <!-- col-sm-6 -->
<div class="col-sm-6"></div>
</div> <!-- row -->

<!-- Progress notes -->
<div class="form-group row">
<label for="progress_notes" class="col-sm-2 control-label"><?php echo xlt('Counselor notes / Treatment dispensed:'); ?></label>
<div class="col-sm-4">
<textarea name="progress_notes" id="progress_notes" class="form-control" rows=6 ><?php
if (!empty($obj['progress_notes'])) {
   echo $obj['progress_notes'];
}
?></textarea>
</div> <!-- col-sm-4 -->
<div class="col-sm-6"></div>
</div> <!-- row form-group -->

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>

</div> <!-- container -->

<?php
// commented out below private field, because no field in database, and causes error.
?>
<!--
<input type="checkbox" name="private" id="private"><label for="private">This note is private</label>
<br>
-->

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
