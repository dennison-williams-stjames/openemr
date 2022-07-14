<?php
/** ************************************************************************************
 *	laboratory/view.php
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
 *  @uses laboratory/common.php
 * 
 ************************************************************************************** */
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

include("common.php");

exit();
?>
