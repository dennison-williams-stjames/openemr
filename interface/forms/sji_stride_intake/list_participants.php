<?php
/**
 *   This program is free software; you can redistribute it and/or
 *   modify it under the terms of the GNU General Public License
 *   as published by the Free Software Foundation; either version 2
 *   of the License, or (at your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
include_once(dirname(__FILE__).'/../../globals.php');
include_once($GLOBALS["srcdir"]."/api.inc");
require_once "$srcdir/user.inc";
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

function get_stride_participants() {
	$query = "
		select pd.id,pd.pid,fname,lname,sex,gender
		from patient_data as pd
		inner join form_sji_stride_intake as s on (s.pid = pd.pid)
	";

	$res = sqlStatement($query);

	$participants = array();
	while ($row = sqlFetchArray($res)) {
		$participants[$row['pid']]['name'] = $row['fname'] .' '. $row['lname'];
		$participants[$row['pid']]['sex'] = $row['sex'];
		$participants[$row['pid']]['gender'] = $row['gender'];
		$participants[$row['pid']]['id'] = $row['id'];
	}

	$query = "
		select pd.id,pd.pid,fname,lname,sex,gender
		from patient_data as pd
		inner join documents as d on (d.foreign_id = pd.pid)
		left join categories_to_documents as cd on (cd.document_id = d.id)
		left join categories as c on (c.id = cd.category_id)
		where c.name like '%STRIDE%'
		and d.deleted = 0
	";

	$res = sqlStatement($query);

	while ($row = sqlFetchArray($res)) {
		$participants[$row['pid']]['name'] = $row['fname'] .' '. $row['lname'];
		$participants[$row['pid']]['sex'] = $row['sex'];
		$participants[$row['pid']]['gender'] = $row['gender'];
		$participants[$row['pid']]['id'] = $row['id'];
	}

	foreach (array_keys($participants) as $pid) {

		// Add on a few core variable columns
		$query = "
			select max(id),pronouns,aliases
			from form_sji_intake_core_variables 
			where pid = ?
			group by pronouns,aliases
			order by id desc 
			limit 1
		";
		$res2 = sqlStatement($query, array($pid));
		$pronouns = sqlFetchArray($res2);
		if ($pronouns['pronouns']) {
			$participants[$pid]['pronouns'] = $pronouns['pronouns'];
		}

		if ($pronouns['aliases']) {
			$participants[$pid]['name'] .= " aka: ". $pronouns['aliases'];
		}

		// Add on the last encounter date
		$query = "
			select max(id),date
			from forms
			where pid = ?
			group by date
			order by id desc 
			limit 1
		";
		$res3 = sqlStatement($query, array($pid));
		$visit = sqlFetchArray($res3);
		if ($visit['date']) {
			$participants[$pid]['last_visit'] = $visit['date'];
			
		}
	}
	return $participants;
}

$participants = get_stride_participants();
//print "<pre>". print_r($participants, 1) ."</pre>\n";
?>
<html>
<head>
    <?php Header::setupHeader(['datatables', 'datatables-colreorder', 'datatables-dt', 'datatables-bs']); ?>
    <title><?php echo xlt("STRIDE Participants"); ?></title>
    <script>
       $document.ready(function() {
          $('#stride-participants').dataTable();
       });
    </script>
<?php
    $arrOeUiSettings = array(
    'heading_title' => xl('STRIDE Participants'),
    'include_patient_name' => false,
    'expandable' => true,
    'expandable_files' => array('dynamic_finder_xpd'),//all file names need suffix _xpd
    'action' => "search",//conceal, reveal, search, reset, link or back
    'action_title' => "",//only for action link, leave empty for conceal, reveal, search
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
    <table id="stride-participants" class="display" style="width:100%">
    <thead>
       <tr>
	  <!-- TODO: this should be translateable -->
          <th>Name</th>
          <th>Pronouns</th>
          <th>Last updated</th>
       </tr>
       <tbody>
    <?php
	foreach ($participants as $participant) {
error_log(print_r($participant, 1));
		print "<tr>". 
		"<td>". $participant['name'] ."</td>".
		"<td>". $participant['pronouns'] ."</td>".
		"<td>". $participant['last_visit'] ."</td>".
		"</tr>\n";
	}
    ?>
       <tbody>
    </table>
    </div>
</body>
</html>
