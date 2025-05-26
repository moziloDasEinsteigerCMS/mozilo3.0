<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

require_once(BASE_DIR_CMS."Mail.php");

function admin() {
    global $ADMIN_CONF;
    global $loginpassword;
    global $specialchars;

    if(function_exists('gzopen') and getRequestValue('get_backup','post') == "true") {
        send_backup_zip();
    }

    if(getRequestValue('chanceadmin','post') == "true") {
        echo set_admin_para();
        exit();
    } elseif(getRequestValue('newpw','post') or getRequestValue('newname','post') or getRequestValue('newpwrepeat','post') or getRequestValue('newuserpw','post') or getRequestValue('newusername','post') or getRequestValue('newuserpwrepeat','post')) {
        if(false !== ($newname = getRequestValue('newname','post',false))
                and false !== ($newpw = getRequestValue('newpw','post',false))
                and false !== ($newpwrepeat = getRequestValue('newpwrepeat','post',false))
                and $newname != "" and $newpw != "" and $newpwrepeat != "") {
            if(ROOT) {
                echo setPassword($newname,$newpw,$newpwrepeat,"root");
                exit();
            } else {
                ajax_return("error",true,returnMessage(false,getLanguageValue("error_no_root")),true,true);
            }
        } elseif(false !== ($newusername = getRequestValue('newusername','post',false))
                and false !== ($newuserpw = getRequestValue('newuserpw','post',false))
                and false !== ($newuserpwrepeat = getRequestValue('newuserpwrepeat','post',false))
                and $newusername != "" and $newuserpw != "" and $newuserpwrepeat != "") {
            echo setPassword($newusername,$newuserpw,$newuserpwrepeat,"user");
            exit();
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("pw_error_missingvalues")),true,true);
        }
    } elseif(getRequestValue('deluser','post') == "true") {
        if(ROOT) {
            $user = $loginpassword->get("username");
            $loginpassword->set("username", "");
            $loginpassword->set("userpw", "");
            ajax_return("success",true,returnMessage(true,getLanguageValue("admin_messages_del_user").'<br><b>'.$user.'</b> '),true,true);
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("error_no_root")),true,true);
        }
    } elseif(USE_CHMOD and getRequestValue('chmodupdate','post') == "true"
            and false !== ($chmodnewfilesatts = getRequestValue('chmodnewfilesatts','post')) and $chmodnewfilesatts != "") {
        if(!preg_match("/^[0-7]{3}$/",$chmodnewfilesatts)) {
            ajax_return("error",true,returnMessage(false,getLanguageValue("admin_error_chmodnewfilesatts")),true,true);
        }
        if($ADMIN_CONF->get('chmodnewfilesatts') != $chmodnewfilesatts) {
            $ADMIN_CONF->set('chmodnewfilesatts', $chmodnewfilesatts);
        }

        if(true !== ($error = setUserFilesChmod())) {
            ajax_return("error",true,$error,true,true);
        }
        ajax_return("success",true,returnMessage(false,getLanguageValue("admin_messages_chmod")),true,true);
    }

    $pagecontent = "";

    $template = array();
    $error = array();
    $show = $ADMIN_CONF->get("admin");
    if(!is_array($show))
        $show = array();

    $titel = "admin_button";

    if(ROOT or in_array("language",$show)) {
        $count = 0;
        if(isset($template[$titel]))
            $count = count($template[$titel]);
        // Zeile "SPRACHAUSWAHL"
        $language_array = getDirAsArray(BASE_DIR_ADMIN.'sprachen',"file","natcasesort");
        if(count($language_array) <= 0) {
            $error[$titel][$count] = getLanguageValue("admin_error_language_empty");
        } elseif(!in_array("language_".$ADMIN_CONF->get('language').".txt",$language_array)) {
            $error[$titel][$count] = getLanguageValue("admin_error_languagefile_error")."<br>".ADMIN_DIR_NAME."/sprachen/language_".$ADMIN_CONF->get('language').".txt";
        } else
            $error[$titel][$count] = false;
        $admin_inhalt = '<div class="mo-select-div flex"><select name="language" class="mo-select js-language flex-100">';

        foreach($language_array as $element) {
            if(substr($element,0,9) == "language_") {
                $selected = NULL;
                $tmp_array = file(BASE_DIR_ADMIN."sprachen/".$element);
                $currentlanguage = NULL;
                foreach($tmp_array as $line) {
                    if (preg_match("/^#/",$line) || preg_match("/^\s*$/",$line)) {
                        continue;
                    }
                    if (preg_match("/^([^=]*)=(.*)/",$line,$matches)) {
                        if(trim($matches[1]) == "_translator") {
                            $currentlanguage = trim($matches[2]);
                            break;
                        }
                    }
                }
                if (substr($element,9,4) == $ADMIN_CONF->get("language")) {
                    $selected = "selected ";
                }
                $admin_inhalt .= "<option ".$selected."value=\"".substr($element,9,4)."\">".substr($element,9,4)." (".getLanguageValue("admin_input_translator")." ".$currentlanguage.")</option>";
            }
        }

        $admin_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("admin_input_language"),$admin_inhalt);
    }

    // Zeile "ADMIN-MAIL"
    if(ROOT or in_array("adminmail",$show)) {
        if(function_exists("isMailAvailable")) {
            $template[$titel][] = array(getLanguageValue("admin_text_adminmail"),'<input type="text" class="mo-input-text" name="adminmail" value="'.$specialchars->rebuildSpecialChars($ADMIN_CONF->get("adminmail"),true,true).'">');
        }
    }
    // Zeile "BACKUP-ERINNERUNG"
    if(ROOT or in_array("backupmsgintervall",$show)) {
        $template[$titel][] = array(getLanguageValue("admin_text_backup"),'<input type="text" class="mo-input-digit js-in-digit" name="backupmsgintervall" value="'.$ADMIN_CONF->get("backupmsgintervall").'">');
    }
 
    // Zeile "Backup"
    if(ROOT or in_array("getbackup",$show)) {
        if(function_exists('gzopen')) {
            $cms_size = dirsize(BASE_DIR_ADMIN) + dirsize(BASE_DIR_CMS);
            if(false !== ($tmp_size = dirsize(BASE_DIR."jquery/")))
                $cms_size += $tmp_size;
            $cms_input = buildCheckBox("backup_include_cms", "true",getLanguageValue("admin_button_include_cms")." (<span class=\"js-file-size\">".convertFileSizeUnit($cms_size)."</span>)").'<br>';
            $catpage_input = "";
            if(false !== ($tmp_size = dirsize(CONTENT_DIR_REL))) {
                $catpage_input = buildCheckBox("backup_include_catpage", "false",getLanguageValue("admin_button_include_catpage")."  (<span class=\"js-file-size\">".convertFileSizeUnit($tmp_size)."</span>)").'<br>';
            }
            $gallery_input = "";
            if(false !== ($tmp_size = dirsize(GALLERIES_DIR_REL))) {
                $gallery_input = buildCheckBox("backup_include_gallery", "false",getLanguageValue("admin_button_include_gallery")."  (<span class=\"js-file-size\">".convertFileSizeUnit($tmp_size)."</span>)").'<br>';
            }
            $layouts_input = "";
            if(false !== ($tmp_size = dirsize(BASE_DIR.LAYOUT_DIR_NAME))) {
                $layouts_input = buildCheckBox("backup_include_layouts", "false",getLanguageValue("admin_button_include_layouts")."  (<span class=\"js-file-size\">".convertFileSizeUnit($tmp_size)."</span>)").'<br>';
            }
            $plugins_input = "";
            if(false !== ($tmp_size = dirsize(BASE_DIR.PLUGIN_DIR_NAME))) {
                $plugins_input = buildCheckBox("backup_include_plugins", "false",getLanguageValue("admin_button_include_plugins")."  (<span class=\"js-file-size\">".convertFileSizeUnit($tmp_size)."</span>)").'<br>';
            }
            $docu_input = "";
            if(false !== ($tmp_size = dirsize(BASE_DIR."docu/"))) {
                $docu_input = buildCheckBox("backup_include_docu", "false",getLanguageValue("admin_button_include_docu")."  (<span class=\"js-file-size\">".convertFileSizeUnit($tmp_size)."</span>)").'<br>';
            }
            $template[$titel][] = array(getLanguageValue("admin_text_get_backup"),
            '<form action="index.php?action='.ACTION.'" method="post">'
            .'<input type="hidden" name="get_backup" value="true">'
            .$cms_input.$catpage_input.$gallery_input.$layouts_input.$plugins_input.$docu_input
  #          .'<div style="font-size:.4em;">&nbsp;</div>'
            .'<input type="submit" title="'.getLanguageValue("admin_button_get_backup").'" name="admin_button_get_backup" class="button mr" value="'.getLanguageValue("admin_button_get_backup").'">'
            .'<span class="js-file-size-summe mo-padding-left">'.convertFileSizeUnit($cms_size).'</span>'
            .'</form>'
            );
        }
    }

    // Zeile "SETZE DATEIRECHTE FÜR NEUE DATEIEN"
    if(ROOT or in_array("chmodnewfilesatts",$show)) {
        if(USE_CHMOD) {
            $template[$titel][] = array(getLanguageValue("admin_text_chmodnewfiles"),'<input type="text" class="mo-input-digit js-in-chmod mr" size="4" maxlength="3" name="chmodnewfilesatts" value="'.$ADMIN_CONF->get("chmodnewfilesatts").'">'.'<input type="button" class="mt" title="'.getLanguageValue("admin_input_chmodupdate").'" name="chmodupdate" value="'.getLanguageValue("admin_input_chmodupdate").'">');
        }
    }
    // Zeile "UPLOAD-FILTER"
     if(ROOT or in_array("upload",$show)) {
        global $ALOWED_IMG_ARRAY;
         global $ALOWED_FILE_ARRAY;
         $line_break = array('.nnn');
         $combined_extension = array_merge($ALOWED_IMG_ARRAY,$ALOWED_FILE_ARRAY,$line_break);
         $extension_list = sort($combined_extension);
         $extension_list = implode(',', $combined_extension);
         $extension_list = str_replace('.', '', $extension_list);
         $extension_list = str_replace('nnn,', '<br>', $extension_list);
    //    $template[$titel][] = array(getLanguageValue("admin_text_uploadfilter"),'<input type="text" class="mo-input-text" name="upload" value="'.$specialchars->rebuildSpecialChars($ADMIN_CONF->get("upload"),true,true).'">');
        $template[$titel][] = getLanguageValue("admin_text_upload_notice")
        .'<div class="flex">'
        .'<div class="mo-in-li-r">'.getLanguageValue("admin_text_uploadfilter").'</div><div>'.''.getLanguageValue("admin_text_upload_available").'<span class="tooltip ml"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path> <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path> </svg>
        
        <span class="tooltiptext">'.$extension_list.'</span>
        </span><input type="text" class="mo-input-text" name="upload" value="'.$specialchars->rebuildSpecialChars($ADMIN_CONF->get("upload"),true,true).'">'.'</div>'
        
        .'</div>';
  //      .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text" name="upload" value="'.$specialchars->rebuildSpecialChars($ADMIN_CONF->get("upload"),true,true).'"></div>';   
    }

    global $loginpassword;
    if(ROOT) {
        $template[$titel][] = getLanguageValue("pw_text_login").' '.getLanguageValue("pw_help")
        .'<div class="flex">'
        .'<div class="mo-in-li-r">'.getLanguageValue("pw_titel_newname").'</div><div class="mo-in-li-r">'.'<input type="text" class="js-in-pwroot mo-input-text" name="newname" value="'.$loginpassword->get("name").'">'.'</div>'
        .'</div>'
        .'<div class="flex mt">'
        .'<div class="mo-in-li-r">'.getLanguageValue("pw_titel_newpw").'</div><div>'.'<input type="password" class="js-in-pwroot mo-input-text" value="'.NULL.'" name="newpw">'.'</div>'
        .'</div>'
        .'<div class="flex mt">'
        .'<div class="mo-in-li-r">'.getLanguageValue("pw_titel_newpwrepeat").'</div><div>'.'<input type="password" class="js-in-pwroot mo-input-text" value="" name="newpwrepeat">'.'</div>'
        .'</div>';
    }

    if(ROOT or in_array("userpassword",$show)) {
        $deluser = NULL;
        $user_allowed_settings = NULL;
        if(ROOT) {
            $deluser = '<input type="button" title="'.getLanguageValue("admin_button_del_user").'" name="deluser" value="'.getLanguageValue("admin_button_del_user").'">';
            $user_allowed_settings = '<div class="c-content">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("admin_noroot_text").'</div>'
                        .'<div class="mo-in-li-r">'.userSettings("tabs").' '.userSettings("config").' '.userSettings("admin").' '.userSettings("plugins").' '.userSettings("template").'</div>'
                    .'</div>';
        }
        $template[$titel][] = getLanguageValue("userpw_text_login").' '.getLanguageValue("pw_help")
        .'<div class="flex mt">'
        .'<div class="mo-in-li-l"></div>'
        .'<div class="mo-in-li-r">'.$deluser.'</div>'
        
        .'</div>'
        .'<div class="flex mt">'
        .'<div class="mo-in-li-r">'.getLanguageValue("userpw_titel_newname").'</div><div class="mo-in-li-r">'.'<input type="text" class="js-in-pwuser mo-input-text" name="newusername" value="'.$loginpassword->get("username").'">'.'</div>'
.'</div>'
        .'<div class="flex mt">'        
        .'<div>'.getLanguageValue("userpw_titel_newpw").'</div><div>'.'<input type="password" class="js-in-pwuser mo-input-text" value="'.NULL.'" name="newuserpw">'.'</div>'
.'</div>'
        .'<div class="flex mt">'        
        .'<div>'.getLanguageValue("userpw_titel_newpwrepeat").'</div><div>'.'<input type="password" class="js-in-pwuser mo-input-text" value="" name="newuserpwrepeat">'.'</div>'
        ."</div>".$user_allowed_settings;
    }

    $pagecontent .= contend_template($template,$error);

    return $pagecontent;
}

function set_admin_para() {
    global $ADMIN_CONF;
    $basic = makeDefaultConf("basic");
    unset($basic['expert']);

    foreach($basic as $type => $type_array) {
        foreach($basic[$type] as $syntax_name => $dumy) {
            if(false === ($syntax_value = getRequestValue($syntax_name,'post')))
                continue;
            if($type == 'text') {
                if($syntax_name == 'adminmail' and $syntax_value != "") {
                    global $specialchars;
                    if(!isMailAddressValid($specialchars->rebuildSpecialChars($syntax_value,false,false)))
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("admin_error_adminmail")),true,true);
                }
                if($ADMIN_CONF->get($syntax_name) != $syntax_value) {
                    $ADMIN_CONF->set($syntax_name, $syntax_value);
                }
                if($syntax_name == "language") {
                    $LANGUAGE  = new Properties(BASE_DIR_ADMIN."sprachen/language_".$ADMIN_CONF->get("language").".txt");
                    return ajax_return("error",false,returnMessage(true,'<input type="button" value="'.$LANGUAGE->get("admin_messages_change_language").'" onclick="window.location.href = \'index.php?action='.ACTION.'\'">'),getLanguageValue("dialog_title_messages"),true);
                }

            }
            if($type == 'digit') {
                if($syntax_name == 'lastbackup') continue;
                $digit = trim($syntax_value);
                if($digit != "" and $syntax_name == 'chmodnewfilesatts' and !preg_match("/^[0-7]{3}$/",$digit)) {
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("admin_error_chmodnewfilesatts")),true,true);
                } elseif($syntax_name == 'backupmsgintervall' and ($digit == "" or $digit < 0 or $digit > 9999)) {
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("admin_error_backupmsgintervall")),true,true);
                } elseif($digit != "" and (!ctype_digit($digit) or strlen($digit) > 4))
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("admin_error_nodigit_tolong")),true,true);
                if($ADMIN_CONF->get($syntax_name) != $digit) {
                    $ADMIN_CONF->set($syntax_name, $digit);
                }
            }
            if($type == 'checkbox') {
                if($syntax_value != "true" and $syntax_value != "false")
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("properties_error_save")),true,true);
                # die checkbox hat immer einen anderen wert als der gespeicherte deshalb keine prüfung
                $ADMIN_CONF->set($syntax_name, $syntax_value);
            }
            if($type == 'userexpert') {
                $ADMIN_CONF->set($syntax_name, $syntax_value);
            }
        }
    }
    return ajax_return("success",false);
}

function setPassword($name = false,$pw = false,$pwrep = false,$type = false) {
    global $loginpassword;
    if(($name !== false and $pw !== false and $pwrep !== false and $type !== false)
        and ($type == "root" or $type == "user")
        and strlen($name) >= 5
        // Neues Paßwort zweimal exakt gleich eingegeben?
        and $pw == $pwrep
        // Neues Paßwort wenigstens sechs Zeichen lang und mindestens aus kleinen und großen Buchstaben sowie Zahlen bestehend?
        and strlen($pw) >= 8
        and preg_match("/[0-9]/", $pw)
        and preg_match("/[a-z]/", $pw)
        and preg_match("/[A-Z]/", $pw)
        ) {
        require_once(BASE_DIR_CMS.'PasswordHash.php');
        $t_hasher = new PasswordHash(8, FALSE);
        $pw = $t_hasher->HashPassword($pw);
#!!!!!!! die fehlermeldung muss geändert werden
        if($pw == '*')
            return ajax_return("error",false,returnMessage(false,getLanguageValue("pw_error_newpwerror")),true,true);
        # Allles gut Speichen
        if($type == "root") {
            $loginpassword->set("name", $name);
             $loginpassword->set("pw", $pw);
        } else {
            $loginpassword->set("username", $name);
            $loginpassword->set("userpw", $pw);
        }
        return ajax_return("success",true,returnMessage(true,getLanguageValue("admin_messages_change_password")),true,true);
    }
    return ajax_return("error",false,returnMessage(false,getLanguageValue("pw_error_newpwerror")),true,true);
}

function userSettings($name) {
    global $ADMIN_CONF;

    if($name == "tabs") {
        global $array_tabs;
        $selectarray = $array_tabs;
    } elseif($name == "admin") {
        $selectarray = makeDefaultConf("basic");
        $selectarray = $selectarray["expert"];
    } elseif($name == "config") {
        $selectarray = makeDefaultConf("main");
        $selectarray = $selectarray["expert"];
    }

    $test = $ADMIN_CONF->get($name);
    if(!is_array($test))
        $test = array();
    if($name != "plugins" and $name != "template") {
        $select = '<div class="mo-select-div"><select title="'.getLanguageValue("admin_noroot_".$name).'" name="'.$name.'[]" multiple="multiple" class="mo-select js-multi js-noroot-'.$name.'">';
        foreach($selectarray as $key) {
            $select_text = getLanguageValue("admin_user_select_".$key);
            if($name == "tabs")
                $select_text = getLanguageValue($key."_button");
            $selected = "";
            if(in_array($key,$test))
                $selected = ' selected="selected"';
            $select .= '<option value="'.$key.'"'.$selected.'>'.$select_text.'</option>';
        }
        $select .= '</select></div>';
    }

    if($name == "plugins") {
        $plugins = getDirAsArray(PLUGIN_DIR_REL,"dir","natcasesort");
        $select = '<div class="mo-select-div"><select title="'.getLanguageValue("admin_noroot_".$name).'" name="'.$name.'[]" multiple="multiple" class="mo-select js-multi js-noroot-'.$name.'">';

        $selected = "";
        # Achtung plugin_-_manage ist deshalb so damit die Gefahr das es ein Plugin mit diesen name gibt so klein wie möglich ist
        if(in_array("plugin_-_manage",$test))
            $selected = ' selected="selected"';
        $select .= '<option value="plugin_-_manage"'.$selected.'>'.getLanguageValue("plugins_title_manage").'</option>';

        $select_option = "";
        foreach($plugins as $plugin) {
            $selected = "";
            if(in_array($plugin,$test))
                $selected = ' selected="selected"';
            $select_option .= '<option value="'.$plugin.'"'.$selected.'>'.$plugin.'</option>';
        }
        if(!empty($select_option))
            $select .= '<optgroup label="Plugins">'.$select_option.'</optgroup>';
        $select .= '</select></div>';
    }

    if($name == "template") {
        $select = '<div class="mo-select-div"><select title="'.getLanguageValue("admin_noroot_".$name).'" name="'.$name.'[]" multiple="multiple" class="mo-select js-multi js-noroot-'.$name.'">';

        $tmp = array("template_manage" => getLanguageValue("template_title_manage"),
            "template_edit" => str_replace("{TemplateName}","",getLanguageValue("template_title_template")),
            "template_plugin_css" => getLanguageValue("template_title_plugins"));
        foreach($tmp as $option => $text) {
            $selected = "";
            if(in_array($option,$test))
                $selected = ' selected="selected"';
            $select .= '<option value="'.$option.'"'.$selected.'>'.$text.'</option>';
        }
        $select .= '</select></div>';
    }

    return $select;


}

function send_backup_zip() {
    $tmp_date = date('Y_m_d_H-i-s');
    $incl = "";
    $make_zip = true;
    $send = false;
    $dirs = array();
    if(getRequestValue('backup_include_cms','post') == "true") {
        $dirs[] = BASE_DIR_ADMIN;
        $dirs[] = BASE_DIR_CMS;
        $dirs[] = BASE_DIR."index.php";
        if(is_file(BASE_DIR."install.php"))
            $dirs[] = BASE_DIR."install.php";
        if(is_file(BASE_DIR."update.php"))
            $dirs[] = BASE_DIR."update.php";
        if(is_file(BASE_DIR."robots.txt"))
            $dirs[] = BASE_DIR."robots.txt";
        if(is_file(BASE_DIR."sitemap.xml"))
            $dirs[] = BASE_DIR."sitemap.xml";
        if(is_file(BASE_DIR."sitemap_addon.xml"))
            $dirs[] = BASE_DIR."sitemap_addon.xml";
        if(is_file(BASE_DIR.".htaccess"))
            $dirs[] = BASE_DIR.".htaccess";
    }
    if(getRequestValue('backup_include_catpage','post') == "true") {
        $dirs[] = CONTENT_DIR_REL;
        $incl .= "catpage_";
    }
    if(getRequestValue('backup_include_gallery','post') == "true") {
        $dirs[] = GALLERIES_DIR_REL;
        $incl .= "gallery_";
    }
    if(getRequestValue('backup_include_layouts','post') == "true") {
        $dirs[] = BASE_DIR.LAYOUT_DIR_NAME;
        $incl .= "layouts_";
    }
    if(getRequestValue('backup_include_plugins','post') == "true") {
        $dirs[] = BASE_DIR.PLUGIN_DIR_NAME;
        $incl .= "plugins_";
    }
    if(getRequestValue('backup_include_docu','post') == "true") {
        $dirs[] = BASE_DIR."docu/";
        $incl .= "docu_";
    }

    if(strlen($incl) > 1)
        $incl = "Include_".$incl;

    if(count($dirs) < 1) {
        $make_zip = false;
        global $message;
        $message .= returnMessage(false,getLanguageValue("admin_error_no_backups_select"));
    }

    if($make_zip) {
        if(!is_dir(BASE_DIR.BACKUP_DIR_NAME)) {
            @mkdir(BASE_DIR.BACKUP_DIR_NAME);
            setChmod(BASE_DIR.BACKUP_DIR_NAME);
        }
        $filename = 'moziloCMS_Backup_'.$incl.$tmp_date.'.zip';
        $file = BASE_DIR.BACKUP_DIR_NAME.'/'.$filename;
        define("PCLZIP_TEMPORARY_DIR",BASE_DIR.BACKUP_DIR_NAME.'/');
        require_once(BASE_DIR_ADMIN."pclzip.lib.php");
        $backup = new PclZip($file);
        if(0 != ($backup->create($dirs,
#                PCLZIP_OPT_ADD_TEMP_FILE_ON,
                PCLZIP_OPT_REMOVE_PATH, BASE_DIR,
                PCLZIP_OPT_ADD_PATH, 'moziloCMS_Backup_'.$tmp_date))) {
            $send = true;
        } else {
            global $message;
            $message .= returnMessage(false,"Error : ".$backup->errorInfo());
            $dh = opendir(BASE_DIR.BACKUP_DIR_NAME);
            while(($entry = readdir($dh)) !== false) {
                if($entry == "." or $entry == "..")
                    continue;
                @unlink(BASE_DIR.BACKUP_DIR_NAME.'/'.$entry);
            }
            closedir($dh);
        }
    }
    if($send) {
        $filesize = filesize($file);
        // Header schreiben
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: public"); 
        header("Content-Description: File Transfer");
        header("Content-Type: application/zip");
        header("Content-Disposition: inline; filename=\"".$filename."\";" );
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: ".$filesize);

        # bei grossen dateien senden in kleinen stücken damit der speicherunter browserdialog schnell aufgeht
        if($filesize > (1048576 * 10)) {# 1048576 = 1mb
            $fp = fopen($file, "r");
            while (!feof($fp)) {
                echo fread($fp, 65536);
                flush(); // this is essential for large downloads
            }
            fclose($fp);
        } else
            @readfile($file);
        @unlink($file);
        exit();
    }
}

?>
