<?php
/** *******************************************************************************************
 *	Generic.class.php
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
 *  @package laboratory
 *  @version 3.0.0
 *  @copyright Medical Technology Services
 *  @author Ron Criswell <ron.criswell@MDTechSvcs.com>
 *
 ******************************************************************************************** */

/**
 * All new classes are defined in the WMT namespace
 */
namespace mdts\classes;

use Document;
use OrderService;
use OrderSupportServiceRequest;
use OrderSupportServiceResponse;

use mdts\objects\Patient;

require_once($GLOBALS['srcdir'].'/quest/OrderService.php');
require_once($GLOBALS['srcdir'].'/classes/Document.class.php');

/**
 * Static class definitions
 */

/**
 * The class QuestOrder submits lab order (HL7 messages) to the MedPlus Hub
 * platform.  Encapsulates the sending of an HL7 order to a Quest Lab
 * via the Hub’s SOAP Web service.
 *	
 * @package laboratory
 * @subpackage Quest
 * 
 */
class GenericClient {
	/** 
	 * Class variables
	 */	
	private $STATUS;
	private $ENDPOINT;
	private $USERNAME;
	private $PASSWORD;
	private $SENDING_APPLICATION;
	private $SENDING_FACILITY;
	private $RECEIVING_APPLICATION;
	private $RECEIVING_FACILITY;
	private $WSDL;
		
	// Document storage directory
	private $DOCUMENT_CATEGORY;
	private $REPOSITORY;
		
	private $order_number = null;
	private $insurance = array();
	private $orders = array();
	private $service = null;
	private $request = null;
	private $response = null;
	private $documents = array();

	private $DEBUG = false;
		
	/**
	 * Constructor for the 'order client' class which initializes a reference 
	 * to the Quest Hub web service.
	 *
	 * @package QuestWebService
	 * @access public
	 */
	public function __construct($lab_id) {

	// NEEDS TO BE IMPLEMENTED !!!!!!!!
	
	}
}
