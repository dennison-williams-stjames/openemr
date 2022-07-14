<?php
/** *******************************************************************************************
 *	RESULT.CLASS.PHP
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
 *  @version 3.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the MDTS namespace
 */
namespace mdts\objects;

class Result {
	public $procedure_report_id;
	public $procedure_order_id;
	public $procedure_order_seq;
	public $date_collected;
	public $date_report;
	public $source;
	public $specimen_num;
	public $report_status;
	public $review_status;
	public $report_notes;
	
	/**
	 * @param int $id record identifier
	 * @param boolean $update - DEPRECATED
	 * @return object instance of result class
	 */
	public function __construct($id = false) {
		// create empty record with no id
		if (!$id) return false;

		$query = "SELECT * FROM `procedure_report` WHERE `procedure_report_id` = ?";
		$data = sqlQuery($query,array($id));
		if (!$data['procedure_report_id'])
			throw new \Exception('mdtsResult::_construct - no procedure report record with procedure_report_id ('.$id.').');
		
		// load everything returned into object
		foreach ($data as $key => $value) {
			$this->$key = $value;
		}

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @static
	 * @param wmtOrder $object
	 * @return int $id identifier for new object
	 */
	public static function insert(Result $object) {
		if($object->procedure_report_id)
			throw new \Exception ("mdtsResult::insert - object already contains identifier");

		// build sql insert from object
		$query = '';
		$params = array();
		$fields = Result::listFields(); 
		
		foreach ($object as $key => $value) {
			if (!in_array($key, $fields)) continue;
			
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the child insert
		$id = sqlInsert("INSERT INTO `procedure_report` SET $query",$params);

		return $id;
	}

	/**
	 * Updates database with information from the given object.
	 *
	 * @return null
	 */
	public function update() {
		// build sql update from object
		$query = '';
		$params = array();
		$fields = $this->listFields();
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields) || $key == 'procedure_report_id') continue;
			if ($value == 'YYYY-MM-DD') continue;
			
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the update
		sqlInsert("UPDATE `procedure_report` SET $query WHERE `procedure_report_id` = $this->procedure_report_id ",$params);

		return;
	}

	/**
	 * Search and retrieve an order object by order number
	 *
	 * @static
	 * @parm string $order_num Order number for the order
	 * @return wmtOrder $object
	 */
	public static function fetchResult($order_num, $order_seq, $update=false) {
		if(!$order_num) return false;

		$result = sqlQuery("SELECT `procedure_report_id` FROM `procedure_report` WHERE `procedure_order_id` = ? AND `procedure_order_seq` = ?",
				array($order_num, $order_seq));
		
		if (!$result['procedure_report_id']) return false;
		$result_data = new Result($result['procedure_report_id'], $update);

		return $result_data;
	}

	/**
	 * Search and retrieve an order object by order number
	 *
	 * @static
	 * @parm string $order_num Order number for the order
	 * @return wmtOrder $object
	 */
	public static function fetchReflex($order_num, $reflex_code, $reflex_set) {
		if(!$order_num || !$reflex_code) return false;

		$query = "SELECT `procedure_result_id` FROM `procedure_report` rep ";
		$query .= "LEFT JOIN `procedure_result` res ON rep.`procedure_report_id` = res.`procedure_report_id` ";
		$query .= "WHERE rep.`procedure_order_id` = ? AND res.`result_code` = ? AND res.`result_set` = ? ";
		$result = sqlQuery($query,array($order_num, $reflex_code, $reflex_set));
		
		if (!$result['procedure_result_id']) return false;
		$result_data = new ResultItem($result['procedure_result_id']);

		return $result_data;
	}

	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public static function listFields() {
		$fields = sqlListFields('procedure_report');
		return $fields;
	}

}
?>