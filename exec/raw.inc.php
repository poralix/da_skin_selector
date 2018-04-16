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

$type=$px_Skin_Selector->get_var_get("type", false);
$collection=$px_Skin_Selector->get_var_get("collection", false);
$skin=$px_Skin_Selector->get_var_get("skin", false);

if ($type == "image") {
    $ctype="image/jpeg";
    $skin=basename($skin);
    $filename=PLUGIN_IMAGES_DIR."/".$collection."__".$skin.".jpg";
    if (!is_file($filename)) {
        $filename=PLUGIN_IMAGES_DIR."/".$skin.".jpg";
        if (!is_file($filename)) {
            $ctype="image/png";
            $filename=PLUGIN_IMAGES_DIR."/no-image-en.png";
        }
    }
    $filesize=filesize($filename);
    print("HTTP/1.1 200 OK\n");
    print("Content-Type: ".$ctype."\n");
    print("Content-Length: ". $filesize. "\n");
    print("Last-Modified: ".gmdate('D, d M Y H:i:s', filemtime($filename))." GMT\n");
    print("Cache-Control: public, max-age=2592000\n\n");
    readfile($filename);
}
