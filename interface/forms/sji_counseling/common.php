<?php
require_once(__DIR__.'/../../globals.php');
include_once($GLOBALS['srcdir'] ."/api.inc");
include_once($GLOBALS['srcdir']."/forms.inc");

global $counseling_columns;
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
	error_log(__FUNCTION__);

    sqlStatement("delete from form_sji_counseling_counseling where pid=?", array($formid));
    if (isset($submission['counseling'])) {
	foreach ($submission['counseling'] as $counseling) {
            sqlInsert("insert into form_sji_counseling_counseling(counseling, pid) values(?, ?)", array($counseling, $formid));
        }
    }
}

