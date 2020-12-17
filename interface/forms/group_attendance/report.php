<?php
/**
 * interface/forms/group_attendance/report.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(__DIR__ . "/../../globals.php");
require_once($GLOBALS["srcdir"] . "/api.inc");
require_once("{$GLOBALS['srcdir']}/group.inc");

function group_attendance_report($pid, $encounter, $cols, $id)
{

    global $therapy_group;

    $sql = "SELECT * FROM `form_group_attendance` WHERE id=? AND group_id = ? AND encounter_id = ?";
    $res = sqlStatement($sql, array($id,$therapy_group, $_SESSION["encounter"]));
    $form_data = sqlFetchArray($res);
    $group_data = getGroup($therapy_group);
    $group_name = $group_data['group_name'];

    if ($form_data) { ?>
        <table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
            <tr>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Date'); ?></span></td>
                <td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Group'); ?></span></td>
            </tr>
            <tr>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($form_data['date']); ?></span></td>
                <td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($group_name); ?></span></td>
            </tr>
        </table>
	<table style='border-collapse:collapse;border-spacing:0;width: 100%;'>
	    <tr>
		<td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Participant'); ?></span></td>
		<td align='center' style='border:1px solid #ccc;padding:4px;'><span class=bold><?php echo xlt('Comment'); ?></span></td>
	    </tr>
        <?php
	    $sql = "SELECT tgpa.meeting_patient_comment as mpc, ".
                "tgpa.meeting_patient_status as mps, ".
                "pd.fname as fname, ".
                "pd.lname as lname ".
		"FROM `therapy_groups_participant_attendance` as tgpa ".
		"LEFT JOIN patient_data as pd on (pd.pid = tgpa.pid) ".
		"WHERE form_id=?";

	    $res = sqlStatement($sql, array($form_data['id']));
	    while ($form_att_data = sqlFetchArray($res)) {

		error_log(print_r($form_att_data, 1));
		?>
		<tr>
		<td style='border:1px solid #ccc;padding:4px;'><span class=text><?php 
			echo text($form_att_data['lname']) .', '. 
				text($form_att_data['fname']) .' ('.
				text($form_att_data['mps']) .')'; 
		?></span></td>
		<td style='border:1px solid #ccc;padding:4px;'><span class=text><?php echo text($form_att_data['mpc']); ?></span></td>
		</tr>
<?php
	    }
		
	print "</table>\n";
   }
}
?>
