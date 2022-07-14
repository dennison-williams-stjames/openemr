<?php

/**
 * Maintenance for the list of procedure providers.
 *
 * @package   OpenEMR
 * @link	  http://www.open-emr.org
 * @author	Rod Roark <rod@sunsetsystems.com>
 * @author	Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2014 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_GET)) {
	if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
		CsrfUtils::csrfNotVerified();
	}
}

if (!empty($_POST)) {
	if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
		CsrfUtils::csrfNotVerified();
	}
}

if (!AclMain::aclCheckCore('admin', 'users')) {
	die(xlt('Access denied'));
}

// Collect user id if editing entry
$ppid = $_REQUEST['ppid'];

$info_msg = "";

function invalue($name)
{
	$fld = add_escape_custom(trim($_POST[$name]));
	return "'$fld'";
}

function onvalue($name)
{
	$fld = ($_POST[$name] == 'on') ? '1' : '0';
	return "'$fld'";
}

?>
<html>
<head>
<?php Header::setupHeader(['opener']);?>
<title><?php echo $ppid ? xlt('Edit') : xlt('Add New{{Processor}}') ?> <?php echo xlt('Laboratory Processor'); ?></title>
<style>
	td {
		font-size: 0.8125rem;
	}

	.inputtext {
	 padding-left:2px;
	 padding-right:2px;
	}

	.button {
	 font-family:sans-serif;
		font-size: 0.75rem;
	 font-weight:bold;
	}


	.label-div > a {
		display:none;
	}
	.label-div:hover > a {
	   display:inline-block;
	}
	/* This is overridden on any theme */
	div[id$="_info"] {
		background: #F7FAB3;
		color: #000;
		padding: 20px;
		margin: 10px 15px 0 15px;
	}
	div[id$="_info"] > a {
		margin-left:10px;
	}
</style>

</head>

<body>
<div class="container-fluid">
	<?php
	// If we are saving, then save and close the window.
	// lab_director is the id of the organization in the users table
	//
	if (!empty($_POST['form_save'])) {
		$org_qry = "SELECT organization FROM users WHERE id = ?";
		$org_res = sqlQuery($org_qry, array($_POST['form_name']));
		$org_name = $org_res['organization'];
		$sets =
			"name = '" . add_escape_custom($org_name) . "', " .
			"lab_director = " . invalue('form_name') . ", " .
			"npi = " . invalue('form_npi') . ", " .
			"type = " . invalue('form_type') . ", " .
			"send_app_id = " . invalue('form_send_app_id') . ", " .
			"send_fac_id = " . invalue('form_send_fac_id') . ", " .
			"recv_app_id = " . invalue('form_recv_app_id') . ", " .
			"recv_fac_id = " . invalue('form_recv_fac_id') . ", " .
			"DorP = " . invalue('form_DorP') . ", " .
			"direction = " . invalue('form_direction') . ", " .
			"protocol = " . invalue('form_protocol') . ", " .
			"remote_host = " . invalue('form_remote_host') . ", " .
			"remote_port = " . invalue('form_remote_port') . ", " .
			"login = " . invalue('form_login') . ", " .
			"password = " . invalue('form_password') . ", " .
			"orders_path = " . invalue('form_orders_path') . ", " .
			"results_path = " . invalue('form_results_path') . ", " .
			"notes = " . invalue('form_notes') . ", " .
			"active = " . onvalue('form_active');

		if ($ppid) {
			$query = "UPDATE procedure_providers SET $sets " .
				"WHERE ppid = '" . add_escape_custom($ppid) . "'";
			sqlStatement($query);
		} else {
			$ppid = sqlInsert("INSERT INTO `procedure_providers` SET $sets");
		}
	} elseif (!empty($_POST['form_delete'])) {
		if ($ppid) {
			sqlStatement("DELETE FROM procedure_providers WHERE ppid = ?", array($ppid));
		}
	}

	if (!empty($_POST['form_save']) || !empty($_POST['form_delete'])) {
		// Close this window and redisplay the updated list.
		echo "<script>\n";
		if ($info_msg) {
			echo " alert(" . js_escape($info_msg) . ");\n";
		}

		echo " window.close();\n";
		echo " if (opener.refreshme) opener.refreshme();\n";
		echo "</script></body></html>\n";
		exit();
	}

	if ($ppid) {
		$row = sqlQuery("SELECT * FROM procedure_providers WHERE ppid = ?", array($ppid));
	}

	$ppid_active = $row['active'] ?? null;

	$org_query = "SELECT id, organization FROM users WHERE abook_type LIKE 'ord_lab'";
	$org_res = sqlStatement($org_query);
	$optionsStr = '';
	while ($org_row = sqlFetchArray($org_res)) {
		$org_name = $org_row['organization'];
		$selected = '';
		if ($ppid) {
			if ($row['lab_director'] == $org_row['id']) {
				$selected = "selected";
				$optionsStr .= "<option value='" . attr($org_row['id']) . "' $selected>" . text($org_name) . "</option>";
			}
		} else {
			$checkName = sqlQuery("SELECT `name` FROM `procedure_providers` WHERE `name` = ?", [$org_name]);
			if (empty($checkName['name'])) {
				$optionsStr .= "<option value='" . attr($org_row['id']) . "' $selected>" . text($org_name) . "</option>";
			}
		}
	}
	?>
	<div class="page-header" name="form_legend" id="form_legend">
		<h4><?php echo xlt('Enter Processor Details'); ?><i id="enter-details-tooltip" class="fa fa-info-circle oe-text-black oe-superscript ml-2" aria-hidden="true"></i></h4>
	</div>
	<div class="row">
		<div class="col-sm-12">
			<form method='post' name='theform' action="laboratory_provider_edit.php?ppid=<?php echo attr_url($ppid); ?>&csrf_token_form=<?php echo attr_url(CsrfUtils::collectCsrfToken()); ?>">
				<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
				<div class="form-check-inline">
					<label class='form-check-label mr-2' for="form_active"><?php echo xlt('Active'); ?></label>
					<input type='checkbox' class='form-check-input' name='form_active' id='form_active'
						<?php if ($ppid) {
							echo !empty($ppid_active) ? " checked" : "";
						} else {
							echo " checked";
						} ?> />
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-6">
						<div class="label-div">
							<label class="col-form-label" for="form_name"><?php echo xlt('Name'); ?>:</label> <a href="#name_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
						</div>
						<select name='form_name' id='form_name' class='form-control'>
							<?php echo $optionsStr ?? ''; ?>
						</select>
					</div>
					<div class="col-sm-6">
						<div class="label-div">
							<label class="col-form-label" for="form_npi"><?php echo xlt('NPI'); ?>:</label> <a href="#npi_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
						</div>
						<input type='text' name='form_npi' id='form_npi' maxlength='10' value='<?php echo attr($row['npi'] ?? ''); ?>' class='form-control' />
					</div>
					<div id="name_info" class="collapse">
						<a href="#name_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Name - Select a provider name from the drop-down list"); ?></p>
						<p><?php echo xlt("For the name to appear on the drop-down list it must be first entered in Administration > Address Book "); ?></p>
						<p><?php echo xlt("Select Lab Service in the Type drop-down box and enter a name under organization"); ?></p>
						<p><?php echo xlt("For detailed instructions close the 'Enter Processor Details' popup and click on the Help icon on the main form. "); ?><i class="fa fa-question-circle" aria-hidden="true"></i></p>
					</div>
					<div id="npi_info" class="collapse">
						<a href="#npi_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("NPI - Enter the Processor's unique 10-digit National Processor Identifier or NPI identification number"); ?></p>
						<p><?php echo xlt("It is issued to health care providers in the United States by the Centers for Medicare and Medicaid Services (CMS)"); ?></p>
						<p><?php echo xlt("This has to entered once in this form"); ?></p>
						<p><?php echo xlt("IMPORTANT: The NPI number also exists in the Address Book entry for the provider, take care to enter the correct NPI number"); ?></p>
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-4">
						<div class="clearfix">
							<div class="label-div">
								<label class="col-form-label" for="form_DorP"><?php echo xlt('Usage'); ?>:</label> <a href="#usage_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							</div>
							<select name='form_DorP' id='form_DorP' class='form-control' title='<?php echo xla('HL7 - MSH-11 - Processing ID'); ?>'>
								<?php
								foreach (
									array(
									'D' => xl('Debugging'),
									'P' => xl('Production'),
									'T' => xl('Testing'),
									) as $key => $value
								) {
									echo "	<option value='" . attr($key) . "'";
									if (!empty($row['DorP']) && ($key == $row['DorP'])) {
										echo " selected";
									}
									echo ">" . text($value) . "</option>\n";
								}
								?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="clearfix">
							<div class="label-div">
								<label class="col-form-label" for="form_DorP"><?php echo xlt('Type'); ?>:</label> <a href="#type_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							</div>
							<select name='form_type' id='form_type' class='form-control' title='<?php echo xla('Processor Interface Type'); ?>'>
								<?php
								foreach (
									array(
									'internal' => xl('Internal Laboratory'),
									'quest' => xl('Quest Diagnostics'),
									'labcorp' => xl('LabCorp Laboratories'),
									'laboratory' => xl('Generic Laboraboty'),
									'radiology' => xl('Radiology Service'),
									) as $key => $value
								) {
									echo "	<option value='" . attr($key) . "'";
									if (!empty($row['type']) && ($key == $row['type'])) {
										echo " selected";
									}
									echo ">" . text($value) . "</option>\n";
								}
								?>
							</select>
						</div>
					</div>
					<div class="col-sm-4">
						<div class="clearfix">
							<div class="label-div">
								<label class="col-form-label" for="form_protocol"><?php echo xlt('Protocol'); ?>:</label> <a href="#protocol_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
							</div>
							<select name='form_protocol' id='form_protocol' class='form-control' title='<?php echo xla('Transmission Protocol'); ?>'>
								<?php
								foreach (
									array(
									'FSS' => xl('sFTP Server'),
									'FSC' => xl('sFTP Client'),
									'WS' => xl('Web Service'),
									'INT' => xl('Internal Only'),
									) as $key => $value
								) {
									echo "	<option value='" . attr($key) . "'";
									if (!empty($row['protocol']) && ($key == $row['protocol'])) {
										echo " selected";
									}
									echo ">" . text($value) . "</option>\n";
								}
								?>
							</select>
						</div>
					</div>
					<div id="usage_info" class="collapse">
						<a href="#usage_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Usage - is only required if you are submitting an electronic order to an external facility"); ?></p>
						<p><?php echo xlt("It is a field in the HL7 Message header known as Processing ID"); ?></p>
						<p><?php echo xlt("Health Level-7 or HL7 refers to a set of international standards for transfer of clinical and administrative data between software applications used by various healthcare providers"); ?></p>
						<p><?php echo xlt("This field is used to decide whether to process the message as defined in HL7 Application (level 7) Processing rules"); ?></p>
						<p><?php echo xlt("Select the appropriate choice - Debugging or Production"); ?></p>
					</div>
					<div id="type_info" class="collapse">
						<a href="#type_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Type - is required to control the type of records used to send and receive orders with this provider (either internal or external)."); ?></p>
						<p><?php echo xlt("Select the appropriate choice from the list of available options."); ?></p>
					</div>
					<div id="protocol_info" class="collapse">
						<a href="#protocol_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Protocol - is required to indicate the electronic protocol to be used to transmit electronic orders to a the processing facility."); ?></p>
						<p><?php echo xlt("Select the appropriate choice from the list of available options."); ?></p>
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-6">
						<div class="label-div">
							<label class="col-form-label" for="form_send_app_id"><?php echo xlt('Sender IDs'); ?>:</label>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="label-div">
							<label class="col-form-label" for="form_send_app_id"><?php echo xlt('Receiver IDs'); ?>:</label>
						</div>
					</div>
				</div>
				<div class="row col-sm-12">
					<div class="col-sm-3">
						<input type='text' name='form_send_app_id' id='form_send_app_id' maxlength='100'
							value='<?php echo attr($row['send_app_id'] ?? ''); ?>'
							title='<?php echo xla('HL7 - MSH-3.1 - Sending application'); ?>'
							placeholder='<?php echo xla('Sending Application'); ?>'
							class='form-control' />
					</div>
					<div class="col-sm-3">
						<input type='text' name='form_send_fac_id' id='form_send_fac_id' maxlength='100'
							value='<?php echo attr($row['send_fac_id'] ?? ''); ?>'
							title='<?php echo xla('HL7 - MSH-4.1 - Sending facility'); ?>'
							placeholder='<?php echo xla('Sending Facility'); ?>'
							class='form-control' />
					</div>
					<div class="col-sm-3">
						<input type='text' name='form_recv_app_id' id='form_recv_app_id' maxlength='100' value='<?php echo attr($row['recv_app_id'] ?? ''); ?>' title='<?php echo xla('HL7 - MSH-5.1 - Receiving application'); ?>' placeholder='<?php echo xla('Receiving Application'); ?>' class='form-control' />
					</div>
					<div class="col-sm-3">
						<input type='text' name='form_recv_fac_id' id='form_recv_fac_id' maxlength='100' value='<?php echo attr($row['recv_fac_id'] ?? ''); ?>' title='<?php echo xla('HL7 - MSH-6.1 - Receiving facility'); ?>' placeholder='<?php echo xla('Receiving Facility'); ?>' class='form-control' />
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-12 label-div">
						<label for="form_login"><?php echo xlt('Login'); ?>:</label> <a href="#login_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
					</div>
				</div>
				<div class="row col-sm-12">
					<div class="col-sm-6">
						<input type='text' name='form_login' id='form_login' maxlength='255' value='<?php echo attr($row['login'] ?? ''); ?>' placeholder='<?php echo xla('Enter User Login ID'); ?>' class='form-control' />
					</div>
					<div class="col-sm-6">
						<input type='text' name='form_password' id='form_password' maxlength='255' value='<?php echo attr($row['password'] ?? ''); ?>' placeholder='<?php echo xla('Enter Password'); ?>' class='form-control' />
					</div>
				</div>
				<div id="login_info" class="collapse">
					<a href="#login_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
					<p><?php echo xlt("Login - details are only required if you are connecting to a facility using a protocol that requires authentication (typically sFTP or Web Service). "); ?></p>
					<p><?php echo xlt("Type in the username and password provided by the facility"); ?></p>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-6 label-div">
						<label class="col-form-label" for="form_remote_host"><?php echo xlt('Remote Host'); ?>:</label> <a href="#remote_host_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
					</div>
				</div>
				<div class="row col-sm-12">
					<div class="col-sm-6">
						<input type='text' name='form_remote_host' id='form_remote_host' maxlength='255' 
							placeholder='<?php echo xla('Enter Remote Host URL'); ?>'
							value='<?php echo attr($row['remote_host'] ?? ''); ?>' class='form-control' />
					</div>
					<div class="col-sm-6">
						<input type='text' name='form_remote_port' id='form_remote_port' maxlength='20' 
							placeholder='<?php echo xla('Enter Port Number'); ?>'
							value='<?php echo attr($row['remote_port'] ?? ''); ?>' class='form-control w-20' />
					</div>
					<div id="remote_host_info" class="collapse">
						<a href="#remote_host_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Remote Host - is only required if you are submitting an electronic order to an external facility or just receiving results from it"); ?></p>
						<p><?php echo xlt("Type in the URL of the external facility to which the order will be sent, this will be provided by the facility"); ?></p>
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-6 label-div">
						<label class="col-form-label" for="form_orders_path"><?php echo xlt('Orders Path'); ?>:</label> <a href="#orders_path_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
					</div>
					<div class="col-sm-6 label-div">
						<label class="col-form-label" for="form_results_path"><?php echo xlt('Results Path'); ?>:</label>
						<a href="#results_path_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
					</div>
				</div>
				<div class="row col-sm-12">
					<div class="col-sm-6">
						<input type='text' name='form_orders_path' id='form_orders_path' maxlength='255'
							value='<?php echo attr($row['orders_path'] ?? ''); ?>' class='form-control' />
					</div>
					<div class="col-sm-6">
						<input type='text' name='form_results_path' id='form_results_path' maxlength='255'
							value='<?php echo attr($row['results_path'] ?? ''); ?>' class='form-control' />
					</div>
					<div id="orders_path_info" class="collapse">
						<a href="#orders_path_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Orders Path - is only required if you are submitting an electronic order to an external facility"); ?></p>
						<p><?php echo xlt("Type in the location of the directory or folder in which the created orders (HL7 messages) will be stored"); ?></p>
					</div>
					<div id="results_path_info" class="collapse">
						<a href="#results_path_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Results Path - is only required if you are submitting an electronic order to an external facility or just receiving results from it"); ?></p>
						<p><?php echo xlt("Type in the location of the directory or folder in which the returned results (HL7 messages) will be stored"); ?></p>
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="col-sm-12 label-div">
						<label class="col-form-label" for="form_notes"><?php echo xlt('Notes'); ?>:</label> <a href="#notes_info" class="info-anchor icon-tooltip" data-toggle="collapse"><i class="fa fa-question-circle" aria-hidden="true"></i></a>
					</div>
					<div class="col-sm-12">
						<textarea rows='3' name='form_notes' id='form_notes' wrap='virtual' class='form-control'><?php echo text($row['notes'] ?? ''); ?></textarea>
					</div>
					<div id="notes_info" class="collapse">
						<a href="#notes_info" data-toggle="collapse" class="oe-pull-away"><i class="fa fa-times oe-help-x" aria-hidden="true"></i></a>
						<p><?php echo xlt("Any additional information pertaining to this provider"); ?></p>
					</div>
				</div>
				<div class="row col-sm-12 mt-3">
					<div class="form-group clearfix" id="button-container">
						<div class="col-sm-12 text-left position-override">
							<div class="btn-group" role="group">
								<button type='submit' name='form_save' class="btn btn-primary btn-save" value='<?php echo xla('Save'); ?>'><?php echo xlt('Save'); ?></button>
								<button type="button" class="btn btn-secondary btn-cancel" onclick='window.close()' ;><?php echo xlt('Cancel'); ?></button>
								<?php if ($ppid) { ?>
								<button type='submit' name='form_delete' class="btn btn-danger btn-cancel btn-delete" value='<?php echo xla('Delete'); ?>'><?php echo xlt('Delete'); ?></button>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div><!--end of container div-->
<script>
	//jqury-ui tooltip
	$(function () {
		$('.icon-tooltip i').attr({"title": <?php echo xlj('Click to see more information'); ?>, "data-toggle": "tooltip", "data-placement": "bottom"}).tooltip({
			show: {
				delay: 700,
				duration: 0
			}
		});
		$('#enter-details-tooltip').attr({"title": <?php echo xlj('Additional help to fill out this form is available by hovering over labels of each box and clicking on the dark blue help ? icon that is revealed. On mobile devices tap once on the label to reveal the help icon and tap on the icon to show the help section'); ?>, "data-toggle": "tooltip", "data-placement": "bottom"}).tooltip();
	});
</script>
</body>
</html>
