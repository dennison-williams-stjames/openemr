<?php
/**
 * soap form
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/api.inc");

// Only clinicians should have access to this
// Clinicians at SJI are those allowed to see lab results
require_once("$srcdir/acl.inc");
if (!acl_check('patients','lab')) die("Access Denied.");
require("C_FormSOAP.class.php");

$c = new C_FormSOAP();
echo $c->view_action($_GET['id']);
