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
        $template[$titel][] = '<div class="c-content">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitetitle").'</div>'
                        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text" name="websitetitle" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitetitle"),true,true).'">'.'</div>'
                    .'</div>'
                    .'<div class="mo-padding-top c-content">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitetitleseparator").'</div>'
                        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text" name="titlebarseparator" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarseparator"),true,true).'">'.'</div>'
                    .'</div>'
                    .'<div class="mo-padding-top c-content">'
                        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitetitlebar").'</div>'
                        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text mo-input-margin-top" name="titlebarformat" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("titlebarformat"),true,true).'">'.'</div>'
                    .'</div>';

    }

    // Zeile "WEBSITE-BESCHREIBUNG" und "WEBSITE-KEYWORDS"
    if(ROOT or in_array("websitedescription",$show)) {
        $error[$titel][] = false;
        $template[$titel][] = '<div class="mo-padding-top c-content">'
        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitedescription").'</div>'
        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text mo-input-margin-top" name="websitedescription" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitedescription"),true,true).'">'.'</div>'
        .'</div>'
                    .'<div class="mo-padding-top c-content">'
        .'<div class="mo-in-li-l">'.getLanguageValue("config_text_websitekeywords").'</div>'
        .'<div class="mo-in-li-r">'.'<input type="text" class="mo-input-text mo-input-margin-top" name="websitekeywords" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("websitekeywords"),true,true).'">'.'</div>'
        .'</div>';
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
        $conf_inhalt = '<div class="mo-select-div flex"><select name="cmslanguage" class="mo-select flex-100">';
        foreach($tmp_array as $file) {
            $currentlanguagecode = substr($file,strlen("language_"),strlen($file)-strlen("language_")-strlen(".txt"));
            $selected = NULL;
            // aktuell ausgewählte Sprache als ausgewählt markieren 
            if($currentlanguagecode == $CMS_CONF->get("cmslanguage")) {
                $selected = " selected";
            }
            $conf_inhalt .= '<option'.$selected.' value="'.$currentlanguagecode.'">';
            // Übersetzer aus der aktuellen Sprachdatei holen
            $languagefile = new Properties(BASE_DIR_CMS."sprachen/".$file);
            $conf_inhalt .= $currentlanguagecode." (".getLanguageValue("config_input_translator")." ".$languagefile->get("_translator_0").")";
            $conf_inhalt .= "</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_cmslanguage"),$conf_inhalt);
    }

    // Zeile "STANDARD-KATEGORIE"
    if(ROOT or in_array("defaultcat",$show)) {
        $tmp_array = getDirAsArray(CONTENT_DIR_REL,"dir","natcasesort");
        if(count($tmp_array) <= 0) {
            $error[$titel][] = getLanguageValue("config_error_defaultcat_emty");
        } elseif(!in_array($CMS_CONF->get('defaultcat'),$tmp_array)) {
            $error[$titel][] = getLanguageValue("config_error_defaultcat_existed")."<br>".$specialchars->rebuildSpecialChars($CMS_CONF->get('defaultcat'),true,true);
        } else
            $error[$titel][] = false;
        $conf_inhalt = '<div class="mo-select-div flex"><select name="defaultcat" class="mo-select flex-100">';
        foreach($tmp_array as $element) {
            if (count(getDirAsArray(CONTENT_DIR_REL.$element,array(EXT_PAGE,EXT_HIDDEN),"none")) == 0) {
                continue;
            }
            $selected = NULL;
            if ($element == $CMS_CONF->get("defaultcat")) {
                $selected = "selected ";
            }
            $conf_inhalt .= '<option '.$selected.'value="'.$element.'">'.$specialchars->rebuildSpecialChars($element, true, true)."</option>";
        }
        $conf_inhalt .= "</select></div>";
        $template[$titel][] = array(getLanguageValue("config_text_defaultcat"),$conf_inhalt);
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
        if(ROOT or in_array("defaultcolors",$show)) {
            $error[$titel][] = false;
            $colors_div = '<div class="c-content">';
            $colors_div .= '<div class="mo-in-li-l flex">';
            $colors_div .= '<span class="js-save-default-color"><svg xmlns="http://www.w3.org/2000/svg" class="mr" width="20" height="20" stroke-width="1.5" viewBox="0 0 24 24" fill="none" > <path d="M3 19V5C3 3.89543 3.89543 3 5 3H16.1716C16.702 3 17.2107 3.21071 17.5858 3.58579L20.4142 6.41421C20.7893 6.78929 21 7.29799 21 7.82843V19C21 20.1046 20.1046 21 19 21H5C3.89543 21 3 20.1046 3 19Z" stroke="currentColor" stroke-width="1.5"/> <path d="M8.6 9H15.4C15.7314 9 16 8.73137 16 8.4V3.6C16 3.26863 15.7314 3 15.4 3H8.6C8.26863 3 8 3.26863 8 3.6V8.4C8 8.73137 8.26863 9 8.6 9Z" stroke="currentColor" stroke-width="1.5"/> <path d="M6 13.6V21H18V13.6C18 13.2686 17.7314 13 17.4 13H6.6C6.26863 13 6 13.2686 6 13.6Z" stroke="currentColor" stroke-width="1.5"/> </svg></span>';
            $colors_div .= '<span id="js-del-config-default-color" class="mo-tool-icon ui-corner-all"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" class="mr" fill="currentColor" viewBox="0 0 16 16"><title>'.getLanguageValue("admin_delete").'</title><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/> <path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/> </svg></span>';
            $colors_div .= '<div id="js-config-default-color-box" class="ce-default-color-box ui-widget-content ui-corner-all flex">';
            $colors_div .= '</div>';
            $colors_div .= '</div>';
            $colors_div .= '<div class="mo-in-li-r">';
            $colors_div .= '<div id="js-menu-config-default-color" class="flex">'
                .'← <img class="js-new-config-default-color ce-bg-color-change ce-default-color-img ui-widget-content ui-corner-all" alt="" title="" src="'.ICON_URL_SLICE.'">'
                .'<input type="text" maxlength="6" value="DD0000" class="ce-bg-color-change js-in-hex ce-in-hex" id="js-new-default-color-value" size="6">'
                .'<i class="js-coloreditor-button ed-icon-border ed-syntax-icon ed-farbeedit" title="'.getLanguageValue("dialog_title_coloredit").'" style="display:none"></i>'
                .'</div>'
                .'</div>'
            .'</div>';
            $template[$titel][] = '<div class="mo-margin-bottom">'.getLanguageValue("config_text_defaultcolors").'</div>'.$colors_div.'<input type="hidden" name="defaultcolors" value="'.$specialchars->rebuildSpecialChars($CMS_CONF->get("defaultcolors"),false,false).'">';
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
