<?php if(!defined('IS_ADMIN') or !IS_ADMIN) die();

// Anzeige der Editieransicht
function showEditPageForm()    {
    global $CMS_CONF;

    // Anzeige der Formatsymbolleiste, wenn die CMS-Syntax aktiviert ist
    $toolbar = NULL;
    if ($CMS_CONF->get("usecmssyntax") == "true" or ACTION == "config") {
        $display = "";
        if ($CMS_CONF->get("usecmssyntax") != "true")
            $display = "display:none";
        $toolbar = '<div id="js-editor-toolbar" '.$display.'>'.returnFormatToolbar().'</div>';
    }
    $content = '<div id="pageedit-box-inhalt" class="card" style="width:100%; height:100%">'
                .$toolbar
    .'<div id="ace-menu-box" class="ui-widget-header ui-corner-top flex card">'

    .'<div class="mo-nowrap flex">'
            .'<span id="show_gutter" class="ed-ace-icon ed-icon-border ed-syntax-icon ed-number" title="'.getLanguageValue("toolbar_editor_linenumber",true).'"></span>'
            .'<span id="show_hidden" class="ed-ace-icon ed-icon-border ed-syntax-icon ed-noprint" title="'.getLanguageValue("toolbar_editor_controlcharacter",true).'"></span>'
    .'</div>'
    .'<div class="mo-ace-td-select flex">'
            .'<div><select name="select-mode" title="'.getLanguageValue("toolbar_editor_highlighter",true).'" id="select-mode" class="mo-ace-in-select js-ace-select">'
                .'<option value="mozilo">'."Mozilo".'</option>'
                .'<option value="text">'."Text".'</option>'
                .'<option value="css">'."CSS".'</option>'
                .'<option value="html">'."HTML".'</option>'
                .'<option value="javascript">'."JavaScript".'</option>'
                .'<option value="php">'."PHP".'</option>'
            ."</select></div>"
    .'</div>'
    .'<div class="mo-ace-td-select flex">'
            .'<div><select name="select-fontsize" title="'.getLanguageValue("toolbar_editor_fontsize",true).'" id="select-fontsize" class="mo-ace-in-select js-ace-select">'
                .'<option value="10px">'."10px".'</option>'
                .'<option value="12px">'."12px".'</option>'
                .'<option value="14px">'."14px".'</option>'
                .'<option value="16px">'."16px".'</option>'
                .'<option value="18px">'."18px".'</option>'
            ."</select></div>"
    .'</div>'
    .'<div class="mo-nowrap flex">'
            .'<span id="undo" class="ed-ace-icon ed-syntax-icon ed-undo" title="'.getLanguageValue("toolbar_editor_undo",true).'"></span>'
            .'<span id="redo" class="ed-ace-icon ed-syntax-icon ed-redo" title="'.getLanguageValue("toolbar_editor_redo",true).'"></span>'

            .'<span id="toggle_fold" class="ed-ace-icon ed-syntax-icon ed-expand" title="'.getLanguageValue("toolbar_editor_togglefold",true).'"></span>'
    .'</div>'
    .'<div id="colordiv-editor" class="mo-nowrap flex">';
    if ($CMS_CONF->get("usecmssyntax") != "true" and ACTION != "config")
        $content .= returnToolbarColoredit();
    $content .= '</div>'
    .'<div class="mo-nowrap flex">'
            .'<input class="mo-ace-in-text" id="search-text" type="text" name="search-text" value="" aria-label="'.getLanguageValue("toolbar_editor_search",true).'">'
            .'<span id="search" class="ed-ace-icon ed-syntax-icon ed-find" title="'.getLanguageValue("toolbar_editor_search",true).'"></span>'
            .'<input class="mo-ace-in-check" type="checkbox" id="search-all" title="'.getLanguageValue("toolbar_editor_searchall",true).'">'
            .'<label class="mo-ace-in-check-label" for="search-all">'.getLanguageValue("toolbar_editor_textall",true).'</label>'
            .'<input class="mo-ace-in-text" id="replace-text" type="text" name="search" value="" aria-label="'.getLanguageValue("toolbar_editor_replace",true).'">'
            .'<span id="replace" class="ed-ace-icon ed-syntax-icon ed-replace" title="'.getLanguageValue("toolbar_editor_replace",true).'"></span>'
    .'</div>'
    .'</div>'
    .'<div id="pagecontent-border" style="position:relative;overflow:hidden;" class="ui-widget-content">'
        .'<div id="pagecontent"></div>'
    .'</div>'
.'</div>';

    $subnav = false;
    if(ACTION == "config")
        $subnav = "editusersyntax";
    elseif(ACTION == "template")
        $subnav = "template";

    $content .= getHelpIcon("editsite",$subnav);
    return $content;
}

function returnFormatToolbar() {
    global $CMS_CONF;
    global $USER_SYNTAX;

    $content = '<div id="format-toolbar" class="mo-menue-row-bottom mo-menue-row-top card flex mb">'

    // Syntaxelemente

    .returnFormatToolbarIcon("link")
    .returnFormatToolbarIcon("mail")
    .returnFormatToolbarIcon("seite")
    .returnFormatToolbarIcon("kategorie")
    .returnFormatToolbarIcon("datei")
    .returnFormatToolbarIcon("bild")
    .returnFormatToolbarIcon("bildlinks")
    .returnFormatToolbarIcon("bildrechts")
    .returnFormatToolbarIcon("absatz")
    .returnFormatToolbarIcon("liste")
    .returnFormatToolbarIcon("numliste")
    .returnFormatToolbarIcon("tabelle")
    .returnFormatToolbarIcon("linie")
    .returnFormatToolbarIcon("html")
    .returnFormatToolbarIcon("include")

    // Textformatierung

    .returnFormatToolbarIcon("ueber1")
    .returnFormatToolbarIcon("ueber2")
    .returnFormatToolbarIcon("ueber3")
    .returnFormatToolbarIcon("links")
    .returnFormatToolbarIcon("zentriert")
    .returnFormatToolbarIcon("block")
    .returnFormatToolbarIcon("rechts")
    .returnFormatToolbarIcon("fett")
    .returnFormatToolbarIcon("kursiv")
    .returnFormatToolbarIcon("unter")
    .returnFormatToolbarIcon("durch")
    .returnFormatToolbarIcon("fontsize=0.8em")

    // Farben

        .returnToolbarColoredit()
    ."</div>";


    // Smileys
    if(($user_icons = returnUserSyntaxIcons()) or $CMS_CONF->get("replaceemoticons") == "true") {
        $content .= '<div id="smiley-toolbar" class="mo-menue-row-bottom flex card mb">';
        if($CMS_CONF->get("replaceemoticons") == "true")
            $content .= returnSmileyBar();
        if($user_icons)
            $content .= $user_icons;
        $content .= '</div>';
    }


    $content .= '<div class="mo-menue-row-bottom flex card mb" style="overflow-x:auto">';
    # Template
    $template_title = NULL;
    $template_selectbox = "&nbsp;";
    if(ACTION == "template") {
        $template_title = "Template CSS und Bilder";
        $template_selectbox = returnTemplateSelectbox();
    }

    $content .= '<div class="mo-select-div shrink-0">'.returnCatPagesSelectbox().'</div>'
    .'<div class="mo-select-div shrink-0">'.returnFilesSelectbox().'</div>'
    .'<div class="mo-select-div shrink-0">'.returnGalerySelectbox().'</div>'
   .'<div class="shrink-0">'.$template_selectbox.'</div>'
    ."</div>"

    .'<div class="mo-menue-row-bottom flex card mb" style="overflow-x:auto">'
    .'<div class="mo-select-div shrink-0">'.returnPluginSelectbox().'</div>'
    .'<div class="mo-select-div shrink-0">'.returnPlatzhalterSelectbox().'</div>'
     // Benutzerdefinierte Syntaxelemente
    .'<div class="flex shrink-0">'.returnUserSyntaxSelectbox().'</div>'
    .'</div>';
    return $content;
}

function returnToolbarColoredit() {
    $content = '<label for="colorPicker" class="sr-only">'.getLanguageValue("dialog_title_coloredit",true).'</label><input type="color" id="colorPicker" style="display:none">'
    .'<button  id="paletteIcon"  class="ed-syntax-icon ed-farbeedit shrink-0" tabindex="0" title="'.getLanguageValue("dialog_title_coloredit",true).'"></button>'
    .'<input type="text" id="colorInput" value="DD0000" aria-label="'.getLanguageValue("toolbar_editor_color",true).'" title="[farbe=RRGGBB| ... ]" readonly>';
    return $content;
}

// Rueckgabe eines Standard-Formatsymbolleisten-Icons
function returnFormatToolbarIcon($tag) {
    if (strpos($tag, "=") > 0) {
        $tagName = substr($tag, 0, strpos($tag, "="));
        return '<button type="button" class="ed-syntax-icon ed-icon-border ed-'.$tagName.' shrink-0" title="['.$tagName.'=|...]" onclick="insert_ace(\'['.$tagName.'=|\', \']\', true)"></button>';
    } elseif ($tag == "tabelle") {
        return '<button type="button" class="ed-syntax-icon ed-icon-border ed-'.$tag.' shrink-0" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\\n&lt;&lt; \', \' |  &gt;&gt;\\n&lt;  |  &gt;\\n]\', true)"></button>';
    } elseif ($tag == "linie") {
        return '<button type="button" class="ed-syntax-icon ed-icon-border ed-'.$tag.' shrink-0" title="[----]" onclick="insert_ace(\'[----]\', false, false)"></button>';
    } elseif($tag == "bild" or $tag == "bildlinks" or $tag == "bildrechts")
        return '<button type="button"  class="ed-syntax-icon ed-icon-border ed-'.$tag.' shrink-0" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\', \'|alt=]\',true)"></button>';
     else {
        return '<button type="button" class="ed-syntax-icon ed-icon-border ed-'.$tag.' shrink-0" title="['.$tag.'|...]" onclick="insert_ace(\'['.$tag.'|\', \']\', true)"></button>';
    }
}



// Icons mit benutzerdefinierten Syntaxelementen
function returnUserSyntaxIcons() {
    global $USER_SYNTAX, $CatPage;
    $user_array = $USER_SYNTAX->toArray();
    $content = NULL;
    foreach($user_array as $key => $value) {
        if(array_key_exists($key.'___icon',$user_array)) {
            $inhalt = getUserSyntaxValueDescription($key,$value);
            $user_array[$key.'___icon'] = trim($user_array[$key.'___icon']);
            if(false !== strpos($user_array[$key.'___icon'],FILE_START)
                    and false !== strpos($user_array[$key.'___icon'],FILE_END)) {
                list($cat,$file) = $CatPage->split_CatPage_fromSyntax($user_array[$key.'___icon'],true);
                if($CatPage->exists_File($cat,$file))
                    $content .= '<input class="ed-syntax-user ed-icon-border" type="image" src="'.$CatPage->get_srcFile($cat,$file).'" title="'.$inhalt.'" value="'.$inhalt.'">';
            } else
                $content .= '<button class="ed-syntax-user ed-icon-border" title="'.$inhalt.'" value="'.$inhalt.'">'.$user_array[$key.'___icon'].'</button>';
        }
    }
    return $content;
}

// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnUserSyntaxSelectbox() {
    global $USER_SYNTAX;

    $content = '<select name="usersyntax" class="usersyntaxselectbox" title="'.getLanguageValue("toolbar_usersyntax",true).'">';
    $user_array = $USER_SYNTAX->toArray();
    foreach($user_array as $key => $value) {
        if(array_key_exists($key.'___icon',$user_array)
                or false !== strpos($key,'___icon'))
            continue;
        $inhalt = getUserSyntaxValueDescription($key,$value);
        $content .= '<option value="'.$inhalt.'">'.$inhalt.'</option>';
    }
    $content .= "</select>";
    return $content;
}

function getUserSyntaxValueDescription($key,$value) {
    if(false !== strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
        return "[".$key."=...|]";
    } elseif(false === strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
        return "[".$key."|...]";
    } elseif(false !== strpos($value,"{DESCRIPTION}") and false !== strpos($value,"{VALUE}")) {
        return "[".$key."=|...]";
    } elseif(false === strpos($value,"{DESCRIPTION}") and false === strpos($value,"{VALUE}")) {
        return (strlen($value) == 0) ? "[".$key."|...]" : "[".$key."]";
    }
    return NULL;
}

// Selectbox mit allen benutzerdefinierten Syntaxelementen
function returnPlatzhalterSelectbox() {
    global $specialchars;
    global $activ_plugins;
    $all = false;
    if(ACTION == "template" or ACTION == "config")
        $all = true;
    $selectbox = '<select name="platzhalter" class="overviewselect" title="'.getLanguageValue("toolbar_platzhalter",true).'">';
    if(ACTION == "config") {
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_VALUE",true).'" value="{VALUE}">{VALUE}</option>';
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_DESCRIPTION",true).'" value="{DESCRIPTION}">{DESCRIPTION}</option>';
    }
    foreach(makePlatzhalter($all) as $value) {
        $language = str_replace(array('{','}'),'',$value);
        if(in_array($language,$activ_plugins))
            continue;
        $selectbox .= '<option title="'.getLanguageValue("toolbar_platzhalter_".$language,true).'" value="'.$value.'">'.$value.'</option>';
    }
    $selectbox .= '</select>';
    return $selectbox;
}

// Selectbox mit allen Plugin Platzhaltern die nichts mit dem Template zu tun haben
function returnPluginSelectbox() {
    global $specialchars;
    global $activ_plugins;
    require_once(BASE_DIR_CMS."Plugin.php");
    $selectbox = '<select name="plugins" class="overviewselect" title="'.getLanguageValue("toolbar_plugins",true).'">';
    foreach($activ_plugins as $currentplugin) {
        if(file_exists(PLUGIN_DIR_REL.$currentplugin."/index.php") and file_exists(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php")) {
            require_once(PLUGIN_DIR_REL.$currentplugin."/index.php");
            $plugin = new $currentplugin();
            $plugin_info = $plugin->getInfo();
            // Plugin nur in der Auswahlliste zeigen, wenn es aktiv geschaltet ist
            $plugin_conf = new Properties(PLUGIN_DIR_REL.$currentplugin."/plugin.conf.php");
            if(isset($plugin_info[5]) and is_array($plugin_info[5])) {
                foreach($plugin_info[5] as $platzh => $info) {
                    $platzh = $specialchars->rebuildSpecialChars($platzh, false, true);
                    $platzhtext = str_replace("|}","|...}",$platzh);
                    $selectbox .= '<option title="'.$specialchars->rebuildSpecialChars($info, false, true).'" value="'.$platzh.'">'.$platzhtext.'</option>';
                }
            }
        }
    }
    $selectbox .= "</select>";
    return $selectbox;
}

// Smiley-Liste
function returnSmileyBar() {
    require_once(BASE_DIR_CMS."Smileys.php");
    $smileys = new Smileys(BASE_DIR_CMS."smileys");
    $content = "";
    foreach($smileys->getSmileysArray() as $icon => $emoticon) {
        $icon = trim($icon);
        $content .= '<button type="button" class="ed-smileys-icon" onclick="insert_ace(\' :'.$icon.': \', \'\',false)">'.$emoticon.'</button>';
    }
    return $content;
}

function returnCatPagesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="pages" class="overviewselect" title="'.getLanguageValue("category_button",true)." &#047; ".getLanguageValue("page_button",true).':">';
    foreach ($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        $optgroup = "";
        foreach($CatPage->get_PageArray($catdir, array(EXT_PAGE,EXT_HIDDEN), true) as $file) {
            $cleanpagename = $CatPage->get_HrefText($catdir,$file);
            $label = NULL;
            if ($CatPage->get_Type($catdir,$file) == EXT_HIDDEN)
                $label = " (".getLanguageValue("page_saveashidden").")";
            $optgroup .= '<option value="'.$cleancatname.":".$cleanpagename.'">'.$cleanpagename.$label."</option>";
        }
        if(!empty($optgroup))
            $select .= '<optgroup label="'.$cleancatname.'">'.$optgroup.'</optgroup>';
    }
    $select .= "</select>";
    return $select;
}
function returnFilesSelectbox() {
    global $specialchars;
    global $CatPage;

    $select = '<select name="files" class="overviewselect" title="'.getLanguageValue("files_button",true).':">';
    foreach($CatPage->get_CatArray(true,false) as $catdir) {
        $cleancatname = $CatPage->get_HrefText($catdir,false);
        $optgroup = "";
        foreach($CatPage->get_FileArray($catdir) as $current_file) {
            $optgroup .= '<option value="'.$cleancatname.":".$specialchars->rebuildSpecialChars($current_file, true, true).'">'.$specialchars->rebuildSpecialChars($current_file, false, true)."</option>";
        }
        if(!empty($optgroup))
            $select .= '<optgroup label="'.$cleancatname.'">'.$optgroup.'</optgroup>';
    }
    $select .= "</select>";
    return $select;
}

function returnGalerySelectbox() {
    global $specialchars;
    $select = '<select name="gals" class="overviewselect" title="'.getLanguageValue("gallery_button",true).':">';
    $galleries = getDirAsArray(GALLERIES_DIR_REL,"dir","natcasesort");
    foreach ($galleries as $currentgallery) {
        $select .= '<option value="'.$specialchars->rebuildSpecialChars($currentgallery, false, false).'">'.$specialchars->rebuildSpecialChars($currentgallery, false, true)."</option>";
    }
    $select .= "</select>";
    return $select;
}

function returnTemplateSelectbox() {
    global $CMS_CONF;
    global $specialchars;

    $LAYOUT_DIR = BASE_DIR.LAYOUT_DIR_NAME."/".$CMS_CONF->get("cmslayout").'/';

    $selectbox = '<select name="template_css" class="overviewselect" title="'.getLanguageValue("toolbar_template",true).':">';
    $selectbox .= '<optgroup label="'.getLanguageValue("toolbar_template_css",true).'">';
    foreach(getDirAsArray($LAYOUT_DIR.'css',array(".css"),"natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/css/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }
    $selectbox .= '</optgroup>';
    $selectbox .= '<optgroup label="'.getLanguageValue("toolbar_template_image",true).'">';

    foreach(getDirAsArray($LAYOUT_DIR.'grafiken',"img","natcasesort") as $file) {
        $selectbox .= '<option value="{LAYOUT_DIR}/grafiken/'.$specialchars->replaceSpecialChars($file,true).'">'.$specialchars->rebuildSpecialChars($file, false, true).'</option>';
    }

    $selectbox .= '</optgroup>';
    $selectbox .= "</select>";
    return $selectbox;
}

?>