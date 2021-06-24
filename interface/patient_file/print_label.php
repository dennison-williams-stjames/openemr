<?php
/**
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Dennison Williams <dennison.williams@stjamesinfirmary.org>
 */

use OpenEMR\Core\Header;

require_once('../globals.php');
require_once($GLOBALS['srcdir'].'/acl.inc');

 $encounterid = $_REQUEST['encounterid'];

 $info_msg = "";

?>
<html>
<head>
    <?php Header::setupHeader('opener'); ?>
<title><?php echo xlt('Print visit labels'); ?></title>

<script language="javascript">
	// Java script function for closing the popup
	function popup_close() {
	    dlgclose();
	}

	function print_labels() {
	    // TODO: what does this do?
	    top.restoreSession();
	    // Not Empty
	    // Strong if required
	    // Matches

	    $.post("print_label_ajax.php", {
		    num:    $("input[name='number']").val(),
		    encounter:    <?php echo attr($encounterid); ?>,
		},
		function(data) {
		    $("#display_msg").html(data);
		}

	    );
	    return false;
	}
</script>
</head>

<body class="body_top">
<?php
if (array_key_exists('form_submit', $_POST)) {
    if ($encounterid) {

        if (!acl_check('encounters', 'coding')) {
            die("Not authorized!");
        }

    } else {
        die("Nothing was recieved to print!");
    }

    if (! $info_msg) {
        $info_msg = xl('labels printed.');
    }

  // Close this window and tell our opener that it's done.
  // Not sure yet if the callback can be used universally.
    echo "<script language='JavaScript'>\n";
    if (!$encounterid) {
        if ($info_msg) {
            echo " alert('" . addslashes($info_msg) . "');\n";
        }
        echo " dlgclose('imdeleted',false);\n";
    } else {
        if ($GLOBALS['sql_string_no_show_screen']) {
            echo " dlgclose('imdeleted', $encounterid);\n";
        } else { // this allows dialog to stay open then close with button or X.
            echo " opener.dlgSetCallBack('imdeleted', $encounterid);\n";
        }
    }
    echo "</script></body></html>\n";
    exit();
}
?>

<div id="display_msg"></div>
<form method='post' name="print_label_form" action='print_label.php?encounterid=<?php echo attr($encounterid) ?>' onsubmit="print_labels();">

<p class="lead">&nbsp;<br>

<?php echo xlt('How many?'); ?>
<input type="text" name="number" maxlength=3 autofocus>
<div class="btn-group">
    <a href="#" onclick="print_labels();" class="btn btn-lg btn-save btn-default"><?php echo xlt('Print labels'); ?></a>
    <a href='#' class="btn btn-lg btn-link btn-cancel" onclick="popup_close();"><?php echo xlt('Cancel');?></a>
</div>
<input type='hidden' name='form_submit' value='<?php echo xla('Yes, Print labels'); ?>'/>
</form>
</body>
</html>
