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

ini_set('display_errors',0);
error_reporting(0);
if(!defined("IN_DA_PLUGIN_ADMIN")){die("You're not allowed to run this programm!");}
require_once("/usr/local/directadmin/plugins/da_skin_selector/exec/functions.inc.php");

$px_Skin_Selector = new px_Skin_Selector(ADMIN_LEVEL);

$usertype=$px_Skin_Selector->get_user_data('usertype');

//
// Admin is the only user who is allowed to run this script
//
if ($usertype !== "admin") {
    print("HTTP/1.1 200 OK\n");
    print("Cache-Control: no-cache, must-revalidate\n");
    print("Content-type: application/json\n\n");
    print json_encode(array('result'=>false,'error'=>'not admin (Usertype='.$usertype.')'));
    exit;
}

if (!$type=$px_Skin_Selector->get_var_post("type", false)){
    $type=$px_Skin_Selector->get_var_get("type", false);
}
if (!$file=$px_Skin_Selector->get_var_post("file", false)){
    $file=$px_Skin_Selector->get_var_get("file", false);
}
if (!$do=$px_Skin_Selector->get_var_post("do", false)){
    $do=$px_Skin_Selector->get_var_get("do", false);
}

//
// Showing plugins logs
//
if ($type == "logs") {
    $file=basename($file);
    $ctype="plain/text";
    $filename=PLUGIN_LOGS_DIR."/".$file;
    print("HTTP/1.1 200 OK\n");
    print("Content-Type: ".$ctype."\n\n");
    if (is_file($filename)) {
        $filesize=filesize($filename);
        print("Content-Length: ". $filesize);
        print("Last-Modified: ".gmdate('D, d M Y H:i:s', filemtime($filename))." GMT\n");
        print("Cache-Control: public, max-age=2592000\n");
        readfile($filename);
    }
    echo "\n";
    exit;
} 

//
// Saving plugins settings
//
else if (($type == "ajax") && ($do == "settings")) {
    $option=$px_Skin_Selector->get_var_get("option", false);
    $value=$px_Skin_Selector->get_var_get("value", false);

    switch ($value):
        case "off":
            $value=0;
            break;
        case "on":
        default:
            $value=1;
            break;
    endswitch;

    $_res = $px_Skin_Selector->save_custom_confs(strtoupper($option), $value);

    print("HTTP/1.1 200 OK\n");
    print("Cache-Control: no-cache, must-revalidate\n");
    print("Content-type: application/json\n\n");

    if ($_res) {
        print json_encode(array('result'=>true));
    } else {
        print json_encode(array('result'=>false));
    }
    print "\n";
    exit;
} 

//
// File uploading
//
else if (($type == "ajax") && ($do == "upload")) {

    $skin=$px_Skin_Selector->get_var_get("skin", false);
    $collection=$px_Skin_Selector->get_var_get("collection", false);
    $file=$px_Skin_Selector->get_var_post(0,false);

    print("HTTP/1.1 200 OK\n");
    print("Cache-Control: no-cache, must-revalidate\n");
    print("Content-type: application/json\n\n");

    if (!$skin || !$collection || !$file) {
        print json_encode(array('result'=>false,'error'=>"No data sent"));
        exit;
    }

    // SAVE FILE AS
    if ($collection == "server") {
        // BASENAME WITHOUT EXTENSION
        $filename=basename($skin);
    } else {
        // BASENAME WITHOUT EXTENSION
        $filename=basename($collection."__".$skin);
    }
    $fullpath_filename=PLUGIN_IMAGES_DIR."/".$filename.".jpg";


    // DO WE HAVE AN OLD FILE THERE?
    if (is_file($fullpath_filename)) {
        rename($fullpath_filename, $fullpath_filename."~old");
    }


    // DOES UPLOADED FILE EXIST
    if (is_file($file)) {
        // =======================================================================
        // UPLOADED FILE IS OWNED BY 0:0 
        // AND HAS PERMISSIONS 0600
        // WE NEED TO BE root TO COPY/CHOWN/CHMOD THE FILE
        // SO WE COULD OPERATE WITH IT
        // WE USE HERE SUID PROGRAMM TO MOVE UPLOADED FILE
        // FROM /home/tmp to PLUGIN_IMAGES_DIR
        // PROGRAMM SHOULD BE USED WITH BASE NAMES, NO DIRECTORIES ARE ALLOWED
        // =======================================================================
        $cmd=PLUGIN_EXEC_DIR."/move-uploaded-file ".basename($file)." ".$filename;
        $res=@exec($cmd, $output, $return);

        if (is_file($fullpath_filename)) {
            $mime_type=@mime_content_type($fullpath_filename);

            if ($mime_type != "image/jpeg") {
                print json_encode(array('result'=>false,'error'=>"Not image/jpeg"));
                exit;
            } else {
                list($width, $height) = getimagesize($fullpath_filename);
                $src=ImageCreateFromJPEG($fullpath_filename);
                $new_width=intval(PREVIEW_WIDTH);
                $new_height=intval(PREVIEW_HEIGHT);

                if ( ($width > $new_width) || ($height > $new_height)) 
                {
                    if ($width > $height) {
                        $koe=$height/$new_height;
                        $new_width=ceil($width/$koe);
                    } else {
                        $koe=$width/$new_width;
                        $new_height=ceil($height/$koe);
                    }
                    $dst=ImageCreateTrueColor($new_width, $new_height);
                }
                ImageCopyResampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                ImageJPEG($dst, $fullpath_filename, 100);
                imagedestroy($src);
            }
        }

    } else {
        print json_encode(array('result'=>false,'error'=>"File was not uploaded, or it is not image/jpeg"));
        exit;
    }

    if (!is_file($fullpath_filename) && is_file($fullpath_filename."~old")) {
        rename($fullpath_filename."~old", $fullpath_filename);
    }

    if ($px_Skin_Selector->get_last_error()) {
        print json_encode(array('result'=>false,'error'=>$px_Skin_Selector->get_last_error()));
    } else {
        print json_encode(array('result'=>true));
    }
    print "\n";
    exit;
}

//
// Saving Skin data
//
else if (($type == "ajax") && ($do == "skins")) {

    $skin=$px_Skin_Selector->get_var_post("skin", false);
    $collection=$px_Skin_Selector->get_var_post("collection", false);
    $description=$px_Skin_Selector->get_var_post("description", false);
    $enabled=$px_Skin_Selector->get_var_post("enabled", false);

    $_res=false;

    if ($skin && $collection)
    {
        if (!$enabled || ($enabled == "false")) {
            $_res=$px_Skin_Selector->mark_as_hidden($skin, $collection);
        } else {
            $_res=$px_Skin_Selector->mark_as_public($skin, $collection);
        }
        if ($description !== false) {
            $description=str_replace("\n","",str_replace("\r\n","\n",strip_tags($description)));
            $_res=$px_Skin_Selector->save_description($description, $skin, $collection);
        }
    }
    print("HTTP/1.1 200 OK\n");
    print("Cache-Control: no-cache, must-revalidate\n");
    print("Content-type: application/json\n\n");
    if ($_res) {
        print json_encode(array('result'=>true));
    } else {
        print json_encode(array('result'=>false,'error'=>$px_Skin_Selector->get_last_error()));
    }
    print "\n";
}
