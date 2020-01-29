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

// TODO: should we ad the join tables to this?
function sji_off_hour_contact_report($pid, $encounter, $cols, $id)
{
    $form_name = "sji_off_hour_contact";
    $count = 0;
    $data = get_oh_form_obj($pid, $id);
    if ($data) {
        $others = array('sex', 'hipaa_voice', 'hipaa_allowsms', 'hipaa_allowemail');
        foreach ($others as $column) {
           if ($column == 'sex' && isset($data[$column])) {
              $data['Gender'] = $data[$column];
           } else if ($column == 'hipaa_voice' && isset($data[$column])) {
              $data['OK to leave a voice message'] = $data[$column];
           } else if ($column == 'hipaa_allowsms' && isset($data[$column])) {
              $data['OK to send text message'] = $data[$column];
           } else if ($column == 'hipaa_allowemail' && isset($data[$column])) {
              $data['OK to send email message'] = $data[$column];
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
