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
$table_name = "form_sji_progress_note";
$form_name = "sji_progress_note";

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

$submission = array();

/* Make some transformations */

if (!empty($_POST["sji_progress_notes_duration"])) {
   if (preg_match('/(\d+)(?::(\d\d))?/', $_POST["sji_progress_notes_duration"], $matches)) {
      if (!empty($matches[2])) {
         $submission['duration'] = $matches[1]*60 + $matches[2];
      } else {
         $submission['duration'] = $matches[1];
      }
   } else {
      // TODO: how do we return parse errors?
   }
}

if (!empty($_POST['inactive']) && $_POST['inactive'] == 'on') {
   $_POST['inactive'] = 1;
} else {
   $_POST['inactive'] = 0;
} 

error_log(__FILE__ .' inactive: '. print_r($_POST['inactive'], 1));

foreach ($progress_note_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($encounter, "Progress note", $newid, $form_name, $pid, $userauthorized);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
