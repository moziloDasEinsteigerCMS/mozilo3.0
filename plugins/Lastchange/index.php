<?php
/**
 * Lastchange Plugin – Version 3.2.1 (PHP 8.0+)
 * -------------------------------------------------------------
 * • Bug‑Fix: geteiltes Deployment < 8.3 → "typed class constant" führte
 *   zu Syntax‑Error 500.  Jetzt ungetypt (public const VERSION ...)
 * • Läuft ab PHP 8.0 bis 8.4; ab 8.2 automatische AllowDynamicProperties.
 * -------------------------------------------------------------
 */

declare(strict_types=1);

if (!defined('IS_CMS')) { die(); }

/* ====================================================================
   Fallback für PHP 8.0/8.1:
   Wenn das eingebaute Attribut »AllowDynamicProperties« noch nicht
   vorhanden ist, definieren wir eine leere Ersatz‑Klasse.
   ==================================================================== */
if (PHP_VERSION_ID < 80200 && !class_exists('AllowDynamicProperties')) {
    #[\Attribute(\Attribute::TARGET_CLASS)]
    class AllowDynamicProperties {}
}

/* ====================================================================
   Kernklasse –  **hier beginnt die eigentliche Plugin‑Logik**
   ==================================================================== */
#[\AllowDynamicProperties]   // ab PHP 8.2 blendet das die Deprecation‑Warnung aus
class Lastchange extends Plugin
{
    /** Seiten‑Typen, die überprüft werden */
    private array $includePages = [EXT_PAGE];

    /** Ausgabeformat für Datum & Uhrzeit */
    private /*readonly (>=8.4)*/ string $dateFormat = 'd.m.Y H:i';

    /** Plugin‑Version */
    public const VERSION = '3.2.1';

    public function getContent(string $value): string
    {
        global $language;

        $this->includePages = $this->settings->get('showhiddenpagesinlastchanged') === 'true'
            ? [EXT_PAGE, EXT_HIDDEN]
            : [EXT_PAGE];

        $messageText = $this->settings->get('messagetext')
            ?: $language->getLanguageValue('message_lastchange_0');

        $rawFormat = $this->settings->get('date')
            ?: $language->getLanguageValue('_dateformat_0');

        $allowed = '#^[dDjFmMnYyGgHhisa\-\.\/\s:]+$#';
        $this->dateFormat = preg_match($allowed, $rawFormat) ? $rawFormat : 'd.m.Y H:i';

        if (!preg_match('/[GgHh].*i|i.*[GgHh]/', $this->dateFormat)) {
            $this->dateFormat .= ' H:i';
        }

        $info = $this->getLastChangedContentPageAndDate();

        return match ($value) {
            'text'     => $messageText,
            'page'     => $info['pageText'],
            'pagelink' => $info['pageLink'],
            'date'     => $info['date'],
            default    => $messageText . ' ' . $info['pageLink'] . ' (' . $info['date'] . ')',
        };
    }

    private function getLastChangedContentPageAndDate(): array
    {
        global $language, $CatPage;

        $latest = ['cat' => '', 'page' => '', 'time' => 0];

        foreach ($CatPage->get_CatArray(false, false, $this->includePages) as $cat) {
            $dirInfo = $this->getLastChangeOfCat($cat);
            if ($dirInfo['time'] > $latest['time']) {
                $latest = ['cat' => $cat, ...$dirInfo];
            }
        }

        $pageText = $CatPage->get_HrefText($latest['cat'], $latest['page']);
        $url      = $CatPage->get_Href($latest['cat'], $latest['page']);
        $titleAttr = $language->getLanguageHTML(
            'tooltip_link_page_2',
            $pageText,
            $CatPage->get_HrefText($latest['cat'], false)
        );

        $pageLink = $CatPage->create_LinkTag(
            $url,
            $pageText,
            false,
            $titleAttr,
            false,
            'lastchangelink'
        );

        $tz   = new DateTimeZone(date_default_timezone_get());
        $time = $latest['time'] ?: time();
        $dt   = (new DateTimeImmutable('@' . $time))->setTimezone($tz);

        return [
            'pageText' => $pageText,
            'pageLink' => $pageLink,
            'date'     => '<time datetime="' . $dt->format('c') . '">' . $dt->format($this->dateFormat) . '</time>',
        ];
    }

    private function getLastChangeOfCat(string $cat): array
    {
        global $CatPage;
        $latest = ['page' => '', 'time' => 0];

        foreach ($CatPage->get_PageArray($cat, $this->includePages, true) as $page) {
            $t = $CatPage->get_Time($cat, $page);
            if ($t > $latest['time']) {
                $latest = ['page' => $page, 'time' => $t];
            }
        }
        return $latest;
    }

    public function getConfig(): array
    {
        global $lang_lastchange_admin, $ADMIN_CONF;

        if (!$lang_lastchange_admin instanceof Properties) {
            $dir  = PLUGIN_DIR_REL . 'Lastchange/';
            $lang = $ADMIN_CONF->get('language');
            $lang_lastchange_admin = new Properties($dir . 'sprachen/admin_language_' . $lang . '.txt', false);
        }

        return [
            'messagetext' => [
                'type' => 'text',
                'description' => $lang_lastchange_admin->get('config_lastchange_messagetext'),
                'maxlength' => '100',
            ],
            'date' => [
                'type' => 'text',
                'description' => $lang_lastchange_admin->get('config_lastchange_date'),
                'maxlength' => '100',
            ],
            'showhiddenpagesinlastchanged' => [
                'type' => 'checkbox',
                'description' => $lang_lastchange_admin->get('config_lastchange_showhiddenpagesinlastchanged'),
            ],
        ];
    }

    public function getInfo(): array
    {
        global $ADMIN_CONF;

        $dir  = PLUGIN_DIR_REL . 'Lastchange/';
        $lang = $ADMIN_CONF->get('language');
        $lng  = new Properties($dir . 'sprachen/admin_language_' . $lang . '.txt', false);

        return [
            self::VERSION,
            '2.0 / 3.0',
            $lng->get('config_lastchange_plugin_desc'),
            'mozilo (refactored)',
            '',
            [
                '{Lastchange}'          => $lng->get('config_lastchange_plugin_lastchange'),
                '{Lastchange|text}'     => $lng->get('config_lastchange_plugin_text'),
                '{Lastchange|page}'     => $lng->get('config_lastchange_plugin_page'),
                '{Lastchange|pagelink}' => $lng->get('config_lastchange_plugin_pagelink'),
                '{Lastchange|date}'     => $lng->get('config_lastchange_plugin_date'),
            ],
        ];
    }
}
