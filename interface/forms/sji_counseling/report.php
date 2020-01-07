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
function sji_counseling_report($pid, $encounter, $cols, $id)
{
    $count = 0;
    $table = '';
    $data = formFetch("form_sji_counseling", $id);
    if ($data) {
        $table .= "<table>";
        $table .= "<tr><td><span class=bold>Type: </span><span class=text>". xlt($data['counseling_type'])."</span></td></tr>";
        $table .= "<tr><td><span class=bold>Duration: </span><span class=text>". xlt($data['counseling_time'])."</span></td></tr>";
        $table .= "<tr><td><span class=bold>Notes: </span><span class=text>". xlt($data['progress_notes'])."</span></td></tr>";
        $table .= "</table>\n";
    }
    print $table;
}
