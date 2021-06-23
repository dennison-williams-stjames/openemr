<?php
/**
 * alert .
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dennison Williams <dennison.williams@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
use OpenEMR\Core\Header;
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt("Heads up!"); ?></title>
</head>
<body>
    <div style="padding: 15px; text-align: center">
        <p class="h2"></p>
<?php
// get the list of participant alerts and display them
// show available documents
$db = $GLOBALS['adodb']['db'];
$sql = "SELECT form_sji_alert.date as date,form_sji_alert.alert as alert FROM form_sji_alert " .
        "LEFT JOIN forms on (form_sji_alert.id=forms.form_id) ".
        "WHERE form_sji_alert.pid = " .  $db->qstr($pid) ." ".
	"AND forms.formdir='sji_alert' ".
        "AND forms.deleted=0";
$result = $db->Execute($sql);
if ($db->ErrorMsg()) {
    echo $db->ErrorMsg();
}

while ($result && !$result->EOF) {
?>
   <p class="h4"><b><?php echo date('Y-m-d', strtotime($result->fields['date'])); ?></b>:
   <?php echo $result->fields['alert'] ?>
   </p>

<?php
   $result->MoveNext();
}        
?>

    </div>
</body>
</html>
