<?php
/*

------------------------------------------
fertig:
------------------------------------------

- install.php ins root geben
- wenn nötig recht zurückgeben sonst nicht

dirs setzen:
- admin
- cms
- plugin
R- admin/conf
R- cms/conf
R- kategorien, layouts, galerien, plugin/*.conf.php

------------------------------------------
todo:
------------------------------------------


- test von install/ ordner mit einbeziehen
- nach passwort absenden die conf schreiben und paswort setzen
- erzeugen conf funktionen, mit echo vorerst mal einbinden

*/

$debug = false;
if(isset($_POST['debug']) or isset($_GET['debug']))
    $debug = true;
if(isset($_GET['debug']) and $_GET['debug'] == "false")
    $debug = false;

define('TEST',true);
define('IS_CMS',true);
define('IS_ADMIN',true);
define('IS_INSTALL',true);
#!!!!!!!! prüfen ob vorhanden gegebenfals ermiteln????????
define("ADMIN_DIR_NAME","admin");
define('CMS_DIR_NAME','cms');

if(strtolower(substr("PHP_OS",0,3)) == "win")
    define("USE_CHMOD", false);
else
    define("USE_CHMOD", true);

// falls da bei winsystemen \\ drin sind in \ wandeln
$base_dir = str_replace("\\\\", "\\", __FILE__);
// zum schluss noch den teil denn wir nicht brauchen abschneiden
$base_dir = substr($base_dir, 0, -(strlen("install.php")));
define("BASE_DIR", $base_dir);
unset($base_dir); // verwendung im script verhindern

$name_id = 'MOZILOID_'.md5($_SERVER['SERVER_NAME'].BASE_DIR);
define("SESSION_MO",$name_id);
unset($name_id);

$LANG_INSTALL = array();
$LANG_INSTALL['deDE'] = 'Deutsch';
$LANG_INSTALL['enEN'] = 'English';
$LANG_INSTALL['frFR'] = 'Français';
$LANG_INSTALL['esES'] = 'Español';
$LANG_INSTALL['itIT'] = 'Italiano';
$LANG_INSTALL['nlNL'] = 'Nederlands';
$LANG_INSTALL['plPL'] = 'Polski';
$LANG_INSTALL['daDK'] = 'Dansk';
$LANG_INSTALL['ptBR'] = 'Português';
$LANG_INSTALL['hrHR'] = 'Hrvatski';

testInstall();

define("BASE_DIR_ADMIN", BASE_DIR.ADMIN_DIR_NAME."/");

$LANG_TMP = "deDE";
if(isset($_POST['language']) and $_POST['language'] != "false")
    $LANG_TMP = $_POST['language'];

#!!!!!! wenn es schon confs gibt nur password und fertig einblenden
$ADMIN_CONF = false;
$CMS_CONF = false;
# eigene session
if(is_file(BASE_DIR_ADMIN."sessionClass.php")) {
   require_once(BASE_DIR_ADMIN."sessionClass.php");
} else
    @session_name(SESSION_MO);

session_start();
# Default conf
require_once(BASE_DIR.CMS_DIR_NAME."/DefaultConfCMS.php");
# wenn die sachen kein die() oder fatal error ergeben ist es gut
require_once(BASE_DIR_CMS."DefaultFunc.php");
// Properties Class
require_once(BASE_DIR_CMS."Properties.php");
// Language Class
require_once(BASE_DIR_CMS."Language.php");

require_once(BASE_DIR_ADMIN."filesystem.php");
require_once(BASE_DIR_CMS."SpecialChars.php");
$specialchars = new SpecialChars();

foreach($LANG_INSTALL as $lang => $tmp) {
    if(!is_file(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$lang.".txt"))
        unset($LANG_INSTALL[$lang]);
}
if(is_file(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php') and isFileRW(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php'))
    $ADMIN_CONF = new Properties(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php');
if(($ADMIN_CONF !== false)
        and (!isset($_POST['language']) or $_POST['language'] == "false")
        and (is_file(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$ADMIN_CONF->get('language').".txt")))
    $LANG_TMP = $ADMIN_CONF->get('language');
$LANG = new Language(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$LANG_TMP.".txt");

if(is_file(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php') and isFileRW(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php')) {
    $CMS_CONF = new Properties(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php');
    setTimeLocale($LANG);
}

ini_set("default_charset", CHARSET);
header('content-type: text/html; charset='.strtolower(CHARSET));

$steps = array("language","help","chmod_test","environment","rewrite","password","finish");

$html_check_update = '';
if(is_file("update.php")) {
    require_once("update.php");
    $steps = array("language","help","chmod_test","environment","rewrite","password","update","finish");
    if(function_exists("testUpdate") and testUpdate(true)) {
        if(!isset($_POST['check_update']))
            $html_check_update = '<input type="hidden" name="check_update" value="true">';
        if(isset($_POST['check_update']) and $_POST['check_update'] == "true")
            $html_check_update = '<input type="hidden" name="check_update" value="'.$_POST['check_update'].'">';
    }
}

$current_step = $steps[0];
if(isset($_POST["current_step"]) and in_array($_POST["current_step"],$steps))
    $current_step = $_POST["current_step"];

if(isset($_POST["only"]) and $current_step == $steps[0]) {
    unset($_POST["only"],$_POST["finish_steps"]);
}
#!!!!!! nur für die testphase
if(isset($_POST["reset"])) {
    $current_step = $steps[0];
    unset($_POST);
}

if(isset($_POST['getonlypassword'])) {
    $_POST["only"] = "password";
    $_POST["finish_steps"] = "help,password,finish";
    if(isset($_POST['getonlypassword']))
        $current_step = "password";
}

if(in_array("update",$steps) and isset($_POST['getonlyupdate'])) {
    $_POST["only"] = "update";
    $_POST["finish_steps"] = "help,update,finish";
    if(isset($_POST['getonlyupdate']))
        $current_step = "update";
}

$clean = false;
if($current_step === "finish" and isset($_POST['clean_finish'])) {
    $clean = cleanUpUpdate();
}

if(function_exists($current_step))
    list($status,$html_step) = $current_step();

echo getHtml("start",$current_step);
echo menu_tabs($steps,$current_step,$status);
echo $html_check_update;
echo '<div class="admin-main install">';


if($clean) {
    echo $clean;
}

echo $html_step;

if($debug) {
    echo '<input type="submit" name="reset" value="Reset">';
    echo '<input type="hidden" name="debug" value="true">';
}
echo '</div>'."\n";


if($debug) {
    echo "current_step=".$current_step."<br>";
    echo "<pre>";
    echo "steps ";
    print_r($steps);
    if(isset($_POST)) {
        echo "<br>POST ";
        print_r($_POST);
    }
    echo "</pre>";
}

echo getHtml("end").'</body>'."\n"
.'</html>';

// -----------------------------------------------------------------------------
// Funktionen
// -----------------------------------------------------------------------------

function testInstall() {
    if(!is_file(BASE_DIR.CMS_DIR_NAME."/Language.php") and !is_file(BASE_DIR.ADMIN_DIR_NAME."/sessionClass.php"))
        exit("Du musst das CMS schon mit FTP hochladen!");

    if(!is_readable(ADMIN_DIR_NAME) and !is_readable(CMS_DIR_NAME) and !is_readable(BASE_DIR.CMS_DIR_NAME."/Language.php") and !is_readable(BASE_DIR.ADMIN_DIR_NAME."/sessionClass.php"))
        exit("Die Rechtevergabe von Deinem Provider ist echt bescheiden.");
}

function help() {
    $status = true;
    $php_version = "";
    $only = "";
    // Zeile "PHP-Version"
    if(version_compare(PHP_VERSION, MIN_PHP_VERSION) < 0) {
        $status = false;
        $php_version = contend_template(getLanguageValue("install_help_php_error",phpversion(),MIN_PHP_VERSION),false);
    }
    if($status) {
        $deact = '';
        $box_status = true;
        $text_deact = '';
        if(!is_file(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php') or !is_file(BASE_DIR_ADMIN.CONF_DIR_NAME.'/basic.conf.php')) {
            $deact = ' disabled';
            $box_status = false;
            $text_deact = '<p class="mo-bold">'.getLanguageValue("install_help_no_button").'</p>';
        }
        $only = contend_template($text_deact.'<p>'.getLanguageValue("install_help_password").'</p><input type="submit" class="button" name="getonlypassword" value="'.getLanguageValue("install_help_password_button").'"'.$deact.'>',$box_status);
        if(is_file('update.php'))
            $only .= contend_template($text_deact.'<p>'.getLanguageValue("install_help_update").'</p><input type="submit" class="button" name="getonlyupdate" value="'.getLanguageValue("install_help_update_button").'"'.$deact.'>',$box_status);
    }
    $update_text = getLanguageValue("install_help_update_title");
    $update_text = '<details><summary><span class="flex ml">'.getLanguageValue("install_toggle_open").'</span></summary>'.getLanguageValue("install_help_update_text").'</details>';

    return array($status,$php_version.contend_template('<div class="c-header">'.getLanguageValue("install_help_title").'</div>'.getLanguageValue("install_help").$update_text).$only);
}


function language() {
    $html1 = getLanguageValue("install_lang_select");
    $html2 = getLanguageSelect();
    return array(true,contend_template('<div class="c-header">'.getLanguageValue("install_lang_help_title").'</div>'.installHelp("install_lang_help"),"")
                .contend_template(array($html1,$html2),""));
}

function chmod_test() {
#return array(true,"");
    $status = false;
    $no_chmod = getLanguageValue("install_chmod_no_chmod");
    $help = contend_template('<div class="c-header">'.getLanguageValue("install_chmod_help_title").'</div>'.installHelp("install_chmod_help"),"");
    $file_test = BASE_DIR."test_install.txt";

    if(!isset($_POST['chmod_test']) or $_POST['chmod_test'] == "false") {
        # die mit FTP Hochgeladen Daterechte Prüfen
        if(!isFileRW(BASE_DIR) or !isFileRW(BASE_DIR.ADMIN_DIR_NAME)) {
            $html = contend_template(getLanguageValue("install_chmod_change_ftp").'<input class="button" type="submit" name="chmod_ftp_change" value="'.getLanguageValue("install_chmod_change_ftp_button").'">',"");
            $chmod = "false";
        # wir ermitel die Dateirechte von PHP Angelegten Dateien
        } else {
            if(!is_file($file_test))
                file_put_contents($file_test, "chmod test");
            if(fileowner($file_test) == fileowner(BASE_DIR."install.php")) {
                $html = contend_template(getLanguageValue("install_chmod_use",$no_chmod),true);
                $html .= '<input type="hidden" name="chmod_test" value="">';
                if(is_file($file_test))
                    unlink($file_test);
                return array(true,$help.$html);
            }
            # chmod um 1 erhöhen
            if(isset($_POST['chmod_testfile']) and $_POST['chmod_testfile'] == getLanguageValue("install_chmod_testfile_next_button")) {
                setInstallChmod($file_test,getNextChmod($file_test));
            }
            clearstatcache();
            $html = contend_template(array(getLanguageValue("install_chmod_testfile_rw",basename($file_test),substr(decoct(fileperms($file_test)),-3)),'<input type="submit" class="button" name="chmod_testfile" value="'.getLanguageValue("yes").'">'.'<input type="submit" class="button" name="chmod_testfile" value="'.getLanguageValue("install_chmod_testfile_next_button").'">'),"");
            $chmod = "false";
            # das ist jetzt der chmod wert den wir benutzen müssen
            if(isset($_POST['chmod_testfile']) and $_POST['chmod_testfile'] == getLanguageValue("yes")) {
#!!!!!! chmode hier anwenden auf alle relewanten dateien??????????
                clearstatcache();
                $chmod = substr(decoct(fileperms($file_test)),-3);
                if(is_file($file_test))
                    unlink($file_test);
                $html = contend_template(getLanguageValue("install_chmod_use",$chmod),true);
                $status = true;
            }
        }
    } else {
        if(is_file($file_test))
            unlink($file_test);
        $chmod = $_POST['chmod_test'];
        $chmod_text = $chmod;
        $status = true;
        if($chmod == "" or $chmod === "false")
            $chmod_text = $no_chmod;
        $html = contend_template(getLanguageValue("install_chmod_use",$chmod_text),true);
    }
    $html .= '<input type="hidden" name="chmod_test" value="'.$chmod.'">';
    return array($status,$help.$html);
}

function environment() {
    $html_ret = "";
    $status = true;

    // conf dateien anlegen
    $conf = makeConfFiles();
    if(true === $conf) {
        $html = array(getLanguageValue("install_environment_conf"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    } else {
        $html = array(getLanguageValue("install_environment_conf"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
        $status = false;
    }

    // Zeile "PHP-Version"
    if(version_compare(PHP_VERSION, '5.5.1') >= 0) {
        $html = array(getLanguageValue("home_phpversion_text"),phpversion());
        $html_ret .= contend_template($html,true);
    } else {
        $status = false;
        $html = array(getLanguageValue("home_phpversion_text"),phpversion());
        $html_ret .= contend_template($html,false);
    }

    // Zeile "Safe Mode"
    if(ini_get('safe_mode')) {
        $html = array(getLanguageValue("home_text_safemode")."<br><b>".getLanguageValue("home_error_safe_mode")."</b>",getLanguageValue("yes"));
        $html_ret .= contend_template($html,false);
    } else {
        $html = array(getLanguageValue("home_text_safemode"),getLanguageValue("no"));
        $html_ret .= contend_template($html,true);
    }

    // Zeile "GDlib installiert"
    if(!extension_loaded("gd")) {
        $status = false;
        $html = array(getLanguageValue("home_text_gd"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
    } else {
        $html = array(getLanguageValue("home_text_gd"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    }

    // Zeile session test
    $_SESSION["test"] = "test";
#!!!! das muss geprüft werden ob das so geht
    if(isset($_SESSION["test"]) and $_SESSION["test"] == "test") {
        $html = array(getLanguageValue("install_environment_session"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,true);
    } else {
        $html = array(getLanguageValue("install_environment_session"),getLanguageValue("no"));
        $html_ret .= contend_template($html,false);
        $status = false;
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
        $html = array(getLanguageValue("home_multiuser_mode_text"),$mu_string);
        $html_ret .= contend_template($html,"");
    } else {
        $html = array(getLanguageValue("home_multiuser_mode_text"),getLanguageValue("no"));
        $html_ret .= contend_template($html,"");
    }

    # backupsystem
    if(function_exists('gzopen')) {
        $html = array(getLanguageValue("home_text_backupsystem"),getLanguageValue("yes"));
        $html_ret .= contend_template($html,"");
    } else {
        $html = array(getLanguageValue("home_error_backupsystem"),getLanguageValue("no"));
        $html_ret .= contend_template($html,"");
    }
    // Aktuelles Datum
    if(true === $conf and function_exists('date_default_timezone_get')) {
        global $CMS_CONF,$LANG;
        $CMS_CONF = new Properties(BASE_DIR_CMS.CONF_DIR_NAME.'/main.conf.php');
        setTimeLocale($LANG);
        $time_zone = @date_default_timezone_get();
    } else
        $time_zone = date("T");
    $html = array(getLanguageValue("home_date_text"),date("Y-m-d H.i.s")." ".$time_zone);
    $html_ret .= contend_template($html,"");


    if($status)
        $html_ret .= '<input type="hidden" name="environment" value="true">';
    $help = contend_template('<div class="c-header">'.getLanguageValue("install_environment_help_title").'</div>'.installHelp("install_environment_help"),"");
    return array($status,$help.$html_ret);
}

function rewrite() {
    global $CMS_CONF;
#!!!!!!!!!! mod_rewrite erst nach botton start starten oder mit botton notest übergehen
#!!!!!!!!!! textaera für provider specifisches einbauen
#if(isset($_POST['start_rewrite']))
#if(isset($_POST['nostart_rewrite']))
    # rewrite anfrage von install.js
    if(isset($_POST['fromajax']) and $_POST['fromajax'] == "true") {
        if(isset($_POST['modconf'])) {
            writeHtaccess("test",$_POST['modconf']);
        }
        echo '<span id="return-modconf">&nbsp;</span>';
        exit();
    }

    # rewrite wurde schon ausgeführt
    $rewrite_step = false;
    if(isset($_POST['rewrite']) and $_POST['rewrite'] != "false") {
        $rewrite_step = $_POST['rewrite'];
    }

    $status = true;
    $text_status = "";
    $input = "";
    if($rewrite_step === false) {
        $status = false;
        $rewrite_step = "false";

        if(!is_dir(BASE_DIR.'install')) {
            mkdir(BASE_DIR.'install');
        }
        if(!is_file(BASE_DIR.'install/test.php')) {
            $test_datei = '<?php sleep(2); if (isset($_GET["rewritetest"]) and $_GET["rewritetest"] == "true") echo \'<span id="mod-rewrite-true">&nbsp;</span>\'; ?>';
            file_put_contents(BASE_DIR.'install/test.php', $test_datei);
        }

        $html = '<img style="margin-right:2em;" src="'.URL_BASE.CMS_DIR_NAME.'/jquery/ajax-loader.gif">'
        .getLanguageValue("install_rewrite_test_text",'<span id="step-mod-conf">0</span>',writeHtaccess("test",0,true))
        .'<script language="Javascript" type="text/javascript">/*<![CDATA[*/'
        .'var finish_test = false;'
        .'var max_step = '.writeHtaccess("test",0,true).';'
        .'/*]]>*/</script>';
    } else {
        if(is_dir(BASE_DIR.'install')) {
            if(is_file(BASE_DIR.'install/test.php'))
                unlink(BASE_DIR.'install/test.php');
            if(is_file(BASE_DIR.'install/.htaccess'))
                unlink(BASE_DIR.'install/.htaccess');
            rmdir(BASE_DIR.'install');
        }
        if($rewrite_step == "no_modrewrite") {
            $html = getLanguageValue("install_rewrite_no");
            $text_status = false;
            if(is_file(BASE_DIR.'.htaccess'))
                $html .= '<br>'.getLanguageValue("install_rewrite_no_htaccess");
            if(is_file(BASE_DIR_ADMIN.'.htaccess'))
                unlink(BASE_DIR_ADMIN.'.htaccess');
            $CMS_CONF->set("modrewrite","false");
        } else {
            $html = '<p>'.getLanguageValue("install_rewrite_yes").'</p>';
            $text_status = true;
            writeHtaccess("cms",$rewrite_step);
            $CMS_CONF->set("modrewrite","true");
        }
        $input = '<input type="hidden" name="rewrite" value="'.$rewrite_step.'">';
    }

    $help = contend_template('<div class="c-header">'.getLanguageValue("install_rewrite_help_title").'</div>'.installHelp("install_rewrite_help"),"");
    $html_ret = contend_template($html,$text_status);
    return array($status,$help.$html_ret.$input);
}

function password() {
    $html = "";
    $status = false;

    $form_errmsg = ""; // buffer für fehlermeldungen
    $form_username  = "";

    // form abgesendet, inhalte prüfen
    if(isset($_POST['pw_submit'])) {
        if((!isset($_POST['username'])
                or !isset($_POST['password1'])
                or !isset($_POST['password2']))
            or (empty($_POST['username'])
                or empty($_POST['password1'])
                or empty($_POST['password2'])
            )
            ) {
            $form_errmsg .= '<p>'.getLanguageValue("pw_error_missingvalues").'</p>';
        }
        if(empty($form_errmsg))
            $form_username  = $_POST['username'];
       //username darf nur Zahlen, Buchstaben, Bindestrich, Unterstrich haben
         if(!preg_match("/^([a-zA-Z0-9\s\_\-]+)$/",$_POST['username'])){
         	$form_errmsg .= '<p>'.getLanguageValue("pw_error_invalidname").'</p>';
}
        // username muss mind. 5 zeichen haben
        if(strlen($_POST['username']) < 5) {
            $form_errmsg .= '<p>'.getLanguageValue("pw_error_tooshortname").'</p>';
        }

        // pw-komplexität check
        if(strlen($_POST['password1']) < 8
             or !preg_match("/[0-9]/", $_POST['password1']) 
             or !preg_match("/[a-z]/", $_POST['password1'])
             or !preg_match("/[A-Z]/", $_POST['password1'])
           ) {
            // pw nicht komplex genug
            $form_errmsg .= '<p>'.getLanguageValue("pw_error_newpwerror").'</p>';
        }

        // stimmen die eingegebenen pw überein?
        if($_POST['password1'] != $_POST['password2']) {
           $form_errmsg .= '<p>'.getLanguageValue("pw_error_newpwmismatch").'</p>';
        }

        // keine fehler, dann daten schreiben
        if(empty($form_errmsg)) {
            $status = true;
            require_once(BASE_DIR.CMS_DIR_NAME.'/PasswordHash.php');
            $t_hasher = new PasswordHash(8, FALSE);
            $pw = $t_hasher->HashPassword($_POST['password1']);
            $loginpassword = new Properties(BASE_DIR.ADMIN_DIR_NAME.'/'.CONF_DIR_NAME."/loginpass.conf.php");
            $loginpassword->set("name", $_POST['username']);
            $loginpassword->set("pw", $pw);
        } else
            $form_errmsg = contend_template($form_errmsg,false);
    }

    $html = getLanguageValue("pw_text_login").' '.getLanguageValue("pw_help")
        .'<div class="card">'
        .'<div class="c-content mo-in-li-r"><div>'.getLanguageValue("pw_titel_newname").'</div><div>'.'<input type="text" class="js-in-pwroot mo-input-text" name="username" value="'.$form_username.'">'.'</div></div>'
        .'<div class="c-content mo-in-li-r"><div>'.getLanguageValue("pw_titel_newpw").'</div><div>'.'<input type="password" class="js-in-pwroot mo-input-text" value="'.NULL.'" name="password1">'.'</div></div>'
        .'<div class="c-content mo-in-li-r"><div>'.getLanguageValue("pw_titel_newpwrepeat").'</div><div>'.'<input type="password" class="js-in-pwroot mo-input-text" value="" name="password2">'.'</div></div>'
        .'<div class="c-content mo-in-li-r"><div></div><div>'.'<input type="submit" class="button" name="pw_submit" value="'.getLanguageValue("button_save").'">'.'</div></div>'
        ."</div>";

    $html_ret = contend_template($html,"");

    if($status) {
        $form_errmsg = getLanguageValue("admin_messages_change_password");
        $form_errmsg .= '<input type="hidden" name="password" value="true">';
        $form_errmsg = contend_template($form_errmsg,true);
    } elseif(isset($_POST['password']) and $_POST['password'] == "true") {
        $status = true;
        $form_errmsg = '<input type="hidden" name="password" value="true">';
    }
    $help = contend_template('<div class="c-header">'.getLanguageValue("install_password_help_title").'</div>'.installHelp("install_password_help"),"");
#$status = true;
    return array($status,$help.$form_errmsg.$html_ret);
}

function finish() {
    $html = "";
    $clean = "install_finish_clean";
    # bei einer localen installation brauchen wir nicht aufräumen
    if((isset($_SERVER['SERVER_ADDR']) and substr($_SERVER['SERVER_ADDR'],0,4) === "127")
        or (isset($_SERVER['SERVER_NAME']) and ($_SERVER['SERVER_NAME'] == "localhost" or substr($_SERVER['SERVER_NAME'],0,3) === "127"))) {
        $clean = "install_finish_local";
    }
    $active = '';
    if(!is_file('install.php'))
        $active = ' disabled="disabled"';

    $html .= contend_template(getLanguageValue($clean).'<input type="submit" class="button" name="clean_finish" value="'.getLanguageValue("install_finish_clean_button").'"'.$active.'>',false);

    $html .= contend_template('<div class="c-header">'.getLanguageValue("install_finish_help_title").'</div>'.installHelp("install_finish_help").'<a class="button" href="'.ADMIN_DIR_NAME.'/index.php">'.getLanguageValue("install_finish_submit").'</a>',"");

    return array(true,$html);
}

function menu_tabs($steps,$current_step,$status) {
    $post_step_status = '';
    $finish_steps = array();
    # es wurden schon tabs erledigt
    if(isset($_POST['finish_steps'])) {
        # die holen wir uns
        $finish_steps = explode(",",$_POST['finish_steps']);
    }

    if(isset($_POST['only'])) {
        $onlypassword = "true";
        $status = false;
        $post_step_status .= '<input type="hidden" name="only" value="'.$_POST['only'].'">'."\n";
    }

    $tabs = '<div class="admin-side install">'."\n"
    .'<ul id="js-menu-tabs" class="nav flex">'."\n";

    foreach($steps as $pos => $step) {
        if(isset($_POST[$step]) and $_POST[$step] != "false")
            $post_step_status .= '<input type="hidden" name="'.$step.'" value="'.$_POST[$step].'">'."\n";
        else
            $post_step_status .= '<input type="hidden" name="'.$step.'" value="false">'."\n";

        $activ = "";
        # ist nicht im finish array dann hidden
        if(!in_array($step,$finish_steps)) {
            $activ = " disabled";
        }
        # der active tab activ setzen
        if($current_step == $step) {
            if(!in_array($step,$finish_steps) and $status)
                $finish_steps[] = $step;
            $activ = " active ui-tabs-selected ui-state-active";
        }
        if($status and count($finish_steps) == $pos) {
            $activ = "";
        }
        $lang_step = getLanguageValue("install_tab_".$step);
        $tabs .= '<li class="flex-100 js-multi-user ui-state-default'.$activ.'">';
        $tabs .= '<a href="install.php" class="step_tabs" title="'.$lang_step.'" name="'.$step.'">'
            .'<span class="mo-install-icon mo-install-'.$step.'"></span><span class="mo-install-text">'.$lang_step.'</span>'
            .'</a>';

        $tabs .= '</li>'."\n";
    }
    $tabs .= '</ul>'."\n";
    $tabs .= '</div>'."\n";
    $tabs .= '<input id="step_input" type="hidden" name="current_step" value="">'."\n";

    $post_finish_steps = '<input type="hidden" name="finish_steps" value="'.implode(",",$finish_steps).'">'."\n";
    return $tabs.$post_step_status.$post_finish_steps;
}


// #############################################################################

function makeConfFiles() {
    if(isset($_POST['environment']) and $_POST['environment'] == "true")
        return true;
    if(version_compare(PHP_VERSION, '5.1.2') < 0)
        return false;

    if(!is_dir(BASE_DIR_ADMIN.CONF_DIR_NAME))
        mkdir(BASE_DIR_ADMIN.CONF_DIR_NAME);
    if(!is_dir(BASE_DIR_CMS.CONF_DIR_NAME))
        mkdir(BASE_DIR_CMS.CONF_DIR_NAME);

    global $page_protect;
    global $ADMIN_CONF;
    global $CMS_CONF;

    require_once(BASE_DIR_ADMIN."default_conf.php");

    $confs = array(
            "basic" => BASE_DIR_ADMIN.CONF_DIR_NAME."/basic.conf.php",
            "logindata" => BASE_DIR_ADMIN.CONF_DIR_NAME."/logindata.conf.php",
            "loginpass" => BASE_DIR_ADMIN.CONF_DIR_NAME."/loginpass.conf.php",
            "gallery" => BASE_DIR_CMS.CONF_DIR_NAME."/gallery.conf.php",
            "main" => BASE_DIR_CMS.CONF_DIR_NAME."/main.conf.php",
            "syntax" => BASE_DIR_CMS.CONF_DIR_NAME."/syntax.conf.php",
            );

    foreach($confs as $name => $dir) {
        $conf = array();
        if($name == "basic" and $ADMIN_CONF !== false)
            $conf = $ADMIN_CONF->toArray();
        elseif($name == "main" and $CMS_CONF !== false)
            $conf = $CMS_CONF->toArray();
        elseif(is_file($dir))
            continue;
        else
            $conf = makeDefaultConf($name,true);
        if($name == "basic") {
            $conf['language'] = $_POST['language'];
            $chmod_test = "";
            if(is_numeric($_POST['chmod_test']) and strlen($_POST['chmod_test']) == 3)
                $chmod_test = $_POST['chmod_test'];
            $conf['chmodnewfilesatts'] = $chmod_test;
        }
        if($name == "main") {
            $conf['cmslanguage'] = $_POST['language'];
#            $rewrite = "false";
#            if($_POST['rewrite'] != "false")
#                $rewrite = "true";
#            $conf['modrewrite'] = $rewrite;
        }
        $conf = $page_protect.serialize($conf);
        if(false === (file_put_contents($dir,$conf,LOCK_EX))) {
            return false;
        }
        if($name == "loginpass")
            @chmod($dir,0600);
    }
    return true;
}

function installHelp($index) {
    return #'<span class="mo-message-erfolg">'
 #   .'<img class="mo-message-icon mo-icons-icon mo-icons-information" src="'.URL_BASE.ADMIN_DIR_NAME.'/gfx/clear.gif" alt="information" />'
    getLanguageValue($index);
  #  '</span>';
}

function getLanguageValue($index,$param1 = '',$param2 = '') {
    global $LANG;
    return str_replace(array("&lt;","&gt;","&quot;"),array("<",">",'"'),$LANG->getLanguageValue($index,$param1,$param2));
}

function writeHtaccess($art,$step,$getcount = false) {

    $base_url = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "install.php"));
    if(strlen($base_url) < 1)
        $base_url = "/";
    $rewrite_base_url     = $base_url.'install/';
    if($art == "cms")
        $rewrite_base_url = $base_url;
#?????????????? siehe http://www.mozilo.de/forum/viewtopic.php?f=8&t=2907&start=30
# php_value session.gc_maxlifetime 100000000000   # nur im admin
# ab PHP Version 5.3.9  php_value max_input_vars 3000
# ab php > 5.2.0 php_value pcre.backtrack_limit 1000000

    $indexes            = 'Options -Indexes';
    $if_module_start_c  = '<IfModule rewrite_module.c>';
    $if_module_start    = '<IfModule rewrite_module>';
    $rewrite_on         = 'RewriteEngine On';
    $rewrite_base       = 'RewriteBase '.$rewrite_base_url;
    $if_module_end      = '</IfModule>';

    $rewrite_rule_test  = 'RewriteRule test\.php$ test\.php?rewritetest=true [QSA,L]';

    $rewrite_rule_cms   = 'RewriteRule ^(.*)/mod_rewrite_t_e_s_t\.html$ $1/index\.php?moderewrite=ok [L]'."\n";
    $rewrite_rule_cms   .= 'RewriteRule \.html$ index\.php [QSA,L]';


    // die verschiedenen test-configs die wir probieren
    // mit -Indexes und ohne <IfModule...>
    $arr_modrewrite_conf[0] = array($indexes,$rewrite_on,${"rewrite_rule_".$art});
    // mit -Indexes und ohne ohne <IfModule...> aber mit RewriteBase
    $arr_modrewrite_conf[1] = array($indexes,$rewrite_on,$rewrite_base,${"rewrite_rule_".$art});
    // mit -Indexes und ohne mit <IfModule...>
    $arr_modrewrite_conf[2] = array($indexes,$if_module_start,$rewrite_on,${"rewrite_rule_".$art},$if_module_end);
    // mit -Indexes und ohne mit <IfModule...c>
    $arr_modrewrite_conf[3] = array($indexes,$if_module_start_c,$rewrite_on,${"rewrite_rule_".$art},$if_module_end);
    // mit -Indexes und ohne mit <IfModule...> mit RewriteBase
    $arr_modrewrite_conf[4] = array($indexes,$if_module_start,$rewrite_on,$rewrite_base,${"rewrite_rule_".$art},$if_module_end);
    // mit -Indexes und ohne mit <IfModule...c> mit RewriteBase
    $arr_modrewrite_conf[5] = array($indexes,$if_module_start_c,$rewrite_on,$rewrite_base,${"rewrite_rule_".$art},$if_module_end);
    // ohne <IfModule...>
    $arr_modrewrite_conf[6] = array($rewrite_on,${"rewrite_rule_".$art});
    // ohne <IfModule...> aber mit RewriteBase
    $arr_modrewrite_conf[7] = array($rewrite_on,$rewrite_base,${"rewrite_rule_".$art});
    // mit <IfModule...>
    $arr_modrewrite_conf[8] = array($if_module_start,$rewrite_on,${"rewrite_rule_".$art},$if_module_end);
    // mit <IfModule...c>
    $arr_modrewrite_conf[9] = array($if_module_start_c,$rewrite_on,${"rewrite_rule_".$art},$if_module_end);
    // mit <IfModule...> mit RewriteBase
    $arr_modrewrite_conf[10] = array($if_module_start,$rewrite_on,$rewrite_base,${"rewrite_rule_".$art},$if_module_end);
    // mit <IfModule...c> mit RewriteBase
    $arr_modrewrite_conf[11] = array($if_module_start_c,$rewrite_on,$rewrite_base,${"rewrite_rule_".$art},$if_module_end);

    if($getcount)
        return count($arr_modrewrite_conf) - 1;

    if(isset($arr_modrewrite_conf[$step])) {
        $base_pfad     = BASE_DIR.'install/';
        $org_htaccess = "";
        $mozilo_start = "# mozilo generated not change from here to mozilo_end";
        $mozilo_end = "# mozilo_end";
        if($art == "cms") {
            $base_pfad = BASE_DIR;
            if(is_file(BASE_DIR.ADMIN_DIR_NAME.'/.htaccess'))
                @unlink(BASE_DIR.ADMIN_DIR_NAME.'/.htaccess');
        }

        if($art == "cms" and is_file($base_pfad.'.htaccess')) {
            if(false !== ($org_htaccess = file($base_pfad.'.htaccess'))) {
                $start_if = false;
                $mozilo_lines = false;
                foreach($org_htaccess as $line_num => $line) {
                    $line = trim($line);
                    $org_htaccess[$line_num] = $line;
                    if($mozilo_lines and $line == $mozilo_end) {
                        $mozilo_lines = false;
                        unset($org_htaccess[$line_num]);
                        continue;
                    }
                    if($mozilo_lines or $line == $mozilo_start) {
                        $mozilo_lines = true;
                        unset($org_htaccess[$line_num]);
                        continue;
                    }
                    if(strpos($line,"#") !== false and strpos($line,"#") < 5)
                        continue;
                    if(strpos($line,"-Indexes") !== false)
                        $org_htaccess[$line_num] = "#mozilo ".$line;
                    if(!$start_if and strpos($line,"<IfModule") !== false and strpos($line,"rewrite_module") !== false) {
                        $start_if = true;
                        $org_htaccess[$line_num] = "#mozilo ".$line;
                    }
                    if($start_if and strpos($line,"</IfModule>") !== false) {
                        $start_if = false;
                        $org_htaccess[$line_num] = "#mozilo ".$line;
                    }
                    if(strpos($line,"Rewrite") !== false)
                        $org_htaccess[$line_num] = "#mozilo ".$line;
                }
                $org_htaccess = implode("\n",$org_htaccess)."\n";
            } else
                $org_htaccess = "";
        }
        file_put_contents($base_pfad.'.htaccess', $org_htaccess.$mozilo_start."\n".implode("\n",$arr_modrewrite_conf[$step])."\n".$mozilo_end."\n");
    }
}

function getNextChmod($file) {
    clearstatcache();
    $file_chmod = substr(decoct(fileperms($file)),-3);
    if(is_dir($file)) {
        if($file_chmod[0] < 7)
            return "7".$file_chmod[1].$file_chmod[2];
        if($file_chmod[1] < 7)
            return "77".$file_chmod[2];
        return "777";
    } else {
        if($file_chmod[0] < 6)
            return "6".$file_chmod[1].$file_chmod[2];
        if($file_chmod[1] < 6)
            return "66".$file_chmod[2];
        return "666";
    }
}

// chmod() setzen und bei dir X-Bit erhöhen
function setInstallChmod($file,$mode) {
#    echo $file."<br>";
    if(is_dir($file)) {
        // X-Bit setzen, um Verzeichniszugriff zu garantieren
        if($mode[0] >= 2 and $mode[0] <= 6) $mode = $mode + 100;
        if($mode[1] >= 2 and $mode[1] <= 6) $mode = $mode + 10;
        if($mode[2] >= 2 and $mode[2] <= 6) $mode = $mode + 1;
    }
    return @chmod($file, octdec($mode));
}

// Datei Les- und Schreibbar?
function isFileRW($file) {
    clearstatcache();
    return (is_readable($file) && is_writeable($file));
}

function cleanUpUpdate() {
    @unlink('install.php');
    if(is_file('update.php')) {
        @unlink('update.php');
    }
    if(is_dir(BASE_DIR.'update') and false !== ($currentdir = opendir(BASE_DIR.'update'))) {
        while(false !== ($file = readdir($currentdir))) {
            if($file == "." or $file == "..") continue;
            @unlink(BASE_DIR.'update/'.$file);
        }
        closedir($currentdir);
        @rmdir(BASE_DIR.'update');
    }
    $tmp = "";
    if(is_file('install.php'))
        $tmp .= 'install.php<br>';
    if(is_file('update.php'))
        $tmp .= 'update.php<br>';
    if(is_dir(BASE_DIR.'update'))
        $tmp .= 'update<br>';
    if(strlen($tmp) > 1)
        return contend_template(getLanguageValue("install_finish_delerror",$tmp),false);
    return contend_template(getLanguageValue("install_finish_del"),true);
}

function mo_unlink($dir) {
    if(defined('TEST') and TEST === true) return;
    unlink($dir);
}

// -----------------------------------------------------------------------------
// Zeile "SPRACHAUSWAHL"
// -----------------------------------------------------------------------------
function getLanguageSelect() {
    global $LANG_INSTALL;
    global $LANG_TMP;
    $admin_inhalt = '<select id="select-lang" name="language" class="mo-select" style="width:100%">';
     	ksort($LANG_INSTALL);
    foreach ($LANG_INSTALL as $key => $element) {
        if(is_file(BASE_DIR_ADMIN.LANGUAGE_DIR_NAME."/language_".$key.".txt")) {
            $selected = "";
            if($key == $LANG_TMP)
                $selected = 'selected="selected" ';
            $admin_inhalt .= '<option '.$selected.'value="'.$key.'">'.$element.'</option>';
        }
    }
    $admin_inhalt .= "</select>";
    return $admin_inhalt;
}

function contend_template($daten_array,$error = NULL) {
    $template = NULL;

    $template_content = NULL;

    if($error === true) {
        $template_content .= '<div class="card green mb">';
    } elseif($error === false) {
        $template_content .= '<div class="card red mb">';
    } else
        $template_content .= '<div class="card mb">';
    if(is_array($daten_array)) {
        $template_content .= '<div class="c-content">'
        				.'<div>'.$daten_array[0].'</div>'
                .'<div>'.$daten_array[1].'</div>'
                .'</div>';
    } else  {
        $template_content .= $daten_array;
    }
    $template_content .= '</div>';

    $template .= $template_content;
    return $template;
}

function getHtml($art,$current_step = false) {
$install_js = 'function test_modrewrite(url,para,step) {
    var send_to_test = false;
    $.ajax({
        global: true,
        cache: false,
        type: "POST",
        url: url,
        data: para,
        async: true,
        dataType: "html",
        timeout:20000,
        success: function(data, textStatus, jqXHR){
            if($("<span>"+data+"</span>").find("#mod-rewrite-true").length > 0) {
                finish_test = true;
            } else if($("<span>"+data+"</span>").find("#return-modconf").length > 0) {
                send_to_test = true;
            }
        },
        complete: function() {
            if(send_to_test === true) {
                test_modrewrite("install/xy/test.php","",step);
            } else if(finish_test === false && step < max_step) {
                step++;
                $("#step-mod-conf").text(step);
                test_modrewrite("install.php","fromajax=true&current_step=rewrite&modconf="+step,step);
            } else if(finish_test === true) {
                $("#step-mod-conf").text(step);
                $(\'input[name="rewrite"]\').val(step);
                $("form").trigger("submit");
            } else {
                $("#step-mod-conf").text(step);
                $(\'input[name="rewrite"]\').val("no_modrewrite");
                $("form").trigger("submit");
            }
        },
    });
}

$(function() {

    if(typeof max_step != "undefined") {
        $("#step-mod-conf").text("0");
        test_modrewrite("install.php","fromajax=true&current_step=rewrite&modconf=0",0);
    }

    $(".step_tabs").on("click", function(event) {
        event.preventDefault();
        if($(this).closest("li").hasClass("js-no-click"))
            return false;
        $("#step_input").val($(this).attr("name"));
        $("form").trigger("submit");
    });

    $("#select-lang").on("change", function(event) {
        event.preventDefault();
        $("form").trigger("submit");
    });

    $(".js-in-pwroot").on("keydown", function(event) {
        if(event.which == 13)
            event.preventDefault();
    });

    $("form").on("submit",function(event) {
        if($("#step_input").val() == "")
            $("#step_input").val($(".ui-tabs-selected a").attr("name"));
    });
});

';

$html_start = '<!DOCTYPE html>'."\n"
        .'<html lang="de">'."\n"
        .'<head>'."\n"
        .'<meta charset="'.CHARSET.'">'."\n"
        .'<meta name="viewport" content="width = device-width, initial-scale = 1.0">'."\n"
        .'<title>moziloCMS - Setup</title>'."\n"
        .'<link rel="shortcut icon" href="'.URL_BASE.ADMIN_DIR_NAME.'/favicon.ico">'."\n"
        .'<link rel="stylesheet" href="'.URL_BASE.ADMIN_DIR_NAME.'/css/admin.css">'."\n"       
    .'</head>'."\n"
    .'<body>'."\n"
    .'<div class="admin-container install">'."\n"
    .'<noscript><div class="mo-noscript slideInDown card red mo-align-center">'.getLanguageValue("error_no_javascript").'</div></noscript>'."\n"
    .'<div class="header">'."\n"
      .'<div class="header-left">'."\n"
  .'<img src="'.URL_BASE.ADMIN_DIR_NAME.'/css/images/mozilo-logo-24.webp" alt="moziloCMS Logo">'."\n"
  .'<span class="mr mo-bold">moziloCMS Setup</span>'."\n"
  .'</div>'."\n"
    .'</div>'."\n";
       $html_start .= '<form class="install-form" action="install.php" method="post">'."\n";
    $html_end = '</form>'."\n"    
.'<div class="footer">'."\n"
.'<small>Powered by <a href="https://www.mozilo.de" target="_blank">moziloCMS</a> &copy; 2006 - '.date("Y").' &#10072; Version: '.CMSVERSION.' ("'.CMSNAME.'") '.CMSSTATUS.'</small>'."\n"
.'</div>'."\n"
        .'</div>'."\n"
                .'<script src="'.URL_BASE.CMS_DIR_NAME.'/jquery/jquery-'.JQUERY.'.min.js"></script>'."\n"
        .'<script>/*<![CDATA[*/'
        .$install_js
        .'/*]]>*/</script>'."\n" ;

    if($art == "start")
        return $html_start;
    if($art == "end")
        return $html_end;
}
?>