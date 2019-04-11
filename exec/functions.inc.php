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

define("PLUGIN_NAME","Skin Selector for Directadmin");
define("PLUGIN_VERSION","v.0.10.6");
define("PLUGIN_RESELLER_TABS_DIR","/usr/local/directadmin/plugins/da_skin_selector/exec/reseller_tabs");
define("PLUGIN_ADMIN_TABS_DIR","/usr/local/directadmin/plugins/da_skin_selector/exec/admin_tabs");
define("PLUGIN_DESC_DATA_DIR","/usr/local/directadmin/plugins/da_skin_selector/data/description");
define("PLUGIN_CUSTOM_DATA_DIR","/usr/local/directadmin/plugins/da_skin_selector/data/custom");
define("PLUGIN_HIDDEN_DATA_DIR","/usr/local/directadmin/plugins/da_skin_selector/data/hidden");
define("PLUGIN_IMAGES_DIR","/usr/local/directadmin/plugins/da_skin_selector/images");
define("PLUGIN_DATA_DIR","/usr/local/directadmin/plugins/da_skin_selector/data");
define("PLUGIN_EXEC_DIR","/usr/local/directadmin/plugins/da_skin_selector/exec");
define("PLUGIN_LOGS_DIR","/usr/local/directadmin/plugins/da_skin_selector/logs");
define("DA_SKINS_DIR","/usr/local/directadmin/data/skins");

define("ADMIN_LEVEL",0);
define("RESELLER_LEVEL",1);
define("USER_LEVEL",2);

define("PREVIEW_WIDTH",200);
define("PREVIEW_HEIGHT",200);

define("PX_CT","PX_CT");

class px_Skin_Selector
{
    private $_CONF=array();
    private $_CONF_CUSTOM=array();
    private $_DEFAULT_CONF=array();
    private $_LANG=array();
    private $_GET_VARS=array();
    private $_POST_VARS=array();
    private $_ERROR=false;
    private $_ERROR_TEXT="";
    private $_USERNAME="";
    private $_USER_CONF_FILE="";
    private $_USER_CONF=array();
    private $_EXEC_LEVEL;

    public function __construct($LVL)
    {
        $this->_CONF=$this->_init_settings();
        $this->_CONF_CUSTOM=$this->_init_custom_settings();

        if ($this->get_conf('WRITE_LOG_ACCESS')) {
            define("PX_LOG_ACCESS",1); // 1 - ON, 0 - OFF
        } else {
            define("PX_LOG_ACCESS",0); // 1 - ON, 0 - OFF
        }
        if ($this->get_conf('WRITE_LOG_DEBUG')) {
            define("PX_LOG_DEBUG",1);  // 1 - ON, 0 - OFF
        } else {
            define("PX_LOG_DEBUG",0);  // 1 - ON, 0 - OFF
        }
        if ($this->get_conf('WRITE_LOG_ERROR')) {
            define("PX_LOG_ERROR",1);  // 1 - ON, 0 - OFF
        } else {
            define("PX_LOG_ERROR",0);  // 1 - ON, 0 - OFF
        }
        if ($this->get_conf('PHP_ERROR_REPORTING')) {
            error_reporting(E_ALL);
        } else {
            error_reporting(0);
        }
        $this->_init_system_vars();

        if ($this->_is_directadmin_version_ok())
        {
            $this->_LANG=$this->_load_language(/*'ru'*/);
        }
        else
        {
            return false;
            //die($this->_ERROR_TEXT);
        }
        if ($LVL === ADMIN_LEVEL){
            // Init username and define _USER_CONF_FILE
            $this->_init_user();
            // Load data from user.conf
            $this->_USER_CONF=$this->_load_conf_data($this->_USER_CONF_FILE);
            // Set exec level
            $this->_EXEC_LEVEL=$LVL;
        } else if ($LVL === RESELLER_LEVEL){
            // Init username and define _USER_CONF_FILE
            $this->_init_user();
            // Load data from user.conf
            $this->_USER_CONF=$this->_load_conf_data($this->_USER_CONF_FILE);
            // Set exec level
            $this->_EXEC_LEVEL=$LVL;
        } else {
            // Init username and define _USER_CONF_FILE
            $this->_init_user();
            // Load data from user.conf
            $this->_USER_CONF=$this->_load_conf_data($this->_USER_CONF_FILE);
            // Set exec level
            $this->_EXEC_LEVEL=USER_LEVEL;
        }
    }

    public function get_last_error()
    {
        return ($this->_ERROR) ? $this->_ERROR_TEXT : false;
    }

    public function save_custom_confs($option, $value)
    {
        $_DEFAULT_CONFS = $this->get_confs();
        $_CUSTOM_CONFS = $this->get_custom_confs();
        $_NEW_CONFS = array_merge($_DEFAULT_CONFS, $_CUSTOM_CONFS);
        $allowed_confs = array_keys($_DEFAULT_CONFS);
        $option = strtoupper($option);
        if (in_array($option, $allowed_confs)) {
            $read_from_file=PLUGIN_CUSTOM_DATA_DIR."/settings.php";
            if (!is_file($read_from_file)) {
                $read_from_file=PLUGIN_DATA_DIR."/settings.php";
            }
            $_new_content = "";
            if ($_old_content = @file_get_contents($read_from_file))
            {
                if ($_old_content_a = explode("\n", $_old_content))
                {
                    $l=sizeof($_old_content_a);
                    $loop=0;
                    foreach ($_old_content_a as $row) {
                        $loop++;
                        if ($row && substr($row,0,1) !== ";" && strpos($row,"=") !== false) {
                            list($key,$val) = explode("=", $row);
                            if ($key == $option) {
                                $_new_content .= $key ."=". $value ."\n";
                            } else {
                                $_new_content .= $row."\n";
                            }
                        } else {
                            $_new_content.= $row;
                            if ($l>$loop) {
                                $_new_content.="\n";
                            }
                        }
                    }
                }
            }
            if($_new_content) {
                $save_to_file=PLUGIN_CUSTOM_DATA_DIR."/settings.php";
                if($fp=fopen($save_to_file, 'w')){
                    fwrite($fp, $_new_content);
                    fclose($fp);
                    return true;
                } else {
                    $this->_ERROR=true;
                    $this->_ERROR_TEXT="Failed to open file for writing!";
                    return false;
                }
            }
        }
        return false;
    }

    public function save_user_new_skin($save_skin, $save_collection="server")
    {
        // IS THAT SERVER WIDE SKIN?
        // DOES IT EXIST?
        if ($save_collection == "server") {
            if (!$this->_is_skin(DA_SKINS_DIR,$save_skin)){
                $this->_ERROR=true;
                $this->_ERROR_TEXT="Selected skin ".$save_skin." from ".$save_collection."'s collection does not exist!";
                return false;
            }
            $new_docsroot="./data/skins/".$save_skin;

        // IS THAT RESELLER'S SKIN?
        // DOES IT EXIST?
        } else {
            if (is_dir("/home")) {
                 $DIR="/home/".$save_collection."/skins";
                 $new_docsroot="../../../home/".$save_collection."/skins/".$save_skin;
            } else {
                 $DIR="/usr/home/".$save_collection."/skins";
                 $new_docsroot="../../home/".$save_collection."/skins/".$save_skin;
            }
            if (!$this->_is_skin($DIR,$save_skin)){
                $this->_ERROR=true;
                $this->_ERROR_TEXT="Selected skin ".$save_skin." from ".$save_collection."'s collection does not exist!";
                return false;
            }
        }

        // WAS USER CONF READ?
        // WAS DATA LOADED?
        if (!is_array($this->_USER_CONF) || !$this->_USER_CONF){
            $this->_ERROR=true;
            $this->_ERROR_TEXT="User conf was not loaded!";
            return false;
        }
        // DOES USER HAVE SKIN IN CONF?
        if (!$this->get_user_data('skin')){
            $this->_ERROR=true;
            $this->_ERROR_TEXT="Can not load user's conf or it is corrupted!";
            return false;
        }
        // IS CONF FILE WRITABLE
        if (!is_writable($this->_USER_CONF_FILE)){
            $this->_ERROR=true;
            $this->_ERROR_TEXT="User conf file is not writable or has wrong ownership!";
            return false;
        }

        $NEW_CONF=$this->_USER_CONF;
        $NEW_CONF['skin']=$save_skin;
        $NEW_CONF['docsroot']=$new_docsroot;

        $new_content="";
        foreach($NEW_CONF as $key => $val){
            $new_content.=$key."=".$val."\n";
        }

        if($new_content) {
            copy($this->_USER_CONF_FILE, $this->_USER_CONF_FILE."~bak");
            if($fp=fopen($this->_USER_CONF_FILE."~new", 'w')){
                fwrite($fp, $new_content);
                fclose($fp);
            } else {
                $this->_ERROR=true;
                $this->_ERROR_TEXT="Failed to open file for writing!";
                return false;
            }
            @copy($this->_USER_CONF_FILE."~new", $this->_USER_CONF_FILE);
            @chmod($this->_USER_CONF_FILE."~bak", 0600);
            @chmod($this->_USER_CONF_FILE, 0600);
            @unlink($this->_USER_CONF_FILE."~new");
            @chown($this->_USER_CONF_FILE, "diradmin");
            @chown($this->_USER_CONF_FILE."~bak", "diradmin");
        }
        return true;
    }

    public function save_description($text, $skin, $collection="server")
    {
        if ($collection != "server") {
            $DIR=$this->_get_reseller_skin_dir($collection);
        } else {
            $DIR=DA_SKINS_DIR;
        }
        if (!$this->_is_skin($DIR,$skin)) {
            $this->_ERROR=true;
            $this->_ERROR_TEXT="The skin=".$skin." was not found in directory=".$DIR;
            write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
            return false;
        }
        if ($collection == "server") {
            $file=PLUGIN_DESC_DATA_DIR."/".$collection."__".$skin;
        } else {
            $dir=PLUGIN_DESC_DATA_DIR."/".$collection;
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $file=$dir."/".$collection."__".$skin;
        }
        if ($fp=fopen($file,'w')){
            fwrite($fp, $text);
            return fclose($fp);
        } else {
            $this->_ERROR=true;
            $this->_ERROR_TEXT="Failed to create file=".$file;
            write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
            return false;
        }
        return true;
    }

    // 
    // FUNCTION TO MARK A SKIN AS HIDDEN
    // ============================================================
    public function mark_as_hidden($skin, $collection="server")
    {
        write_debug_log("[ADMIN DEBUG] Going to mark skin=".$skin." as hidden\n");
        if ($collection != "server") {
            $DIR=$this->_get_reseller_skin_dir($collection);
        } else {
            $DIR=DA_SKINS_DIR;
        }
        if (!$this->_is_skin($DIR,$skin)) {
            $this->_ERROR=true;
            $this->_ERROR_TEXT="The skin=".$skin." was not found in directory=".$DIR;
            write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
            return false;
        }
        if ($this->_EXEC_LEVEL==ADMIN_LEVEL) {
            // ADMIN PREFERENCES ARE AT HIGHER PRIORITY
            $file=PLUGIN_HIDDEN_DATA_DIR."/".$collection."__".$skin;
        } else if ($this->_EXEC_LEVEL==RESELLER_LEVEL) {
            $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_username();
            if (!is_dir($dir)){
                mkdir($dir);
            }
            $file=$dir."/".$collection."__".$skin;
        }
        if (!is_file($file)) {
            if ($fp=fopen($file,'w')){
                return fclose($fp);
            } else {
                $this->_ERROR=true;
                $this->_ERROR_TEXT="Failed to create file=".$file;
                write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
                return false;
            }
        }
        return true;
    }

    // 
    // FUNCTION TO MARK A SKIN AS PUBLIC
    // ============================================================
    public function mark_as_public($skin, $collection="server")
    {
        write_debug_log("[ADMIN DEBUG] Going to mark skin=".$skin." as public\n");
        $file=false;
        if ($collection != "server") {
            $DIR=$this->_get_reseller_skin_dir($collection);
        } else {
            $DIR=DA_SKINS_DIR;
        }
        if (!$this->_is_skin($DIR,$skin)) {
            $this->_ERROR=true;
            $this->_ERROR_TEXT="The skin=".$skin." was not found in directory=".$DIR;
            write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
            return false;
        }
        if ($this->_EXEC_LEVEL==ADMIN_LEVEL) {
            // ADMIN PREFERENCES ARE AT HIGHER PRIORITY
            $file=PLUGIN_HIDDEN_DATA_DIR."/".$collection."__".$skin;
        } else if ($this->_EXEC_LEVEL==RESELLER_LEVEL) {
            $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_username();
            if (!is_dir($dir)){
                mkdir($dir);
            }
            $file=$dir."/".$collection."__".$skin;
        }
        if ($file && is_file($file)) {
            return unlink($file);
        }
        return true;
    }


    public function get_skin_description($skin, $collection="server")
    {
        if ($collection == "server") {
            $file=PLUGIN_DESC_DATA_DIR."/".$collection."__".$skin;
        } else {
            $file=PLUGIN_DESC_DATA_DIR."/".$collection."/".$collection."__".$skin;
        }
        if (is_file($file)) {
            return file_get_contents($file);
        }
        return false;
    }

    //
    // FUNCTION TO GET STATUS OF A SKIN
    // ============================================================
    public function is_hidden_skin($search, $collection="server")
    {
        write_debug_log("Checking whether or not skin ".$search." is hidden by admin collection=".$collection."\n");
        if (!$collection) {$collection="server";}

        // ADMIN PREFERENCES ARE AT HIGHER PRIORITY
        if (($collection == "server") && is_file(PLUGIN_HIDDEN_DATA_DIR."/".$collection."__".$search)) {
            write_debug_log("Skin is hidden skin=".$search." by=admin collection=server\n");
            return array('hidden'=>true, 'by'=>'admin');
        } else {
            //write_debug_log("Skin is public skin=".$search." by=admin collection=server\n");
        }

        write_debug_log("Checking whether or not skin ".$search." is hidden by reseller collection=".$collection."\n");
        // RESELLER PREFERENCES ARE AT LOWER PRIORITY
        if ($this->_EXEC_LEVEL==ADMIN_LEVEL) {
            $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_username();
        } else if ($this->_EXEC_LEVEL==RESELLER_LEVEL) {
            $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_username();
        } else {
            $usertype=$this->get_user_data('usertype');
            if (($usertype == "reseller") || ($usertype == "admin")) {
                $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_username();
            } else if (($usertype == "reseller") || ($usertype == "admin")) {
                $dir=PLUGIN_HIDDEN_DATA_DIR."/".$this->get_user_data('creator');
            }
        }

        $check_file=$dir."/".$collection."__".$search;
        if (is_file($check_file)) {
            write_debug_log("Skin is hidden skin=".$search." by=reseller collection=".$collection."\n");
            return array('hidden'=>true, 'by'=>'reseller');
        } else {
            //write_debug_log("Skin is public skin=".$search." by=reseller collection=".$collection."\n");
        }
        return false;
    }

    public function get_username()
    {
        return $this->_USERNAME;
    }

    public function get_var_post($search, $default=false)
    {
        $var=(isset($this->_POST_VARS[$search])) ? $this->_POST_VARS[$search] : false;
        return ($var) ? $var : (($default) ? $default : false);
    }

    public function get_var_get($search, $default=false)
    {
        $var=(isset($this->_GET_VARS[$search])) ? $this->_GET_VARS[$search] : false;
        return ($var) ? $var : (($default) ? $default : false);
    }

    public function get_lang($search)
    {
        return (isset($this->_LANG[$search])) ? $this->_LANG[$search] : $search;
    }

    public function get_conf($search)
    {
        return (isset($this->_CONF_CUSTOM[$search])) ? $this->_CONF_CUSTOM[$search] : $this->_CONF[$search];
    }

    public function get_confs()
    {
        return $this->_CONF;
    }

    public function get_custom_confs()
    {
        return $this->_CONF_CUSTOM;
    }

    public function get_user_data($search)
    {
        return (isset($this->_USER_CONF[$search])) ? $this->_USER_CONF[$search] : NULL;
    }

    public function get_skins($type="all")
    {
        $skins=array();
        switch ($type):
            case "server":
            case "reseller":
                break;
            case "all":
            default:
                $type="all";
                break;
            break;
        endswitch;
        $usertype=$this->get_user_data('usertype');
        if (($this->get_conf('ALLOW_SERVER_WIDE_SKINS') == "1") && ($type == "all" || $type == "server")) {
            $skins['server']=$this->list_server_skins();
        } else {
            write_debug_log("Server skins are disabled by admin (User:".$this->get_username()." Usertype:".$usertype.")\n");
        }
        if (($this->get_conf('ALLOW_RESELLER_SKINS') == "1") && ($type == "all" || $type == "reseller")) {
            write_debug_log("Going to list reseller skins for User:".$this->get_username()." Usertype:".$usertype."\n");
            if ($usertype == "user") {
                $skins['reseller']=$this->list_reseller_skins($this->get_user_data('creator'));
            } else if (($usertype == "reseller") || ($usertype == "admin")) {
                $skins['reseller']=$this->list_reseller_skins($this->get_username());
            } else {
                write_error_log("Could not detect Usertype (User: ".$this->get_username().")\n");
            }
        } else {
            write_debug_log("Reseller skins are disabled by admin (User:".$this->get_username()." Usertype:".$usertype.")\n");
        }
        return $skins;
    }

    public function list_server_skins($force_all=false)
    {
        $_SKINS=array();
        write_debug_log("Going to read directory ".DA_SKINS_DIR." with server skins (User: ".$this->get_username().")\n");
        if ($handle = opendir(DA_SKINS_DIR)) {
            while (false !== ($entry = readdir($handle))) {
                if (in_array($entry,array('.','..'))){continue;}
                if (!is_dir(DA_SKINS_DIR.'/'.$entry)){continue;}
                if (!$this->_is_skin(DA_SKINS_DIR,$entry)){continue;}
                if (!in_array($entry,$_SKINS)){$_SKINS[]=$entry;}
            }
            closedir($handle);
        }
        sort($_SKINS);
        return $_SKINS;
    }

    public function list_reseller_skins($resellername, $force_all=false)
    {
        $_SKINS=array();
        $DIR="/usr/home/".$resellername."/skins";
        if (!is_dir($DIR)) {
            write_debug_log("The directory ".$DIR." does not exist (User: ".$this->get_username().")\n");
            $DIR="/home/".$resellername."/skins";
            if (!is_dir($DIR)) {
                write_error_log("The directory ".$DIR." does not exist (User: ".$this->get_username().")\n");
                return false;
            }
        }
        write_debug_log("Going to read directory ".$DIR." with reseller skins (User: ".$this->get_username().")\n");
        if ($handle = opendir($DIR)) {
            while (false !== ($entry = readdir($handle))) {
                if (in_array($entry,array('.','..'))){continue;}
                if (!is_dir($DIR.'/'.$entry)){continue;}
                if (!$this->_is_skin($DIR,$entry)){continue;}
                if (!in_array($entry,$_SKINS)){$_SKINS[]=$entry;}
            }
            closedir($handle);
        } else {
            write_error_log("Could not open directory ".$DIR." for reading (User: ".$this->get_username().")\n");
            return false;
        }
        sort($_SKINS);
        return $_SKINS;
    }


    private function _is_skin($dir,$name)
    {
        if (!is_readable($dir."/".$name."/files_admin.conf")){return false;}
        if (!is_readable($dir."/".$name."/files_user.conf")){return false;}
        return true;
    }


    private function _get_reseller_skin_dir($resellername)
    {
        $DIR="/usr/home/".$resellername."/skins";
        if (!is_dir($DIR)) {
            $DIR="/home/".$resellername."/skins";
            if (!is_dir($DIR)) {
                $this->_ERROR=true;
                $this->_ERROR_TEXT="Reseller=".$collection." does not seem to have skin directory=".$DIR;
                write_error_log("".$this->_ERROR_TEXT." (User: ".$this->get_username().")\n");
                return false;
            }
        }
        return $DIR;
    }


    // ===============================
    // Function to parse data 
    // ===============================
    private function _load_conf_data($file)
    {
        $data=array();
        if (is_file($file)){$data=@parse_ini_file($file,false,INI_SCANNER_RAW);}
        return $data;
    }


    // ===============================
    // Function to init user 
    // ===============================
    private function _init_user()
    {
        $this->_USERNAME=(isset($_SERVER['USER']) && $_SERVER['USER']) ? $_SERVER['USER'] : false;
        $this->_USER_CONF_FILE="/usr/local/directadmin/data/users/".$this->_USERNAME."/user.conf";
        return ($this->_USERNAME) ? true : false;
    }


    // ===============================
    // Function to load default and
    // user language files
    // ===============================
    private function _load_language($force_lang=false)
    {
        $DEFAULT_LANG=array();
        $USER_LANG=array();
        $DEFAULT_LANG=$this->_load_conf_data(PLUGIN_DATA_DIR."/lang_en.php");
        $selected_lang=($force_lang !== false) ? strtolower($force_lang) : strtolower($_SERVER["LANGUAGE"]);
        if ($selected_lang != "en") {
            $USER_LANG=$this->_load_conf_data(PLUGIN_DATA_DIR."/lang_".$selected_lang.".php");
        }
        return array_merge((array)$DEFAULT_LANG, (array)$USER_LANG);
    }

    // ===============================
    // Function to load settings
    // ===============================
    private function _init_settings()
    {
        $file=PLUGIN_DATA_DIR."/settings.php";
        return $this->_load_conf_data($file);
    }

    // ===============================
    // Function to load custom settings
    // ===============================
    private function _init_custom_settings()
    {
        $file=PLUGIN_CUSTOM_DATA_DIR."/settings.php";
        return $this->_load_conf_data($file);
    }

    // ===============================
    // Check Directadmin version
    // ===============================
    private function _is_directadmin_version_ok()
    {
        if (!isset($_SERVER['RUNNING_AS']) || ($_SERVER['RUNNING_AS']!="diradmin")) {
            $this->_ERROR_TEXT="Plugin should be executed from user diradmin, but not ".$_SERVER['RUNNING_AS']."!";
            $this->_ERROR=true;
            return false;
//        } else if (/* NOT IMPLEMENTED YET */) {
//            $this->_ERROR_TEXT="Directadmin version is too old!";
//            $this->_ERROR=true;
//            return false;
        } else {
            return true;
        }
    }

    // ===============================
    // Init System GET and POST vars
    // ===============================
    private function _init_system_vars()
    {
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']){parse_str($_SERVER['QUERY_STRING'], $this->_GET_VARS);}
        if (isset($_SERVER['POST']) && $_SERVER['POST']){parse_str($_SERVER['POST'], $this->_POST_VARS);}
    }

    private function _write_error_log($str)
    {
        return write_error_log($str);
    }
    private function _write_access_log($str)
    {
        return write_access_log($str);
    }
    private function _write_debug_log($str)
    {
        return write_debug_log($str);
    }
}


function s($s){
    return str_rot13($s);
}
function do_output($HTML, $display=true){
    if($display){if($HTML==PX_CT){print(px_sprintf(strrev(cpr()),pn(),pv())."\n");}else{print $HTML."\n"; return NULL;}}else{return $HTML."\n";}
}
function _js_css($_selected_tab)
{
    $_img_preview_width=intval(PREVIEW_WIDTH)."px";
    $_img_preview_height=intval(PREVIEW_HEIGHT)."px";
    $line_height=(intval(PREVIEW_HEIGHT)*2.2) ."px";
    $skin_desc_top=intval(PREVIEW_HEIGHT)*0.8 . "px";
    $skin_desc_line_height=intval(PREVIEW_HEIGHT)*2 . "px";

    $HTML_code=<<<EOF
    <!-- by Alex S Grebenschikov $ plugins-da.net //-->
    <style type="text/css">
    #px__skin_selector { min-width: 500px; max-width: 750px; overflow:hidden;text-align:left;}
    .skin_tab li {/*margin:0; padding:0;*/ list-style: none; float:left; clear:right;}
    .skin_tab ul {margin:0; padding:0;padding-left:20px;}
    .px_server_skin, .px_reseller_skin, .px_server_skin_selected, .px_reseller_skin_selected {display:block; width:$_img_preview_width; height:$_img_preview_height; border: 1px solid #AAA; color:#AAA; margin: 5px 10px 30px !Important; text-align:center;/*line-height:$line_height;*/opacity: 0.6;}
    .px_server_skin span, .px_reseller_skin span, .px_server_skin_selected span, .px_reseller_skin_selected span {padding-top:5px; position: relative; top:$_img_preview_height; opacity: 1;}
    .px_server_skin:hover, .px_reseller_skin:hover {border: 1px solid #9ACD32; color:#9ACD32;opacity: 1;}
    .px_server_skin_selected, .px_reseller_skin_selected {border: 1px solid #9ACD32; color:#9ACD32; font-weight:bold; margin: 5px 10px 28px; opacity: 1;}
    .px__skin_desc {position:relative; display:none; background: #DDD; min-height:40px; opacity: 0.8; text-decoration:none;}
    .px__skin_desc:hover {display: block;text-decoration:none;}
    .px__skin_desc p, a:hover .px__skin_desc p, a:link .px__skin_desc p {margin:0; padding: 4px 2px; opacity:1; color:#000 !Important; text-decoration:none !Important;}
    #ps_skin_copyright {display:block !Important; text-align:center; margin:20px 0 0 0; color:#AAA !Important;}
    #ps_skin_copyright A {color:#AAA !Important;}
    #px__logs, #px__skins_list {margin: 0 0 0 30px; padding:0;}
    #px__logs li {height: 34px; list-style:none; border: 1px solid #AAA; margin: 0 0 16px 0px; line-height: 34px; padding-left:10px;}
    #px__skins_list li {list-style:none; margin: 0 0 16px 0px; padding-left:10px; border: 1px solid #AAA;}
    #px__skins_list li div {margin: 10px 10px 0px 10px;display:block;}
    .px__preview {background: #FFF;float:left;margin: 0; display:block; border: 1px solid #AAA; margin-top: 24px!Important;}
    .px__preview span {position:relative;top:$_img_preview_height;}
    .px__form {float:right;min-height:250px; width:400px;}
    .px__form2 {float:right;min-height:220px; width:400px;}
    .px__row {margin-left: 30px;display:block; min-width:500px; max-width: 700px; height:34px; border: 1px solid #AAA;}
    .px__row:hover, #px__logs li:hover, #px__logs li.active {border: 1px solid #9ACD32; cursor:pointer;}
    #px__skins_list li:hover {border: 1px solid #9ACD32;}
    .px__option {margin-left:10px; height: 34px; width: 500px; float:left;line-height:34px; text-align:left;}
    .px__on {cursor:pointer;background: URL('images/ON_OFF.png') no-repeat 0 4px; display:block; float:right; width:100px; height:34px; overflow:hidden;}
    .px__off {cursor:pointer;background: URL('images/ON_OFF.png') no-repeat -100px 4px; display:block; float:right; width:100px; height:34px; overflow:hidden;}
    .px__form_description, .px__form_description_on, .px__form_description_off {font-style: italic; width:380px; height:84px; border: 1px solid #EEE;overflow:hidden;}
    .px__form_description_on textarea {border:1px solid #9ACD32 !Important; height:83px;}
    .px__form_description_on {cursor:pointer;}
    .px__form textarea, .px__form input {color: #AAA !Important; border:1px solid #AAA !Important; background-color: #fff !Important; background-image: none !Important;}
    .px__form input.px__btn { border: 1px solid RGB(137, 172, 90) !Important; background-color: RGBA(154, 205, 50, 0.8) !important; color: #5D7843 !important; padding: 6px 12px; border-radius: 4px; cursor: pointer; transition: all .5s ease 0s;}
    .px__form input.px__btn:hover {border: 1px solid #D0D0D0 !Important; background-color: #9ACD32 !important;}
    </style>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/smoothness/jquery-ui.css" />
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
    <script type="text/javascript">
    jQuery.noConflict();
    (function( $ ) {
      $(function() {
        window.onload = function() {
            $("#px__tabs").tabs({ active: $_selected_tab }).show();
            if ($(".skin_tab").length) {
                var timer1 = {};
                var timer2 = {};
                $(".skin_tab a").on({
                    'mouseover': function () {
                        var el_div=$(this).find(".px__skin_desc");
                        var el_span=$(this).find("span");
                        var el_id=$(el_div).attr("id");
                        clearTimeout(timer2[el_id]);
                        $(el_span).hide();
                        timer1[el_id] = setTimeout(function () {
                            $(el_div).show();
                        }, 1000);
                    },
                    'mouseout' : function () {
                        var el_div=$(this).find(".px__skin_desc");
                        var el_span=$(this).find("span");
                        var el_id=$(el_div).attr("id");
                        clearTimeout(timer1[el_id]);
                        timer2[el_id] = setTimeout(function () {
                            $(el_div).hide();
                            $(el_span).show();
                        }, 300);
                    }
                });
            }
            if ($("li a.px__logs_view").length) {
                $("#px__logs li").click(function(event){
                    event.preventDefault();
                    $("#px__logs li").removeClass("active");
                    $(this).addClass("active");
                    $("#px__log_viewer").html("Loading...");
                    $("#px__log_viewer_container").show();
                    URL=$(this).find("a").attr("href");
                    NAME=$(this).html().split("-");
                    $.get(URL, function( data ) {
                        $("#px__log_viewer").html(data);
                        $("#px__selected_log").html(NAME[0]);
                    })
                        .fail(function() {
                            $("#px__log_viewer").html("");
                        });
                });
            }
            if ($(".px__row").length) {
                $(".px__row").click(function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    this_id=$(this).attr('id');
                    if($(this).find("#val__"+this_id).attr('class') == "px__on") {
                        URL="/CMD_PLUGINS_ADMIN/da_skin_selector/index.raw?type=ajax&do=settings&option="+this_id+"&value=off";
                        var el_id="val__"+this_id;
                        $.get(URL, function( data ) {
                            $("#"+el_id).removeClass("px__on").addClass("px__off");
                        });
                    } else {
                        URL="/CMD_PLUGINS_ADMIN/da_skin_selector/index.raw?type=ajax&do=settings&option="+this_id+"&value=on";
                        var el_id="val__"+this_id;
                        $.get(URL, function( data ) {
                            $("#"+el_id).removeClass("px__off").addClass("px__on");
                        });
                    }
                });
            }
            if ($(".px__skin_manager_res").length) {
                $(".px__skin_manager_res").find(".px__off,.px__on").each(function(){
                    var el_id=$(this).attr('id');
                    $(this).click(function(event){
                        event.preventDefault();
                        event.stopPropagation();
                        var is_hidden_el_id=$(this).parents('form:first').find("input[name=hidden]").attr('id');
                        var action=$(this).parents('form:first').attr('action');
                        var rm_class;
                        var add_class;
                        if ($(this).attr('class') == "px__on") {
                            $("#"+is_hidden_el_id).val(1);
                            rm_class="px__on";
                            add_class="px__off";
                        } else if ($(this).attr('class') == "px__off") {
                            $("#"+is_hidden_el_id).val(0);
                            rm_class="px__off";
                            add_class="px__on";
                        }
                        formData=$(this).parents('form:first').serialize();
                        $.ajax({
                            type: 'POST',
                            url: action,
                            data: formData,
                            dataType: 'json',
                            cache: false,
                            success: function(data) {
                                $("#"+el_id).removeClass(rm_class).addClass(add_class);
                            }
                        });
                    });
                });
            }
            if ($(".px__form_description_on").length) {
                $(".px__form_description_on").click(function(event){
                    event.preventDefault();
                    event.stopPropagation();
                    var orig_val=$(this).html();
                    var orig_el_id=$(this).attr('id');
                    var el_id="ta_"+orig_el_id;
                    var ta='<textarea class="px__form_description" name="description" id="'+el_id+'">'+orig_val+'</textarea>';
                    $(this).css("border","0").html(ta).attr('onclick','').unbind('click');
                    $("#"+el_id).focus();
                });
            }
            if ($("#px__skins_list").length) {
                var files = new Array();
                $('input[type=file]').on('change', function(event){
                    el_id=$(this).parents('form:first').attr("id");
                    files[el_id] = event.target.files;
                });

                $("#px__skins_list form").submit(function(event){
                    var action=$(this).attr("action");
                    var formId=$(this).attr('id');
                    event.preventDefault();
                    event.stopPropagation();
                    $(this).find("input, textarea").prop( "disabled", true );
                    var formData = new FormData();
                    if (typeof(files[formId]) == 'undefined' || (files[formId].length  < 1)){
                        //console.log('No file to upload');
                        submitForm(formId);
                        return false;
                    }
                    var r = new Date();
                    var bk_img = $("#"+formId+" .px__preview").css('background-image').replace(/^url|[\(\"\"\)]/g, ''); // "
                    var new_bk_img=bk_img+'&r='+r.getTime();
                    $("#"+formId+" .px__preview").css('background-image', 'none').css('background','URL(/CMD_PLUGINS/da_skin_selector/images/load.gif?) 50% 50% no-repeat');
                    $.each(files[formId], function(key, value){
                        formData.append(key, value);
                    });
                    var skin=$(this).find("input[name=skin]").val();
                    var collection=$(this).find("input[name=collection]").val();
                    $.ajax({
                        url: action+'?type=ajax&do=upload&skin='+skin+'&collection='+collection,
                        type: 'POST',
                        data:  formData,
                        cache: false,
                        dataType: 'json',
                        processData: false,
                        contentType: false,
                        success: function(data, textStatus, jqXHR)
                        {
                            if(typeof data.error === 'undefined'){
                                // Success so call function to process the form
                                $("#"+formId+" .px__preview").css('background-image','none').css('background','URL('+new_bk_img+') 50% 50% no-repeat');
                                submitForm(formId);
                            }
                            else {
                                // Handle errors here
                                //console.log('ERRORS: ' + data.error);
                            }
                            $("#"+formId).find("input, textarea").prop( "disabled", false );
                        },
                        error: function(jqXHR, textStatus, errorThrown)
                        {
                            // Handle errors here
                            //console.log('ERRORS: ' + textStatus);
                            $("#"+formId).find("input, textarea").prop( "disabled", false );
                            // STOP LOADING SPINNER
                        }
                    });
                });
            }
            function submitForm(formId)
            {
                console.log(formId);
                var formData = {};
                var action = $("#"+formId).attr("action");
                $("#"+formId).find("input, textarea").each(function(){
                    el_name=$(this).attr("name");
                    if (el_name != undefined) {
                        if ( $(this).attr("type") == "checkbox") {
                            if ($(this).prop("checked")) {
                                formData[el_name]="1";
                            }
                        } else {
                            formData[el_name]=$(this).val();
                        }
                    }
                });
                //console.log( formData );
                $.ajax({
                    type: 'POST',
                    url: action,
                    data: formData,
                    dataType: 'json',
                    cache: false,
                })
                    .fail(function(data) {
                        $("#"+formId).find("input, textarea").prop( "disabled", false );
                    })
                    .done(function(data) {
                        $("#"+formId).find("input, textarea").prop( "disabled", false );
                    });
            }
        }
      });
    })(jQuery);
    </script>
EOF;
    return $HTML_code;
}
// PX custom sprintf function
function px_sprintf($a1,$a2,$a3){
    return sprintf(s($a1),$a2,$a3);
}
// Write file function
function px_write_file($str, $file){
    if($fp=fopen($file, 'a+')){fwrite($fp, $str);fclose($fp); return true;}else{return false;}
}
// Return plguin name function
function pn(){
    return PLUGIN_NAME;
}
// Return plugin version number function
function pv(){
    return PLUGIN_VERSION;
}
// Return encoded copyright text string function
function cpr(){
    return base64_decode("Pml2cS88Pm4vPGdyYS5OUS1mYXZ0aHlDPid4YW55b18nPWdydGVuZyAnZWJncHJ5cmZfYXZ4Zl9rYz1lPy9ncmEubnEtZmF2dGh5Yy5qamovLzpjZ2d1Jz1zcmV1IG48O2Nmb2EmNDEwMjtjZm9hJjtsY2JwJiBmJTtjZm9hJmYlPidndXR2ZWxjYnBfYXZ4Zl9mYyc9cXYgaXZxPA==");
}
// Write error log function
function write_error_log($str){
    if (defined("PX_LOG_ERROR") && (intval(PX_LOG_ERROR) > 0)){return px_write_file("[".@date('r')."] ".$str, PLUGIN_LOGS_DIR."/error_log");} else {return false;}
}
// Write access log function
function write_access_log($str){
    if (defined("PX_LOG_ACCESS") && (intval(PX_LOG_ACCESS) > 0)){return px_write_file("[".@date('r')."] ".$str, PLUGIN_LOGS_DIR."/access_log");} else {return false;}
}
// Write debug log function
function write_debug_log($str, $level=10){
    if (defined("PX_LOG_DEBUG") && (intval(PX_LOG_DEBUG) >= 1)){return px_write_file("[".@date('r')."] LOG_DEBUG:".PX_LOG_DEBUG." ".$str, PLUGIN_LOGS_DIR."/debug_log");} else {return false;}
}
// Prepare a formated error-message table
function error_message($title,$details){
    $HTML_code =<<<EOF
<br><br><br><br>
<table width="100%" height="100%" cellspacing="0" cellpadding="5">
<tbody>
<tr>
    <td><p align="center">$title</p></td>
</tr>
<tr>
    <td height="1" align="center"><table width="50%"><tbody><tr><td bgcolor="#C0C0C0"></td></tr></tbody></table></td>
</tr>
<tr>
    <td valign="top" align="center"><p align="center"><b>Details</b></p><p align="center">$details</p></td>
</tr>
</tbody>
</table>
EOF;
    return ($HTML_code);
}

// END
