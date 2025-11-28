<?php
declare(strict_types=1);

if (!defined('IS_CMS')) {
    die();
}

class Cookies extends Plugin
{
    private Language $admin_lang;
    private string $cms_lang = '';

    public function getContent(string $value): string
    {
        global $CatPage, $CMS_CONF, $specialchars, $language, $syntax, $lang_cookies;

        $dir = PLUGIN_DIR_REL . "Cookies/";
        $lang_cookies = new Language($dir . "sprachen/cms_language_" . $CMS_CONF->get("cmslanguage") . ".txt");

        $cookieDays     = $this->settings->get("cookieDays")     ?? '30';
        $cookieTitle    = $this->settings->get("cookieTitle")    ?? '';
        $cookieDesc     = $this->settings->get("cookieDesc")     ?? $lang_cookies->getLanguageHtml("cookieDesc");
			$cookieSr     = $this->settings->get("cookieSr")     ?? $lang_cookies->getLanguageHtml("cookieSr");
        $cookieBtn      = $this->settings->get("cookieBtn")      ?? $lang_cookies->getLanguageHtml("cookieBtn");
        $cookiePrivacy  = $this->settings->get("cookiePrivacy")  ?? $lang_cookies->getLanguageHtml("cookiePrivacy");

        $cat = $this->settings->get("cookieCat") ?? '';
        $page = $this->settings->get("cookiePage") ?? '';

        $linkprivacy = "index.php?cat=" . $cat . "&amp;page=" . $page;
        if ($CMS_CONF->get("modrewrite") === "true") {
            $linkprivacy = URL_BASE . $cat . "/" . $page . ".html";
        }

        $content = '<div id="cookie" class="cookie-banner" role="dialog" aria-modal="true" aria-labelledby="cookieTitle" aria-describedby="cookieDesc" style="display:none;">';
$content .= '<span class="sr-only" id="sr-context">' . $cookieSr . '</span>';
$content .= '<div id="cookieTitle" class="cookieTitle">' . $cookieTitle . '</div>';
$content .= '<div id="cookieDesc" class="cookieDesc">' . $cookieDesc . ' ';

        if (!$CatPage->exists_CatPage($cat, $page)) {
            $category_text = $specialchars->rebuildSpecialChars($cat, true, true);
            $page_text     = $specialchars->rebuildSpecialChars($page, true, true);
            $deadlink      = $language->getLanguageValue("tooltip_link_page_error_2", $page_text, $category_text);
            $content .= "<br><span class=\"deadlink\">" . $deadlink . "</span>";
        } else {
            $content .= "<a href=\"" . $linkprivacy . "\">" . $cookiePrivacy . "</a>";
        }

        $content .= '</div>';
        $content .= '<div class="cookieBtn"><button id="close" class="button" aria-label="' . $cookieBtn . '">' . $cookieBtn . '</button></div>';
        $content .= '</div>';

        $tail = <<<JS
<script>
document.addEventListener("DOMContentLoaded", function() {
    var cookieBox = document.getElementById('cookie');
    var closeBtn = document.getElementById('close');

    if (!readCookie('mozilo') && cookieBox) {
        cookieBox.style.display = 'block';
        cookieBox.setAttribute('tabindex', '-1');
        cookieBox.focus();
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (cookieBox) cookieBox.style.display = 'none';
            createCookie('mozilo', true, {$cookieDays});
        });

        // Enter and Space key triggers click
        closeBtn.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                closeBtn.click();
            }
        });
    }

    // ESC key to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && cookieBox && cookieBox.style.display === 'block') {
            cookieBox.style.display = 'none';
            createCookie('mozilo', true, {$cookieDays});
        }
    });

    function createCookie(name, value, days) {
        var expires = "";
        if (days) {
            var date = new Date();
            date.setTime(date.getTime() + (days*24*60*60*1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    function readCookie(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for (var i=0; i < ca.length; i++) {
            var c = ca[i].trim();
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length);
        }
        return null;
    }
});
</script>
JS;


        $syntax->insert_in_tail($tail);
        return $content;
    }

    public function getConfig(): array
    {
        return [
            'cookieDays' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieDays'),
                'maxlength' => '3',
                'size' => '5',
                'regex' => "/^[0-9]{1,3}$/",
                'regex_error' => $this->admin_lang->getLanguageValue('config_cookieDays_error')
            ],
            'cookieTitle' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieTitle'),
            ],
            'cookieDesc' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieDesc'),
            ],
            'cookieSr' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieSr'),
            ],
            'cookieBtn' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieBtn'),
            ],
            'cookiePrivacy' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookiePrivacy'),
            ],
            'cookieCat' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookieCat'),
                'maxlength' => '100',
            ],
            'cookiePage' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_cookiePage'),
                'maxlength' => '100',
            ]
        ];
    }

    public function getInfo(): array
    {
        global $ADMIN_CONF;

        $this->admin_lang = new Language(PLUGIN_DIR_REL . 'Cookies/sprachen/admin_language_' . $ADMIN_CONF->get('language') . '.txt');

        return [
            'Version 3.1',
            '2.0 / 3.0',
            $this->admin_lang->getLanguageValue('description'),
            'moziloCMS',
            'https://www.mozilo.de',
            [
                '{Cookies}' => $this->admin_lang->getLanguageValue('placeholder'),
            ]
        ];
    }
}
?>
