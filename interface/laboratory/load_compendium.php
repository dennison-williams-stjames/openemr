<?php

/**
* Administrative loader for lab compendium data.
*
* Supports loading of lab order codes and related order entry questions from CSV
* format into the procedure_order and procedure_questions tables, respectively.
*
* Copyright (C) 2012-2013 Rod Roark <rod@sunsetsystems.com>
*
* LICENSE: This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://opensource.org/licenses/gpl-license.php>.
*
* @package  OpenEMR
* @author	Rod Roark <rod@sunsetsystems.com>
* 
* @since	2022-03-01
* @author	Ron Criswell <ron@mdtechsvcs.com>
* @desc		Modified to support many additional data types and other
* 			providers not originally supported.
*/

set_time_limit(0);
ini_set('max_execution_time', 259200);
ini_set('post_max_size', '64M');
ini_set('upload_max_filesize', '64M');

require_once("../globals.php");

use OpenEMR\Common\Acl\AclMain;

use OpenEMR\Core\Header;

// This array is an important reference for the supported labs and their NPI
// numbers as known to this program.  The clinic must define at least one
// procedure provider entry for a lab that has a supported NPI number.
//
//$lab_npi = array(
//  '1235138868' => 'Diagnostic Pathology Medical Group',
//  '1235186800' => 'Pathgroup Labs LLC',
//  '1598760985' => 'Yosemite Pathology Medical Group',
//);
$lab_type = array(
	'quest'		=> 'Quest Diagnostics',
	'labcorp'	=> 'LabCorp Laboratories',
	'generic'	=> 'Generic Laboratory'
);
/**
 * Get lab's ID from the users table given its NPI.  If none return 0.
 *
 * @param  string  $npi		   The lab's NPI number as known to the system
 * @return integer				The numeric value of the lab's address book entry
 */
function getLabID($ppid)
{
	$lrow = sqlQuery(
		"SELECT `npi` FROM `procedure_providers` WHERE " .
		"`ppid` = ? ORDER BY ppid LIMIT 1",
		array($ppid)
	);
	if (empty($lrow['ppid'])) {
		return 0;
	}

	return intval($lrow['ppid']);
}

// Collect a list of laboratory providers
$query = "
	SELECT `npi`, `name`, `type`, `ppid`
	FROM `procedure_providers`
	WHERE `active` = 1 AND `type` IN ('quest','labcorp','generic')
	ORDER BY `npi`
";

$result = sqlStatement($query);
while ($record = sqlFetchArray($result)) $lab_list[$record['ppid']] = $record;

if (!AclMain::aclCheckCore('admin', 'super')) {
	die(xlt('Not authorized', '', '', '!'));
}

$form_step   = isset($_POST['form_step']) ? trim($_POST['form_step']) : '0';
$form_status = isset($_POST['form_status' ]) ? trim($_POST['form_status' ]) : '';

if (!empty($_POST['form_import'])) {
	$form_step = 1;
}

// When true the current form will submit itself after a brief pause.
$auto_continue = false;

// Set up main paths.
$EXPORT_FILE = $GLOBALS['temporary_files_dir'] . "/openemr_config.sql";
?>
<html>

<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Load Compendium'); ?></title>
</head>

<body>
	<div class="container mt-3">
		<div class="row">
			<div class="col-12">
				<h2><?php echo xlt('Load Lab Compendium'); ?></h2>
			</div>
		</div>
		<form class="jumbotron py-4" method='post' action='load_compendium.php' enctype='multipart/form-data'>
			<table class="table table-borderless">
				<?php if ($form_step == 0) { ?>
					<tr>
						<td class="text-nowrap">
							<?php echo xlt('Vendor'); ?>
						</td>
						<td>
							<select class='form-control' name='vendor'>
								<?php foreach ($lab_list as $key => $data) {
									echo "<option value='" . attr($key) . "' labtype='" . attr($data['type']) . "'";
									echo ">" . text($data['npi']) . ": " . text($data['name']) . "</option>";
								} ?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="text-nowrap">
							<?php echo xlt('Action'); ?>
						</td>
						<td>
							<select class='form-control' name='action'>
								<option value='1'><?php echo xlt('Load Order Definitions'); ?></option>
								<option value='2'><?php echo xlt('Load Order Entry Questions'); ?></option>
								<option value='3'><?php echo xlt('Load Question Options'); ?></option>
				  				<option value='4'><?php echo xlt('Load Profile Definitions'); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="text-nowrap">
							<?php echo xlt('Container Group Name'); ?>
						</td>
						<td>
							<select class='form-control' name='group'>
								<?php
								$gres = sqlStatement("SELECT procedure_type_id, name FROM procedure_type " .
								"WHERE procedure_type = 'grp' AND parent = 0 ORDER BY name, procedure_type_id");
								while ($grow = sqlFetchArray($gres)) {
									echo "<option value='" . attr($grow['procedure_type_id']) . "'>" .
									text($grow['name']) . "</option>";
								}?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="text-nowrap">
							<?php echo xlt('Parameter File'); ?>
						</td>
						<td>
							<div class="custom-file">
								<label class="custom-file-label" for="userfile"><?php echo xlt('Choose file'); ?></label>
								<input type='hidden' class="custom-file-input" name='MAX_FILE_SIZE' value='30000000' />
								<input class='form-control' type='file' name='userfile' id='userfile' />
							</div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<button type="submit" class="btn btn-primary btn-save" value='<?php echo xla('Submit'); ?>'>
								<?php echo xlt('Submit'); ?>
							</button>
						</td>
					</tr>
				<?php }

				echo " <tr>\n";
				echo "  <td colspan='2'>\n";

				if ($form_step == 1) {
					// Process uploaded config file.
					if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
						$lab_id = $_POST['vendor'];
						$form_action = intval($_POST['action']);
						$form_group  = intval($_POST['group']);
						$form_vendor = getLabID($form_vendor);

						$fhcsv = fopen($_FILES['userfile']['tmp_name'], "r");

						if ($fhcsv) {
							// Load the correct vendor import module
							switch ($form_vendor) {
								case 'quest':
									require_once('loaders/quest.inc.php');
									break;
								case 'labcorp':
									require_once('loaders/labcorp.inc.php');
									break;
								case 'generic':
									require_once('loaders/cpl.inc.php');
									break;
								default:
									echo xlt('No import module available for this provider');
									$form_step = -1;
							}
						
							fclose($fhcsv);
						} else {
							echo "<p class='text-danger'>" . xlt('Internal error accessing uploaded file!') . "</p>";
							$form_step = -1;
						}
						
					} else {
						echo "<p class='text-danger'>" . xlt('Upload failed!') . "</p>";
						$form_step = -1;
					}

					$auto_continue = true;
				}

				if ($form_step == 2) {
					$form_status .= xlt('Done') . ".";
					echo nl2br(text($form_status));
				}

				++$form_step;
							
?>
			</td>
			</tr>
		</table>
	<input type='hidden' name='form_step' value='<?php echo attr($form_step); ?>' />
	<input type='hidden' name='form_status' value='<?php echo attr($form_status); ?>' />
</form>

<?php
ob_flush();
flush();
?>

<?php if ($auto_continue) { ?>
<script>
	setTimeout("document.forms[0].submit();", 500);
</script>
<?php } ?>
</div>

</body>
</html>

