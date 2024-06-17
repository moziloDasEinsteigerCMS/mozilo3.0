<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function admin_Template($pagecontent,$message) {
    $packJS = get_HtmlHead();

    if(!defined('PLUGINADMIN')) {
        echo '<body>'."\n";
        if(!LOGIN) {echo '<div class="container">'."\n";
        } else {
            echo '<div id="mo-admin-td" class="mo-td-content-width admin-container">'."\n";
         }
            echo '<noscript><div class="mo-noscript mo-td-content-width ui-state-error ui-corner-all slideInDown card red mo-align-center">'.getLanguageValue("error_no_javascript").'</div></noscript>'."\n";

        if(LOGIN) {        
        get_Head();
        }

        if(LOGIN) {
            get_Tabs();
            $menu_fix = "";
                    if(is_array($pagecontent)) {
            $menu_fix = '<div id="menu-fix" class="ui-widget ui-widget-content ui-corner-right card">'."\n"
                .'<div id="menu-fix-content" class="ui-corner-all">'.$pagecontent[1].'</div>'."\n"
                .'</div>';
            $pagecontent = $pagecontent[0];
        }
        
            echo '<div class="'.ACTION.' admin-main mo-ui-tabs-panel ui-widget-content ui-corner-bottom mo-no-border-top">'."\n";
            echo $menu_fix;
        }
        

        echo $pagecontent;
        if(LOGIN) {
            echo '</div>'."\n";
        }

  #          ."</div>"
  			if(LOGIN) {
  			echo	'<div class="footer">'."\n"
.'<small>Powered by <a href="https://www.mozilo.de" target="_blank" title="moziloCMS Website">moziloCMS</a> &copy; 2006 - '.date("Y").' &#10072; Version: '.CMSVERSION.' ("'.CMSNAME.'") '.CMSSTATUS.'</small>'."\n"
.'</div>'."\n";}
  #          '<div class="mo-td-content-width" id="out"></div>';
        if(LOGIN)
            echo get_Message($message);
 #       echo '<img class="mo-td-content-width" src="'.ICON_URL_SLICE.'" alt=" " height="1" hspace="0" vspace="0" align="left" border="0" />'
             echo '</div>'."\n";

    } else {
        echo '<body class="ui-widget body-pluginadmin" style="font-size:12px;">'
            .$pagecontent;
        if(LOGIN)
            echo get_Message($message);
    }

    
echo '<script src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-'.ADMIN_JQUERY.'.min.js"></script>'."\n"
        .'<script src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-ui-'.ADMIN_JQUERY_UI.'.custom.min.js"></script>'."\n";        
          if(ACTION == "catpage" or ACTION == "gallery") {
        echo '<script src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/jquery-ui-touch-punch/jquery.ui.touch-punch.js"></script>'."\n";
    } 
    $javaScriptPacker = new JavaScriptPacker();
    $javaScriptPacker->echoPack($packJS);
    echo '</body>'."\n"
    .'</html>';
}

function get_HtmlHead() {
    global $ADMIN_CONF;
    global $CMS_CONF;
    global $specialchars;
    $packJS = array();
    $packCSS = array();

    echo '<!DOCTYPE html>'."\n"
        .'<html lang="de">'."\n"
        .'<head>'."\n"
        .'<script src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/theme.js" ></script>'."\n"
        .'<meta charset="'.CHARSET.'">'."\n"
        .'<meta name="viewport" content="width = device-width, initial-scale = 1.0">'."\n"
        .'<meta name="robots" content="noindex">'."\n"
        .'<title>'.getLanguageValue("cms_admin_titel",true).' - '.getLanguageValue(ACTION."_button").'</title>'."\n"
        .'<link rel="shortcut icon" href="'.URL_BASE.ADMIN_DIR_NAME.'/favicon.ico">'."\n"
        .'<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/css/admin.css">'."\n"
        .'<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.css">'."\n"   
    	  .'<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/jquery.multiselect.filter.css">'."\n";

    if(ACTION == "files" or ACTION == "gallery" or ACTION == "template") {
        echo '<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.css">'."\n";
 //       echo '<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/bootstrap.cms.css">'."\n";
    }

    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
        echo '<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/coloredit/coloredit.min.css">'."\n";

    if(defined('PLUGINADMIN') and is_file(BASE_DIR.PLUGIN_DIR_NAME.'/'.PLUGINADMIN.'/plugin.css'))
        $packCSS[] = PLUGIN_DIR_NAME.'/'.PLUGINADMIN.'/plugin.css';

//prüfen ob die beiden Zeilen wirklich gebraucht werden: Zeilen 99,100 auskommentiert, macht <style></style> in der Seitenansicht
    $cssMinifier = new cssMinifier();
    $cssMinifier->echoCSS($packCSS);

    $dialog_jslang = array("close","yes","no","button_cancel","button_save","button_preview","page_reload","page_edit_discard","page_cancel_reload","dialog_title_send","dialog_title_error","dialog_title_messages","dialog_title_save_beforeclose","dialog_title_delete","dialog_title_lastbackup","dialog_title_docu","login_titel_dialog","error_name_no_freename","error_save_beforeclose","dialog_title_coloredit","error_exists_file_dir","error_datei_file_name","error_zip_nozip","filter_button_all_hide","filter_button_all_show","filter_text","filter_text_gallery","filter_text_plugins","filter_text_files","filter_text_catpage","filter_text_template","config_error_modrewrite","template_title_editor","gallery_text_subtitle","pixels");

    $home_jslang = array("home_error_test_mail");

    $gallery_jslang = array("files","url_adress","page_error_save","images","gallery_delete_confirm");

    $catpage_jslang = array("self","blank","target","page_status","files","pages","page_edit","url_adress","page_error_save",array(EXT_PAGE,"page_saveasnormal"),array(EXT_HIDDEN,"page_saveashidden"),array(EXT_DRAFT,"page_saveasdraft"));

    echo '<script>/*<![CDATA[*/'."\n"
        .'var FILE_START = "'.FILE_START.'";'
        .'var FILE_END = "'.FILE_END.'";'
        .'var EXT_PAGE = "'.EXT_PAGE.'";'
        .'var EXT_HIDDEN = "'.EXT_HIDDEN.'";'
        .'var EXT_DRAFT = "'.EXT_DRAFT.'";'
        .'var EXT_LINK = "'.EXT_LINK.'";'
        .'var EXT_LENGTH = '.EXT_LENGTH.';'
        .'var action_activ = "'.ACTION.'";'
        .'var URL_BASE = "'.URL_BASE.'";'
        .'var ADMIN_DIR_NAME = "'.ADMIN_DIR_NAME.'";'
        .'var ICON_URL = "'.ICON_URL.'";'
        .'var ICON_URL_SLICE = "'.ICON_URL_SLICE.'";'
        .'var usecmssyntax = "'.$CMS_CONF->get("usecmssyntax").'";'
        .'var modrewrite = "'.$CMS_CONF->get("modrewrite").'";'
        .'var defaultcolors = "'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'";'
        .'var MULTI_USER = "'.((defined('MULTI_USER') and MULTI_USER) ? "true" : "false").'";';

    if(isset(${ACTION."_jslang"}) and is_array(${ACTION."_jslang"}))
        echo makeJsLanguageObject(array_merge($dialog_jslang,${ACTION."_jslang"} ));
    else
        echo makeJsLanguageObject($dialog_jslang);

    $acceptfiletypes = "/(\\.".str_replace("%2C","|\\.",$ADMIN_CONF->get("noupload")).")$/i;";
    if(strlen($acceptfiletypes) > 0)
        # nur die nicht in der liste sind
        echo 'var mo_acceptFileTypes = '.$acceptfiletypes;
    else
        # alle erlauben
        echo 'var mo_acceptFileTypes = /#$/i;';
/*
    if(LOGIN and defined('MULTI_USER') and MULTI_USER)
       echo 'var multi_user_time = '.((MULTI_USER_TIME - 10) * 1000).';'; # Sekunde * 1000 = Millisekunden
*/
    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
         echo 'var mo_docu_coloredit = \''.str_replace("/",'\/',getHelpIcon("editsite","color")).'\';';

    echo '/*]]>*/</script>'."\n";
                  
        

/*
    if(LOGIN and defined('MULTI_USER') and MULTI_USER)
        $packJS[] = ADMIN_DIR_NAME.'/jquery/multi_user.js';
*/

    if(ACTION == "catpage" or ACTION == "files" or ACTION == "plugins" or ACTION == "gallery" or ACTION == "template")
        $packJS[] = ADMIN_DIR_NAME.'/jquery/filter.js';

    $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/default.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.js';
    $packJS[] = ADMIN_DIR_NAME.'/jquery/ui-multiselect-widget/src/jquery.multiselect.filter.js';


    if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'.js')) {
        $packJS[] = ADMIN_DIR_NAME.'/jquery/'.ACTION.'.js';
        if(file_exists(BASE_DIR_ADMIN."jquery/".ACTION.'_func.js')) {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/'.ACTION.'_func.js';
        }
    }

    if(ACTION == "catpage" or ACTION == "config" or ACTION == "template")
        $packJS[] = ADMIN_DIR_NAME.'/jquery/coloredit/coloredit.js';

    if((ACTION == "config" and (ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config")))) or ACTION == "catpage" or ACTION == "template") {
        $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog-editor-ace.js';
        require_once(BASE_DIR_ADMIN."ace_editor/mozilo_edit_ace.php");
        echo $editor_area_html;
    }

    if(ACTION == "files" or ACTION == "gallery" or ACTION == "template") {
        echo '<script src="'.URL_BASE.ADMIN_DIR_NAME.'/jquery/File-Upload/load-image.min.js"></script>'."\n";

        $packJS[] = ADMIN_DIR_NAME.'/jquery/dialog_prev.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.iframe-transport.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ip.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/jquery.fileupload-ui.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/locale.js';
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload-cms-ui.js';

        if(ACTION != "gallery") {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template.js';
        } else {
            $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.template_gal.js';
        }
        $packJS[] = ADMIN_DIR_NAME.'/jquery/File-Upload/fileupload.js';
    }

#!!!!!!!!!!! nee function insert_in_head und alle js und css über die einzelnen ACTION.php steuern
    # der plugin eigene admin ist im dialog fenster
    global $PLUGIN_ADMIN_ADD_HEAD;
    $unique = false;
    $packCSS = array();
    if(defined('PLUGINADMIN') and is_array($PLUGIN_ADMIN_ADD_HEAD)) {
        foreach($PLUGIN_ADMIN_ADD_HEAD as $pos => $item) {
            if(strpos($item,"<script") !== false and strpos($item,"src=") !== false) {
                preg_match('#<(script){1,1}[^>]*?(src){1,1}=["\'](.*)["\'][^>]*?>#is', $item,$match);
                if(isset($match[3]) and strpos($match[3],".min.js") === false) {
                    $packJS[] = substr_replace($match[3],"",0,strlen(URL_BASE));
                    unset($PLUGIN_ADMIN_ADD_HEAD[$pos]);
                    $unique = true;
                }
            } elseif(strpos($item,"<link") !== false and strpos($item,"href=") !== false) {
                preg_match('#<(link){1,1}[^>]*?(href){1,1}=["\'](.*)["\'][^>]*?>#is', $item,$match);
                if(isset($match[3]) and strpos($match[3],".min.css") === false) {
                    $packCSS[] = substr_replace($match[3],"",0,strlen(URL_BASE));
                    unset($PLUGIN_ADMIN_ADD_HEAD[$pos]);
                }
            }
        }
        //prüfen ob gebraucht wird: Zeilen 222,223 auskommentiert
        if(count($packCSS) > 0)
            $cssMinifier->echoCSS($packCSS);

        if($unique)
            $packJS = array_unique($packJS);
        echo implode("",$PLUGIN_ADMIN_ADD_HEAD);
    }

    echo "</head>"."\n";
    return $packJS;
}

function get_Head() {
    global $CMS_CONF, $specialchars;

    echo '<div class="mo-td-content-width mo-margin-bottom header">'."\n"
        .'<div class="mo-align-center mo-head-box ui-widget ui-state-default ui-corner-all mo-li-head-tag-no-ul mo-li-head-tag mo-td-middle header-left">'."\n"
  #          .'<span class="mo-td-middle">'
  #              .getHelpIcon()
  #              .'<a href="../index.php?draft=true" title="'.getLanguageValue("help_website_button",true).'" target="_blank" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-website" src="'.ICON_URL_SLICE.'" alt="" /></a>'
  #              .'<span class="mo-bold mo-td-middle mo-padding-left">'
                .'<img src="'.URL_BASE.ADMIN_DIR_NAME.'/css/images/mozilo-logo-24.webp" alt="moziloCMS Logo">'."\n"
                    .'<span class="hide-mobile mr mo-bold">'.getLanguageValue("cms_admin_titel",true).'</span>'."\n"
   #             .'</span>'
    #        .'</span>'
    #        .'<span id="admin-websitetitle" class="mo-bold mo-td-middle">'
    #            .$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"), false, true)
    #        .'</span>'
    #        .'<img style="width:1px;" class="mo-icons-icon mo-icons-blank mo-td-middle" src="'.ICON_URL_SLICE.'" alt="" />'
    #        .'<a href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" class="mo-butten-a-img"><img class="mo-icons-icon mo-icons-logout" src="'.ICON_URL_SLICE.'" alt="" /></a>'
        .'</div>'."\n"
        .'<div class="header-center">'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"), false, true).'</div>'."\n"
        .'<div class="header-right flex">'."\n"
           			.'<button class="theme-toggle mr" id="theme-toggle" title="'.getLanguageValue("admin_theme_toggle",).'" aria-label="Theme toggle">
  <svg class="sun-and-moon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24">
    <mask class="moon" id="moon-mask">
      <rect x="0" y="0" width="100%" height="100%" fill="white" />
      <circle cx="24" cy="10" r="6" fill="black" />
    </mask>
    <circle class="sun" cx="12" cy="12" r="6" mask="url(#moon-mask)" fill="currentColor" />
    <g class="sun-beams" stroke="currentColor">
      <line x1="12" y1="1" x2="12" y2="3" />
      <line x1="12" y1="21" x2="12" y2="23" />
      <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
      <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
      <line x1="1" y1="12" x2="3" y2="12" />
      <line x1="21" y1="12" x2="23" y2="12" />
      <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
      <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
     </g>
  </svg>
</button>'."\n";
        if(empty($_SESSION['username'])){
    $_SESSION['username'] = '';
  }
echo '<div id="admin-user" class="mr ml"><input id="admin-user-check" type="checkbox" name="menu">
<label for="admin-user-check">'.getLanguageValue("admin_user_greeting").', '.$_SESSION['username'].'</label>
<ul class="submenu card">
  <li><a href="../index.php?draft=true" title="'.getLanguageValue("help_website_button",true).'" target="_blank" class="mo-butten-a-img" style="color: var(--main-font-color)"><span>'.getLanguageValue("button_preview").'</span></a></li>
  <li>'.getHelpMenu().'</li>
  <li><a href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" class="mo-butten-a-img logout"><span>'.getLanguageValue("logout_button",true).'</span></a></li>
</ul>'."\n"
.'</div>'."\n"
   #             .'<a href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" class="mo-butten-a-img"><i class="las la-sign-out-alt la-2x"></i><img class="mo-icons-icon mo-icons-logout" src="'.ICON_URL_SLICE.'" alt="" /></a>'

        .'</div>'."\n"
        .'<span class="nav-wrapper">
  <label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
                    </span>'."\n"
    .'</div>'."\n";
}


function get_Tabs() {
    global $array_tabs;
    global $users_array;

    $multi_user = "";
    if(defined('MULTI_USER') and MULTI_USER)
        $multi_user = "&amp;multi=true";

    echo '<div class="admin-side">'
    .'<input class="menu-btn" type="checkbox" id="menu-btn">'
    .'<ul id="js-menu-tabs" class="mo-menu-tabs ui-tabs-nav ui-helper-reset ui-widget-header ui-corner-top nav">';
    foreach($array_tabs as $position => $language) {
        echo '<li class="js-multi-user ui-state-default ui-corner-top'
            .((ACTION == $language) ? " ui-tabs-selected ui-state-active active" : " js-hover-default mo-ui-state-hover")
            .(($language != "home" and in_array($language,$users_array)) ? " ui-state-disabled js-no-click disabled" : "").'">'
            .'<a href="index.php?nojs=true&amp;action='.$language.$multi_user.'" title="'.getLanguageValue($language."_button",true).'" id="'.$language.'"><span class="js-menu-icon mo-icon-text-right mo-tabs-icon mo-tab-'.$language.'"></span><span>'.getLanguageValue($language."_button").'</span></a>'
            .'</li>';
    }
    echo '<li><a href="index.php?logout=true" title="'.getLanguageValue("logout_button",true).'" class="mo-butten-a-img logout"><span class="mo-tabs-icon mo-tab-logout"></span><span>'.getLanguageValue("logout_button",true).'</span></a></li>'
    .'</ul>'
    .'</div>';
}

function get_Message($message) {
    global $LOGINCONF;
    global $ADMIN_CONF;

    $html = "";

    if(!empty($message)) {
        if(is_array($message)) {
            foreach($message as $inhalt) {
                $html .= $inhalt;
            }
        } else {
            $html .= $message;
        }
    }

    // Warnung, wenn seit dem letzten Login Logins fehlgeschlagen sind
    if ($LOGINCONF->get("falselogincount") > 0) {
        $html .= returnMessage(false, getLanguageValue("messages_false_logins")." ".$LOGINCONF->get("falselogincount"));
        // Gesamt-Counter fuer falsche Logins zuruecksetzen
        $LOGINCONF->set("falselogincount", 0);
    }

    // Warnung, wenn die letzte Backupwarnung mehr als $intervallsetting Tage her ist
    if(ROOT or (is_array($ADMIN_CONF->get("admin"))
            and in_array("backupmsgintervall",$ADMIN_CONF->get("admin")))) {
        $intervallsetting = $ADMIN_CONF->get("backupmsgintervall");
        if($intervallsetting != "" and $intervallsetting > 0) {
            $intervallinseconds = 60 * 60 * 24 * $intervallsetting;
            $lastbackup = $ADMIN_CONF->get("lastbackup");
            // initial: nur setzen 
            if($lastbackup == "") {
                $ADMIN_CONF->set("lastbackup",time());
            // wenn schon gesetzt: pruefen und ggfs. warnen
            } else {
                $nextbackup = $lastbackup + $intervallinseconds;
                if($nextbackup <= time())    {
                    $html .= '<div id="lastbackup">'.returnMessage(true,getLanguageValue("admin_messages_backup")).'</span><span style="display:none" id="lastbackup_yes">lastbackup_yes=true</div>';
                }
            }
        }
    }

    if(strlen($html) > 1)
        return '<div id="dialog-auto" style="display:none">'.$html.'</div>';
    else
        return "";
}

function getLanguageJsVar($key) {
    global $LANGUAGE;
    return str_replace(array("[","]","{","}","'",'"',"(",")"),
                   array("\[","\]","\{","\}","\'",'\"',"\(","\)"),
                    $LANGUAGE->getLanguageValue($key));
}

function makeJsLanguageObject($lang_array) {
    $tmp = 'var mozilo_lang = new Object(); ';
    foreach($lang_array as $key) {
        if(is_array($key))
            $tmp .= 'mozilo_lang["'.$key[0].'"] = "'.getLanguageJsVar($key[1]).'"; ';
        else
            $tmp .= 'mozilo_lang["'.$key.'"] = "'.getLanguageJsVar($key).'"; ';
    }
    return $tmp;
}

?>