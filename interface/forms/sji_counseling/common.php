<?php
require_once(dirname(__FILE__).'/../../globals.php');
include_once("$srcdir/api.inc");
include_once("$srcdir/forms.inc");

$counseling_columns = array(
   'counseling_type', 'counseling_time', 'progress_notes'
);

function sji_counseling_formFetch($formid) {
	$return = array();

	// Add on counseling list
	$query = "select counseling from form_sji_counseling_counseling where pid=?";
	$res = sqlStatement($query, array($formid));
	$counseling = array();
	while ($row = sqlFetchArray($res)) {
	   $counseling[] = $row['counseling'];
	}
	if (sizeof($counseling)) {
	   $return['counseling'] = $counseling;
	}

	return $return;
}

function sji_extendedCounseling($formid, $submission) {
    global $pid;

    sqlStatement("delete from form_sji_counseling_counseling where pid=?", array($formid));
    if (isset($submission['counseling'])) {
	foreach ($submission['counseling'] as $counseling) {
            sqlInsert("insert into form_sji_counseling_counseling(counseling, pid) values(?, ?)", array($counseling, $formid));
        }
    }
}

