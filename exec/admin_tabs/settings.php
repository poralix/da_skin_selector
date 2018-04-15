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

$_DEFAULT_CONFS = $px_Skin_Selector->get_confs();
$_CUSTOM_CONFS = $px_Skin_Selector->get_custom_confs();
$_CONFS = array_merge($_DEFAULT_CONFS, $_CUSTOM_CONFS);
$skip_confs = array('PHP_ERROR_REPORTING');

$HTML_result = "";
$HTML_title = $px_Skin_Selector->get_lang('SKIN_SELECTOR_SETTINGS');

foreach ($_CONFS as $key => $val) {
    if (in_array($key, $skip_confs)){continue;}
    $HTML_result.= "<div class='px__row ui-corner-all' id='".strtolower($key)."'><div class='px__option'>".$px_Skin_Selector->get_lang(strtoupper($key))."</div>&nbsp;<div class='".(($val)? "px__on" : "px__off" )."' id='val__".strtolower($key)."'></div></div><br clear='all'>";
}

$content=<<<EOF
<h1>$HTML_title</h1>
<br>
<div id="px__settings">$HTML_result</div>
EOF;

unset($HTML_result);
unset($_DEFAULT_CONFS);
unset($_CUSTOM_CONFS);
unset($_NEW_CONFS);
