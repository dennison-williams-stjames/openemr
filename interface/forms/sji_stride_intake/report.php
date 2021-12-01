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

function sji_stride_intake_get_data($pid, $id = 0) {
    $form_name = "sji_stride_intake";
    $count = 0;

    if (!$id) {
       // If we did not recieve a form id then look it up
       $query = "select id from form_sji_stride_intake where pid=? order by date desc limit 1";
       $res = sqlStatement($query, array($pid));
       $row = sqlFetchArray($res);
       $id = $row['id'];
    }

    $data = formFetch("form_".$form_name, $id);

    // Add on name, address, phone and pronouns
    $query = "select fname,lname,street,city,state,postal_code from patient_data where pid=?";
    $res = sqlStatement($query, array($pid));
    $row = sqlFetchArray($res);
    $data['Name'] = $row['fname'] .' '. $row['lname'];
    $data['Address'] = $row['street'] .', '. $row['city'] .', '. $row['state'] .' '. $row['postal_code'];

    $query = "select pronouns from form_sji_intake_core_variables where pid=? order by date desc limit 1";
    $res = sqlStatement($query, array($pid));
    $row = sqlFetchArray($res);
    if (isset($row['pronouns'])) {
       $data['Pronouns'] = $row['pronouns'];
    }

    // Add on supportive people
    $query = "select id from form_sji_intake where pid = ? order by id DESC limit 1";
    $res = sqlStatement($query, array($pid));
    $intake = sqlFetchArray($res);
    $intake_id = $intake['id'];
    if (isset($intake_id)) {
       $query = "select supportive_people from form_sji_intake_supportive_people where pid=?";
       $res = sqlStatement($query, array($intake_id));
       $supportive_people = array();

       while ($row = sqlFetchArray($res)) {
          $supportive_people[] = $row['supportive_people'];
       }
       if (sizeof($supportive_people)) {
          $data['supportive_people'] = implode(', ', $supportive_people);
       }
    }

    return $data;
}

function sji_stride_intake_report_string($pid) {
    $data = sji_stride_intake_get_data($pid);
    $return = '';
    if ($data) {
        $return .= "<table>\n";
        foreach ($data as $key => $value) {
            if ($key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "Phone" ||
                $key == "Address" ||
                $key == "date" ||
                $value == "" ||
                preg_match('/^0000/', $value) )
            {
                continue;
            }
    
            if ($value == "on") {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            $return .= "<tr>\n";

            if ($key == "Why Are You Here") {
               $key = xlt('Why are you here');
            } else if ($key == "Hormone Duration"){
               $key = xlt('Duration hormones have been taken');
            } else if ($key == "Hormone Program"){
               $key = xlt('Programs that have provided hormones');
            }

            $return .= "<td><span class=bold>" . 
               xlt($key) . ": </span><span class=text>" . 
               text($value) . "</span></td>\n";

	    $return .= "</tr>\n";
        }
    }

    $return .= "</table>\n";
    return $return;
}

function sji_stride_intake_report($pid, $encounter, $cols, $id = 0)
{

    $data = sji_stride_intake_get_data($pid, $id);

    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if ($key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "Phone" ||
                $key == "Address" ||
                $key == "date" ||
                $value == "" ||
                preg_match('/^0000/', $value) )
            {
                continue;
            }
    
            if ($value == "on") {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            print("<tr>\n");
            print("<tr>\n");

            if ($key == "Why Are You Here") {
               $key = xlt('Why are you here');
            } else if ($key == "Hormone Duration"){
               $key = xlt('Duration hormones have been taken');
            } else if ($key == "Hormone Program"){
               $key = xlt('Programs that have provided hormones');
            }

            print "<td><span class=bold>" . 
               xlt($key) . ": </span><span class=text>" . 
               text($value) . "</span></td>";

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
    }

    print "</tr></table>";
}
