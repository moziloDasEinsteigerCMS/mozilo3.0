<?php

define("CHARSET","utf-8");
define("DOCU_DIR_NAME","docu");
define("DOCU_PHP","index.php");
$URL_BASE = substr($_SERVER['SCRIPT_NAME'],0,strpos($_SERVER['SCRIPT_NAME'],DOCU_DIR_NAME."/".DOCU_PHP));
$URL_BASE = htmlentities($URL_BASE,ENT_COMPAT,CHARSET);
define("URL_BASE",$URL_BASE);
unset($URL_BASE);
# fals da bei winsystemen \\ drin sind in \ wandeln
$BASE_DIR = str_replace("\\\\", "\\",__FILE__);
# zum schluss noch den teil denn wir nicht brauchen abschneiden
$BASE_DIR = substr($BASE_DIR,0,-(strlen(DOCU_DIR_NAME."/".DOCU_PHP)));
define("BASE_DIR",$BASE_DIR);
unset($BASE_DIR);

if(is_readable(BASE_DIR.DOCU_DIR_NAME."/docuClass.php")) {
    include_once(BASE_DIR.DOCU_DIR_NAME."/docuClass.php");
    $DocuClass = new moziloDocuClass();
} else
    exit("Fatal Error Can't read file: docuClass.php");
    
#$DocuClass->docu_artikel = $docu_error;
if(false !== ($html = $DocuClass->makeDocuArtikel()))
    exit($html);
    
$do_lang = array();
$do_lang['deDE'] = 'Deutsch';
$do_lang['enEN'] = 'English';
$do_lang['frFR'] = 'Français';
$do_lang['esES'] = 'Español';
$do_lang['itIT'] = 'Italiano';
$do_lang['nlNL'] = 'Nederlands';
$do_lang['plPL'] = 'Polski';
$do_lang['daDK'] = 'Dansk';
$do_lang['ptBR'] = 'Português';
$do_lang['hrHR'] = 'Hrvatski';

$html = '<!DOCTYPE html>'."\n"
        .'<html lang="'.$DocuClass->getDocuLanguage('do_lang').'">'."\n";       
$html .= '<head>'."\n";
$html .= '<meta charset="'.CHARSET.'">'."\n";
$html .= '<meta name="viewport" content="width = device-width, initial-scale = 1.0">'."\n";
$html .= '<meta name="robots" content="noindex">'."\n";
$html .= '<title>'.$DocuClass->getDocuLanguage('do_title').'</title>'."\n";

$html .= $DocuClass->getDocuHead();

$css = ' do-body-nodialog';
if($DocuClass->dialog)
    $css = ' do-body-dialog';

$html .= '</head>'."\n";
$html .= '<body class="ui-widget'.$css.'">'."\n";

if(!$DocuClass->dialog) {
    $html .= '<div id="do-box" class="admin-container">'."\n";
    $html .= '<div class="header">'."\n"
        .'<div class="header-left">'."\n"
        .'<img src="'.BASE_URL_DOCU.'css/images/mozilo-logo-24.webp" alt="moziloCMS Logo">'."\n"
        .'<span class="mr mo-bold">'.$DocuClass->getDocuLanguage("do_title").'</span>'."\n"
        .'</div>'."\n"
    .'<div class="header-center">'.$DocuClass->getDocuLanguage('do_websitetitle').'</div>'."\n"
    .'<div class="header-right flex">'."\n";
     if($DocuClass->docu_writer)
        $html .= $DocuClass->makeDocuLink('<img class="mo-icons-icon mo-icons-help" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="">',$DocuClass->docu_writer,false,false,true);
                $langs = $DocuClass->getLanguages();
    if(count($langs) > 1) {
        $html .= '<div id="do-lang">';
        $html .= '<div class="dropdown" data-open="false">';
        $html .= '<button class="dropdown-button" aria-haspopup="true" aria-expanded="false">';
        $img_lang = $DocuClass->curent_lang; 
        $html .='<span><img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="'.$do_lang[$img_lang].'" width="16" height="11">'.$do_lang[$img_lang].'</span>';
        $html .= '<span class="dropdown-button-arrow" ><svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"></path></svg></span>';
        $html .= '</button>';
        $html .='<ul class="dropdown-menu slide" role="menu" aria-hidden="true">';
        foreach($langs as $img_lang) {
            $cssaktiv = " mo-ui-state-hover";
            if($img_lang == $DocuClass->curent_lang)
                $cssaktiv = ' ui-tabs-selected ui-state-active';
        $html .= '<li class="dropdown-menu-item ui-state-default ui-corner-all'.$cssaktiv.'" tabindex="-1" role="menuitem">'.$DocuClass->makeDocuLink('<img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="'.$do_lang[$img_lang].'">'.$do_lang[$img_lang].'',$DocuClass->artikel,$DocuClass->subartikel,$img_lang).'</li>';
        }
        $html .='</ul>';

        $html .= '</div>';
        $html .= '</div>';
    }
        $html .= '<span class="nav-wrapper">
  						<label class="menu-icon" for="menu-btn"><span class="navicon"></span></label>
                  </span>'
        			.'</div>';
    $html .= '</div>'
        .$DocuClass->makeDocuMenu()
        .'<div id="do-content" class="docu admin-main ui-widget-content ui-corner-bottom mo-no-border-top"><div class="do-tabs">'.$DocuClass->makeSubMenu().'</div>'.$DocuClass->docu_artikel.'</div>';
} else {
	     if($DocuClass->docu_writer)
        $html .= $DocuClass->makeDocuLink('<img class="mo-icons-icon mo-icons-help" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="">',$DocuClass->docu_writer,false,false,true);
                $langs = $DocuClass->getLanguages();
				
		$html .= '<div class="header card">';
		$html .= '<div class="header-left">';
		$html .= $DocuClass->makeDocuLink('<img class="mo-td-middle mr ml" src="'.BASE_URL_DOCU.'css/images/mozilo-logo-24.webp" alt="moziloCMS Logo" title="'.$DocuClass->getDocuLanguage('do_home').'">',$DocuClass->docu_writer);
		$html .= '</div>';
		$html .= '<div class="header-right">';
				
    if(count($langs) > 1) {
        $html .= '<div id="do-lang">';
        $html .= '<div class="dropdown" data-open="false">';
        $html .= '<button class="dropdown-button" aria-haspopup="true" aria-expanded="false">';
        $img_lang = $DocuClass->curent_lang; 
        $html .='<span><img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="'.$do_lang[$img_lang].'" width="16" height="11">'.$do_lang[$img_lang].'</span>';
        $html .= '<span class="dropdown-button-arrow" ><svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"></path></svg></span>';
        $html .= '</button>';
        $html .='<ul class="dropdown-menu slide" role="menu" aria-hidden="true">';
        foreach($langs as $img_lang) {
            $cssaktiv = " mo-ui-state-hover";
            if($img_lang == $DocuClass->curent_lang)
                $cssaktiv = ' ui-tabs-selected ui-state-active';
        $html .= '<li class="dropdown-menu-item ui-state-default ui-corner-all'.$cssaktiv.'" tabindex="-1" role="menuitem">'.$DocuClass->makeDocuLink('<img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="'.$do_lang[$img_lang].'">'.$do_lang[$img_lang].'',$DocuClass->artikel,$DocuClass->subartikel,$img_lang).'</li>';
        }
        $html .='</ul>';

        $html .= '</div>';
        $html .= '</div>';
    }
	
	$html .= '</div>';
	$html .= '</div>';
	
    $html .= '<div id="do-box">'."\n";
    $html .= '<div id="do-content" class="docu admin-main ui-tabs mo-ui-tabs">'.$DocuClass->makeSubMenu().$DocuClass->docu_artikel.'</div>';
}
$html .= '</div>'."\n";
$html .= '</body>'."\n";
$html .= '</html>';

header('content-type: text/html; charset='.CHARSET.'');

echo $html;

?>