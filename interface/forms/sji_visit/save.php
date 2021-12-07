<?php
/**
 * Encounter form save script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once('shared.php');
include_once("$srcdir/api.inc");

if ($encounter == "") {
    $encounter = date("Ymd");
}

if (!$pid) {
    $pid = $_SESSION['pid'];
}

$submission = array();
foreach ($visit_columns as $column) {
   if (isset($_REQUEST[$column])) {
      $submission[$column] = $_REQUEST[$column];
   }
}

if ($_REQUEST["mode"] == "new") {
   $newid = formSubmit($table_name, $submission, '', $userauthorized);
   addForm($encounter, "St. James Infirmary Visit Intake", $newid, "sji_visit", $pid, $userauthorized);
   sji_extendedVisit($newid, $_REQUEST);
} elseif ($_REQUEST["mode"] == "update") {
   $success = formUpdate($table_name, $submission, $_REQUEST["id"], $userauthorized);
   sji_extendedVisit($_REQUEST["id"], $_REQUEST);
} else {
   die("Unknown mode '" . text($mode) . "'");
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
