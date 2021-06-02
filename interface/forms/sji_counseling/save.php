<?php
/*
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
include_once("$srcdir/forms.inc");
require_once("common.php");

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_counseling";
$form_name = "sji_counseling";

if ($encounter == "") {
    $encounter = date("Ymd");
}

if (!$pid) {
    $pid = $_SESSION['pid'];
}

$submission = array();
foreach ($counseling_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($encounter, "Counseling", $newid, $form_name, $pid, $userauthorized);
    sji_extendedCounseling($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedCounseling($_GET['id'], $_POST);
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
