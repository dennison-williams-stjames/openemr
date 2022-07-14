<?php
/** *******************************************************************************************
 *	ORDER.CLASS.PHP
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
 * Provides standardized processing for procedure order detail form elements.
 *
 * @package mdts
 * @subpackage objects
 */
class ResultItem {
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
	 * Constructor for the 'form' class which retrieves the requested
	 * information from the database or creates an empty object.
	 *
	 * @param int $id record identifier
	 * @param boolean $update - DEPRECATED
	 * @return object instance of form class
	 */
	public function __construct($proc_result_id = false) {
		// create empty record with no id
		if (!$proc_result_id) return false;
		
		// retrieve data
		$query = "SELECT * FROM procedure_result WHERE procedure_result_id = ?";
		$results = sqlStatement($query, array($proc_result_id));

		if ($data = sqlFetchArray($results)) {
			// load everything returned into object
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			throw new Exception('ResultItem::_construct - no procedure result item record with key ('.$proc_result_id.')');
		}

		return;
	}

	/**
	 * Inserts data from a form object into the database.
	 *
	 * @static
	 * @param Form $object
	 * @return int $id identifier for new object
	 */
	public static function insert(ResultItem $object) {
		// build sql insert from object
		$query = '';
		$params = array();
		$fields = ResultItem::listFields();
		foreach ($object as $key => $value) {
			if (!in_array($key, $fields)) continue;
			if ($value == 'YYYY-MM-DD') continue;
			
			// substitutions
			if ($key == 'abnormal' && $value == 'N') $value = '';
			
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the insert
		sqlInsert("INSERT INTO procedure_result SET $query",$params);

		return;
	}

	/**
	 * Updates database with information from the given object.
	 *
	 * @return null
	 */
	public function update() {
		// set appropriate default values
		$object->do_not_send = 0;

		// build sql update from object
		$query = '';
		$fields = ResultItem::listFields();
		$params = array($this->procedure_result_id); // keys
		foreach ($this as $key => $value) {
			if (!in_array($key, $fields) || $key == 'procedure_result_id') continue;
			if ($value == 'YYYY-MM-DD') continue;
					
			// substitutions 
			if ($key == 'abnormal' && $value == 'N') $value = '';
			if ($key == 'result_status') $value = ListLook($value,'proc_res_status');
							
			$query .= ($query)? ", `$key` = ? " : "`$key` = ? ";
			$params[] = ($value == 'NULL')? "" : $value;
		}

		// run the update
		sqlStatement("UPDATE procedure_result SET $query WHERE procedure_result_id = ?",$params);

		return;
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

		$query = "SELECT procedure_result_id FROM procedure_result ";
		$query .= "WHERE procedure_report_id = ? ORDER BY procedure_result_id ";

		$results = sqlStatement($query, array($proc_report_id));

		$objectList = array();
		while ($data = sqlFetchArray($results)) {
			$objectList[] = new ResultItem($data['procedure_result_id']);
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
?>