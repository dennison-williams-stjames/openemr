<?php
/** *****************************************************************************************
 *	LABORATORY.CLASS.PHP
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
 *  @subpackage laboratory
 *  @version 3.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace mdts\objects;

/** 
 * Provides a representation of the patient data record. Fields are dymanically
 * processed based on the current database definitions. 
 *
 * @package mdts
 * @subpackage laboratory
 */
class Laboratory {
	// Selected elements
	public $ppid;
	public $uuid;
	public $name;
	public $npi;
	public $send_app_id;
	public $send_fac_id;
	public $recv_app_id;
	public $recv_fac_id;
	public $DorP;
	public $direction;
	public $protocol;
	public $remote_host;
	public $remote_port;
	public $login;
	public $password;
	public $orders_path;
	public $results_path;
	public $notes;
	public $lab_director;
	public $active;
	public $type;
	
	/**
	 * Constructor for the 'laboratory' class which retrieves the requested 
	 * procedure laboratory information from the database or creates an empty object.
	 * 
	 * @param int $ppid provider record identifier
	 * @return object instance of laboratory provider object class
	 */
	public function __construct($ppid = false) {
		// create empty record or retrieve
		if (!$ppid) return false;

		// retrieve data
		$query = "SELECT * FROM `procedure_providers` WHERE `ppid` = ?";
		$binds = array($ppid);
		$data = sqlQuery($query, $binds);

		if ($data) {
			foreach ($data AS $field => $value) {
				// load everything returned into object
				$this->$field = $value;
			}
		} else {
			throw new \Exception('mdtsLaboratory::_construct - no laboratory provider record with ppid ('.$ppid.').');
		}
		
		return;
	}	

	/**
	 * Retrieve list of laboratory provider objects
	 *
	 * @static
	 * @parm string 	$type - laboratory provider type
	 * @param boolean 	$active - active status flag
	 * @return array 	$list - list of lab provider objects
	 */
	public static function fetchLabs($type=false, $active=true) {
		$binds = null;
		$query = "
			SELECT `ppid` FROM `procedure_providers` 
			WHERE 1 = 1 
		";
		
		if ($type && $type != 'laboratory') {
			$query .= "AND `type` LIKE ? ";
			$binds[] = $type;
		}
		
		if ($active) {
			$query .= "AND `active` = 1 ";
		}
		
		$query .= "ORDER BY name";
		
		$list = array();
		$result = sqlStatementNoLog($query, $binds);
		while ($record = sqlFetchArray($result)) {
			$list[$record['ppid']] = new Laboratory($record['ppid']);
		}
		
		return $list;
	}

	
	/**
	 * Retrieve a provider object by USERNAME value. Uses the base constructor for the 'provider' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @method		getUsername
	 * @param 		string $username provider user name
	 * @return 		Laboratory
	 * 
	 */
	public static function getUsername($username) {
		if(!$username)
			throw new \Exception('mdtsLaboratory::getUserLaboratory - no provider username provided.');
		
		$data = sqlQuery("SELECT `id` FROM `users` WHERE `username` LIKE ?", array($username));
		if(!$data || !$data['id'])
			throw new \Exception('mdtsLaboratory::getUserLaboratory - no provider with username provided.');
		
		return new Laboratory($data['id']);
	}

	/**
	 * Retrieve a new provider object by npi.
	 * 
	 * @method		getNpi
	 * @param		string 	$npi
	 * @return		Laboratory
	 * @static
	 * 
	 */
	public static function getNpi($npi) {
		$id = null;
		
		// look for existing entry
		if ($npi) {
			$record = sqlQuery("SELECT id FROM users WHERE npi LIKE ?", array($npi));
			$id = $record['id'];
		}

		// create/retrieve data object
		$provider = new Laboratory($id);

		return $provider;
	}

	/**
	 * Retrieve a new provider object by cda guid.
	 * 
	 * @method		getCdaGuid
	 * @param		string 	$npi
	 * @return		Laboratory
	 * @static
	 * 
	 */
	public static function getCdaGuid($cda_guid) {
		$id = null;
		
		// look for existing entry
		if ($cda_guid) {
			$record = sqlQuery("SELECT id FROM users WHERE cda_guid LIKE ?", array($cda_guid));
			$id = $record['id'];
		}

		// create/retrieve data object
		$provider = new Laboratory($id);

		return $provider;
	}

	/**
	 * Retrieve a new provider object by name.
	 * 
	 * @method		getName
	 * @param		string 	$fname
	 * @param		string 	$mname
	 * @param		string 	$lname
	 * @return		Laboratory
	 * @static
	 * 
	 */
	public static function getName($fname,$mname,$lname) {
		$id = null;
		
		// look for existing entry
		if ($fname && $mname && $lname) {
			$record = sqlQuery("SELECT id FROM users WHERE fname LIKE ? AND mname LIKE ? AND lname LIKE ?", array($fname,$mname,$lname));
			$id = $record['id'];
		} elseif ($fname && $lname) {
			$record = sqlQuery("SELECT id FROM users WHERE fname LIKE ? AND lname LIKE ?", array($fname,$lname));
			$id = $record['id'];
		}

		// create/retrieve data object
		$provider = new Laboratory($id);

		return $provider;
	}

	
	/**
	 * Retrieve a provider object by NPI value. Uses the base constructor for the 'provider' class 
	 * to create and return the object. 
	 * 
	 * @static
	 * @param string $npi provider npi
	 * @return object instance of provider class
	 */
	public static function getNpiLaboratory($npi) {
		if(!$npi)
			throw new \Exception('mdtsLaboratory::getNpiLaboratory - no provider NPI provided.');
		
		$data = sqlQuery("SELECT `id` FROM `users` WHERE `npi` LIKE ?", array($npi));
		if(!$data || !$data['id'])
			throw new \Exception('mdtsLaboratory::getNpiLaboratory - no provider with NPI provided.');
		
		return new Laboratory($data['id']);
	}
	
	/**
	 * Build selection list from table data.
	 *
	 * @param int $id - current entry id
	 */
	public function getOptions($id, $default='') {
		$result = '';
		
		// create default if needed
		if ($default) {
			$result .= "<option value='' ";
			$result .= (!$id || $id == '')? "selected='selected'" : "";
			$result .= ">".$default."</option>\n";
		}

		// get providers
		$list = self::fetchLaboratorys();
		
		// build options
		foreach ($list AS $provider) {
			$result .= "<option value='" . $provider->id . "' ";
			if ($id == $provider->id) 
				$result .= "selected=selected ";
			$result .= ">" . $provider->format_name ."</option>\n";
		}
	
		return $result;
	}
	
	/**
	 * Echo selection option list from table data.
	 *
	 * @param id - current entry id
	 * @param result - string html option list
	 */
	public function showOptions($id, $default='') {
		echo self::getOptions($id, $default);
	}
	
	/**
	 * Returns an array of valid database fields for the object. Note that this
	 * function only returns fields that are defined in the object and are
	 * columns of the specified database.
	 *
	 * @return array list of database field names
	 */
	public function listFields() {
		$fields = array();

		$columns = sqlListFields('users');
		foreach ($columns AS $property) {
			if (property_exists($this, $property)) $fields[] = $property;
		}
		
		return $fields;
	}
	
}

?>