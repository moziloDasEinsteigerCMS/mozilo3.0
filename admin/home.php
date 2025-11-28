<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

function home() {
    require_once(BASE_DIR_CMS."Mail.php");
    // Testmail schicken und gleich raus hier
    if(false !== ($test_mail_adresse = getRequestValue('test_mail_adresse','post'))
            and $test_mail_adresse != "") {
        header('content-type: text/html; charset='.CHARSET.'');
        global $specialchars;
        $test_mail_adresse = $specialchars->rebuildSpecialChars($test_mail_adresse,false,false);
        if(isMailAddressValid($test_mail_adresse)) {
            sendMail(getLanguageValue("home_mailtest_mailsubject"),
                getLanguageValue("home_mailtest_mailcontent"),
                $test_mail_adresse,
                $test_mail_adresse);
            ajax_return("success",true,returnMessage(true,getLanguageValue("home_messages_test_mail")."<p class=\"mo-bold\">".$test_mail_adresse.'</p>'),true,true);
        } else {
            ajax_return("error",true,returnMessage(false,getLanguageValue("home_error_test_mail")."<p class=\"mo-bold\">".$test_mail_adresse.'</p>'),true,true);
        }
        exit();
    }
    global $CMS_CONF;
    if($CMS_CONF->get('usesitemap') == "true") {
        global $message;
        if(!is_file(BASE_DIR.'robots.txt')) {
            if(true !== ($error_message = write_robots()))
                    $message .= $error_message;
        }
        if(!is_file(BASE_DIR.'sitemap.xml')) {
            if(true != ($error_message = write_xmlsitmap()))
                $message .= $error_message;
        }
    }
    
    // CMS-INFOS
    $titel = "home_cmsinfo";
    // Zeile "CMS-VERSION"
    $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_cmsversion_text"),CMSVERSION.' ("'.CMSNAME.'") - '.CMSSTATUS);

    // Zeile "CMS-Update"
    $versionhere = CMSVERSION;
    $version = @file_get_contents('https://www.mozilo.de/update-check/version.txt');
    
    if( !ini_get('allow_url_fopen') ) {
    $error[$titel][] = true;
      $template[$titel][] = array(getLanguageValue("home_cmsupdate_text"),getLanguageValue("home_cmsupdate_text_failed_fopen"));
}
else {
        if ($version === false) {
      // Handle error
      $error[$titel][] = true;
      $template[$titel][] = array(getLanguageValue("home_cmsupdate_text"),getLanguageValue("home_cmsupdate_text_failed"));
    } else {    
      if ($versionhere != trim($version)) {
        $error[$titel][] = true;
        
        if ($versionhere < trim($version)) {
          $template[$titel][] = array(getLanguageValue('home_cmsupdate_text'),getLanguageValue('home_cmsupdate_text_update').'('.$version.')'.'<br/> <a class="button" href="https://github.com/moziloDasEinsteigerCMS/mozilo3.0/archive/refs/heads/main.zip" title="'.getLanguageValue("home_cmsupdate_text_download_title").'" download>'.getLanguageValue('home_cmsupdate_text_download').'</a>'); 
        } else {   
          //
          // Debug: 2022-01-01
          //
          //        Entweder unbekannt oder höhere Versionsnummer 
          //        in der Datei cms/DefaultConfCMS.php
          //     
          $mozilo_unknown = getLanguageValue("home_cmsupdate_unknown_version");
          $mozilo_download = getLanguageValue("home_cmsupdate_stable_version");
          $template[$titel][] = array($mozilo_unknown, $mozilo_download
          .'<br> <a class="button" href="https://github.com/moziloDasEinsteigerCMS/mozilo3.0/archive/refs/heads/main.zip" title="' . $mozilo_download . '" download>'.getLanguageValue("home_cmsupdate_text_download").'</a>');
        }

      } else {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_cmsupdate_text"),getLanguageValue("home_cmsupdate_text_noupdate"));
      }
    }
}

    // Zeile "Gesamtgröße des CMS"
    $cmssize = convertFileSizeUnit(dirsize(BASE_DIR));
    if($cmssize === false) {
        $error[$titel][] = true;
        $cmssize = "0";
    } else
        $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_cmssize_text"),$cmssize);

    // Zeile "Installationspfad" und alle 40 Zeichen einen Zeilenumbruch einfügen
    $path = BASE_DIR;
    if(strlen($path) >= 40) {
        $path = explode("/",$path);
        if(is_array($path)) {
            if(empty($path[count($path)-1]))
                unset($path[count($path)-1]);
            $i = 0;
            $new_path[$i] = "";
            foreach($path as $string) {
                $string = $string."/";
                if(strlen($new_path[$i].$string) <= 40)
                    $new_path[$i] = $new_path[$i].$string;
                else {
                    $i++;
                    $new_path[$i] = $string;
                }
            }
        }
        $path = implode("<br>",$new_path);
    }
    $error[$titel][] = false;
    $template[$titel][] = array(getLanguageValue("home_installpath_text"),$path);

    // CMS-Hilfe
    $titel = "home_help";
    if(file_exists(BASE_DIR."docu/index.php")) {
        $error[$titel][] = false;
        $template[$titel][] = '<div class="flex"><a href="'.URL_BASE.'docu/index.php" target="_blank" class="mo-butten-a-img box"><svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="42" width="42"><path d="M.3013 17.6146c-.1299-.3387-.5228-1.5119-.1337-2.4314l9.8273 5.6738a.329.329 0 0 0 .3299 0L24 12.9616v2.3542l-13.8401 7.9906-9.8586-5.6918zM.1911 8.9628c-.2882.8769.0149 2.0581.1236 2.4261l9.8452 5.6841L24 9.0823V6.7275L10.3248 14.623a.329.329 0 0 1-.3299 0L.1911 8.9628zm13.1698-1.9361c-.1819.1113-.4394.0015-.4852-.2064l-.2805-1.1336-2.1254-.1752a.33.33 0 0 1-.1378-.6145l5.5782-3.2207-1.7021-.9826L.6979 8.4935l9.462 5.463 13.5104-7.8004-4.401-2.5407-5.9084 3.4113zm-.1821-1.7286.2321.938 5.1984-3.0014-2.0395-1.1775-4.994 2.8834 1.3099.108a.3302.3302 0 0 1 .2931.2495zM24 9.845l-13.6752 7.8954a.329.329 0 0 1-.3299 0L.1678 12.0667c-.3891.919.003 2.0914.1332 2.4311l9.8589 5.692L24 12.1993V9.845z"></path></svg>
'.getLanguageValue("home_help_text_docu").'</a><a href="'.URL_BASE.'docu/index.php?menu=false&amp;artikel=start" target="_blank" class="js-docu-link mo-butten-a-img box"><svg role="img" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="42" width="42"><path d="M.3013 17.6146c-.1299-.3387-.5228-1.5119-.1337-2.4314l9.8273 5.6738a.329.329 0 0 0 .3299 0L24 12.9616v2.3542l-13.8401 7.9906-9.8586-5.6918zM.1911 8.9628c-.2882.8769.0149 2.0581.1236 2.4261l9.8452 5.6841L24 9.0823V6.7275L10.3248 14.623a.329.329 0 0 1-.3299 0L.1911 8.9628zm13.1698-1.9361c-.1819.1113-.4394.0015-.4852-.2064l-.2805-1.1336-2.1254-.1752a.33.33 0 0 1-.1378-.6145l5.5782-3.2207-1.7021-.9826L.6979 8.4935l9.462 5.463 13.5104-7.8004-4.401-2.5407-5.9084 3.4113zm-.1821-1.7286.2321.938 5.1984-3.0014-2.0395-1.1775-4.994 2.8834 1.3099.108a.3302.3302 0 0 1 .2931.2495zM24 9.845l-13.6752 7.8954a.329.329 0 0 1-.3299 0L.1678 12.0667c-.3891.919.003 2.0914.1332 2.4311l9.8589 5.692L24 12.1993V9.845z"></path></svg>'.getLanguageValue("home_help_text_info").'</a></div>';
    } else {
        $error[$titel][] = true;
        $template[$titel][] = getLanguageValue("home_no_help");
    }
    // Zeile "Multiuser Reset"
    if(defined('MULTI_USER') and MULTI_USER) {
        $titel = "home_multiuser";
        $error[$titel][] = false;
        $template[$titel][] = array(getLanguageValue("home_multiuser_text"),
            '<form action="index.php?action='.ACTION.'" method="post">'
            .'<input type="hidden" name="logout_other_users" value="true">'
            .'<input type="submit" title="'.getLanguageValue("home_multiuser_button").'" name="submitlogout_other_users" class="button" value="'.getLanguageValue("home_multiuser_button").'">'
            .'</form>');
    }

    // E-Mail test
    if(isMailAvailable()) {
        $titel = "home_titel_test_mail";
        $error[$titel][] = false;
        $template[$titel][] = array(
    '<label for="test_mail_adresse">' . getLanguageValue("home_text_test_mail") . '</label>',
    '<input type="text" id="test_mail_adresse" class="mo-input-text" name="test_mail_adresse" value="">'
);

    } else {
        $titel = "home_titel_test_mail";
        $error[$titel][] = true;
        $template[$titel][] = getLanguageValue("home_messages_no_mail");
    }

     // SERVER-INFOS
    $titel = "home_serverinfo";

    // Aktuelles Datum
    $error[$titel][] = false;
    $time_zone = date("T");
    if(function_exists('date_default_timezone_get'))
        $time_zone = @date_default_timezone_get();
    $template[$titel][] = array(getLanguageValue("home_date_text"),date("Y-m-d H:i:s")." ".$time_zone);

    // Sprache
    $error[$titel][] = false;
    if(false !== ($locale = @setlocale(LC_TIME, "0"))) {
        $template[$titel][] = array(getLanguageValue("home_text_locale"),$locale);
    } else
        $template[$titel][] = array(getLanguageValue("home_text_locale"),getLanguageValue("home_text_nolocale"));

    // Zeile "PHP-Version"
    if(version_compare(PHP_VERSION, MIN_PHP_VERSION) >= 0) {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_phpversion_text"),phpversion());
    } else {
        $error[$titel][] = getLanguageValue("home_error_phpversion_text");
        $template[$titel][] = array(getLanguageValue("home_phpversion_text"),phpversion());
    }

    // Zeile "GDlib installiert"
    if(!extension_loaded("gd")) {
        $error[$titel][] = getLanguageValue("home_error_gd");
        $template[$titel][] = array(getLanguageValue("home_text_gd"),getLanguageValue("no"));
    } else {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_text_gd"),getLanguageValue("yes"));
    }

    if($CMS_CONF->get('modrewrite') == "true") {
        # mod_rewrite wird mit javascript ermitelt und ausgetauscht
        $error[$titel][] = getLanguageValue("home_error_mod_rewrite");
        $template[$titel][] = array('<span id="mod-rewrite-false">'.getLanguageValue("home_mod_rewrite").'</span>',getLanguageValue("no"));
    } else {
        $error[$titel][] = false;
        $template[$titel][] = array('<span id="mod-rewrite-false">'.getLanguageValue("home_mod_rewrite").'</span>',getLanguageValue("home_mod_rewrite_deact"));
    }
    # backupsystem
    if(function_exists('gzopen')) {
        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_text_backupsystem"),getLanguageValue("yes"));
    } else {
        $error[$titel][] = true;
        $template[$titel][] = array(getLanguageValue("home_error_backupsystem"),getLanguageValue("no"));
    }

    # MULTI_USER
    if(defined('MULTI_USER') and MULTI_USER) {
        $mu_string = "";
        $rest_time = MULTI_USER_TIME;
        if($rest_time >= 86400) {
            $mu_string .= floor(MULTI_USER_TIME / 86400)." ".((floor(MULTI_USER_TIME / 86400) > 1) ? getLanguageValue("days") : getLanguageValue("day"))." ";
            $rest_time = $rest_time - (floor(MULTI_USER_TIME / 86400) * 86400);
        }
        if($rest_time >= 3600) {
            $mu_string .= floor($rest_time / 3600)." ".((floor($rest_time / 3600) > 1) ? getLanguageValue("hours") : getLanguageValue("hour"))." ";
            $rest_time = $rest_time - (floor($rest_time / 3600) * 3600);
        }
        if($rest_time >= 60) {
            $mu_string .= floor($rest_time / 60)." ".((floor($rest_time / 60) > 1) ? getLanguageValue("minutes") : getLanguageValue("minute"))." ";
            $rest_time = $rest_time - (floor($rest_time / 60) * 60);
        }
        if($rest_time > 0)
            $mu_string .= $rest_time." ".(($rest_time > 1) ? getLanguageValue("seconds") : getLanguageValue("second"));

        $error[$titel][] = "ok";
        $template[$titel][] = array(getLanguageValue("home_multiuser_mode_text"),$mu_string);
    } else {
        $error[$titel][] = true;
        $template[$titel][] = array(getLanguageValue("home_multiuser_mode_text"),getLanguageValue("no"));
    }

    

    return contend_template($template,$error);
}


?>
