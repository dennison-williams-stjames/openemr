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

function sji_intake_core_variables_fetch($pid, $id = 0) {
   $form_name = "sji_intake_core_variables";

   if (!isset($id) || !$id) {
      $query = 'select form_id from forms where pid=? and form_name=? order by date desc limit 1';
      $data = sqlQuery($query, array($pid, $form_name));
      $id = $data['form_id'];
   }

   $data = formFetch("form_".$form_name, $id);

   // get partners gender
   $query = 'select `partners_gender` from form_sji_intake_core_variables_partners_gender where pid=?';
   $result = sqlStatement($query, array($id));
   $genders = '';
   while ($row = sqlFetchArray($result)) {
      if (strlen($genders)) {
         $genders .= ', ';
      }
      $genders .= $row['partners_gender'];
   }
   $data['partners_gender'] = $genders;

   // get a few other values
   $query = 'select `DOB`,`sex`,`postal_code` from patient_data where pid=?';
   $result = sqlQuery($query, array($pid));
   foreach ($result as $key => $value) {
      $key=ucwords(str_replace("_", " ", $key));
      $data[$key] = $value;
   }
   //unset $data['Sex'];

   return $data;
}

// TODO: should we ad the join tables to this?
function sji_intake_core_variables_report($pid, $encounter, $cols, $id)
{
    $form_name = "sji_intake_core_variables";
    $count = 0;
    $data = sji_intake_core_variables_fetch($pid, $id);
    if ($data) {
        $others = array('DOB', 'Sex', 'Postal Code');
        foreach ($others as $column) {
           if ($column == 'DOB' && isset($data[$column])) {
              $data['Date of birth'] = $data[$column];
           } else if ($column == 'Sex' && isset($data[$column])) {
              $data['Sex assigned at birth'] = $data[$column];
           } else if ($column == 'postal_code' && isset($data[$column])) {
              $data['Zip'] = $data[$column];
           } 
           unset($data[$column]);
        }

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
    
            if ($value == "on" || $value == 1) {
                $value = "yes";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            print "<tr>\n";
            print "<td><span class=bold>" . xlt($key) . ": </span><span class=text>" . text($value) . "</span></td>\n";
            print "</tr>\n";
        }

        // get a few other values
       
        print "</table>";
    }
}
