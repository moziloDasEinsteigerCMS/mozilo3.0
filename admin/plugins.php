<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

$debug = "";

function plugins() {
    global $ADMIN_CONF;
    global $CatPage;
    global $message;
    global $specialchars;

global $debug;

    $plugin_manage_open = false;
    # plugins löschen
    if(getRequestValue('plugin-all-del','post') and getRequestValue('plugin-del','post')) {
        plugin_del();
        $plugin_manage_open = true;
    }
    # hochgeladenes plugin installieren
    if(isset($_FILES["plugin-install-file"]["error"])
            and getRequestValue('plugin-install','post')
            and $_FILES["plugin-install-file"]["error"] == 0
            and strtolower(substr($_FILES["plugin-install-file"]["name"],-4)) == ".zip") {
$debug .= "install=".$_FILES["plugin-install-file"]["name"]."<br>\n";
        plugin_install();
        $plugin_manage_open = true;
    }
    # per FTP hochgeladenes plugin installieren
    elseif(($plugin_select = $specialchars->rebuildSpecialChars(getRequestValue('plugin-install-select','post'),false,false))
            and getRequestValue('plugin-install','post')
            and is_file(PLUGIN_DIR_REL.$specialchars->replaceSpecialChars($plugin_select,false)) !== false
            and strtolower(substr($plugin_select,-4)) == ".zip") {
$debug .= "local install=".getRequestValue('plugin-install-select','post')."<br>\n";
        plugin_install($plugin_select);
        $plugin_manage_open = true;
    }

$showdebug = false;
if($showdebug and !empty($debug))
    $message .= returnMessage(false,$debug);


    require_once(BASE_DIR_CMS."Plugin.php");

    if(false !== ($plugin_name = getRequestValue('pluginadmin'))) {#,'get'
        if(file_exists(PLUGIN_DIR_REL.$plugin_name)) {
            define("PLUGINADMIN",$plugin_name);
            if(file_exists(PLUGIN_DIR_REL.PLUGINADMIN."/plugin.conf.php") and file_exists(PLUGIN_DIR_REL.PLUGINADMIN."/index.php")) {
                require_once(PLUGIN_DIR_REL.PLUGINADMIN."/index.php");
                # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
                if(class_exists(PLUGINADMIN) and in_array(PLUGINADMIN, get_declared_classes())) {
                    # $PLUGIN_ADMIN_ADD_HEAD gibts nur hier und ist für sachen die in den head sollen
                    global $PLUGIN_ADMIN_ADD_HEAD;
                    $PLUGIN_ADMIN_ADD_HEAD = array();
                    $multi_user = "";
                    if(defined('MULTI_USER') and MULTI_USER)
                        $multi_user = "&amp;multi=true";
                    define("PLUGINADMIN_GET_URL",URL_BASE.ADMIN_DIR_NAME."/index.php?pluginadmin=".PLUGINADMIN."&amp;nojs=true&amp;action=".ACTION.$multi_user);
                    $plugin = new $plugin_name();
                    $info = $plugin->getInfo();
                    $config = $plugin->getConfig();
                    if(PLUGIN_DIR_REL.$plugin_name.'/'.$config["--admin~~"]["datei_admin"] == PLUGIN_DIR_REL.PLUGINADMIN."/index.php")
                        return $plugin->getContent("");
                    else
                        return require_once(PLUGIN_DIR_REL.$plugin_name.'/'.$config["--admin~~"]["datei_admin"]);
                }
            } else
                die();
        } else
            die();
    }

    if(getRequestValue('chanceplugin','post') == "true"
            and false !== ($plugin_name = getRequestValue('plugin_name','post'))) {
        if(file_exists(PLUGIN_DIR_REL.$plugin_name)
                and file_exists(PLUGIN_DIR_REL.$plugin_name."/plugin.conf.php")
                and file_exists(PLUGIN_DIR_REL.$plugin_name."/index.php")) {
            $conf_plugin = new Properties(PLUGIN_DIR_REL.$plugin_name."/plugin.conf.php");
        } else
            die("Fatal Error");
        if(false !== ($activ = getRequestValue(array($plugin_name,'active'),'post'))
                    and ($activ == "true" or $activ == "false")) {
                $conf_plugin->set("active",$activ);
                ajax_return("success",true);
        } elseif($conf_plugin->get("active") == "true") {
            require_once(PLUGIN_DIR_REL.$plugin_name."/index.php");
            # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
            if(class_exists($plugin_name) and in_array($plugin_name, get_declared_classes())) {
                $plugin = new $plugin_name();
                # das ist nötig weil es sein kann das in getInfo() variblen initaliesiert werden
                $tmp = $plugin->getInfo();
                $config = $plugin->getConfig();
                echo save_plugin_settings($conf_plugin,$config,$plugin_name);
                exit();
            } else
                die("Fatal Error");
        }
        die("Fatal Error");
    }

    $pagecontent = '';

    $show = $ADMIN_CONF->get("plugins");
    if(!is_array($show))
        $show = array();

    if(ROOT or in_array("plugin_-_manage",$show)) {
        $multi_user = "";
        if(defined('MULTI_USER') and MULTI_USER)
            $multi_user = "&amp;multi=true";

        $html_manage = "";
        $plugin_manage = array();
        $disabled = '';
        if(!function_exists('gzopen'))
            $disabled = ' disabled="disabled"';

        $plugin_install = array();
        foreach(getDirAsArray(PLUGIN_DIR_REL,array(".zip")) as $zip_file) {
            $plugin_install[] = '<option value="'.mo_rawurlencode($zip_file).'">'.$zip_file.'</option>';
        }
        $plugin_install_html = "";
        if(count($plugin_install) > 0) {
            $plugin_install_html .= '<br><select class="mo-install-select mo-select-div" name="plugin-install-select" size="1"'.$disabled.'>'
                    .'<option value="">'.getLanguageValue("plugins_select",true).'</option>'
                    .implode("",$plugin_install)
                .'</select>';
        }
        $plugin_manage["plugins_title_manage"][] = '<form id="js-plugin-manage" class="fadeIn" action="index.php?nojs=true&amp;action=plugins'.$multi_user.'" method="post" enctype="multipart/form-data">'
 //           .'<div class="mo-nowrap align-right ui-helper-clearfix">'
            .'<div class="flex mo-wrap">'
                .'<div class="align-left"><span class="mo-bold">'.getLanguageValue("plugins_text_filebutton").'</span><br><span>'.getLanguageValue("plugins_text_fileinfo").'</span></div>'
                .'<input type="file" id="js-plugin-install-file" name="plugin-install-file" class="mo-select-div button"'.$disabled.'>'
                .$plugin_install_html
                .'<input type="submit" id="js-plugin-install-submit" name="plugin-install" class="button" value="'.getLanguageValue("plugins_button_install",true).'"'.$disabled.'>'
                .'</div>'
                .'<div class="mo-align-right">'
                .'<input type="submit" id="js-plugin-del-submit" value="'.getLanguageValue("plugins_button_delete",true).'" class="mo-margin-top js-send-del-stop button">'
            .'</div>'
 //           .'</div>'
            .'</form>';

        $plugin_manage["plugins_title_manage"]["toggle"] = true;
        $html_manage = contend_template($plugin_manage);
        $html_manage = str_replace("js-toggle","js-toggle-manage",$html_manage);
        # es wurde in der template verwaltung was gemacht dann soll die aufgeklapt bleiben
  //      if($plugin_manage_open)
  //          $html_manage = str_replace("display:none","",$html_manage);
        $pagecontent .= $html_manage;
    }

    $pagecontent .= '<div class="js-plugins mo-ul card">'."\n";

    $dircontent = getDirAsArray(PLUGIN_DIR_REL,"dir","natcasesort");
    foreach ($dircontent as $currentelement) {
        $new_plugin_conf = false;
        if(!ROOT and !in_array($currentelement,$show))
            continue;
        if(file_exists(PLUGIN_DIR_REL.$currentelement."/index.php")) {
            if(!is_file(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php")) {
                if(false === (newConf(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php")))
                    die();
                else
                    $new_plugin_conf = true;
            }
            require_once(PLUGIN_DIR_REL.$currentelement."/index.php");
            # Enthält der Code eine Klasse mit dem Namen des Plugins und ist es auch der Dirname?
            if(class_exists($currentelement) and in_array($currentelement, get_declared_classes()))
                $plugin = new $currentelement();
            else
                # Plugin Dirname stimt nicht mit Plugin Classnamen überein
                continue;

            # plugin.conf.php wurde neu erstelt.
            # Wenn es die getDefaultSettings() gibt fühle die plugin.conf.php damit
            if($new_plugin_conf and method_exists($plugin,'getDefaultSettings')) {
                $plugin->settings->setFromArray($plugin->getDefaultSettings());
            }
            $plugin_css_li_error = NULL;
            $plugin_error = false;
            $plugin_info = $plugin->getInfo();
            # Plugin Info Prüfen
            if(isset($plugin_info) and count($plugin_info) > 0) {
                $plugin_name = strip_tags($plugin_info[0],'<b>');
                if(substr(strip_tags($plugin_name),0,(strlen($currentelement))) != $currentelement)
                    $plugin_name = "<b>".$currentelement."</b> ".strip_tags($plugin_name);
                $plugin_name = htmlentities($plugin_name,ENT_COMPAT,CHARSET);
                $plugin_name = str_replace(array("&lt;","&gt;","$"),array("<",">",""),$plugin_name);
            } else {
                $plugin_error = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M11.99 22C6.46846 21.9945 1.99632 17.5149 2 11.9933C2.00368 6.47179 6.48179 1.99816 12.0033 2C17.5249 2.00184 22 6.47845 22 12C21.9967 17.5254 17.5154 22.0022 11.99 22ZM4 12.172C4.04732 16.5732 7.64111 20.1095 12.0425 20.086C16.444 20.0622 19.9995 16.4875 19.9995 12.086C19.9995 7.6845 16.444 4.10977 12.0425 4.08599C7.64111 4.06245 4.04732 7.59876 4 12V12.172ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"></path> </svg>'.getLanguageValue('plugins_error').' <b>'.$currentelement.'</b>'."\n";
                $plugin_css_li_error = ' ui-state-error';
            }
            $pagecontent .= '<details class="js-plugin mo-li ui-widget-content ui-corner-all card mb'.$plugin_css_li_error.'">'."\n"
    						.'<summary>'."\n"
            .'<span class="js-tools-show-hide mo-li-head-tag mo-li-head-tag-no-ul ui-state-active ui-corner-all ui-helper-clearfix flex">'."\n";
  //          $check_show = ' style="display:none"';
  //          if($plugin_manage_open)
  //              $check_show = '';
            if($plugin_error === false) {
            	
            	$test_plugin_config = "ok";
                $plugin_setting_active = "";
                $plugin_config_edit = "";
                $plugin_active = "";
                $config = $plugin->getConfig();
                
if (is_null($config)) {
                
                  // echo "BUMM..." . $plugin_name . " -> config() array ist NULL !<br />" ;
 
                  $config = array();
                  $test_plugin_config = "notok";
                  $plugin_error = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M11.99 22C6.46846 21.9945 1.99632 17.5149 2 11.9933C2.00368 6.47179 6.48179 1.99816 12.0033 2C17.5249 2.00184 22 6.47845 22 12C21.9967 17.5254 17.5154 22.0022 11.99 22ZM4 12.172C4.04732 16.5732 7.64111 20.1095 12.0425 20.086C16.444 20.0622 19.9995 16.4875 19.9995 12.086C19.9995 7.6845 16.444 4.10977 12.0425 4.08599C7.64111 4.06245 4.04732 7.59876 4 12V12.172ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"></path> </svg>' . ' <span class="mo-bold mr ml">' . $plugin_name . '</span>' . getLanguageValue('plugins_error_getconfig')."\n";
                  $pluginwarning = '<span class="js-plugin-name mo-padding-left mo-middle flex red">'.$plugin_error.'</span>';
                  $plugin_config_edit = "";
                  $plugin_active = "";
                  
                  $conf_plugin = new Properties(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php");
                  $conf_plugin->set("active","false");
                  $plugin_setting_active = "";
                  // print_r($conf_plugin);
                  $writeplugin = save_plugin_settings($conf_plugin,$config,$currentelement);
                  
                }   else {

                  //
                  // Plugin Setting active (true/false) Parameter auslesen
                  //
                  $plugin_setting_get_active = "";
                  $plugin_setting_get_active = $plugin->settings->get("active");
                  // echo "Plugin " . $currentelement . " Setting = " . $plugin_setting_active . "<br />";
                
                  $pluginwarning = '<span class="js-plugin-name mo-padding-left mo-middle flex-100">'.$plugin_name.'</span>'."\n";
                  $plugin_setting_active = '<span class="js-plugin-active mo-staus">'.buildCheckBox($currentelement.'[active]', ($plugin->settings->get("active") == "true"),getLanguageValue("plugins_input_active")).'</span>'."\n";
   #               $plugin_config_edit = '<img class="js-tools-icon-show-hide js-toggle mo-tool-icon mo-icons-icon mo-icons-edit" src="'.ICON_URL_SLICE.'" alt="edit" />';
                  $plugin_active = '<input type="checkbox" value="'.$currentelement.'" class="mo-checkbox mo-checkbox-del js-plugin-del ml fadeIn">'."\n";
                }
                 
                // 
                // Debug: Plugin Version abfragen, 
                //
                //        1.1  - altes moziloCMS 1.10/1.11/1.12 
                //        2.0  - aktuelles moziloCMS 
                //
                // print_r($plugin_info);
                $pl_ver = 0;
                if (!empty($plugin_info[1])) {
                  $needed_mozilo_version = $plugin_info[1];
                } else {
                  $pl_ver = 1;
                }
                
                $pos = strpos($needed_mozilo_version, '1.1');
                if ($pos!==false) {
                  $pl_ver = 1;
                }                   
                $pos = strpos($needed_mozilo_version, '2');
                if ($pos!==false) {
                  $pl_ver = 2;
                } 
                
                if ($pl_ver < 2) {
                  // echo "Version: " . $needed_mozilo_version . " <br />";               
                  $plugin_error = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M11.99 22C6.46846 21.9945 1.99632 17.5149 2 11.9933C2.00368 6.47179 6.48179 1.99816 12.0033 2C17.5249 2.00184 22 6.47845 22 12C21.9967 17.5254 17.5154 22.0022 11.99 22ZM4 12.172C4.04732 16.5732 7.64111 20.1095 12.0425 20.086C16.444 20.0622 19.9995 16.4875 19.9995 12.086C19.9995 7.6845 16.444 4.10977 12.0425 4.08599C7.64111 4.06245 4.04732 7.59876 4 12V12.172ZM13 17H11V15H13V17ZM13 13H11V7H13V13Z" fill="currentColor"></path> </svg>' . ' <span class="mo-bold mr ml">' . $plugin_name . '</span>' . getLanguageValue('plugins_error_version')."\n";
                  $pluginwarning = '<span class="js-plugin-name mo-padding-left mo-middle flex red">'.$plugin_error.'</span>'."\n";
                  $conf_plugin = new Properties(PLUGIN_DIR_REL.$currentelement."/plugin.conf.php");
                  $conf_plugin->set("active","false");
                  $plugin_setting_active = "";
                  // print_r($conf_plugin);
                  $writeplugin = save_plugin_settings($conf_plugin,$config,$currentelement);
                  $plugin_active = "";
                  $plugin_config_edit = "";
                }
                
                $pagecontent .= $pluginwarning
                    .'<span class="mo-tag-height-from-icon mo-middle mo-nowrap">'."\n"
                    .$plugin_setting_active
                    .$plugin_config_edit
                    .$plugin_active
                    .'</span>'."\n"
                    .'</span>'    ."\n"         
                
            	
 #        #       $pagecontent .=   '<span class="js-plugin-name mo-padding-left mo-middle flex-100">'.$plugin_name.'</span>'
 #        #           .'<div class="mo-tag-height-from-icon mo-middle mo-nowrap">'
 #        #           .'<span class="js-plugin-active mo-staus">'.buildCheckBox($currentelement.'[active]', ($plugin->settings->get("active") == "true"),getLanguageValue("plugins_input_active")).'</span>'
              //      .'<span class="js-tools-icon-show-hide js-toggle mo-tool-icon mo-icons-icon mo-icons-edit"></span>'
 #         #          .'<input type="checkbox" value="'.$currentelement.'" class="mo-checkbox mo-checkbox-del js-plugin-del ml"'.$check_show.' />'
 #          #         .'</div>'
          #          .'</div>'
                    .'</summary>'."\n"
                    .'<div class="js-toggle-content mo-in-ul-ul ui-helper-clearfix fadeIn">'."\n"
                    .get_plugin_info($plugin_info);
                if ($test_plugin_config == "ok") {
                  # geändert damit getConfig() nicht 2mal ausgeführt wird
                  $config = $plugin->getConfig();                
                }
                # Beschreibung und inputs der Konfiguration Bauen und ausgeben
                $pagecontent .= get_plugin_config($plugin->settings,$config,$currentelement);

            } else
                $pagecontent .= $plugin_error;
            $pagecontent .= '</div>'."\n"
            .'</details>'."\n";
            unset($plugin);
        }
    }
    $pagecontent .= '</div>'."\n";
    return $pagecontent;
}

function save_plugin_settings($conf_plugin,$config,$currentelement) {
    $messages = NULL;

    if(count($config) < 1)
        return ajax_return("success",false);

    foreach($config as $name => $inhalt) {
        if($name == "--admin~~" or $name == "--template~~") continue;
        if(false !== ($conf_inhalt = getRequestValue(array($currentelement,$name),'post',false))) {
            # ist array bei radio und select multi
            if(is_array($conf_inhalt)) {
                $conf_inhalt = implode(",", $conf_inhalt);
            # alle die kein array sind
            } else {
                $conf_inhalt = str_replace(array("\r\n","\r","\n"),"<br />",$conf_inhalt);
            }

            if(isset($config[$name]['regex_error'])) {
                $regex_error = $config[$name]['regex_error'];
            } else {
                $regex_error = getLanguageValue("plugins_error_regex_error");
            }
            if(isset($config[$name]['regex']) and strlen($conf_inhalt) > 0) {
                if(preg_match($config[$name]['regex'], $conf_inhalt)) {
                    # bei Password und verschlüsselung an
                    if($config[$name]['type'] == "password" and $config[$name]['saveasmd5'] == "true") {
                        $conf_inhalt = md5($conf_inhalt);
                    }
                    # nur in conf schreiben wenn sich der wert geändert hat
                    if($conf_plugin->get($name) != $conf_inhalt) {
                        $conf_plugin->set($name,$conf_inhalt);
                    }
                } else {
                    $messages .= ajax_return("error",false,returnMessage(false, $regex_error),true,true);
                }
            } else {
                # nur in conf schreiben wenn sich der wert geändert hat und es kein password ist
                if($conf_plugin->get($name) != $conf_inhalt and $config[$name]['type'] != "password") {
                    $conf_plugin->set($name,$conf_inhalt);
                }
            }
        # checkbox request gibts nicht wenn kein hacken gesetzt ist
        } elseif($config[$name]['type'] == "checkbox" and $conf_plugin->get($name) != "false") {
            $conf_plugin->set($name,"false");
        # variable gibts also schreiben mit lehren wert
        } elseif($conf_plugin->get($name)) {
            $conf_plugin->set($name,"");
        }
    }
    
        // PHP 8.1 Alpha 1 erzeugt Fehler wenn strlen($messages) = null ist!
    if (empty($messages)) {
      $messages = "";
    }
    
    if(strlen($messages) > 0)
        return $messages;
    return ajax_return("success",false);
}
function get_plugin_info($plugin_info) {
    $template = array();

    if(isset($plugin_info[1]) and strlen($plugin_info[1]) > 1)
        $template["plugins_info"][] = array(getLanguageValue("plugins_titel_version"),$plugin_info[1]);

    if(isset($plugin_info[3]) and strlen($plugin_info[3]) > 1)
        $template["plugins_info"][] = array(getLanguageValue("plugins_titel_author"),$plugin_info[3]);
    if(isset($plugin_info[4]) and !empty($plugin_info[4])) {
        $link = '';
        $link_text = '';
        if(is_array($plugin_info[4]) and count($plugin_info[4]) == 2) {
            $pli4_0 = $plugin_info[4][0];
            $pli4_1 = $plugin_info[4][1];
            $link = strip_tags($pli4_0);
            $link_text = strip_tags($pli4_1);
        } else {
            $pli4 = $plugin_info[4][0];
            $link = strip_tags($pli4);
            $link_text = strip_tags($pli4);
        }
        if(strlen($link_text) > 1 and (stristr($link,"http://") or stristr($link,"https://")))
            $template["plugins_info"][] = array(getLanguageValue("plugins_titel_web"),'<a href="'.$link.'" target="_blank">'.$link_text.'</a>');
    }
    if(isset($plugin_info[2]) and strlen($plugin_info[2]) > 1)
        $template["plugins_info"][] = ''
            .'<details><summary class="js-help-plugin"><span title="'.getLanguageValue("plugins_info").'">
<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16"> <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"></path> <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"></path> </svg>'."\n"
.'</span>'."\n"
.'</summary>'."\n"
.'<div class="mo-help-box js-plugin-help-content ui-corner-all fadeIn">'.$plugin_info[2].'</div>'."\n"
.'</details>'."\n";

    return contend_template($template);
}

function get_plugin_config($conf_plugin,$config,$currentelement) {
    if(count($config) < 1)
        return NULL;
    $search = array();
    $replace = array();
    $template = false;
    $pagecontent_conf = NULL;
    $ul_template = array();
    if(isset($config["--template~~"])) {
        $template = $config["--template~~"];
        unset($config["--template~~"]);
    }
    if(isset($config["--admin~~"]) and isset($config["--admin~~"]['description']) and isset($config["--admin~~"]['buttontext'])) {
        $ul_template["config_button"][] = array($config["--admin~~"]['description'],'<input type="button" name="'.$currentelement.'[pluginadmin]" value="'.$config["--admin~~"]['buttontext'].'" class="js-config-adminlogin" />');
        unset($config["--admin~~"]);
    }
    foreach($config as $name => $inhalt) {

        $value = NULL;
        
                // PHP 8.1 Alpha 1 erzeugt Fehler wenn $conf_plugin->get($name) = null ist!
        $get_name = $conf_plugin->get($name);
        if (empty($get_name)) {
          $get_name = "";
        }
        
        if(strlen($get_name) > 0) {
            # in einem input feld type text darf der inhalt keine " haben
            if($config[$name]['type'] == "text") $value = ' value="'.str_replace('"',"&#34;",$conf_plugin->get($name)).'"';
            if($config[$name]['type'] == "textarea")
                $value = $conf_plugin->getToTextarea($name);
            if($config[$name]['type'] == "password") $value = NULL;
        }
        $template_conf = false;
        if(isset($config[$name]['template']))
            $template_conf = true;
        $maxlength = NULL;
        if(isset($config[$name]['maxlength'])) $maxlength = ' maxlength="'.$config[$name]['maxlength'].'"';
        $size = NULL;
        if(isset($config[$name]['size'])) $size = ' size="'.$config[$name]['size'].'"';
        $cols = NULL;
        if(isset($config[$name]['cols'])) $cols = ' cols="'.$config[$name]['cols'].'"';
        $rows = NULL;
        if(isset($config[$name]['rows'])) $rows = ' rows="'.$config[$name]['rows'].'"';
        $multiple = NULL;
        if(isset($config[$name]['multiple']) and $config[$name]['multiple'] == "true") $multiple = ' multiple';
        $css_add = NULL;
        if(empty($size) and empty($cols))
            $css_add = " mo-plugin-size-cols";
        $type = NULL;
        $input = NULL;
        if(isset($config[$name]['type'])) {
            $type = ' type="'.$config[$name]['type'].'"';
            if($config[$name]['type'] == "textarea") {
                $input = '<textarea name="'.$currentelement.'['.$name.']"'.$cols.$rows.' class="mo-plugin-textarea'.$css_add.'" style="width:100%">'.$value.'</textarea>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_textarea}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "select") {
                $plus_array = NULL;
                $css_multi = NULL;
                if(!empty($multiple)) {
                    $css_multi = " js-multi";
                    $plus_array = '[]';
                }
                $input = '<select name="'.$currentelement.'['.$name.']'.$plus_array.'"'.$size.$multiple.' class="mo-plugin-select js-select'.$css_multi.$css_add.'">';
                if(is_array($config[$name]['descriptions'])) {
                    $select_array = array();
                    if($conf_plugin->get($name))
                        $select_array = explode(",",$conf_plugin->get($name));
                    foreach($config[$name]['descriptions'] as $key => $descriptions) {
                        if(is_array($descriptions)) {
                            if(count($descriptions) < 1)
                                continue;
                            $input .= '<optgroup label="'.$key.'">';
                            foreach($descriptions as $opt_key => $opt_descriptions) {
                                $selected = NULL;
                                if(in_array($opt_key,$select_array))
                                    $selected = ' selected="selected"';
                                $input .= '<option value="'.$opt_key.'"'.$selected.'>'.$opt_descriptions.'</option>';
                            }
                            $input .= '</optgroup>';
                        } else {
                            $selected = NULL;
                            if(in_array($key,$select_array))
                                $selected = ' selected="selected"';
                            $input .= '<option value="'.$key.'"'.$selected.'>'.$descriptions.'</option>';
                        }
                    }
                }
                $input .= '</select>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_select}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "radio") {
                if(is_array($config[$name]['descriptions'])) {
                    $input = '';
                    foreach($config[$name]['descriptions'] as $key => $descriptions) {
                        $value = ' value="'.$key.'"';
                        $checked = NULL;
                        if($conf_plugin->get($name) == $key) {
                            $checked = ' checked="checked"';
                        }
                        $in_radio = '<input name="'.$currentelement.'['.$name.']"'.$type.$value.$checked.' id="'.$currentelement.'-'.$name.'-'.$key.'">';
                        $descriptions = '<label for="'.$currentelement.'-'.$name.'-'.$key.'">'.$descriptions.'</label>';
                        $input .= $descriptions.'&nbsp;&nbsp;'.$in_radio.'<br />';
                        if($template !== false or $template_conf === true) {
                            $search[] = '{'.$name.'_radio_'.$key.'}';
                            $replace[] = $in_radio;
                            $search[] = '{'.$name.'_descriptions_'.$key.'}';
                            $replace[] = $descriptions;
                        }
                    }
                }
            } elseif($config[$name]['type'] == "checkbox") {
                $checked = NULL;
                if($conf_plugin->get($name) == "true") {
                    $checked = ' checked="checked"';
                }
                $input = '<input name="'.$currentelement.'['.$name.']"'.$type.$checked.' value="true" id="'.$currentelement.'-'.$name.'">';
                $config[$name]['description'] = '<label for="'.$currentelement.'-'.$name.'">'.$config[$name]['description'].'</label>';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_checkbox}';
                    $replace[] = $input;
                }
            } elseif($config[$name]['type'] == "file") {
                $input = '<span style="background-color:#FF0000;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>';
            } else {
                $input = '<input name="'.$currentelement.'['.$name.']"'.$type.$value.$maxlength.$size.' class="mo-plugin-input'.$css_add.'">';
                if($template !== false or $template_conf === true) {
                    $search[] = '{'.$name.'_'.$config[$name]['type'].'}';
                    $replace[] = $input;
                }
            }
        }
        if($template !== false or $template_conf === true) {
            $search[] = '{'.$name.'_description}';
            $replace[] = $config[$name]['description'];
        }

        if(($template === false and $template_conf === false) or $template == "template_test") {
            $ul_template["config_button"][] = array($config[$name]['description'],$input);
        } elseif($template_conf === true) {
            if($config[$name]['template'] == "template_test")
                # wennn in $config[name][template] nur template_test steht alle Platzhalter ausgeben
                $ul_template["config_button"][] = array("Template Platzhalter",implode("<br />", $search));
            else
                $ul_template["config_button"][] = str_replace($search,$replace,$config[$name]['template']);
        }
    }
    # Ausgeben template
    if($template !== false and $template != "template_test") {
        $ul_template["config_button"][] = str_replace($search,$replace,$template);
    }
    # Achtung wenn in $config['--template~~'] nur "template_test" steht werden
    # alle $search ergebnisse ausgegeben
    if($template == "template_test") {
        $ul_template["config_button"][] = array("Template Platzhalter",implode("<br />", $search));
    }
    $show = '';
    if($conf_plugin->get("active") != "true")
       $show = ' style="display:none"';
    return '<div class="js-config card" '.$show.'>'."\n"
 //   			.'<div class="mo-ul">'
 //           .'<div class="mo-li ui-widget-content ui-corner-all card">'
            .'<div class="js-tools-show-hide mo-li-head-tag mo-tag-height-from-icon mo-li-head-tag-no-ul mo-middle ui-state-default ui-corner-top ui-helper-clearfix c-header">'."\n"
            .'<span class="mo-bold">'.getLanguageValue("config_button").'</span>'."\n"
            .'<span class="js-save-plugin" title="'.getLanguageValue("button_save").'" style="float:right">'."\n"
            .'<svg width="22" height="22" stroke-width="1.5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"> <path d="M3 19V5C3 3.89543 3.89543 3 5 3H16.1716C16.702 3 17.2107 3.21071 17.5858 3.58579L20.4142 6.41421C20.7893 6.78929 21 7.29799 21 7.82843V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19Z" stroke="currentColor" stroke-width="1.5"/> <path d="M8.6 9H15.4C15.7314 9 16 8.73137 16 8.4V3.6C16 3.26863 15.7314 3 15.4 3H8.6C8.26863 3 8 3.26863 8 3.6V8.4C8 8.73137 8.26863 9 8.6 9Z" stroke="currentColor" stroke-width="1.5"/> <path d="M6 13.6V21H18V13.6C18 13.2686 17.7314 13 17.4 13H6.6C6.26863 13 6 13.2686 6 13.6Z" stroke="currentColor" stroke-width="1.5"/> </svg>'."\n"
            .'</span>'."\n"
           .'</div>'."\n"
           .'<div class="mo-in-ul-ul">'."\n"
            .contend_template($ul_template,false,true)."\n"
            .'</div>'."\n"
//            .'</div>'
//            .'</div>'
            .'</div>'."\n";
}


function plugin_del() {
    global $specialchars;
    global $message;
global $debug;

    $plugin_del = getRequestValue('plugin-del','post');
    if(is_array($plugin_del)) {
        foreach($plugin_del as $plugin) {
$debug .= "del=".$plugin."<br>\n";
            if(true !== ($error = deleteDir(PLUGIN_DIR_REL.$plugin)))
                $message .= $error;
        }
    } else {
        $message .= returnMessage(false,getLanguageValue("error_post_parameter"));
    }
}

function plugin_install($zip = false) {
    if(!function_exists('gzopen'))
        return;
global $debug;

    @set_time_limit(600);
    global $message, $specialchars;

    $dir = PLUGIN_DIR_REL;

    if($zip === false)
        $zip_file = $dir.$specialchars->replaceSpecialChars($_FILES["plugin-install-file"]["name"],false);
    else {
        if(getChmod() !== false)
            setChmod($dir.$zip);
        $zip_file = $dir.$zip;
    }
$debug .= $zip_file."<br>";
#    if(true === (move_uploaded_file($_FILES["plugin-install-file"]["tmp_name"], $zip_file))) {
    if(($zip !== false
                and strlen($zip_file) > strlen($dir))
            or ($zip === false
                and true === (move_uploaded_file($_FILES["plugin-install-file"]["tmp_name"], $zip_file)))) {

        require_once(BASE_DIR_ADMIN."pclzip.lib.php");
        $archive = new PclZip($zip_file);

        if(0 != ($file_list = $archive->listContent())) {

            uasort($file_list,"helpUasort");

            $find = installFindPlugins($file_list,$archive);

            if(count($find) > 0) {
                foreach($find as $liste) {
                    if(strlen($liste['index']) > 0) {
$debug .= '<pre>';
$debug .= var_export($liste,true);
$debug .= '</pre>';
                        if(getChmod() !== false) {
                            $tmp1 = $archive->extractByIndex($liste['index']
                                    ,PCLZIP_OPT_PATH, $dir
                                    ,PCLZIP_OPT_ADD_PATH, $liste['name']
                                    ,PCLZIP_OPT_REMOVE_PATH, $liste['remove_dir']
                                    ,PCLZIP_OPT_SET_CHMOD, getChmod()
                                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                                    ,PCLZIP_OPT_REPLACE_NEWER);
                            setChmod($dir.$liste['name']);
                        } else {
                            $tmp1 = $archive->extractByIndex($liste['index']
                                    ,PCLZIP_OPT_PATH, $dir
                                    ,PCLZIP_OPT_ADD_PATH, $liste['name']
                                    ,PCLZIP_OPT_REMOVE_PATH, $liste['remove_dir']
                                    ,PCLZIP_CB_PRE_EXTRACT, "PclZip_PreExtractCallBack"
                                    ,PCLZIP_OPT_REPLACE_NEWER);
                        }
                    } else {
                        # die file strucktur im zip stimt nicht
                        $message .= returnMessage(false,getLanguageValue("error_zip_structure"));
                    }
                }
            } else {
                # die file struktur im zip stimmt nicht
                $message .= returnMessage(false,getLanguageValue("error_zip_structure"));
            }
        } else {
            # scheint kein gültiges zip zu sein
            $message .= returnMessage(false,getLanguageValue("error_zip_nozip")."<br>".$zip_file);
        }
        unlink($zip_file);
    } else {
        # das zip konnte nicht hochgeladen werden
        $message .= returnMessage(false,getLanguageValue("error_file_upload")."<br>".$zip_file);
    }
}

function helpUasort($a,$b) {
    if($a['stored_filename'] == $b['stored_filename'])
        return 0;
    return (strlen($a['stored_filename']) < strlen($b['stored_filename'])) ? -1 : 1;
}

function installFindPlugins($file_list,$archive,$no_subfolder = false) {
    global $specialchars;
    $find = array();
    $count_file_list = count($file_list);
    foreach($file_list as $tmp) {
        # fehler im zip keine ../ im pfad erlaubt
        if(false !== strpos($tmp["stored_filename"],"../"))
            continue;
        if(basename($tmp["stored_filename"]) == "index.php") {
            $name = dirname($tmp["stored_filename"]) == "." ? "" : basename(dirname($tmp["stored_filename"]));
            if(strlen($name) > 0 and $name[0] == ".")
                continue;
            $content = $archive->extractByIndex($tmp['index'],PCLZIP_OPT_EXTRACT_AS_STRING);
            # wurde die index.php entpackt
            if(isset($content[key($content)]["content"])) {
                # die index.php einlessen
                preg_match("/class[\s]+([a-zA-Z\_][a-zA-Z0-9\_\-]*)[\s]+extends[\s]+Plugin[\s]+\{/U",$content[key($content)]["content"],$tmp_name);
                if(isset($tmp_name[1])) {
                    $name = trim($tmp_name[1]);
                    if(!isset($find[$name])) {
                        $remove_dir = dirname($tmp["stored_filename"]);
                        if($remove_dir and $remove_dir == ".")
                            $remove_dir = "";
                        $index = array();
                        foreach($file_list as $key => $tmp1) {
                            $test_dir = substr($tmp1["stored_filename"],0,(strlen($remove_dir)+1));
                            if($no_subfolder and strlen($test_dir) === 1 and $test_dir[0] !== "/")
                                $test_dir = "/";
                            if($test_dir == $remove_dir."/") {
                                $index[] = $tmp1["index"];
                                unset($file_list[$key]);
                            }
                        }
                        if(count($index) > 0) {
                            $find[$name]['name'] = $name;
                            $find[$name]['remove_dir'] = $remove_dir;
                            sort($index,SORT_NUMERIC);
                            $find[$name]['index'] = implode(",",$index);
                        }
                    }
                }
            }
        }
    }
    if(!$no_subfolder and $count_file_list == count($file_list)) {
        $find = installFindPlugins($file_list,$archive,true);
    }
    return $find;
}

function PclZip_PreExtractCallBack($p_event, &$p_header) {
    if(basename($p_header['filename']) == "plugin.conf.php" and is_file($p_header['filename']))
        return 0;
    if(!$p_header['folder'] and substr($p_header['filename'],-4) != ".php" and !isValidDirOrFile(basename($p_header['filename'])))
        return 0;
    return 1;
}


?>
