<?php
/**
 * Encounter form save script.
 *
 * Copyright (C) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @author  Roberto Vasquez <robertogagliotta@gmail.com>
 * @link    http://www.open-emr.org
 */


require_once('shared.php');


use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$date = $_POST['date']             = 
   (isset($_POST['form_date']))            ? 
      DateToYYYYMMDD($_POST['form_date']) : '';

$onset_date = $_POST['onset_date'] = 
   (isset($_POST['form_onset_date']))      ? 
      DateToYYYYMMDD($_POST['form_onset_date']) : '';

$sensitivity = $_POST['sensitivity'] = 
   (isset($_POST['form_sensitivity']))     ? 
      $_POST['form_sensitivity'] : '';

$pc_catid = 
   (isset($_POST['pc_catid']))             ? 
   $_POST['pc_catid'] : '';

$facility_id = 
   (isset($_POST['facility_id'])) ? 
      $_POST['facility_id'] : '';

$billing_facility = 
   (isset($_POST['billing_facility'])) ? 
      $_POST['billing_facility'] : '';

$reason = 
    (isset($_POST['reason'])) ? 
       $_POST['reason'] : '';

$mode = (isset($_POST['mode']))? 
   $_POST['mode'] : '';

$referral_source  = $_POST['referral_source'] = 
   (isset($_POST['form_referral_source'])) ? 
      $_POST['form_referral_source'] : '';

$pos_code = (isset($_POST['pos_code'])) ? 
   $_POST['pos_code'] : '';

// save therapy group if exist in external_id column
$external_id = $_POST['external_id'] = 
   isset($_POST['form_gid']) ? $_POST['form_gid'] : '';

$facilityresult = $facilityService->getById($facility_id);

$facility = $_POST['facility'] = $facilityresult['name'];

$normalurl = "patient_file/encounter/encounter_top.php";

$nexturl = $normalurl;

if (!$pid) {
    $pid = $_SESSION['pid'];
}

if ($mode == 'new') {
   new_visit(array_merge($_POST, $_GET), $pid);
} else if ($mode == 'update') {
   update_visit(array_merge($_POST, $_GET), $pid);
} else {
   die("Unknown mode '" . text($mode) . "'");
}

setencounter($encounter);

// Update the list of issues associated with this encounter.
sqlStatement("DELETE FROM issue_encounter WHERE " .
    "pid = ? AND encounter = ?", array($pid, $encounter));
if (isset($_POST['issues']) && is_array($_POST['issues'])) {
    foreach ($_POST['issues'] as $issue) {
        $query = "INSERT INTO issue_encounter ( pid, list_id, encounter ) VALUES (?,?,?)";
        sqlStatement($query, array($pid,$issue,$encounter));
    }
}

// Custom for Chelsea FC.
//
if ($mode == 'new' && $GLOBALS['default_new_encounter_form'] == 'football_injury_audit') {
  // If there are any "football injury" issues (medical problems without
  // "illness" in the title) linked to this encounter, but no encounter linked
  // to such an issue has the injury form in it, then present that form.

    $lres = sqlStatement("SELECT list_id " .
    "FROM issue_encounter, lists WHERE " .
    "issue_encounter.pid = ? AND " .
    "issue_encounter.encounter = ? AND " .
    "lists.id = issue_encounter.list_id AND " .
    "lists.type = 'medical_problem' AND " .
    "lists.title NOT LIKE '%Illness%'", array($pid,$encounter));

    if (sqlNumRows($lres) > 0) {
        $nexturl = "patient_file/encounter/load_form.php?formname=" .
        $GLOBALS['default_new_encounter_form'];
        while ($lrow = sqlFetchArray($lres)) {
            $frow = sqlQuery("SELECT count(*) AS count " .
             "FROM issue_encounter, forms WHERE " .
             "issue_encounter.list_id = ? AND " .
             "forms.pid = issue_encounter.pid AND " .
             "forms.encounter = issue_encounter.encounter AND " .
             "forms.formdir = ?", array($lrow['list_id'],$GLOBALS['default_new_encounter_form']));
            if ($frow['count']) {
                $nexturl = $normalurl;
            }
        }
    }
}

$result4 = sqlStatement("SELECT fe.encounter,fe.date,openemr_postcalendar_categories.pc_catname FROM form_encounter AS fe ".
    " left join openemr_postcalendar_categories on fe.pc_catid=openemr_postcalendar_categories.pc_catid  WHERE fe.pid = ? order by fe.date desc", array($pid));
?>
<html>
<body>
<script language='JavaScript'>
    EncounterDateArray=new Array;
    CalendarCategoryArray=new Array;
    EncounterIdArray=new Array;
    Count=0;
        <?php
        if (sqlNumRows($result4)>0) {
            while ($rowresult4 = sqlFetchArray($result4)) {
        ?>
        EncounterIdArray[Count]='<?php echo attr($rowresult4['encounter']); ?>';
    EncounterDateArray[Count]='<?php echo attr(oeFormatShortDate(date("Y-m-d", strtotime($rowresult4['date'])))); ?>';
    CalendarCategoryArray[Count]='<?php echo attr(xl_appt_category($rowresult4['pc_catname'])); ?>';
            Count++;
    <?php
            }
        }
        ?>

    // Get the left_nav window, and the name of its sibling (top or bottom) frame that this form is in.
    // This works no matter how deeply we are nested.
    var my_left_nav = top.left_nav;
    var w = window;
    for (; w.parent != top; w = w.parent);
    var my_win_name = w.name;
    my_left_nav.setPatientEncounter(EncounterIdArray,EncounterDateArray,CalendarCategoryArray);
    top.restoreSession();
<?php if ($mode == 'new') { ?>
    my_left_nav.setEncounter(<?php echo "'" . attr(oeFormatShortDate($date)) . "', " . attr($encounter) . ", window.name"; ?>);
    // Load the tab set for the new encounter, w is usually the RBot frame.
    w.location.href = '<?php echo "$rootdir/patient_file/encounter/encounter_top.php"; ?>';
<?php } else { // not new encounter ?>
    // Always return to encounter summary page.
    window.location.href = '<?php echo "$rootdir/patient_file/encounter/forms.php"; ?>';
<?php } // end if not new encounter ?>

</script>

</body>
</html>
