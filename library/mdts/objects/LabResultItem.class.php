<?php
/** *******************************************************************************************
 *	LabResultItem.class.php
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

/**
 * Provides standardized processing for procedure order forms.
 *
 * @package mdts
 * @subpackage laboratory
 */
class LabResultItem {
	public $procedure_result_id;
	public $procedure_report_id;
	public $result_data_type; 
	public $result_code;
	public $result_text;
	public $date;
	public $facility;
	public $units;
	public $result;
	public $normal; // range is a reserved word
	public $abnormal;
	public $comments;
	public $result_status;
	
	/**
	 * Constructor for the class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @return object instance of form class
	 */
	public function __construct($proc_result_id = false) {
		// create empty record with no id
		if (!$proc_result_id) return false;
		
		// retrieve data
		$query = "SELECT * FROM `procedure_result` WHERE `procedure_result_id` = ?";
		$results = sqlStatement($query, array($proc_result_id));

		if ($data = sqlFetchArray($results)) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			throw new \Exception('mdtsLabResultItem::_construct - no procedure result item record with key ('.$proc_result_id.')');
		}

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @return int $id identifier for new object
	 */
	public function store() {
		$insert = true;
		if ($this->procedure_result_id) $insert = false;

		// create record
		$sql = '';
		$binds = array();
		$this->activity = 1;
		$fields = $this->listFields();
		
		// selective updates
		foreach ($this AS $key => $value) {
			if ($key == 'procedure_result_id') continue;
			if ($value == 'YYYY-MM-DD' || $value == "_blank") $value = "";

			// both object and database
			if (array_search($key, $fields) !== false) {
				$sql .= ($sql)? ", `$key` = ? " : "`$key` = ? ";
				$binds[] = ($value == 'NULL')? "" : $value;
			}
		}
		
		// run the child insert
		if ($insert) { // do insert
			$this->procedure_result_id = sqlInsert("REPLACE `procedure_result` SET $sql", $binds);
		} else { // do update
			$binds[] = $this->proc_result_id;
			sqlStatement("UPDATE `procedure_result` SET $sql WHERE `procedure_result_id` = ?", $binds);
		}
				
		return $this->procedure_result_id;
	}


	/**
	 * Returns an array list of procedure order item objects associated with the
	 * given order.
	 *
	 * @static
	 * @param int $proc_report_id Procedure report identifier (parent result)
	 * @return array $objectList list of selected objects
	 */
	public static function fetchItemList($proc_report_id = false) {
		if (!$proc_report_id) return false;

		$query = "SELECT `procedure_result_id` FROM `procedure_result` ";
		$query .= "WHERE `procedure_report_id` = ? ORDER BY `procedure_result_id` ";

		$results = sqlStatement($query, array($proc_report_id));

		$objectList = array();
		while ($data = sqlFetchArray($results)) {
			$objectList[] = new LabResultItem($data['procedure_result_id']);
		}

		return $objectList;
	}

	/**
	 * Returns an array of valid database fields for the object.
	 *
	 * @static
	 * @return array list of database field names
	 */
	public static function listFields() {
		$fields = sqlListFields('procedure_result');
		return $fields;
	}
}

