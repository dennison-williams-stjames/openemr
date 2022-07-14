<?php
/** *******************************************************************************************
 *	LabsAjax Class
 *
 *	Copyright (c)2022 - Medical Technology Services <MDTechSvcs.com>
 *
 *	This program is free software: you can redistribute it and/or modify it under the 
 *  terms of the GNU General Public License as published by the Free Software Foundation, 
 *  either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY
 *	WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 *  PARTICULAR PURPOSE. DISTRIBUTOR IS NOT LIABLE TO USER FOR ANY DAMAGES, INCLUDING 
 *  COMPENSATORY, SPECIAL, INCIDENTAL, EXEMPLARY, PUNITIVE, OR CONSEQUENTIAL DAMAGES, 
 *  CONNECTED WITH OR RESULTING FROM THIS AGREEMENT OR USE OF THIS SOFTWARE.
 *
 *	See the GNU General Public License <http://www.gnu.org/licenses/> for more details.
 *
 *  @package mdts
 *  @subpackage lalboratory
 *  @version 3.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

// Global setup
require_once("../../globals.php");
require_once($GLOBALS['srcdir']."/mdts/mdts.globals.php");


// Get request type
$type = $_REQUEST['type'];

// retrieve matching diagnosis codes
if ($type == 'code') {
	$code = strtoupper($_REQUEST['code']);
	$words = explode(' ', $code);

	$xcode = str_replace('.', '', $code);
	$query = "SELECT CONCAT('ICD10:',`formatted_dx_code`) AS code, `short_desc`, `long_desc` FROM `icd10_dx_order_code` ";
	$query .= "WHERE `active` = 1 AND `valid_for_coding` = 1 AND (`formatted_dx_code` LIKE '".$code."%' ";
	if (!is_numeric($code)) {
		$short = $long = "";
		foreach ($words AS $word) {
			if ($short) $short .= " AND ";				
			$short .= "`short_desc` LIKE '%".$word."%' ";
			if ($long) $long .= " AND ";				
			$long .= "`long_desc` LIKE '%".$word."%' ";
		}
		$query .= "OR ($short) OR ($long) ";
	}
	$query .= ") OR (`dx_code` IN (SELECT `dx_icd10_target` FROM `icd10_gem_dx_9_10` WHERE `dx_icd9_source` LIKE '".$xcode."%') ) ";
	$query .= "ORDER BY `dx_code` LIMIT 25";
	$result = sqlStatement($query);

	// transmit appropriate results
	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('code'=>$record['code'],'short_desc'=>$record['short_desc'],'long_desc'=>$record['long_desc']);		
	}
	
	echo json_encode($data);
}

// retrieve matching procedure tests
if ($type == 'test') {
	$code = strtoupper($_REQUEST['code']);
	$lab_id = $_REQUEST['lab_id'];

	$query = "SELECT `procedure_type_id` AS id, `procedure_type` AS type, `description`, `procedure_code` AS code, `name` AS title, `lab_id` FROM `procedure_type` ";
	$query .= "WHERE `activity` = 1 AND `lab_id` = ".$lab_id." ";
	$query .= "AND (`procedure_type` = 'ord' OR `procedure_type` = 'pro') ";
	$query .= "AND (`procedure_code` LIKE '%".$code."%' ";
	if (!is_numeric($code)) $query .= "OR `name` LIKE '%".$code."%'";
	$query .= ") GROUP BY `procedure_code` ORDER BY `procedure_code` LIMIT 25"; 
	$result = sqlStatement($query);

	$count = 1;
	$data = array();
	while ($record = sqlFetchArray($result)) {
		$data[$count++] = array('id'=>$record['id'],'code'=>$record['code'],'type'=>$record['type'],'title'=>$record['title'],'description'=>$record['description'],'lab_id'=>$record['lab_id']);
	}

	echo json_encode($data);
}

// retrieve single test details
if ($type == 'details') {
	$code = strtoupper($_REQUEST['code']);
	$lab_id = $_REQUEST['lab_id'];
	
	// determine the type of test
	$query = "SELECT `procedure_code` AS code, `standard_code` AS unit, `procedure_type`, `related_code` AS components, `specimen`, `transport`, `title` AS state_name ";
	$query .= "FROM `procedure_type` pt ";
	$query .= "LEFT JOIN `list_options` lo ON `list_id` LIKE 'Lab_Transport' AND `option_id` LIKE `transport` ";
	$query .= "WHERE pt.`activity` = 1 AND lo.`activity` = 1 AND `lab_id` = ? AND `procedure_code` = ?";
	$query .= "AND (`procedure_type` = 'ord' OR `procedure_type` = 'pro') ";
	$record = sqlQuery($query,array($lab_id,$code));

	$type = ($record['specimen'])? $record['specimen'] : '';
	$state = $record['transport'];
	$name = $record['state_name'];
	if (!$name || $name == '* Not Found *') $name = 'CODE: '.$state;
	$unit = ($record['unit'])? str_replace('UNIT:', '', $record['unit']) : '';
	
	$type = null;
	if ($record['procedure_type']) $type = $record['procedure_type'];
	
	// retrieve all component test if profile
	$codes = "";
	$profile = array();
	if ($type == 'pro' && $record['components']) {
		$comps = explode("^", $record['components']);
		if (!is_array($comps)) $comps = array($comps); // convert to array if necessary
		foreach ($comps AS $comp) {
			if ($codes) $codes .= ",";
			$codes .= "'UNIT:$comp'"; 	
		}
	}
	
	if ($codes) {
		$query = "SELECT `procedure_type_id` AS id, `procedure_code` AS component, `description`, `name` AS title FROM `procedure_type` ";
		$query .= "WHERE `activity` = 1 AND `lab_id` = ".$lab_id." AND `procedure_type` = 'ord' ";
		$query .= "AND `standard_code` IN ( ".$codes." ) ";
		$query .= "GROUP BY `procedure_code` ORDER BY `procedure_code` ";
		$result = sqlStatement($query);
	
		while ($record = sqlFetchArray($result)) {
			$description = ($record['description'])? $record['description'] : $record['title'];
			$profile[$record['component']] = array('code'=>$code,'component'=>$record['component'],'description'=>$description);
		}
	}
	
	// retrieve all AOE questions
	$aoe = array();
	$result = sqlStatement("SELECT `question_code`, `question_text`, `tips` FROM `procedure_questions` ".
		"WHERE `procedure_code` = ? AND `lab_id` = ? AND `activity` = 1 ORDER BY `seq`",
			array($code,$lab_id));
	
	while ($record = sqlFetchArray($result)) {
		$aoe[] = array('code'=>$record['question_code'],'question'=>$record['question_text'],'prompt'=>$record['tips']);
	}
	
	$data = array('profile'=>$profile,'aoe'=>$aoe,'type'=>$type,'state'=>$state,'name'=>$name,'unit'=>$unit);
	echo json_encode($data);
}


if ($type == 'overview') {
	$code = strtoupper($_REQUEST['code']);

	$dos = array();
	
	//$query = "SELECT * FROM labcorp_dos ";
	//$query .= "WHERE test_cd = '".$code."' ";
	//$query .= "LIMIT 1 ";
	//$data = sqlQuery($query);
	
	$query = "SELECT det.name, ord.procedure_code AS code, det.name AS title, det.description, det.notes FROM procedure_type det ";
	$query .= "LEFT JOIN procedure_type ord ON ord.procedure_type_id = det.parent ";
	$query .= "WHERE ord.activity = 1 AND det.procedure_type = 'det' AND ord.procedure_code  = ? ";
	$query .= "ORDER BY det.seq ";
	$result = sqlStatement($query, array($code));
	
//	echo "<div style='width:480px;text-align:center;padding:10px;font-weight:bold;font-size:16px;background-color:#7ABEF3;color:black'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div class='wmtLabBar'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div style='overflow-y:auto;overflow-x:hidden;height:350px;width:450p;margin-top:10px'>\n";

	$none = true;
	while ($data = sqlFetchArray($result)) {
		if (empty($data['notes'])) continue;
		$none = false;
		echo "<h4 style='margin-bottom:0'>".$data['name']."</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap;font-family:monospace'>\n";
		echo $data['notes']."\n";
		echo "</div>\n";
	}

	if ($none) {
		echo "<h4 style='margin-bottom:0'>NO DETAILS AVAILABLE</h4>\n";
		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap;font-family:monospace'>\n";
		echo "Please contact your Quest Diagnostics representative for information\n";
		echo "about this laboratory test. Additional information may be available\n";
		echo "on the <a href='http://www.questdiagnostics.com/testcenter/TestCenterHome.action' target='_blank'>http://questdiagnostics.com/testcenter</a> website.";
		echo "</div>\n";
	}
	echo "<br/></div>";
}

if ($type == 'dynamic') {
	$code = strtoupper($_REQUEST['code']);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "http://www.interpathlab.com/tests/testfiles/".$code.".htm");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$contents = curl_exec($ch);
	curl_close ($ch);
	
	echo "<div class='body_title' style='width:680px;text-align:center;padding:10px;font-weight:bold;font-size:16px;color:black'>DIRECTORY OF SERVICE INFORMATION</div>\n";
	echo "<div class='dos' style='overflow-y:auto;overflow-x:hidden;height:350px;width:650p;margin-top:10px'>\n";

//	while ($data = sqlFetchArray($result)) {
//		echo "<h4 style='margin-bottom:0'>".$data['name']."</h4>\n";
//		echo "<div class='wmtOutput' style='padding-right:10px;white-space:pre-wrap'>\n";
//		echo "<b>".$data['notes']."</b><br/>\n";
//		echo "</div>\n";
//	}

	$start = stripos($contents, '<table');
	$contents = substr($contents, $start);
	$end = strripos($contents, '</table>');
	$contents = substr($contents, 0, $end);
	
	echo $contents;
	
	echo "</div>";
}

if ($type == 'label') {
//	require_once("{$GLOBALS['srcdir']}/wmt/wmt.include.php");

	$printer = $_REQUEST['printer'];
	$order = $_REQUEST['order'];
	$patient = strtoupper($_REQUEST['patient']);
	$account = $_REQUEST['account'];
	$lab = $_REQUEST['lab'];
	$pid = $_REQUEST['pid'];
	
	$count = 1;
	if ($_REQUEST['count']) $count = $_REQUEST['count'];
	
	require_once($GLOBALS['vendor_dir']."/tecnickcom/tcpdf/tcpdf.php");
	
	// create new PDF document
	$pdf = new TCPDF('L', 'pt', array(54,144), true, 'UTF-8', false);
	
	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	
	//set margins
	$pdf->SetMargins(15,5,20);
	$pdf->SetAutoPageBreak(FALSE, 35);
	
	//set some language-dependent strings
	$pdf->setLanguageArray($l);
	
	// define barcode style
	$style = array(
		'position' => '',
		'align' => 'L',
		'stretch' => true,
		'fitwidth' => false,
		'cellfitalign' => '',
		'border' => false,
		'hpadding' => 4,
		'vpadding' => 2,
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255),
		'text' => false,
		'font' => 'helvetica',
		'fontsize' => 8,
		'stretchtext' => 4
	);
	
	// ---------------------------------------------------------
	
	do {
		$pdf->AddPage();
	
		$pdf->SetFont('times', '', 7);
		$pdf->Cell(0,5,'Client #: '.$account,0,1);
		$pdf->Cell(0,5,'Order #: '.$order,0,1);
	
		$pdf->SetFont('times', 'B', 8);
		$pdf->Cell(0,0,$patient,0,1,'','','',1);
	
		$pdf->write1DBarcode($client.'-'.$order, 'C39', '', '', 110, 25, '', $style, 'N');
		
		$count--;
		
	} while ($count > 0);

	// ---------------------------------------------------------
	
	// get the content
	$content = $pdf->Output($label_file,'S');

	if ($printer == 'file') {
		// save and print the new document
		$file_name = $order . "_LABELS";
		$label_data = new Document();
		$label_data->createDocument($pid, $cat_id, $file_name, "application/pdf", $content);
		echo $GLOBALS['web_root'].'/controller.php?document&retrieve&patient_id='.$pid.'&document_id='.$label_data->get_id();
	} else {
		// print the new document
		$CMDLINE = "lpr -P $printer ";
		$pipe = popen("$CMDLINE" , 'w' );
		if (!$pipe) {
			echo "Label printing failed...";
		} else {
			fputs($pipe, $content);
			pclose($pipe);
			echo "Labels printing at $printer ...";
		}
	}
}

if ($type == 'insurance') {
	$ins1 = $_REQUEST['ins1'];	
	$code1 = strtoupper($_REQUEST['code1']);

	if ($ins1 && $code1) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins1."', title = '".$code1."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}
	
	$ins2 = $_REQUEST['ins2'];	
	$code2 = strtoupper($_REQUEST['code2']);
	
	if ($ins2 && $code2) {
		$query = "REPLACE INTO list_options SET option_id = '".$ins2."', title = '".$code2."', list_id = 'LabCorp_Insurance' ";
		sqlStatement($query);
	}
}


?>
