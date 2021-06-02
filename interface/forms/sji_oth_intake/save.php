<?php
/**  Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30
 *   Copyright (C) Open Source Medical Software
 *
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
require_once('common.php');

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_oth_intake";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

$submission = array();
foreach ($oth_intake_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
      if ($submission[$column] === 'on') {
         $submission[$column] = 1;
      }
   }
}

foreach ($oth_intake_binary_columns as $column) {
   if (!isset($submission[$column])) {
      $submission[$column] = 0;
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($_SESSION["encounter"], "OTH Rental Subsidy Applicartion", $newid, "sji_oth_intake", $pid, $userauthorized);
    sji_extendedOTHIntake($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedOTHIntake($_GET["id"], $_POST);
}

formHeader("Redirecting....");
formJump();
formFooter();
