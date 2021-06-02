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


require_once('common.php');
include_once("../../globals.php");
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_triage";
$form_name = "sji_triage";

if (!$pid) {
    $pid = $_SESSION['pid'];
}

if ($encounter == "") {
    if (!empty($_SESSION['encounter'])) {
       $encounter = $_SESSION['encounter'];
    } else {
       $encounter = date("Ymd");
    }
}

/* Make some transformations */
if (!empty($_POST['contact_preferences'])) {
   // set the hippa values based on which preferences is selected
   $_POST['hipaa_allowemail'] = 'NO';
   $_POST['hipaa_allowsms'] = 'NO';
   $_POST['hipaa_voice'] = 'NO';
   switch ($_POST['contact_preferences']) {
      // TODO: we need a way to return errors here
      case 'email':
         $_POST['hipaa_allowemail'] = 'YES';
         break;
      case 'text':
         $_POST['hipaa_allowsms'] = 'YES';
         break;
      case 'phone call':
         $_POST['hipaa_voice'] = 'YES';
         break;
          
          
   }
} 

if (isset($_POST['evaluate_manage_established'])) {
   $_POST['evaluate_manage_established'] = 1;
} else {
   $_POST['evaluate_manage_established'] = 0;
}

$submission = array();

foreach ($triage_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($encounter, "Triage", $newid, $form_name, $pid, $userauthorized);
    sji_extendedTriage($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedTriage($_GET['id'], $_POST);
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
