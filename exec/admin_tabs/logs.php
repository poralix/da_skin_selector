<?php
##############################################################################
#
#                 SKIN SELECTOR PLUGIN FOR DIRECTADMIN $ v.0.10
#
#    Copyright (C) 2014-2016  Alex S Grebenschikov
#            web-site:  www.plugins-da.net
#            emails to: support@plugins-da.net
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#############################################################################

if(!defined("IN_DA_PLUGIN_ADMIN")){die("You're not allowed to run this programm!");}

$HTML_title1=$px_Skin_Selector->get_lang('SELECT_LOG_TO_VIEW');
$HTML_title2=$px_Skin_Selector->get_lang('LOADED_LOG_TO_VIEW');
$HTML_result="<ul id='px__logs'>";
$allowed_logs=array("access_log","debug_log","error_log");

if ($handle = opendir(PLUGIN_LOGS_DIR)) {
    while (false !== ($entry = readdir($handle))) {
        if (in_array($entry,array('.','..'))){continue;}
        if (!is_file(PLUGIN_LOGS_DIR.'/'.$entry)){continue;}
        if (in_array($entry,$allowed_logs)){
            $l_size=filesize(PLUGIN_LOGS_DIR.'/'.$entry);
            if ($l_size > 1000000) {
                $l_size = round($l_size / 1024 / 1024, 2) ." Mb";
            } else if ($l_size > 1000) {
                $l_size = round($l_size / 1024, 2) ." Kb";
            } else {
                $l_size = $l_size ." B";
            }
            $HTML_result.="<li class='ui-corner-all'><a href='/CMD_PLUGINS_ADMIN/da_skin_selector/index.raw?type=logs&file=".$entry."' class='px__logs_view'>".$px_Skin_Selector->get_lang(strtoupper($entry))." - ".PLUGIN_LOGS_DIR."/".$entry." - ".$l_size."</a></li>";
        }
    }
    closedir($handle);
}
$HTML_result.="</ul>";

$content=<<<EOF
<h1>$HTML_title1</h1>
<br>
<div id="px__select_log">$HTML_result</div>
<div id="px__log_viewer_container" style="display:none;">
    <div id="px__selected_log_title"><h1>$HTML_title2 <span id="px__selected_log"></span></h1></div>
    <textarea id="px__log_viewer" style="width:100%; height: 500px;" wrap="off" readonly="readonly"></textarea>
</div>
EOF;

unset($HTML_result);
unset($allowed_logs);
