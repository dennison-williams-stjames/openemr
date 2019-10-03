<?php
/**
 * Controller to handle label print requests
 *
 * <pre>
 * Expected REQUEST parameters
 * $_REQUEST['num'] - The numbe rof labels to print
 * $_REQUEST['encounter'] - The encounter we are printing the labels for
 * </pre>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dennison Williams <dennison.williams@gmail.com>
 */


require_once("../globals.php");
require_once("$srcdir/authentication/password_change.php");

$num=intval($_REQUEST['num']);
$encounter=$_REQUEST['encounter'];

if (!is_int($num) || $num>100 || $num<1) {
    echo "<div class='alert alert-danger'>" . xlt("Invalid number of labels") . "</div>";
    exit;
}

$host = '192.168.20.246';
$port = 9100;
$errMsg='';

// TODO:
// Get chart number, visit date, name, DOB, Sex assigned at birth, and HAP #
$sql = 'SELECT date,pid FROM form_encounter WHERE encounter=?';
$res = sqlStatement($sql, array($encounter));
$row = sqlFetchArray($res);
$date = date('m/d/Y', strtotime($row['date']));
$pid = $row['pid'];

$sql = 'SELECT DOB,sex,fname,lname FROM patient_data WHERE pid=?';
$res = sqlStatement($sql, array($pid));
$row = sqlFetchArray($res);
$dob = date('m/d/Y', strtotime($row['DOB']));
$sex = $row['sex'];
if (isset($row['lname'])) {
   $name = $row['lname'] .', ';
}
$name .= $row['fname'];

$sql = 'SELECT gender FROM form_sji_intake_core_variables WHERE pid=?';
$res = sqlStatement($sql, array($pid));
$row = sqlFetchArray($res);
$gender = $row['gender'];
$sex_code = code_sex($sex, $gender);
error_log(__FUNCTION__ ."() gender: $gender sex_code: $sex_code");

$sql = 'SELECT name,policy_number FROM insurance_data '.
       'LEFT JOIN insurance_companies on (insurance_data.provider=insurance_companies.id) '.
       'WHERE pid=? AND type="primary"';
$res = sqlStatement($sql, array($pid));
$ins = '';
while ( $row = sqlFetchArray($res) ) {
   if (strlen($row['name']) > 0) {
	   $ins .= '^FD'. $row['name'] .': '. $row['policy_number'] ."^FS\n";
   }
}
error_log(__FUNCTION__ ."() ins: $ins");

// Create label string
$message = <<<MSG
^XA
^LH45,20
^PQ$num
^CFF
^FO15,00
^FDSJI/CTYC: $pid^FS
^CFD
^FO00,30
^FDVisit: $date^FS
^FO00,55
^FD$name^FS
^FO00,80
^FDDOB: $dob $sex_code^FS
^FO00,105
$ins
^PH
^XZ
MSG;


// create socket
$success = true;
$socket = socket_create(AF_INET, SOCK_STREAM, 0);
if (!$socket) {
   $success = false;
   $errMsg = 'Could not connect to label printer';
} 

$result = false;
if ($socket) {
   // connect to server
   $result = socket_connect($socket, $host, $port);
}
if (!$result) {
   $success = false;
   $errMsg = 'Could not connect to label printer';
}

if ($result) {
   // send string to server
   $sent = socket_write($socket, $message, strlen($message));
   if ($sent === false) {
      $success = false;
      $errMsg = 'Could not connect to label printer';
   }
}

if ($socket) {
   // close socket
   socket_close($socket);
}

if ($success) {
    echo "<div class='alert alert-success'>$num " . 
       xlt("labels sent to printer") . "</div>";
} else {
    // If update_password fails the error message is returned
    echo "<div class='alert alert-danger'>" . text($errMsg) . "</div>";
}

/*
if sex == 'A': sex = 'TF'
if sex == 'B': sex = 'TM'
if sex == 'C': sex = 'I'
if sex == 'CF': sex = 'IF'
if sex == 'CM': sex = 'IM'
if sex == 'M': sex = 'CM'
if sex == 'F': sex = 'CF'
if sex == 'OF': sex = 'NB AFAB'
if sex == 'OM': sex = 'NB AMAB'
*/
function code_sex($sex, $gender) {
   if (strcmp($sex, 'Male') == 0 && strlen($gender) == 0) { return 'CM'; }
   if (strcmp($sex, 'Female') == 0 && strlen($gender) == 0) { return 'CF'; }

   if ($gender == 'Transgender Female') { return 'TF'; }
   if ($gender == 'Cisgender Female') { return 'CF'; }
   if ($gender == 'Cisgender Male') { return 'CM'; }
   if ($gender == 'Transgender Male') { return 'TM'; }
   if ($gender == 'Intersex male') { return 'IM'; }
   if ($gender == 'Other Male') { return 'NB AMAB'; }
   if ($gender == 'Other Female') { return 'NB AFAB'; }
   if ($gender == 'Intersex Female') { return 'IF'; }
}
