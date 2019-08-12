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

/* 
 * name of the database table associated with this form
 */
$table_name = "form_sji_stride_intake";

if ($encounter == "") {
    // TODO: this should use the system sequence generator
    $encounter = date("Ymd");
}

if (!$pid) {
    $pid = $_SESSION['pid'];
}

function sji_extendedIntake($formid, $submission) {
    global $pid;

    // Look for the external values which will likely have a different $encounter id */

    // TODO: look up the encounter 
    sqlStatement("delete from form_sji_intake_supportive_people where pid=?", array($formid));
    if (isset($submission['supportive_people'])) {
        // TODO: audit this
	foreach ($submission['supportive_people'] as $person) {
            sqlInsert("insert into form_sji_intake_supportive_people(supportive_people, pid) values(?, ?)", array($person, $formid));
        }
    }

}

$intake_columns = array(
   'why_are_you_here', 'hormone_duration', 'hormone_form_dosage',
   'hormone_program', 'why_stopped', 'why_continue', 
   'affect_expectations', 'effect_hopes', 'hormone_concerns',
   'who_out_to', 'financial_situation', 'safety_concerns',
   'useful_support', 'clinician_narrative'
);

$submission = array();
foreach ($intake_columns as $column) {
   if (isset($_POST[$column])) {
      $submission[$column] = $_POST[$column];
   }
}

if ($_GET["mode"] == "new") {
    $newid = formSubmit($table_name, $submission, '', $userauthorized);
    addForm($encounter, "St. James Infirmary STRIDE Intake", $newid, "sji_stride_intake", $pid, $userauthorized);
    sji_extendedIntake($newid, $_POST);
} elseif ($_GET["mode"] == "update") {
    $success = formUpdate($table_name, $submission, $_GET["id"], $userauthorized);
    sji_extendedIntake($_GET["id"], $_POST);
}

$_SESSION["encounter"] = $encounter;
formHeader("Redirecting....");
formJump();
formFooter();
