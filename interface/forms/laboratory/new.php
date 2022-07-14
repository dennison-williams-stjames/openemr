<?php
/** ***************************************************************************************
 *	laboratory/new.php
 *
 *	Copyright (c)2022 - Medical Technology Services
 *
 *	This program is licensed software: licensee is granted a limited nonexclusive
 *  license to install this Software on more than one computer system, as long as all
 *  systems are used to support a single licensee. Licensor is and remains the owner
 *  of all titles, rights, and interests in program.
 *  
 *  Licensee will not make copies of this Software or allow copies of this Software 
 *  to be made by others, unless authorized by the licensor. Licensee may make copies 
 *  of the Software for backup purposes only.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT 
 *	ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 *  FOR A PARTICULAR PURPOSE. LICENSOR IS NOT LIABLE TO LICENSEE FOR ANY DAMAGES, 
 *  INCLUDING COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL 
 *  DAMAGES, CONNECTED WITH OR RESULTING FROM THIS LICENSE AGREEMENT OR LICENSEE'S 
 *  USE OF THIS SOFTWARE.
 *
 *  @package laboratory
 *  @version 3.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron@MDTechSvcs.com>
 *  @uses quest/common.php
 * 
 ***************************************************************************************** */

// Global setup
require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/mdts/mdts.globals.php");

use OpenEMR\Core\Header;

use mdts\objects\Laboratory;
use function mdts\LogError;

// Grab session data
$authid = $_SESSION['authId'];
$authuser = $_SESSION['authUser'];
$groupname = $_SESSION['authProvider'];
$authorized = $_SESSION['userauthorized'];

// Security violation
if (!$authuser) {
	mdts\LogError(E_ERROR, "Attempt to access program without authorization credentials.");
	die ();
}

// initialization
$lab_type = ($_REQUEST['formname'])? $_REQUEST['formname'] : false;
$lab_id = ($_REQUEST['labid'])? $_REQUEST['labid'] : false;

$lab_data = null;
if ($lab_id) {
	// retrieve specific lab
	$lab_data = new Laboratory($lab_id);
	if (empty($lab_data->ppid)) {
		LogError(E_ERROR, "No data for laboratory id [" .$lab_id. "]");
		die();
	} 
} else {
	// retrieve lab list
	//$lab_list = Laboratory::fetchLabs($lab_type); // returns array or objects
	$lab_list = Laboratory::fetchLabs('quest'); // returns array or objects
	if (count($lab_list) < 1) {
		LogError(E_ERROR, "Laboratory providers have not been created.");
		die();
	} elseif (count($lab_list) == 1) { // skip selection if only one laboratory
		$lab_data = reset($lab_list);  // retrieve first element
		$lab_id = $lab_data->ppid;
	}
}

// single lab identified
if ($lab_id) {
	$mode = 'new';
	include("common.php");
	exit;
}

?>
<!DOCTYPE html>
<html>
<head>
	<?php Header::setupHeader(); ?>
	<title>Laboratory Order</title>

	<script>
		function saveClicked() {
			var labid = $('#labid').val();
			if (parseInt(labid) > 0) {
				location.href="<?php echo $GLOBALS['webroot'] ?>/interface/forms/laboratory/new.php?labid=" + labid;
				exit();
			}
			alert('You must select a laboratory processor before continuing.');
		}

		function cancelClicked() {
			top.restoreSession();
			parent.closeTab(window.name, false);
		}

		</script>
</head>

	<div class="container">
		<div class="page-header col-md-12">
			<input type=hidden name='mode' value='<?php echo ($viewmode)? 'update' : 'new'; ?>' />
			<input type=hidden name='id' value='<?php echo $order_data->id ?>' />
			<h2>Laboratory Order</h2>
		</div>
		<div id="sections" class="col-12">
			<form id="lab_form" class="form form-horizontal" method="post" action="">
				<input type="hidden" name="csrf_token_form" value="<?php //echo attr(CsrfUtils::collectCsrfToken()); ?>" />

				<!-- LABORATORY PROCESSOR SELECT -->
				<div id="order_entry" class="card mb-1">
					<div class="card-header pl-2" style="font-size:1.1rem"><?php echo xlt('Laboratory Processor'); ?></div>

					<div id="order_submit" class="collapse show">
						<div class="card-body p-3">
							<div class="control-label text-nowrap w-25">Select Laboratory Processor:</div>
							<select class="form-control form-control-sm form-select w-25" name='labid' id='labid'>
								<option value=''>-- select --</option>
<?php 
	foreach ($lab_list as $lab) {
		echo "<option value='" . $lab->ppid . "'>" .$lab->name. "</option>";
  	}
?>
							</select>
							<div class="btn-group mt-3" role="group">
								<button type="button" class="btn btn-success btn-save" name="btn_save" id="bn_save" onclick="saveClicked()">Continue</button>
								<button type="button" class="btn btn-secondary btn-cancel" onclick="cancelClicked()">Cancel/Exit</button>
							</div>
						</div>
					</div>
					
				</div>
			</div>
			
		</form>
	</body>

</html>
