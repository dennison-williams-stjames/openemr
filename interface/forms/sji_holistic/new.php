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
formHeader("Form: Holistic encounter");
$returnurl = 'encounter_top.php';
$provider_results = sqlQuery("select fname, lname from users where username=?", array($_SESSION{"authUser"}));
/* name of this form */
$form_name = "sji_holistic";

// get the record from the database
if ($_GET['id'] != "") {
    $obj = formFetch("form_".$form_name, $_GET["id"]);
}

/* A helper function for getting list options */
function getListOptions($list_id, $fieldnames = array('option_id', 'title', 'seq')) {
    global $obj;
    $output = "";
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '<option value="'. $list_options['option_id'] .'" ';
        if ($obj['holistic_type'] == $list_options['option_id']) {
           $output .= 'selected="selected" ';
        } 
        $output .= '>'. $list_options['title'] .'</option>';
    }

    return $output;
}
?>

<html><head>
<?php Header::setupHeader(['bootstrap', 'datetime-picker', 'select2']); ?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

<!-- supporting javascript code -->
<script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js?v=<?php echo $v_js_includes; ?>"></script>

</head>

<body class="body_top">
<?php echo date("F d, Y", time()); ?>
<div class="container">

<form method=post action="<?php 
   if (isset($_GET['id'])) {
      echo $rootdir."/forms/".$form_name."/save.php?mode=update&id=".attr($_GET["id"]);
   } else {
      echo $rootdir."/forms/".$form_name."/save.php?mode=new";
   }
?>" name="my_form" id="my_form">

<div class="row bg-primary">
<div class="col-sm-12">
<span class="title"><?php echo xlt('Holistic record'); ?></span>
</div> <!-- col-sm-12 -->
</div> <!-- roww bg-primary -->

<!-- Holistic Type -->
<div class="form-group row">
<label for="holistic_type" class="col-sm-2 control-label"><?php echo xlt('Holistic encounter type:'); ?></label>
<div class="col-sm-4">
<select name="holistic_type" id="holistic_type" class="select2 form-control" data-placeholder="Select or enter a holistic type...">
<?php echo getListOptions('sji_holistic'); ?>
</select>
</div> <!-- col-sm-4 -->
<div class="col-sm-6"></div>
</div> <!-- row form-group -->

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

    $('#holistic_type').select2({create: true});
});

</script>

</html>
