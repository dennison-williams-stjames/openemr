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
require_once("common.php");

function sji_counseling_report($pid, $encounter, $cols, $id = 0) {

    $form_name = "sji_counseling";
    $count = 0;

    if (!$id) {
       // If we did not recieve a form id then look it up
       $query = "select id from form_sji_counseling where pid=? order by id desc limit 1";
       $res = sqlStatement($query, array($pid));
       $row = sqlFetchArray($res);
       $id = $row['id'];
    }

    // TODO: should we add the associated join tables here too?
    $data = array_merge(
	formFetch("form_".$form_name, $id),
	sji_counseling_formFetch($id));

    if ($data) {
        print "<table><tr>";
        foreach ($data as $key => $value) {
            if ($key == "id" ||
                $key == "pid" ||
                $key == "user" ||
                $key == "groupname" ||
                $key == "authorized" ||
                $key == "activity" ||
                $key == "date" ||
                $value == "" ||
                (is_string($value) && preg_match('/^0000/', $value) ) )
            {
                continue;
            }
   
            if ($value == "on") {
                $value = "Yes";
            } else if (empty($value)) {
               // TODO: this may not be right
               $value = "No";
            }
    
            $key=ucwords(str_replace("_", " ", $key));
            print("<tr>\n");
            print("<tr>\n");
            print "<td><span class=bold>". xlt($key) .": </span><span class=text>";
            if (is_array($value)) {
                print join(', ', $value);
            } else {
                print text($value);
            } 
            print "</span></td>";

            $count++;
            if ($count == $cols) {
                $count = 0;
                print "</tr><tr>\n";
            }
        }
        print "</tr></table>";
    }

}
