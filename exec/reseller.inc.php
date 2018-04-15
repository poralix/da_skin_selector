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

error_reporting(0);
if(!defined("IN_DA_PLUGIN_RESELLER")){die("You're not allowed to run this programm!");}
require_once("/usr/local/directadmin/plugins/da_skin_selector/exec/functions.inc.php");

$px_Skin_Selector = new px_Skin_Selector(RESELLER_LEVEL);

$reseller_tabs = array();
if ($px_Skin_Selector->get_conf('ALLOW_SERVER_WIDE_SKINS')) {
    $reseller_tabs[] = "server_skins";
}
if ($px_Skin_Selector->get_conf('ALLOW_RESELLER_SKINS')) {
    $reseller_tabs[] = "reseller_skins";
}
$reseller_tabs[] = "about";
$reseller_tabs_content = array();

    $_selected_tab=0;
    do_output("<div id='px__skin_selector'><h1>".$px_Skin_Selector->get_lang('RESELLER_CONFIGURE_PLUGIN').":</h1>");
    do_output("<div id='px__tabs' style='display:none;'>\n<ul>");
    foreach ($reseller_tabs as $tab)
    {
        $reseller_tabs_content[$tab] = "";
        do_output("<li><a href='#".$tab."'>".$px_Skin_Selector->get_lang('RESELLER_TAB_'.strtoupper($tab))."</a></li>");
    }
    do_output("</ul>");
    foreach ($reseller_tabs_content as $tab => $content)
    {
        if (is_file(PLUGIN_RESELLER_TABS_DIR."/".$tab.".php")) {
            require_once(PLUGIN_RESELLER_TABS_DIR."/".$tab.".php");
        }
        do_output("<div id='".$tab."' class='skin_tab_reseller'>".$content."</div>");
    }
    do_output("<p><br clear='all'></p></div>");
    do_output("</div>");
    do_output(PX_CT);
    do_output(_js_css($_selected_tab));
