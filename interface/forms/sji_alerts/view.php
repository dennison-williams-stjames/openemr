<?php
/**
 *   This program is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU General Public License
 *   as published by the Free Software Foundation; either version 2
 *   of the License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */



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
    $found = 0;
    $query = sqlStatement("SELECT ".implode(',', $fieldnames)." FROM list_options where list_id = ? AND activity = 1 order by seq", array($list_id));
    while ($list_options = sqlFetchArray($query)) {
        $output .= '<option value="'. $list_options['option_id'] .'" ';
        if ($obj['holistic_type'] == $list_options['option_id']) {
           $output .= 'selected="selected" ';
           $found = 1;
        } 
        $output .= '>'. $list_options['title'] .'</option>';
    }

    if (!$found && $obj['holistic_type'] && $list_id == 'sji_holistic') {
       $output .= '<option selected="selected" value="'. $obj['holistic_type'] .'">'. $obj['holistic_type'] .'</>';
    }

    return $output;
}
?>
<html><head>
<?php Header::setupHeader(['bootstrap', 'datetime-picker', 'select2']); ?>

<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">

</head>
<body class="body_top">

<form method=post action="<?php echo $rootdir."/forms/".$form_name."/save.php?mode=update&id=".attr($_GET["id"]);?>" name="my_form" id="my_form">
<span class="title"><?php echo xlt('Holistic record'); ?></span><br></br>

<label for="holistic_type"><?php echo xlt('Holistic type:'); ?></label>
<select name="holistic_type" id="holistic_type" data-placeholder="Select or enter a holistic type...">
<?php echo getListOptions('sji_holistic'); ?>
</select>
<br> <br>

<div style="margin: 10px;">
<input type="button" class="save" value="    <?php echo xla('Save'); ?>    "> &nbsp;
<input type="button" class="dontsave" value="<?php echo xla('Don\'t Save'); ?>"> &nbsp;
</div>

</form>

</body>

<script language="javascript">

// jQuery stuff to make the page a little easier to use

$(document).ready(function(){
    $(".save").click(function() { top.restoreSession(); $("#my_form").submit(); });
    $(".dontsave").click(function() { parent.closeTab(window.name, false); });

    // disable the Print ability if the form has changed
    // this forces the user to save their changes prior to printing
    $("input").keydown(function() { $(".printform").attr("disabled","disabled"); });
    $("select").change(function() { $(".printform").attr("disabled","disabled"); });

});

</script>

</html>

