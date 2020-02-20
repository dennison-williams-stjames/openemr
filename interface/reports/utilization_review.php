<?php
/**
 * This report lists a Utilization Review Report for the state of CA
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Dennison Williams <dennison.williams@stjamesinfirmary.org>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2006-2016 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

use OpenEMR\Core\Header;

// Prepare a string for CSV export.
function qescape($str)
{
    $str = str_replace('\\', '\\\\', $str);
    return str_replace('"', '\\"', $str);
}

$from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : '';
$to_date   = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : '';
if (empty($to_date) && !empty($from_date)) {
    $to_date = date('Y-12-31');
}

if (empty($from_date) && !empty($to_date)) {
    $from_date = date('Y-01-01');
}

?>
<html>
<head>

<title><?php echo xlt('Annual Utilization Report'); ?></title>

<?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<script language="JavaScript">

$(document).ready(function() {
    oeFixedHeaderSetup(document.getElementById('mymaintable'));
    top.printLogSetup(document.getElementById('printbutton'));

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
});

</script>

<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
        margin-bottom: 10px;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
    #report_results {
        width: 100%;
    }
}

</style>

<head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Annual Utilization'); ?></span>

<div id="report_parameters_daterange">
<?php if (!(empty($to_date) && empty($from_date))) { ?>
    <?php echo text(oeFormatShortDate($from_date)) ." &nbsp; " . xlt('to') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
<?php } ?>
</div>

<form name='theform' id='theform' method='post' action='utilization_review.php' onsubmit='return top.restoreSession()'>

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='60%'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='control-label'>
                <?php echo xlt('Visits From'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
            </td>
            <td class='control-label'>
                <?php echo xlt('To'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td align='left' valign='middle' height="100%">
    <table style='border-left:1px solid; width:100%; height:100%' >
        <tr>
            <td>
        <div class="text-center">
                  <div class="btn-group" role="group">
                    <a href='#' class='btn btn-default btn-save' onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (isset($_POST['form_refresh'])) { ?>
                      <a href='#' id='printbutton' class='btn btn-default btn-print'>
                            <?php echo xlt('Print'); ?>
                      </a>
                    <?php } ?>
              </div>
        </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<?php

if (isset($_POST['form_refresh']) || isset($_POST['form_csvexport'])) {

?>
  <div id="report_results">
  <div class="col-sm-12">
   <span class="title"><?php echo xlt('Race'); ?></span>
  </div>
  <table id='mymaintable'>
   <thead>
    <th> <?php echo xlt('White'); ?> </th>
    <th> <?php echo xlt('Black'); ?> </th>
    <th> <?php echo xlt('Native American/Alaskan Native'); ?> </th>
    <th> <?php echo xlt('Asian/Pacific Islander'); ?> </th>
    <th> <?php echo xlt('More than one race'); ?> </th>
    <th> <?php echo xlt('Other/Unknown'); ?> </th>
    <th> <?php echo xlt('Total'); ?> </th>
 </thead>
 <tbody>
<?php
    $totalpts = 0;
    $sqlArrayBind = array();
    $query = "SELECT " .
    "race,p.pid as mrn,max(e.id) ".
    "FROM patient_data AS p ";
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON " .
        "e.pid = p.pid WHERE " .
        "e.date >= ? AND " .
        "e.date <= ? ";
        array_push($sqlArrayBind, $from_date .' 00:00:00', $to_date . ' 23:59:59');
    } else {
         $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
    }

    $query .= 'group by race,mrn';

    $res = sqlStatement($query, $sqlArrayBind);

    $race = array();
    $total = 0;
    while ($row = sqlFetchArray($res)) {

        if ($row['race'] === 'NULL' || $row['race'] === '' || empty($row['race']) ) {
           if (isset($race['Unknown'])) {
              $race['Unknown'] = $race['Unknown'] + 1;
           } else {
              $race['Unknown'] = 1;
           }
        } else if ($row['race'] === 'amer_ind_or_alaska_native') {
           $race['Native'] = $race['Native'] + 1;
        } else if ($row['race'] === 'Asian') {
           $race['Asian'] = $race['Asian'] + 1;
        } else if ($row['race'] === 'black_or_afri_amer') {
           $race['Black'] = $race['Black'] + 1;
        } else if ($row['race'] === 'white') {
           $race['White'] = $race['White'] + 1;
        } else {

           if (preg_match('/,/', $row['race'])) {
              if (isset($race['Mixed'])) {
                 $race['Mixed'] = $race['Mixed'] + 1;
              } else {
                 $race['Mixed'] = 1;
              }
           } else {
              error_log('Did not match race: '. $row['race']);
           }
        }

        $total = $total + 1;
    }

        ?>
   <tr>
   <td> <?php echo text($race['White']); ?> </td>
   <td> <?php echo text($race['Black']); ?> </td>
   <td> <?php echo text($race['Native']); ?> </td>
   <td> <?php echo text($race['Asian']); ?> </td>
   <td> <?php echo text($race['Mixed']); ?> </td>
   <td> <?php echo text($race['Unknown']); ?> </td>
   <td> <?php echo text($total); ?> </td>
  </tr>

</tbody>
</table>

  <div class="col-sm-12">
   <span class="title"><?php echo xlt('Ethnicity'); ?></span>
  </div>
  <table id='myethnicitytable'>
   <thead>
    <th> <?php echo xlt('Hispanic'); ?> </th>
    <th> <?php echo xlt('Non-Hispanic'); ?> </th>
    <th> <?php echo xlt('Other/Unknown'); ?> </th>
    <th> <?php echo xlt('Total'); ?> </th>
 </thead>
 <tbody>
<?php
    $totalpts = 0;
    $sqlArrayBind = array();
    $query = "SELECT " .
    "ethnicity,p.pid as mrn,max(e.id) ".
    "FROM patient_data AS p ";
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON " .
        "e.pid = p.pid WHERE " .
        "e.date >= ? AND " .
        "e.date <= ? ";
        array_push($sqlArrayBind, $from_date .' 00:00:00', $to_date . ' 23:59:59');
    } else {
         $query .= "LEFT OUTER JOIN form_encounter AS e ON " .
         "e.pid = p.pid ";
    }

    $query .= 'group by ethnicity,mrn';

    $res = sqlStatement($query, $sqlArrayBind);

    $ethnicity = array();
    $total = 0;
    while ($row = sqlFetchArray($res)) {
        
        if (
           $row['ethnicity'] === 'NULL' || 
           $row['ethnicity'] === '' || 
           empty($row['ethnicity']) || 
           $row['ethnicity'] === 'refused' ) {

           if (isset($ethnicity['Unknown'])) {
              $ethnicity['Unknown'] = $ethnicity['Unknown'] + 1;
           } else {
              $ethnicity['Unknown'] = 1;
           }
        } else if ($row['ethnicity'] === 'hisp_or_latin') {
           $ethnicity['Hispanic'] = $ethnicity['Hispanic'] + 1;
        } else if ($row['ethnicity'] === 'not_hisp_or_latin') {
           $ethnicity['Non-Hispanic'] = $ethnicity['Non-Hispanic'] + 1;
        } else {
           error_log('Did not match ethnicity '. $row['ethnicity']);
        }

        $total = $total + 1;
    }

        ?>
   <tr>
   <td> <?php echo text($ethnicity['Hispanic']); ?> </td>
   <td> <?php echo text($ethnicity['Non-Hispanic']); ?> </td>
   <td> <?php echo text($ethnicity['Unknown']); ?> </td>
   <td> <?php echo text($total); ?> </td>
  </tr>

</tbody>
</table>

  <div class="col-sm-12">
   <span class="title"><?php echo xlt('Federal Poverty Level'); ?></span>
  </div>
  <table id='mypovertytable'>
   <thead>
    <th> <?php echo xlt('Under 100%'); ?> </th>
    <th> <?php echo xlt('100 - 138%'); ?> </th>
    <th> <?php echo xlt('139 - 200%'); ?> </th>
    <th> <?php echo xlt('201 - 400%'); ?> </th>
    <th> <?php echo xlt('Above 400%'); ?> </th>
    <th> <?php echo xlt('Unknown'); ?> </th>
    <th> <?php echo xlt('Total'); ?> </th>
 </thead>
 <tbody>
<?php
    // https://www.thebalance.com/federal-poverty-level-definition-guidelines-chart-3305843
    // The Federal poverty level is a function of the years poverty level values and the
    // number of dependents.  We are collecting monthly income in our core variables 
    // form but we have incompletly collected this information
    $totalpts = 0;
    $sqlArrayBind = array();
    $query = "SELECT " .
    "12 * monthly_income as yearly_income, monthly_income, family_size, p.pid as mrn, max(e.id) ".
    "FROM patient_data AS p ";
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON " .
        "e.pid = p.pid WHERE " .
        "e.date >= ? AND " .
        "e.date <= ? ";
        array_push($sqlArrayBind, $from_date .' 00:00:00', $to_date . ' 23:59:59');
    } else {
         $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
    }

    $query .= " GROUP BY yearly_income,monthly_income,family_size,mrn";

    $res = sqlStatement($query, $sqlArrayBind);

    $poverty = array();
    $total = 0;

/*
https://www.thebalance.com/federal-poverty-level-definition-guidelines-chart-3305843

Number of People in Household	48 States & DC	Alaska	Hawaii
One	$12,760	$15,950	$14,680
Two	$17,240	$21,550	$19,830
Three	$21,720	$27,150	$24,980
Four	$26,200	$32,750	$30,130
Five	$30,680	$38,350	$35,280
Six	$35,160	$43,950	$40,430
Seven	$39,640	$49,550	$45,580
Eight	$44,120	$55,150	$50,730
Nine+   $4,480	$5,600	$5,150
For nine or more, add this amount for each additional person
*/
    $poverty_chart_2020 = array(
       1 => 12760,
       2 => 17240,
       3 => 21720,
       4 => 26200,
       5 => 30680,
       6 => 35160,
       7 => 39640,  // we don't have any participants with greater than this
       8 => 44120,
    );

    while ($row = sqlFetchArray($res)) {

        $total = $total + 1;

        if (!isset($row['monthly_income']) || strlen($row['monthly_income']) == 0) {
           if (isset($poverty['Unknown'])) {
              $poverty['Unknown'] = $poverty['Unknown'] + 1;
           } else {
              $poverty['Unknown'] = 1;
           }
           continue;
        }

        $family_size = 1;
        if (isset($row['family_size']) && $row['family_size'] != 0) {
           $family_size = $row['family_size'];
        } 

        $level = round ($row['yearly_income'] / $poverty_chart_2020[$family_size], 2);

        if ($level < 1) {
           if (isset($poverty['Under'])) {
              $poverty['Under'] = $poverty['Under'] + 1;
           } else {
              $poverty['Under'] = 1;
           }
        } else if ($level >= 1 && $level < 1.38) {
           if (isset($poverty[1])) {
              $poverty[1] = $poverty[1] + 1;
           } else {
              $poverty[1] = 1;
           }
        } else if ($level >= 1.39 && $level < 2) {
           if (isset($poverty[2])) {
              $poverty[2] = $poverty[2] + 1;
           } else {
              $poverty[2] = 1;
           }
        } else if ($level >= 2.01 && $level < 4) {
           if (isset($poverty[3])) {
              $poverty[3] = $poverty[3] + 1;
           } else {
              $poverty[3] = 1;
           }
        } else if ($level >= 4) {
           if (isset($poverty[4])) {
              $poverty[4] = $poverty[4] + 1;
           } else {
              $poverty[4] = 1;
           }
        } else {
           error_log('Unknown poverty level('. $level .') for pid: '. $row['mrn'] );
           if (isset($poverty['Unknown'])) {
              $poverty['Unknown'] = $poverty['Unknown'] + 1;
           } else {
              $poverty['Unknown'] = 1;
           }
        }
       
    }

        ?>
   <tr>
   <td> <?php echo text($poverty['Under']); ?> </td>
   <td> <?php echo isset($poverty[1]) ? text($poverty[1]) : 0; ?> </td>
   <td> <?php echo isset($poverty[2]) ? text($poverty[2]) : 0; ?> </td>
   <td> <?php echo isset($poverty[3]) ? text($poverty[3]) : 0; ?> </td>
   <td> <?php echo isset($poverty[4]) ? text($poverty[4]) : 0; ?> </td>
   <td> <?php echo isset($poverty['Unknown']) ? text($poverty['Unknown']) : 0; ?> </td>
   <td> <?php echo text($total); ?> </td>
  </tr>

</tbody>
</table>

  <div class="col-sm-12">
   <span class="title"><?php echo xlt('Age'); ?></span>
  </div>
  <table id='myagetable'>
   <thead>
    <th> <?php echo xlt('Under 1 year'); ?> </th>
    <th> <?php echo xlt('1 - 4 years'); ?> </th>
    <th> <?php echo xlt('5 - 12 years'); ?> </th>
    <th> <?php echo xlt('13 - 14 years'); ?> </th>
    <th> <?php echo xlt('15 - 19 years'); ?> </th>
    <th> <?php echo xlt('20 - 34 years'); ?> </th>
    <th> <?php echo xlt('35 - 44 years'); ?> </th>
    <th> <?php echo xlt('45 - 64 years'); ?> </th>
    <th> <?php echo xlt('65 and Over'); ?> </th>
    <th> <?php echo xlt('Unknown'); ?> </th>
    <th> <?php echo xlt('Total'); ?> </th>
 </thead>
 <tbody>
<?php
    $totalpts = 0;
    $sqlArrayBind = array();
    $query = "SELECT year(DOB) as birth_year, p.pid as mrn,max(e.id) " .
    "FROM patient_data AS p ";
    if (!empty($from_date)) {
        $query .= "JOIN form_encounter AS e ON " .
        "e.pid = p.pid WHERE " .
        "e.date >= ? AND " .
        "e.date <= ? ";
        array_push($sqlArrayBind, $from_date .' 00:00:00', $to_date . ' 23:59:59');
    } else {
        $query .= "LEFT OUTER JOIN form_encounter AS e ON e.pid = p.pid ";
    }
    $query .= 'GROUP BY birth_year,mrn';

    $res = sqlStatement($query, $sqlArrayBind);

    $ages = array();
    $total = 0;
    $this_year = date("Y");

    while ($row = sqlFetchArray($res)) {

        $total = $total + 1;

        if (!isset($row['birth_year']) || strlen($row['birth_year']) == 0) {
           error_log('ERROR: could not determine birth year');
           exit;
        }

        $age = $this_year - $row['birth_year'];

	// handle a few of the DOB's that are incorrectly entered
	if ($row['birth_year'] > '2017' || $row['birth_year'] == '0000' ) {
           if (isset($ages['Unknown'])) {
              $ages['Unknown'] = $ages['Unknown'] + 1;
           } else {
              $ages['Unknown'] = 1;
           }
        } else if ($age < 1) {
           if (isset($ages['Under'])) {
              $ages['Under'] = $ages['Under'] + 1;
           } else {
              $ages['Under'] = 1;
           }
        } else if ($age < 5) {
           if (isset($ages[4])) {
              $ages[4] = $ages[4] + 1;
           } else {
              $ages[4] = 1;
           }
        } else if ($age < 12) {
           if (isset($ages[12])) {
              $ages[12] = $ages[12] + 1;
           } else {
              $ages[12] = 1;
           }
        } else if ($age < 15) {
           if (isset($ages[14])) {
              $ages[14] = $ages[14] + 1;
           } else {
              $ages[14] = 1;
           }
        } else if ($age < 20) {
           if (isset($ages['19'])) {
              $ages['19'] = $ages['19'] + 1;
           } else {
              $ages['19'] = 1;
           }
        } else if ($age < 35) {
           if (isset($ages['34'])) {
              $ages['34'] = $ages['34'] + 1;
           } else {
              $ages['34'] = 1;
           }
        } else if ($age < 45) {
           if (isset($ages['44'])) {
              $ages['44'] = $ages['44'] + 1;
           } else {
              $ages['44'] = 1;
           }
        } else if ($age < 65) {
           if (isset($ages['64'])) {
              $ages['64'] = $ages['64'] + 1;
           } else {
              $ages['64'] = 1;
           }
        } else if ($age >= 65) {
           if (isset($ages['65'])) {
              $ages['65'] = $ages['65'] + 1;
           } else {
              $ages['65'] = 1;
           }
        } else {
           error_log('Unknown age '. $age .' for pid: '. $row['mrn'] );
           exit;
        }
       
    }

        ?>
   <tr>
   <td> <?php echo isset($ages['Under']) ? text($ages['Under']) : 0; ?> </td>
   <td> <?php echo isset($ages[4]) ? text($ages[4]) : 0; ?> </td>
   <td> <?php echo isset($ages[12]) ? text($ages[12]) : 0; ?> </td>
   <td> <?php echo isset($ages[14]) ? text($ages[14]) : 0; ?> </td>
   <td> <?php echo isset($ages[19]) ? text($ages[19]) : 0; ?> </td>
   <td> <?php echo isset($ages[34]) ? text($ages[34]) : 0; ?> </td>
   <td> <?php echo isset($ages[44]) ? text($ages[44]) : 0; ?> </td>
   <td> <?php echo isset($ages[64]) ? text($ages[64]) : 0; ?> </td>
   <td> <?php echo isset($ages[65]) ? text($ages[65]) : 0; ?> </td>
   <td> <?php echo isset($ages['Unknown']) ? text($ages['Unknown']) : 0; ?> </td>
   <td> <?php echo text($total); ?> </td>
  </tr>

</tbody>
</table>


</div> <!-- end of results -->
<?php
    } 
if (!isset($_POST['form_refresh']) && !isset($_POST['form_csvexport'])) {
?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
<?php
}
?>

</form>
</body>

</html>
