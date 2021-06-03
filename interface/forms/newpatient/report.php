<?php
/**
 * Encounter form report function.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once(dirname(__file__)."/../../globals.php");

function newpatient_report($pid, $encounter, $cols, $id)
{
    $res = sqlStatement("select e.*, f.name as facility_name from form_encounter as e join facility as f on f.id = e.facility_id where e.pid=? and e.id=?", array($pid,$id));
    print "<table><tr><td>\n";
    while ($result = sqlFetchArray($res)) {
        print "<span class=bold>" . xlt('Facility') . ": </span><span class=text>" . text($result{"facility_name"}) . "</span><br>\n";
        if (empty($result['sensitivity']) || acl_check('sensitivities', $result['sensitivity'])) {
            print "<span class=bold>" . xlt('Reason') . ": </span><span class=text>" . nl2br(text($result{"reason"})) . "</span><br>\n";
        }
    }

    $columns = array(
       'symptoms' => xlt('Currently experiencing symptoms:'), 
       'initial_test_for_hiv' => xlt('Initial test for HIV:'), 
       'test_results_for_hiv' => xlt('Expecting HIV test results:'),
       'last_tested_for_hiv' => xlt('Last HIV test:'), 
       'last_tested_for_sti' => xlt('Last STI test:'), 
       'counselor_name' => xlt('Counselor name:'),
       'massage' => xlt('Massage:'), 
       'massage_apt_time' => xlt('Massage appointment time:'), 
       'ear_accupuncture' => xlt('Ear accupunture:'), 
       'full_body_accupuncture' => xlt('Full body accupunture:'), 
       'full_body_accupuncture_apt_time' => xlt('Full body accupuncture time:'),
       'reiki' => xlt('Reiki:'), 
       'reiki_apt_time' => xlt('Reiki appointment time:'), 
       'phone_visit' => xlt('Phone visit:'), 
       'phone_visit_specify' => xlt('Phone visit specify:'),
       'talent_testing' => xlt('Talent testing:'), 
       'food' => xlt('Food:'), 
       'clothing' => xlt('Clothing:'), 
       'condoms' => xlt('Condoms:'), 
       'nex_syringes' => xlt('NEX syringes:'),
       'hygiene_supplies' => xlt('Hygiene supplies:'), 
       'referrals_to_other_services' => xlt('Referrals to other services:'), 
       'referrals_to_other_services_specify' => xlt('Referrals to other services (specify):'), 
       'other_harm_reduction_supplies' => xlt('Other harm reduction supplies:'),
       'other_harm_reduction_supplies_specify' => xlt('Other harm reduction supplies specify:'), 
       'support_group' => xlt('Support group:')
    );

    $res = sqlStatement("select id as visit_id,".
          implode(',', array_keys($columns))
          ." from form_sji_visit ".
          "where form_sji_visit.pid=? and form_sji_visit.encounter=? ".
          "order by id desc ".
          "limit 0,1", array($pid,$encounter));

    $result = sqlFetchArray($res);
    $visit_id = $result['visit_id'];
    foreach ($columns as $name => $label) {
        if (isset($result[$name]) && $result[$name]) {
           if ($result[$name] == 1) { $result[$name] = 'Yes'; }
           if (preg_match('/ (\d\d:\d\d):\d\d/', $result[$name], $matches)) { $result[$name] = $matches[1]; }
           if ($result[$name] == '00:00') { continue; }
           if ($name === 'counselor_name') {
		$sql = "select users.id as uid, fname, lname, list_options.title as title from users ".
			"left join list_options on users.physician_type = list_options.option_id ".
			"where users.id = ? and " .
			"list_options.list_id = 'physician_type' ".
			"order by fname,lname,title";
		$counselor = sqlQuery($sql, array($result[$name]));
		$result[$name] = $counselor['fname'] .' '. $counselor['lname'] .' ('. $counselor['title'] .')';
           }
           print "<span class=bold>$label </span><span id=$name class=text>". $result[$name] . "</span><br>\n";
        }
    }

    $sql = "
       select medical_service
          from form_sji_visit_medical_services
          where pid=?
       ";
    $res = sqlStatement($sql, array($visit_id));

    $services = '';
    while ($result = sqlFetchArray($res)) {
        if (strlen($services)) { $services .= ', '; }
        $services .= $result['medical_service'];
    }

    if (strlen($services)) {
       print "<span class=bold>" . xlt('Seeking medical services') . ": </span><span class=text>" . nl2br(text($services)) . "</span><br>\n";
    } 

    $sql = "
       select initial_test_for_sti
          from form_sji_visit_initial_test_for_sti
          where pid=?
       ";
    $res = sqlStatement($sql, array($visit_id));

    $services = '';
    while ($result = sqlFetchArray($res)) {
        if (strlen($services)) { $services .= ', '; }
        $services .= $result['initial_test_for_sti'];
    }

    if (strlen($services)) {
       print "<span class=bold>" . xlt('Seeking test(s) for') . ": </span><span class=text>" . nl2br(text($services)) . "</span><br>\n";
    } 

    $sql = "
       select test_results_for_sti
          from form_sji_visit_test_results_for_sti
          where pid=?
       ";
    $res = sqlStatement($sql, array($visit_id));

    $services = '';
    while ($result = sqlFetchArray($res)) {
        if (strlen($services)) { $services .= ', '; }
        $services .= $result['test_results_for_sti'];
    }

    if (strlen($services)) {
       print "<span class=bold>" . xlt('Expecting test results for') . ": </span><span class=text>" . nl2br(text($services)) . "</span><br>\n";
    } 

    $sql = "
       select counseling_services
          from form_sji_visit_counseling_services
          where pid=?
       ";
    $res = sqlStatement($sql, array($visit_id));

    $services = '';
    while ($result = sqlFetchArray($res)) {
        if (strlen($services)) { $services .= ', '; }
        $services .= $result['counseling_services'];
    }

    if (strlen($services)) {
       print "<span class=bold>" . xlt('Seeking counseling service(s)') . ": </span><span class=text>" . nl2br(text($services)) . "</span><br>\n";
    } 

    print "</td></tr></table>\n";
}
