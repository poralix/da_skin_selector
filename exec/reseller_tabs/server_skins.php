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

if(!defined("IN_DA_PLUGIN_RESELLER")){die("You're not allowed to run this programm!");}

$list_skins=$px_Skin_Selector->list_server_skins(true);
$HTML_title=$px_Skin_Selector->get_lang('SKIN_PREVIEWS_MANAGER');
$HTML_skin_manager="<div><a href=\"/CMD_SKINS\" target=\"_top\">".$px_Skin_Selector->get_lang('SKIN_MANAGER')."</a></div>";

$HTML_result = "";
$s = 0;

foreach ($list_skins as $skin){
    $s++;
    if ($hidden_status=$px_Skin_Selector->is_hidden_skin($skin, "server")) {
        $is_hidden=$hidden_status['hidden'];
        $hidden_by=$hidden_status['by'];
    } else {
        $is_hidden=false;
        $hidden_by=false;
    }
    if ($is_hidden && ($hidden_by == "admin")) {
        continue;
    }
    if ($is_hidden && ($hidden_by == "reseller")) {
        // SET HIDDEN BY RESELLER
        $checked=" ";
        $is_hidden_val="1";
        $is_hidden_class="px__off";
    } else {
        // SET PUBLIC BY RESELLER
        $checked=" checked='checked' ";
        $is_hidden_val="0";
        $is_hidden_class="px__on";
    }
    $description = htmlspecialchars(strip_tags($px_Skin_Selector->get_skin_description($skin, "server")));
    $HTML_result.="<form method='POST' action='/CMD_PLUGINS_RESELLER/da_skin_selector/index.raw' enctype='multipart/form-data' id='px__form_".$s."'>";
    $HTML_result.="<input type='hidden' name='skin' value='".$skin."' />";
    $HTML_result.="<input type='hidden' name='type' value='ajax' />";
    $HTML_result.="<input type='hidden' name='do' value='skins' />";
    $HTML_result.="<input type='hidden' name='collection' value='server' />";
    $HTML_result.="<input type='hidden' name='hidden' value='".$is_hidden_val."' id='hidden_".$s."' />";
    $HTML_result.="<li class='ui-corner-all'>";
    $HTML_result.="<div class='px__preview' style='background:URL(/CMD_PLUGINS/da_skin_selector/index.raw?type=image&collection=server&skin=".$skin.") no-repeat 0px 50% / ". PREVIEW_WIDTH ."px ". PREVIEW_HEIGHT ."px transparent; width:". PREVIEW_WIDTH ."px; min-height:". PREVIEW_HEIGHT ."px'>";
    $HTML_result.="</div>";
    $HTML_result.="<div class='px__form2'>";
    $HTML_result.="<div><b>".$px_Skin_Selector->get_lang('SKIN_NAME')."</b>: ".$skin."<div style='float:right;position:relative;top:-10px;' id='status_".$s."' class='".$is_hidden_class."' title='".$px_Skin_Selector->get_lang('SKIN_IS_HIDDEN_DESC')."'></div></div>";
    $HTML_result.="<div><b>".$px_Skin_Selector->get_lang('SKIN_DESCRIPTION')."</b>:</div>";
    $HTML_result.="<div class='px__form_description_off'>".htmlspecialchars($description)."</div>";
    $HTML_result.="<div><small>".$px_Skin_Selector->get_lang('CAN_BE_CHANGED_ONLY_BY_ADMIN')."</small></div>";
    $HTML_result.="</div><br clear='all'><br></li>";
    $HTML_result.="</form>";
}

$content=<<<EOF
<h1>$HTML_title</h1>
<br>
<div id="px__skins_list_container">
<ul id="px__skins_list" class="px__skin_manager_res">
$HTML_result
</ul>
<br>
$HTML_skin_manager
</div>
EOF;


