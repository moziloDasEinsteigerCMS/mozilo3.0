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

$html = '<!DOCTYPE html>'."\n"
        .'<html lang="de">'."\n";
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
    .'<div class="header-right flex">'."\n";
     if($DocuClass->docu_writer)
        $html .= $DocuClass->makeDocuLink('<img class="mo-icons-icon mo-icons-help" src="'.BASE_URL_DOCU.'admin/gfx/clear.gif" alt="">',$DocuClass->docu_writer,false,false,true);
                $langs = $DocuClass->getLanguages();
    if(count($langs) > 1) {
        $html .= '<div class="do-lang flex">';
        foreach($langs as $img_lang) {
            $cssaktiv = " mo-ui-state-hover";
            if($img_lang == $DocuClass->curent_lang)
                $cssaktiv = ' ui-tabs-selected ui-state-active';
        $html .= '<div class="ui-state-default ui-corner-all'.$cssaktiv.'">'.$DocuClass->makeDocuLink('<img src="'.BASE_URL_DOCU.'css/do_flags/'.$img_lang.'.png" alt="">',$DocuClass->artikel,$DocuClass->subartikel,$img_lang).'</div>';
        }
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
    $html .= '<div id="do-box">'."\n";
    $html .= '<div id="do-content" class="docu admin-main ui-tabs mo-ui-tabs">'.$DocuClass->makeSubMenu().$DocuClass->docu_artikel.'</div>';
}
$html .= '</div>'."\n";
$html .= '</body>'."\n";
$html .= '</html>';

header('content-type: text/html; charset='.CHARSET.'');

echo $html;

?>