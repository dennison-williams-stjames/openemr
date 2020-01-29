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

$submission = array();
foreach ($off_hour_contact_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

// make a few transformations 
if (isset($_POST['hipaa_voice']) && $_POST['hipaa_voice'] === 'on') {
   $_POST['hipaa_voice'] = 'YES';
}

if (isset($_POST['hipaa_allowsms']) && $_POST['hipaa_allowsms'] === 'on') {
   $_POST['hipaa_allowsms'] = 'YES';
}

if (isset($_POST['hipaa_allowemail']) && $_POST['hipaa_allowemail'] === 'on') {
   $_POST['hipaa_allowemail'] = 'YES';
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($_SESSION['encounter'], "St. James Infirmary Off-hour Contact", $newid, "sji_off_hour_contact", $pid, $userauthorized);
    sji_extendedOffHourContact($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedOffHourContact($_GET["id"], $_POST);
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
