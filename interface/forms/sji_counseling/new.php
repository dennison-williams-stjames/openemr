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
formHeader("Form: Counseling encounter");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_counseling";

// get the record from the database
if ($_GET['id'] != "") {
    $obj = formFetch("form_".$form_name, $_GET["id"]);
}

/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "";
    $found = 0;
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '<option value="'. $list_options['option_id'] .'" ';
        if ($obj['counseling_type'] == $list_options['option_id']) {
           $output .= 'selected="selected" ';
           $found = 1;
        } else if ($obj['counseling_time'] == $list_options['option_id']) {
           $output .= 'selected="selected" ';
           $found = 1;
        }
        $output .= '>'. $list_options['title'] .'</option>';
    }

    if (!$found && $obj['counseling_type'] && $list_id == 'sji_counseling') {
       $output .= '<option selected="selected" value="'. $obj['counseling_type'] .'">'. $obj['counseling_type'] .'</>';
    }

    if (!$found && $obj['counseling_time'] && $list_id == 'sji_counseling_time') {
       $output .= '<option selected="selected" value="'. $obj['counseling_time'] .'">'. $obj['counseling_time'] .'</>';
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
<span class="title"><?php echo xlt('Counseling record'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- Counseling Type -->
<div class="form-group row">
<label for="counseling_type" class="col-sm-2 control-label"><?php echo xlt('Counseling type:'); ?></label>
<div class="col-sm-4">
<select name="counseling_type" id="counseling_type" class="select2 form-control" data-placeholder="Select or enter a counseling type...">
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
<?php echo getListOptions('sji_counseling_time'); ?>
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
    $('#counseling_type').select2({create: true});
    $('#counseling_time').select2({create: true});
});

</script>

</html>
