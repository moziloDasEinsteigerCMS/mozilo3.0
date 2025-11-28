<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();
function config() {
    global $CMS_CONF;
    global $specialchars;
    global $CONTACT_CONF;
    global $ADMIN_CONF;
    global $USER_SYNTAX;

    if(getRequestValue('savesyntax','post') == "true") {
        if(false !== ($content = getRequestValue('content','post',false))
                and $CMS_CONF->get('usecmssyntax') == "true") {
            $USER_SYNTAX->setFromTextarea($content);
            require_once(BASE_DIR_ADMIN.'editsite.php');
            $selctbox = '<span id="replace-item">'.returnUserSyntaxSelectbox().'</span>';
            $var_UserSyntax = '0E0M0P0T0Y0';
            # die userSxntax hat sich geändert deshalb schiecken wir dem editor userSyntax die neuen
            global $USER_SYNTAX;
            $moziloUserSyntax = $USER_SYNTAX->toArray();
            if(count($moziloUserSyntax) > 0) {
                $moziloUserSyntax = array_keys($moziloUserSyntax);
                rsort($moziloUserSyntax);
                $var_UserSyntax = implode('|',$moziloUserSyntax);
            }
            $var_UserSyntax = '<span id="moziloUserSyntax">'.$var_UserSyntax.'</span>';
            echo ajax_return("success",false).$selctbox.$var_UserSyntax;
        } elseif($CMS_CONF->get('usecmssyntax') == "true") {
            require_once(BASE_DIR_ADMIN.'editsite.php');
            $selctbox = '<span id="replace-item">'.returnUserSyntaxSelectbox().'</span>';
            $selctbox .= '<textarea id="page-content">'.$USER_SYNTAX->getToTextarea().'</textarea>';
            echo ajax_return("success",false).$selctbox;
        }
        exit();
    } elseif(getRequestValue('chanceconfig','post') == "true") {
        echo set_config_para();
        exit();
    }

    $pagecontent = NULL;

    $show = $ADMIN_CONF->get("config");
    if(!is_array($show))
        $show = array();

    $template = array();
    $error = array();
    // ALLGEMEINE EINSTELLUNGEN
    $titel = "config_titel_cmsglobal";
    // Zeile "WEBSITE-TITEL", "TITEL-TRENNER" und "WEBSITE-TITELLEISTE"
    if(ROOT or in_array("websitetitle",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="websitetitle">'.getLanguageValue("config_text_websitetitle").'</label>
            </div>
            <div class="mo-in-li-r">
                <input id="websitetitle" type="text" class="mo-input-text" name="websitetitle" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),true,true).'">
            </div>
        </div>';

        $template[$titel][] = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="titlebarseparator">'.getLanguageValue("config_text_websitetitleseparator").'</label>
            </div>
            <div class="mo-in-li-r">
                <input id="titlebarseparator" type="text" class="mo-input-text" name="titlebarseparator" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true).'">
            </div>
        </div>';

        $template[$titel][] = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="titlebarformat">'.getLanguageValue("config_text_websitetitlebar").'</label>
            </div>
            <div class="mo-in-li-r">
                <input id="titlebarformat" type="text" class="mo-input-text mo-input-margin-top" name="titlebarformat" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true).'">
            </div>
        </div>';

    }

    // Zeile "WEBSITE-BESCHREIBUNG" und "WEBSITE-KEYWORDS"
    if(ROOT or in_array("websitedescription",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="websitedescription">'.getLanguageValue("config_text_websitedescription").'</label>
            </div>
            <div class="mo-in-li-r">
                <input id="websitedescription" type="text" class="mo-input-text mo-input-margin-top" name="websitedescription" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),true,true).'">
            </div>
        </div>';

        $template[$titel][] = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="websitekeywords">'.getLanguageValue("config_text_websitekeywords").'</label>
            </div>
            <div class="mo-in-li-r">
                <input id="websitekeywords" type="text" class="mo-input-text mo-input-margin-top" name="websitekeywords" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),true,true).'">
            </div>
        </div>';
    }

    // Zeile "SPRACHAUSWAHL"
        if(ROOT or in_array("cmslanguage",$show)) {
        $tmp_array = getDirAsArray(BASE_DIR_CMS.'sprachen',"file","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_language_empty");
        } elseif(!in_array("language_".$CMS_CONF->get('cmslanguage').".txt",$tmp_array)) {
            $error[$titel][] = getLanguageValue("config_error_languagefile_error")."<br>".CMS_DIR_NAME."/sprachen/language_".$CMS_CONF->get('cmslanguage').".txt";
        } else
            $error[$titel][] = false;

        $conf_inhalt = '<div class="c-content">
            <div class="mo-in-li-l">
                <label for="cmslanguage">'.getLanguageValue("config_text_cmslanguage").'</label>
            </div>
            <div class="mo-in-li-r">
                <div class="mo-select-div flex">
                    <select id="cmslanguage" name="cmslanguage" class="mo-select flex-100">';
        foreach($tmp_array as $file) {
            $currentlanguagecode = substr($file,strlen("language_"),-4);
            $selected = ($currentlanguagecode == $CMS_CONF->get("cmslanguage")) ? "selected" : "";
            $languagefile = new Properties(BASE_DIR_CMS."sprachen/".$file);
            $conf_inhalt .= '<option value="'.$currentlanguagecode.'" '.$selected.'>'.$currentlanguagecode.' ('.getLanguageValue("config_input_translator").' '.$languagefile->get("_translator_0").')</option>';
        }
        $conf_inhalt .= '</select></div></div></div>';
        $template[$titel][] = $conf_inhalt;
    }

    // Zeile "STANDARD-KATEGORIE"
    if(ROOT || in_array("defaultcat",$show)) {
        $tmp_array = getDirAsArray(CONTENT_DIR_REL,"dir","natcasesort");
        $conf_inhalt = '<div class="c-content">
            <div class="mo-in-li-l"><label for="defaultcat">'.getLanguageValue("config_text_defaultcat").'</label></div>
            <div class="mo-in-li-r"><div class="mo-select-div flex">
            <select id="defaultcat" name="defaultcat" class="mo-select flex-100">';
        foreach($tmp_array as $element) {
            if (count(getDirAsArray(CONTENT_DIR_REL.$element,array(EXT_PAGE,EXT_HIDDEN),"none")) == 0) continue;
            $selected = ($element == $CMS_CONF->get("defaultcat")) ? "selected" : "";
            $conf_inhalt .= '<option value="'.$element.'" '.$selected.'>'.$specialchars->rebuildSpecialChars($element,true,true).'</option>';
        }
        $conf_inhalt .= '</select></div></div></div>';
        $template[$titel][] = $conf_inhalt;
    }

    if(ROOT or in_array("draftmode",$show)) {
        $conf_checkbox = buildCheckBox("draftmode", $CMS_CONF->get("draftmode"),getLanguageValue("config_input_draftmode"));
        $conf_select = "";
        $tmp_array = getDirAsArray(BASE_DIR."layouts","dir","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_layouts_emty");
        } else
            $error[$titel][] = false;
        $conf_select .= '<div class="mo-select-div flex mt"><select name="draftlayout" class="mo-select flex-100">';
        $conf_select .= '<option value="false">'.getLanguageValue("config_input_draftlayout").'</option>';
        foreach ($tmp_array as $file) {
            $selected = NULL;
            if ($file == $CMS_CONF->get("draftlayout")) {
                $selected = " selected";
            }
            $conf_select .= '<option'.$selected.' value="'.$file.'">';
            $conf_select .= $specialchars->rebuildSpecialChars($file, true, true);
            $conf_select .= "</option>";
        }
        $conf_select .= "</select></div>";

        $template[$titel][] = array(getLanguageValue("config_text_draftmode"),$conf_checkbox.$conf_select);
    }

    # sitemap.xml
    if(ROOT or in_array("usesitemap",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usesitemap"),buildCheckBox("usesitemap", $CMS_CONF->get("usesitemap"),getLanguageValue("config_input_usesitemap")));
    }

    // Zeile "NUTZE CMS-SYNTAX"
    // SYNTAX-EINSTELLUNGEN
    $titel = "config_titel_cmssyntax";
    if(ROOT or in_array("usecmssyntax",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usesyntax"),buildCheckBox("usecmssyntax", $CMS_CONF->get("usecmssyntax"),getLanguageValue("config_input_usesyntax")));
    }

    if(ROOT or ((in_array("usecmssyntax",$show))
        or (!in_array("usecmssyntax",$show) and $CMS_CONF->get("usecmssyntax") == "true"))) {

        if(ROOT or in_array("editusersyntax",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_usersyntax"),'<div class="js-usecmssyntax">'.'<span class="js-editsyntax"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" height="20" width="20" fill="currentColor"><title>'.getLanguageValue("admin_edit").'</title> <g> <path fill="none" d="M0 0h24v24H0z"></path> <path d="M21 6.757l-2 2V4h-9v5H5v11h14v-2.757l2-2v5.765a.993.993 0 0 1-.993.992H3.993A1 1 0 0 1 3 20.993V8l6.003-6h10.995C20.55 2 21 2.455 21 2.992v3.765zm.778 2.05l1.414 1.415L15.414 18l-1.416-.002.002-1.412 7.778-7.778z"></path> </g> </svg></span>'.'</div>');
        }

        // Zeile "ERSETZE EMOTICONS"
        if(ROOT or in_array("replaceemoticons",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_replaceemoticons"),'<div class="js-usecmssyntax">'.buildCheckBox("replaceemoticons", ($CMS_CONF->get("replaceemoticons") == "true"),getLanguageValue("config_input_replaceemoticons")).'</div>');
        }
    }

    // Erweiterte Einstellungen
    // Zeile "showhiddenpagesin"
    $titel = "config_titel_expert";
    if(ROOT or in_array("hiddenpages",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_showhiddenpages"),
                buildCheckBox("showhiddenpagesinsearch", ($CMS_CONF->get("showhiddenpagesinsearch") == "true"),getLanguageValue("config_input_search")).'<br>'
                .buildCheckBox("showhiddenpagesinsitemap", ($CMS_CONF->get("showhiddenpagesinsitemap") == "true"),getLanguageValue("config_input_sitemap")).'<br>'
                .buildCheckBox("showhiddenpagesasdefaultpage", ($CMS_CONF->get("showhiddenpagesasdefaultpage") == "true"),getLanguageValue("config_input_pagesasdefaultpage"))
                );
    }

    // Zeile "Links öffnen self blank"
    if(ROOT or in_array("targetblank",$show)) {
           $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_target"),buildCheckBox("targetblank_download", ($CMS_CONF->get("targetblank_download") == "true"),getLanguageValue("config_input_download")).'<br>'.buildCheckBox("targetblank_link", ($CMS_CONF->get("targetblank_link") == "true"),getLanguageValue("config_input_link")));
    }
    // Zeile "wenn page == cat"
    if(ROOT or in_array("hidecatnamedpages",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_catnamedpages"),buildCheckBox("hidecatnamedpages", ($CMS_CONF->get("hidecatnamedpages") == "true"),getLanguageValue("config_input_catnamedpages")));
    }
    // Zeile "mod_rewrite"
    if(ROOT or in_array("modrewrite",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_modrewrite"),buildCheckBox("modrewrite", ($CMS_CONF->get("modrewrite") == "true"),getLanguageValue("config_input_modrewrite")));
    }
    // Zeile "showsyntaxtooltips"
    if(ROOT or in_array("showsyntaxtooltips",$show)) {
            $error[$titel][] = false;
            $template[$titel][] = array(getLanguageValue("config_text_showsyntaxtooltips"),buildCheckBox("showsyntaxtooltips", ($CMS_CONF->get("showsyntaxtooltips") == "true"),getLanguageValue("config_input_showsyntaxtooltips")));
    }

    $pagecontent .= contend_template($template,$error);

    if(ROOT or in_array("editusersyntax",$ADMIN_CONF->get("config"))) {
        $pagecontent .= pageedit_dialog();
    }

    return $pagecontent;
}

function set_config_para() {
    global $CMS_CONF, $specialchars;

    $title = "";
    $main = makeDefaultConf("main");
    unset($main['expert']);
    foreach($main as $type => $type_array) {
        foreach($main[$type] as $syntax_name => $dumy) {
            if(false === ($syntax_value = getRequestValue($syntax_name,'post')))
                continue;
            if($type == 'text') {
                if($CMS_CONF->get($syntax_name) != $syntax_value) {
                    $CMS_CONF->set($syntax_name, $syntax_value);
                    if($syntax_name == "websitetitle")
                        $title = '<span id="replace-item"><span id="admin-websitetitle" class="mo-bold mo-td-middle">'.$specialchars->rebuildSpecialChars($syntax_value, false, true).'</span></span>';
                }
            }
            if($type == 'checkbox') {
                if($syntax_value != "true" and $syntax_value != "false")
                    return ajax_return("error",false,returnMessage(false,getLanguageValue("properties_error_save")),true,true);
                # die checkbox hat immer einen anderen wert als der gespeicherte deshalb keine prüfung
                $CMS_CONF->set($syntax_name, $syntax_value);
                if($syntax_name == "modrewrite" and true !== ($error = write_modrewrite($syntax_value)))
                    return $error;
                if($syntax_name == "usesitemap") {
                    if(true !== ($error = write_robots()))
                        return $error;
                    if(true != ($error = write_xmlsitmap(true)))
                        return $error;
                }
            }
        }
    }
    return ajax_return("success",false).$title;
}
?>
