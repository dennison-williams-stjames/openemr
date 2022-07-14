<?php
/** *************************************************************************************
 *	laboratory/process.php
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

use mdts\objects\Insurance;
use mdts\classes\QuestClient;
use mdts\classes\GenericClient;

use function mdts\LogError;
use function mdts\LogException;

/*
require_once("../../globals.php");
require_once("{$GLOBALS['srcdir']}/options.inc.php");
require_once("{$GLOBALS['srcdir']}/lists.inc");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.class.php");
require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");
require_once("{$GLOBALS['srcdir']}/wmt/quest/QuestOrderClient.php");
//require_once("{$GLOBALS['srcdir']}/wmt/quest/QuestModelHL7v2.php");
*/

// set processing date/time
$order_data->date_transmitted = date('Y-m-d H:i:s');

// get all AOE questions and answers
$query = "SELECT * FROM `procedure_order_code` pc ";
$query .= "LEFT JOIN `procedure_questions` pq ON pq.`lab_id` = ? AND pc.`procedure_code` = pq.`procedure_code` ";
$query .= "LEFT JOIN `procedure_answers` pa ON pa.`question_code` = pq.`question_code` AND pa.`procedure_order_id` = pc.`procedure_order_id` ";
$query .= "		AND pa.`procedure_order_seq` = pc.`procedure_order_seq` ";
$query .= "WHERE pc.`procedure_order_id` = ? ";
$query .= "ORDER BY pa.`procedure_order_id`, pa.`procedure_order_seq`, pa.`answer_seq`";

$binds = array();
$binds[] = $order_data->lab_id;
$binds[] = $order_item->procedure_order_id;
$results = sqlStatement($query,$binds);

$aoe_list = array();
while ($data = sqlFetchArray($results)) {
	if ($data['answer']) $aoe_list[] = $data;
}

// validate aoe responses (loop)
$aoe_errors = "";
if (count($aoe_list) > 0) {
	foreach ($aoe_list as $aoe_data) {
		if ($aoe_data['required'] && !$aoe_data['answer']) {
			$aoe_errors .= "\nQuestion [".$aoe_data['question_text']."] for test [".$aoe_data['procedure_code']."] requires a valid response.";
		}
	}
}
	
if ($aoe_errors) { // oh well .. have to terminate process with errors
	echo "The following errors must be corrected before submitting:";
	echo "<pre>\n";
	echo $aoe_errors;
	exit; 
}

echo "<pre>\n";

try { // catch any processing errors
	
	// get a handle to processor
	$client = null;
	if ($lab_data->type == 'quest') {
		$client = new QuestClient($lab_id);
	} else {
		$client = new GenericClient($lab_id);
	}

	// create request message
	$client->buildRequest($order_data);

	// determine third-party payment information
	$ins_primary_type = 0; // default self
	if ($order_data->work_flag) { // workers comp claim
		$order_data->billing_type = 'T';

		// build workers comp insurance record
		$ins_data = new Insurance($work_insurance);
		$ins_data->plan_name = "Workers Comp"; // IN1.08
		$ins_data->group_number = ""; // IN1.08
		$ins_data->work_flag = "Y"; // IN1.31
		$ins_data->policy_number = $order_data->work_case; // IN1.36

		// create hl7 segment
		$client->addInsurance(1, $order_data->billing_type, $ins_data);
	} else { // normal insurance
		// get current insurance
		$ins = Insurance::getPid($pid);
		if (isset($ins[0])) $order_data->ins_primary = $ins[0]->id;
		if (isset($ins[1])) $order_data->ins_secondary = $ins[1]->id;
		
		// SFA PROPERLY ORDER INSURANCE
		if ($GLOBALS['wmt::lab_ins_pick']) { // special processing for sfa
			if ( !in_array($order_data->billing_type, array('C','T','P','')) ) {
				// assume its an insurance id
				$ins_primary = $order_data->billing_type; // use selected
				$order_data->ins_primary = $ins_primary;
				$ins_secondary = null;  // no secondary
				$order_data->ins_secondary = $ins_secondary;
				$order_data->billing_type = 'T'; // make third-party
			}
		}
		
		$ins_primary = false;
		if ($order_data->ins_primary) {
			$ins_primary = new Insurance($order_data->ins_primary);
			$ins_primary_type = $ins_primary->ins_type_code; // save for ABN check
		}

		$ins_secondary = false;
		if ($order_data->ins_secondary) {
			$ins_secondary = new Insurance($order_data->ins_secondary);
		}

		// create insurance records
		if ( $order_data->billing_type != 'C' && !$ins_primary ) {
			$order_data->billing_type = 'P'; // if not client bill and no insurance must be patient bill
		}
		if ($order_data->billing_type == 'T' && $ins_primary ) { // only add insurance for third-party bill with insurance
			$client->addInsurance(1, $order_data->billing_type, $ins_primary);
			if ($ins_secondary)
				$client->addInsurance(2, $order_data->billing_type, $ins_secondary);
		} else {
			$client->addInsurance(1, $order_data->billing_type, false);
		}
	}
	
	// add guarantor (use insured if available, patient otherwise)
	$client->addGuarantor($order_data->pid, $ins_primary);

	// create orders (loop)
	$seq = 1;
	$test_list = array(); // for requisition
	foreach ($item_list as $item_data) {
		$client->addOrder($seq++, $order_data, $item_data, $aoe_list);
		$test_list[] = array('code'=>$item_data->procedure_code,'name'=>$item_data->procedure_name);
	}
	
	$abn_needed = false;
	if ($ins_primary_type == 2 && !$order_data->work_flag) { // medicare but not workers comp
		$doc_list = $client->getOrderDocuments($order_data->pid,'ABN');
		if (count($doc_list)) {
			$order_data->order_abn_id = $doc_list[0]->get_id();
			$order_data->store();	
			
			if (!$order_data->order_abn) {
				echo "\n\nMedicare 'Advance Beneficiary Notice of Noncoverage' required.";
				echo "\nPlease print the ABN document and obtain the patient's signature.";
				echo "\nThen resubmit this order with the ABN SIGNED checkbox marked.\n\n\n";	
				$abn_needed = true;		
			}
		}
	}
	
	if (!$order_data->order_abn_id || $order_data->order_abn) { // only submit if ABN not necessary or signed
		// generate requisition
		$doc_list = $client->getOrderDocuments($order_data->pid,'REQ');

		if (count($doc_list)) { // got a document so suceess
			$order_data->status = 's'; // processed
			$order_data->order_req_id = $doc_list[0]->get_id();
			$order_data->order_status = 'processed';
			$order_data->store();	
		}
		else {
			LogError(E_ERROR,"Laboratory processing failed to generate requisition document!!");
			die();
		}
		
		// SFA Automatic lab draw billing!!
		if ($GLOBALS['wmt::auto_draw_bill'] && $order_data->specimen_draw == 'int') {
			// include the FeeSheet class
			require_once($GLOBALS['srcdir']."/FeeSheet.class.php");
		
			// create a new billing object (PID and ENC required)
			$fs = new FeeSheet($pid, $encounter);
		
			// build billing fee item
			$fs->addServiceLineItem(array(
					'codetype'  => 'CPT4',
					'code'  => '36415',  // code item number
					'auth'  => '1',
					'units'  => '1', // as appropriate
					'justify'  => $drg_string,  // ICD10|123.45:ICD10|9876 (not required)
					'provider_id' => $provider_id  // if missing uses enc provider
			));
		
			// create dx entries if present
			if (count($drg_array) > 0) {
				foreach ($drg_array AS $dx_code => $dx_text) {
					// insert diagnosis code
					$fs->addServiceLineItem(array(
							'codetype'  => 'ICD10',
							'code'  => $dx_code,  // as listed in the ICD10 table
							'auth'  => '1',
							'provider_id' => $provider_id  // if missing uses enc provider
					));
				}
			}
		
			// save billing after all items added (service items & product items generated above)
			$fs->save($fs->serviceitems, $fs->productitems);
		}
		// End SFA billing
		
	}
}
catch (Exception $e) {
	LogException($e);
	die ();
}
?>
</pre>
<h2 class='mt-1'>Processing Successful!!</h2>
