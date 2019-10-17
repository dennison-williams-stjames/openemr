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
$sql = "SELECT date,alert FROM form_sji_alert " .
        "WHERE pid = " .  $db->qstr($pid);
$result = $db->Execute($sql);
if ($db->ErrorMsg()) {
    echo $db->ErrorMsg();
}

while ($result && !$result->EOF) {
?>
   <p class="h3"><b><?php echo $result->fields['date']; ?></b>:
   <?php echo $result->fields['alert'] ?>
   </p>

<?php
   $result->MoveNext();
}        
?>

    </div>
</body>
</html>
