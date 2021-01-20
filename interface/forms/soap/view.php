<?php
include_once("../../globals.php");
include_once("$srcdir/api.inc");
// Only clinicians should have access to this
// Clinicians at SJI are those allowed to see lab results
require_once("$srcdir/acl.inc");
if (!acl_check('patients','lab')) die("Access Denied.");

require("C_FormSOAP.class.php");

$c = new C_FormSOAP();
echo $c->view_action($_GET['id']);
