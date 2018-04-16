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

$usertype=$px_Skin_Selector->get_user_data('usertype');
$username=$px_Skin_Selector->get_username();


//
// Resellers, admins are allowed to run this script
//
if (($usertype !== "reseller") and ($usertype !== "admin")) {
    print("HTTP/1.1 200 OK\n");
    print("Content-Type: application/javascript\n\n");
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


if (($type=="ajax") && ($do == "skins"))
{
    $skin=$px_Skin_Selector->get_var_post("skin", false);
    $collection=$px_Skin_Selector->get_var_post("collection", false);
    $hidden=$px_Skin_Selector->get_var_post("hidden", false);

    $_res=false;

    // SERVER WIDE COLLECTION
    if ($skin && ($collection == "server"))
    {
        if (!$hidden || ($hidden == "false")) {
            $_res=$px_Skin_Selector->mark_as_public($skin);
        } else {
            $_res=$px_Skin_Selector->mark_as_hidden($skin);
        }
    } 
    // RESELLER'S PRIVATE COLLECTION
    else if ($skin && ($collection != "server")) 
    {
        if (!$hidden || ($hidden == "false")) {
            $_res=$px_Skin_Selector->mark_as_public($skin, $username);
        } else {
            $_res=$px_Skin_Selector->mark_as_hidden($skin, $username);
        }
        $description=$px_Skin_Selector->get_var_post("description", false);
        if ($description !== false) {
            $description=str_replace("\n","",str_replace("\r\n","\n",strip_tags($description)));
            $_res=$px_Skin_Selector->save_description($description, $skin, $collection);
        }
    }
    print("HTTP/1.1 200 OK\n");
    print("Content-Type: application/javascript\n\n");
    if ($_res) {
        print json_encode(array('result'=>true));
    } else {
        print json_encode(array('result'=>false,'error'=>$px_Skin_Selector->get_last_error()));
    }
    print "\n";

}

//
// File uploading
//
else if (($type == "ajax") && ($do == "upload")) {

    $skin=$px_Skin_Selector->get_var_get("skin", false);
    $collection=$px_Skin_Selector->get_var_get("collection", false);
    $file=$px_Skin_Selector->get_var_post(0,false);

    print("HTTP/1.1 200 OK\n");
    print("Content-Type: application/javascript\n\n");

    if (!$skin || !$collection || !$file) {
        print json_encode(array('result'=>false,'error'=>"No data sent"));
        exit;
    }

    // SAVE FILE AS
    if ($collection == "server") {
        print json_encode(array('result'=>false,'error'=>"Only private collection is allowed!"));
        exit;
    } else if ($collection != $username) {
        print json_encode(array('result'=>false,'error'=>"Wrong collection!"));
        exit;
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

    print("HTTP/1.1 200 OK\n");
    print("Content-Type: application/javascript\n\n");

    if ($px_Skin_Selector->get_last_error()) {
        print json_encode(array('result'=>false,'error'=>$px_Skin_Selector->get_last_error()));
    } else {
        print json_encode(array('result'=>true));
    }
    print "\n";
    exit;
}




exit;
