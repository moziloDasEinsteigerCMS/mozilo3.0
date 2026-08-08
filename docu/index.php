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
$html .= '<script src="'.BASE_URL_DOCU.'jquery/theme.js" ></script>'."\n";
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
    $html .= '<header class="header">'."\n"
        .'<div class="header-left">'."\n"
        .'<img src="'.BASE_URL_DOCU.'css/images/mozilo-logo-24.webp" alt="moziloCMS Logo">'."\n"
        .'<span class="mr mo-bold">'.$DocuClass->getDocuLanguage("do_title").'</span>'."\n"
        .'</div>'."\n"
    .'<div class="header-center">'.$DocuClass->getDocuLanguage('do_websitetitle').'</div>'."\n"
    .'<div class="header-right flex">'."\n"
    .'<button class="theme-toggle mr" id="theme-toggle" aria-label="'.$DocuClass->getDocuLanguage("admin_theme_toggle",true).'" aria-pressed="false">
            <svg class="sun-and-moon" aria-hidden="true" width="24" height="24" viewBox="0 0 24 24">
              <mask class="moon" id="moon-mask">
                <rect x="0" y="0" width="100%" height="100%" fill="white" />
                <circle cx="24" cy="10" r="6" fill="black" />
              </mask>
              <circle class="sun" cx="12" cy="12" r="6" mask="url(#moon-mask)" fill="currentColor" />
              <g class="sun-beams" stroke="currentColor" aria-hidden="true">
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
     if($DocuClass->docu_writer)
        $html .= $DocuClass->makeDocuLink('<img class="mo-icons-icon mo-icons-help" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="">',$DocuClass->docu_writer,false,false,true);
                $langs = $DocuClass->getLanguages();
    if(count($langs) > 1) {
        $html .= '<div id="do-lang">';
        $html .= '<div class="dropdown" data-open="false">';
        $html .= '<button class="dropdown-button" aria-haspopup="true" aria-expanded="false">';
        $img_lang = $DocuClass->curent_lang; 
        $html .='<span><img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="" width="16" height="11">'.$do_lang[$img_lang].'</span>';
        $html .= '<span class="dropdown-button-arrow" ><svg viewBox="0 0 24 24"><path d="M7.41 8.59L12 13.17l4.59-4.58L18 10l-6 6-6-6 1.41-1.41z"></path></svg></span>';
        $html .= '</button>';
        $html .='<ul class="dropdown-menu slide" role="menu" aria-hidden="true">';
        foreach($langs as $img_lang) {
            $cssaktiv = " mo-ui-state-hover";
            if($img_lang == $DocuClass->curent_lang)
                $cssaktiv = ' ui-tabs-selected ui-state-active';
        $html .= '<li class="dropdown-menu-item ui-state-default ui-corner-all'.$cssaktiv.'" tabindex="-1" role="menuitem">'.$DocuClass->makeDocuLink('<img class="mr" src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="">'.$do_lang[$img_lang].'',$DocuClass->artikel,$DocuClass->subartikel,$img_lang).'</li>';
        }
        $html .='</ul>';

        $html .= '</div>';
        $html .= '</div>';
    }
        $html .= '<span class="nav-wrapper">
  						<label class="menu-icon" for="menu-btn"><span class="navicon" aria-hidden="true"></span>
    <span class="sr-only">Menü öffnen</span></label>
                  </span>'
        			.'</div>';
    $html .= '</header>'
        .$DocuClass->makeDocuMenu()
        .'<main id="do-content" class="docu admin-main ui-widget-content ui-corner-bottom mo-no-border-top"><div class="do-tabs">'.$DocuClass->makeSubMenu().'</div>'.$DocuClass->docu_artikel.'</main>';
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
    $html .= '<main id="do-content" class="docu admin-main ui-tabs mo-ui-tabs">'.$DocuClass->makeSubMenu().$DocuClass->docu_artikel.'</main>';
}
$html .= '</div>'."\n";
$html .= '</body>'."\n";
$html .= '</html>';

header('content-type: text/html; charset='.CHARSET.'');

echo $html;

?>