<?php
/** **************************************************************************************
 *	LABORATORY/RUN_BATCH.PHP
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
 * 
 **************************************************************************************** */

// Global setup
require_once("../globals.php");
require_once($GLOBALS['srcdir']."/mdts/mdts.globals.php");

use OpenEMR\Core\Header;

use function mdts\ToTime;
use function mdts\LogError;
use function mdts\LogException;

// grab inportant stuff
// Set defaults
if (!empty($_POST['start_date']) && strtotime($_POST['start_date']) !== false) {
	$form_start = date('Y-m-d', ToTime($_POST['start_date']));
}
if (!empty($_POST['end_date']) && strtotime($_POST['end_date']) !== false) {
	$form_end = date('Y-m-d', ToTime($_POST['end_date']));
}
$form_processor = $_REQUEST['form_processor'];
$form_detail = (isset($_REQUEST['form_detail']))? true : false;

?><!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<?php Header::setupHeader(); ?>

	<title>Batch Result Processing</title>
	<meta name="author" content="Ron Criswell" />
	<meta name="description" content="Batch result processing" />
	<meta name="keywords" content="Report Keywords" />
	<meta name="copyright" content="&copy;<?php echo date('Y') ?> Williams Medical Technologies, Inc.  All rights reserved." />

	<style>
		#report_inputs { float:left;padding:6px 10px;border-radius:8px;border:solid var(--gray300) 1px;box-shadow:2px 2px 2px var(--light);max-width:85%;margin-right:20px; }
		#report_inputs div { float:left;margin: 2px 10px 2px 0; }
		#report_buttons { float:left;margin:4px 0; }
	</style>
	
	<!-- Set system variables in local scope -->
	<script>
		var currentMonth = "<?php echo ltrim(date('m'), '0') ?>";
		var currentYear = "<?php echo date('Y') ?>";
		var errorMessage = "<?php echo $errorMessage ?>";
		
		var alertMessage = "<?php echo $alertMessage ?>";
		if (alertMessage != "") alert(alertMessage);
	</script>

</head>

<body class="body_top">
	<div id="container">

		<header>
			<!-- HEADER (if desired) -->
			<span class="title" style="margin-left:10px">Laboratory - Batch Processing</span>
		</header>

		<div id="content">
			<form method='post' name='theform' id='theform' action='batch_process.php'>
				<input type="hidden" id="process" name="process" value="report" />
	
				<div id="report_parameters" class="clearfix">
	
					<!-- REPORT PARAMETERS -->
					<div id="report_inputs">
						<div class="form-inline mr-4">
							<div class="control-label text-nowrap pr-2">Processor:</div>
							<select class="form-control form-control-sm w-auto" name='lab' id='lab_id'>
								<option value=''>-- select --</option>
<?php 
	// Build a drop-down list of processor names.
	$query = "SELECT `ppid`, `name` FROM `procedure_providers` ";
	$query .= "WHERE `type` NOT LIKE 'quick%' ORDER BY `name`";
	$res = sqlStatement($query);
	
	while ($row = sqlFetchArray($res)) {
		$ppid = $row['ppid'];
		echo "    <option value='$ppid'";
		if ($ppid == $form_processor) echo " selected";
		echo ">" . $row['name'] . "\n";
	}
?>
							</select>
						</div>
						<div class="form-inline mr-4">
							<div class="control-label text-nowrap pr-2">Start Date:</div>
							<input class="form-control form-control-sm w-auto" type='date' name='from' id='start_date'
									value='<?php echo $form_start ?>' />
						</div>
						<div class="form-inline mr-4">
							<div class="control-label text-nowrap pr-2">End Date:</div>
							<input class="form-control form-control-sm w-auto" type='date' name='thru' id='end_date'
									value='<?php echo $form_end ?>' />
						</div>
						<div class="form-inline mr-4">
							<div class="control-label text-nowrap pr-2">Include Details:</div>
							<input type='checkbox' class="form-check-input" id='form_detail' name='debug' 
										value="1" <?php if ($form_detail) echo "checked" ?> />
						</div>
					</div>

					<!-- REPORT BUTTON -->
					<div id="report_buttons">
						<button type="button" class="btn btn-primary" id="btn_report" onclick="doSubmit()">Submit</button>
					</div>

				</div>

				<div class="m-2">
					Leave the date fields <b>BLANK</b> for normal processing.
					Enter dates <b>ONLY</b> if previously processed results must be re-processed.  
					<br/>
					The dates entered represent the dates the result transactions where originally processed by the gateway.
					<br/> 
 					Select whether to display processing details using the checkbox and click <b>Submit</b>.
				</div>
				
				<input type="hidden" name="browser" value="1" />
			</form>

			<div id="dynamic" style="visibility:hidden">

				<!-- GENERATED OUTPUT -->
				<table style="width:100%" class="display" id="report">
					<thead>  </thead>
					<tbody>  </tbody>
					<tfoot>  </tfoot>
				</table>

			</div>
		</div>

		<footer>
			<!-- FOOTER (if desired) -->
		</footer>

	</div> <!-- end of #container -->


<script>
	function doSubmit() {
		if ($('#lab_id').val() == '') {
			alert("Processor required for execution!!");
			return false;
		} else {
			$('#theform').submit();
		}
	}
	
<?php 
	if ($alertmsg) { echo " alert('$alertmsg');\n"; } 
?>

</script>
</body>

</html>

