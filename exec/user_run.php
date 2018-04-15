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
define("IN_DA_PLUGIN",true);
require_once("/usr/local/directadmin/plugins/da_skin_selector/exec/functions.inc.php");

$USER=(isset($argv[1]) && $argv[1]) ? trim($argv[1]) : false;
$save_collection=(isset($argv[2]) && $argv[2]) ? trim($argv[2]) : false;
$save_skin=(isset($argv[3]) && $argv[3]) ? trim($argv[3]) : false;

write_access_log("Executed for user=".$USER." collection=".$save_collection." and skin=".$save_skin."\n");

if (!$USER || !$save_collection || !$save_skin) {
    $_error = "Script was executed with missing arguments! Aborting...\n";
    write_error_log($_error);
    echo $_error;
    exit(1);
}

// =====================================================================
// Here we emulate some $_SERVER variables available in 
// Directadmin Web-GUI and needed for our class to work properly.
// =====================================================================
$_SERVER=array();
if (is_file("/usr/local/directadmin/data/users/".$USER."/user.conf")){
    $_SERVER['USER']=$USER;
} else {
    $_error = "Specified user [".$USER."] does not exist! Aborting...\n";
    write_error_log($_error);
    echo $_error;
    exit(1);
}
$_SERVER['RUNNING_AS']=@getenv("RUNNING_AS");
$_SERVER['LANGUAGE']="en";
// =====================================================================

write_debug_log("[DEBUG] getenv(USER) ".getenv("RUNNING_AS")." \n");

# Wait directadmin to finish its processing
sleep(1);

$px_Skin_Selector = new px_Skin_Selector(USER_LEVEL);

if ($px_Skin_Selector->get_last_error()) {
    $_error = "[ERROR] ".$px_Skin_Selector->get_last_error()."\n";
    write_error_log($_error);
    echo $_error;
    exit(1);
} else {
    write_debug_log("[DEBUG] Passed 1 \n");
}

$user_current_skin = $px_Skin_Selector->get_user_data('skin');
$user_current_docsroot = $px_Skin_Selector->get_user_data('docsroot');
$user_creator = $px_Skin_Selector->get_user_data('creator');
$list_skins = $px_Skin_Selector->get_skins();
$user_allowed_collections = array("server", "reseller", $user_creator);

write_debug_log("[DEBUG] Passed 2 \n");

if (!in_array($save_collection, $user_allowed_collections))
{
    $_error = "[ERROR] Failed to change skin for user ".$USER." to ".$save_skin." from ".$save_collection."'s collection. Wrong collection!\n";
    write_error_log($_error);
    echo $_error;
    exit(1);
}
else
{
    if ($save_collection == "reseller") {$save_collection = $user_creator;}
}

write_debug_log("[DEBUG] Passed 3 \n");

if (in_array($save_skin, $list_skins['server']) || in_array($save_skin, $list_skins['reseller']))
{
    write_debug_log("[DEBUG] Passed 4 \n");

    if ($px_Skin_Selector->save_user_new_skin($save_skin, $save_collection)) {
        $_res = "[OK] User ".$USER."'s skin was changed to ".$save_skin." from ".$save_collection."'s collection!\n";
        write_debug_log($_res);
        echo $_res;
        exit(0);
    } else {
        $_error = "[ERROR] Failed to change skin for user ".$USER." to ".$save_skin." from ".$save_collection."'s collection!\n";
        write_error_log($_error);
        echo $_error;
        $_error = "[ERROR] ".$px_Skin_Selector->get_last_error()."\n";
        write_error_log($_error);
        echo $_error;
        exit(1);
    }
}

write_debug_log("[DEBUG] Passed 5 \n");

exit(1);
