<?php
/**
 *   Work/School Note Form created by Nikolai Vitsyn: 2004/02/13 and update 2005/03/30
 *   Copyright (C) Open Source Medical Software
 *
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
include_once("common.php");

function sji_intake_core_variables_fetch($pid, $id = 0) {
   return get_cv_form_obj($pid, $id);
}

function sji_intake_core_variables_build_data($pid, $id = 0) {
    $form_name = "sji_intake_core_variables";
    $count = 0;
    $data = sji_intake_core_variables_fetch($pid, $id);
    if ($data) {
        $others = array('dob', 'sex', 'postal_code', 
           'partners_gender', 'ethnicity', 'race', 
           'emergency_relationsh', 'contact_relationship',
           'phone_contact');
        foreach ($others as $column) {
           if ($column == 'dob' && isset($data[$column])) {
              $data['Date of birth'] = $data[$column];
           } else if ($column == 'sex' && isset($data[$column])) {
              $query = "select title from list_options where list_id='sex' and option_id = ?";
              $res = sqlStatement($query, array($data[$column]));
              $gender = sqlFetchArray($res);
 
              if (isset($gender['title'])) {
                 $data['Gender'] = $gender['title'];
              } else {
                 $data['Gender'] = $data[$column];
              }
           } else if ($column == 'postal_code' && isset($data[$column])) {
              $data['Zip'] = $data[$column];
           } else if ($column == 'partners_gender' && isset($data[$column])) {
              $data['Partners gender'] = join(', ', $data[$column]);
           } else if ($column == 'ethnicity' && isset($data[$column])) {
              $query = "select title from list_options where list_id='ethnicity' and option_id = ?";
              $res = sqlStatement($query, array($data[$column]));
              $ethnicity = sqlFetchArray($res);

              if (isset($ethnicity['title'])) {
                 $data['Ethnicity'] = $ethnicity['title'];
              }
           } else if ($column == 'race' && isset($data[$column])) {
              $query = "select title from list_options where list_id='race' and option_id = ?";
              $res = sqlStatement($query, array($data[$column]));
              $race = sqlFetchArray($res);

              if (isset($race['title'])) {
                 $data['Race'] = $race['title'];
              }
           } else if (($column == 'emergency_relationsh') && isset($data['emergency_relationsh'])){
              $data['relationship with emergency contact'] =
                 $data['emergency_relationsh'];
           } else if (($column == 'contact_relationship') && isset($data['contact_relationship'])){
              $data['emergency contact'] =
                 $data['contact_relationship'];
           } else if (($column == 'phone_contact') && isset($data['phone_contact'])){
              $data['emergency contact number'] =
                 $data['phone_contact'];
           }
           unset($data[$column]);
	}
	return $data;
    } // if
}

function sji_intake_core_variables_report_string($pid) {
	$data = sji_intake_core_variables_build_data($pid);
	$return = '';
        $return .= "<table>";
        foreach ($data as $key => $value) {
            if ($key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "date" ||
                $value == "" ||
                preg_match('/^0000/', $value) )
            {
                continue;
            }
    
            if ($value == "on" ) {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            $return .= "<tr>\n";
	    $return .= "<td><span class=bold>". 
		    xlt($key). 
		    ": </span><span class=text>". 
		    text($value). 
		    "</span></td>\n";
            $return .= "</tr>\n";
        } // foreach

        // get a few other values
	$return .= "</table>";

	return $return;
}


// TODO: should we ad the join tables to this?
function sji_intake_core_variables_report($pid, $encounter, $cols, $id) {

	$data = sji_intake_core_variables_build_data($pid, $id);
        print "<table>";
        foreach ($data as $key => $value) {
            if ($key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "date" ||
                $value == "" ||
                preg_match('/^0000/', $value) )
            {
                continue;
            }
    
            if ($value == "on" ) {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            print "<tr>\n";
            print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>\n";
            print "</tr>\n";
        } // foreach

        print "</table>";
}
