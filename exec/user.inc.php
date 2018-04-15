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
if(!defined("IN_DA_PLUGIN")){die("You're not allowed to run this programm!");}
require_once("/usr/local/directadmin/plugins/da_skin_selector/exec/functions.inc.php");

$px_Skin_Selector = new px_Skin_Selector(USER_LEVEL);
$username = $px_Skin_Selector->get_username();

$is_allowed_use_plugin = strtolower($px_Skin_Selector->get_user_data('da_skin_selector'));

if (($is_allowed_use_plugin == "no") || ($is_allowed_use_plugin == "off")) {
    do_output(error_message($px_Skin_Selector->get_lang('ACCESS_DENIED'), $px_Skin_Selector->get_lang('ACCESS_DENIED_DETAILS')));
    write_access_log("User ".$username." is not allowed to run this plugin!\n");
    exit;
}

$user_current_skin = $px_Skin_Selector->get_user_data('skin');
$user_current_docsroot = $px_Skin_Selector->get_user_data('docsroot');
$user_creator = $px_Skin_Selector->get_user_data('creator');
$user_current_usertype = $px_Skin_Selector->get_user_data('usertype');
$list_skins = $px_Skin_Selector->get_skins();

$save_skin=$px_Skin_Selector->get_var_get("skin", false);
$save_collection=$px_Skin_Selector->get_var_get("collection", false);

$_selected_tab=0;

write_access_log("Executed for user=".$username." collection=".$save_collection." and skin=".$save_skin."\n");

if ($save_skin && $save_collection && (in_array($save_skin,$list_skins['server']) || in_array($save_skin,$list_skins['reseller']))){
    $cmd=PLUGIN_EXEC_DIR."/php-forker ".$px_Skin_Selector->get_username()." ".$save_collection." ".$save_skin;
    $res=@exec($cmd, $output, $return);
    do_output("<br><br><center>".$px_Skin_Selector->get_lang('SKIN_WAS_CHANGED_TO')." $save_skin!</b>");
    do_output("<br><a href='/CMD_SHOW_DOMAIN?domain=".$_SERVER['SESSION_SELECTED_DOMAIN']."'>".$px_Skin_Selector->get_lang('WILL_BE_REDIRECTED_NOW')."</a><br><br><a href='?'>".$px_Skin_Selector->get_lang('CHOOSE_ANOTHER_SKIN')."</a>.</center><br><br>");
    do_output("<script type='text/javascript'>\n");
    do_output("<!--\n");
    do_output("function _px_redirect() { location.href=\"/CMD_SHOW_DOMAIN?domain=".$_SERVER['SESSION_SELECTED_DOMAIN']."\"; }\n");
    do_output("setTimeout('_px_redirect();', 3*1000);\n");
    do_output("//-->\n");
    do_output("</script>\n");
}
else
{
    $HTML_server_delayed='';
    $HTML_reseller_delayed='';

    // 
    // SERVER WIDE SKINS (THEMES)
    // ========================================
    $shown_skins=0;
    do_output("<form action='?' method='GET'>");
    do_output("<div id='px__skin_selector'><h1>".$px_Skin_Selector->get_lang('PLEASE_SELECT_SKIN').":</h1>");
    do_output("<div id='px__tabs' style='display:none;'><ul>");
    if (isset($list_skins['server'])) {
        do_output("<li><a href='#server'>".$px_Skin_Selector->get_lang('SERVER_WIDE_SKINS')."</a></li>");
        if ($list_skins['server']){
            foreach ($list_skins['server'] as $skin){
                if ($px_Skin_Selector->is_hidden_skin($skin, "server")) {continue;}
                $description = htmlspecialchars(strip_tags($px_Skin_Selector->get_skin_description($skin, "server")));
                if (($user_current_docsroot == "./data/skins/".$skin) && ($user_current_skin == $skin)) {
                    $_css="px_server_skin_selected";
                    $_selected_tab="0";
                } else {
                    $_css="px_server_skin";
                }
                $HTML_server_delayed.=do_output("<li><a href='?collection=server&skin=".$skin."' title='".@sprintf($px_Skin_Selector->get_lang('CLICK_TO_INSTALL_SKIN'),$skin)."' class='".$_css." ui-corner-all' style='background:URL(/CMD_PLUGINS/da_skin_selector/index.raw?type=image&collection=server&skin=".$skin.") no-repeat 50% 50%;'><div class='px__skin_desc' id='skin_desc_".$skin."'><p>".$description."</p></div><span>".$skin."</span></a>",false);
                $shown_skins++;
            }
        } 
    }
    if ($shown_skins==0) {
        $_selected_tab="1";
        $HTML_server_delayed.=do_output("<div style='text-align:center; line-height: 200px'>".$px_Skin_Selector->get_lang('NO_SKINS_INSTALLED')."</div>",false);
    }


    // 
    // RESELLER's (PRIVATE) SKINS (THEMES)
    // ========================================
    $shown_skins=0;
    if (isset($list_skins['reseller'])) {
        do_output("<li><a href='#reseller'>".$px_Skin_Selector->get_lang('RESELLER_SKINS')."</a></li>");
        if ($list_skins['reseller']){
            foreach ($list_skins['reseller'] as $skin){
                if ($user_current_usertype == "user") {
                    if ($px_Skin_Selector->is_hidden_skin($skin, $user_creator)) {continue;}
                    $description = htmlspecialchars(strip_tags($px_Skin_Selector->get_skin_description($skin, $user_creator)));
                    $collection=$user_creator;
                } else if (($user_current_usertype == "reseller") || ($user_current_usertype == "admin")) {
                    if ($px_Skin_Selector->is_hidden_skin($skin, $username)) {continue;}
                    $description = htmlspecialchars(strip_tags($px_Skin_Selector->get_skin_description($skin, $username)));
                    $collection=$username;
                }
                if (($user_current_docsroot != "./data/skins/".$skin) && ($user_current_skin == $skin)) {
                    $_css="px_reseller_skin_selected";
                    $_selected_tab="1";
                } else {
                    $_css="px_reseller_skin";

                }
                $HTML_reseller_delayed.=do_output("<li><a href='?collection=reseller&skin=".$skin."' title='".@sprintf($px_Skin_Selector->get_lang('CLICK_TO_INSTALL_SKIN'),$skin)."' class='".$_css." ui-corner-all' style='background:URL(/CMD_PLUGINS/da_skin_selector/index.raw?type=image&collection=".$collection."&skin=".$skin.") no-repeat 50% 50%;'><div class='px__skin_desc' id='skin_desc_".$skin."'><p>".$description."</p></div><span>".$skin."</span></a>",false);
                $shown_skins++;
            }
        }
    }
    if ($shown_skins==0) {
        $_selected_tab="0";
        $HTML_reseller_delayed.=do_output("<div style='text-align:center; line-height: 200px'>".$px_Skin_Selector->get_lang('NO_SKINS_INSTALLED')."</div>",false);
    }
    do_output("</ul>");
    do_output("<div id='server' class='skin_tab'><ul>".$HTML_server_delayed."</ul></div>");
    do_output("<div id='reseller' class='skin_tab'><ul>".$HTML_reseller_delayed."</ul></div>");
    do_output("<p><br clear='all'></p></div>");
    do_output("<p>".$px_Skin_Selector->get_lang('USER_CURRENT_SKIN')." is <b>".$user_current_skin."</b> ".(($_selected_tab=="0")? $px_Skin_Selector->get_lang('CURRENT_SKIN_SERVER') : $px_Skin_Selector->get_lang('CURRENT_SKIN_RESELLER'))."</p>");
    do_output("</div></form>");
    do_output(PX_CT);
    do_output(_js_css($_selected_tab));
}
