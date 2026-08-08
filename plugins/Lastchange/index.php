<?php
/**
 * Lastchange Plugin – Version 3.2.2 (PHP 8.0+)
 * -------------------------------------------------------------
 * • 16.01.2026: optional Uhrzeit anzeigen; Admin-Hilfe überarbeitet
 * • Läuft ab PHP 8.0 bis 8.4; ab 8.2 automatische AllowDynamicProperties.
 * -------------------------------------------------------------
 */

declare(strict_types=1);

if (!defined('IS_CMS')) {
    die();
}

/* ====================================================================
   Fallback für PHP 8.0/8.1:
   Wenn das eingebaute Attribut »AllowDynamicProperties« noch nicht
   vorhanden ist, wird leere Ersatz-Klasse definiert.
   ==================================================================== */
if (PHP_VERSION_ID < 80200 && !class_exists('AllowDynamicProperties')) {
    #[\Attribute(\Attribute::TARGET_CLASS)]
    class AllowDynamicProperties {}
}

#[\AllowDynamicProperties] // ab PHP 8.2 blendet das die Deprecation-Warnung aus
class Lastchange extends Plugin
{
    /** Seiten-Typen, die überprüft werden */
    private array $includePages = [EXT_PAGE];

    /** Ausgabeformat für Datum & Uhrzeit */
    private string $dateFormat = 'd.m.Y H:i';

    /** Plugin-Version */
    public const VERSION = '3.2.2';

    /** Erlaubte Zeichen für PHP date()-Formatstrings (Hardening gegen ungewollte Eingaben) */
    private const DATE_FORMAT_ALLOWED_PATTERN = '#^[dDjFmMnYyGgHhisaA\-\.\/\s:]+$#';

    public function getContent(string $value): string
    {
        global $language;

        // Robust: Settings sind Strings ('true'/'false') – fehlende Keys => Default.
        $includeHidden = ($this->settings->get('showhiddenpagesinlastchanged') === 'true');
        $this->includePages = $includeHidden ? [EXT_PAGE, EXT_HIDDEN] : [EXT_PAGE];

        $messageText = $this->settings->get('messagetext')
            ?: $language->getLanguageValue('message_lastchange_0');

        // Datumsformat: entweder Plugin-Setting oder Fallback aus Sprache.
        $rawFormat = $this->settings->get('date')
            ?: $language->getLanguageValue('_dateformat_0');

        // Hardening: nur erlaubte date()-Token/Separatoren akzeptieren.
        $this->dateFormat = (is_string($rawFormat) && preg_match(self::DATE_FORMAT_ALLOWED_PATTERN, $rawFormat))
            ? $rawFormat
            : 'd.m.Y H:i';

        $showTime = (($this->settings->get('showtime') ?? 'false') === 'true');

        // Format normalisieren, abhängig von "Uhrzeit anzeigen".
        $this->dateFormat = $this->normalizeDateFormat($this->dateFormat, $showTime);

        $info  = $this->getLastChangedContentPageAndDate();
        $value = trim((string)$value);

        return match ($value) {
            'text'     => $messageText,
            'page'     => $info['pageText'],
            'pagelink' => $info['pageLink'],
            'date'     => $info['date'],
            default    => $messageText . ' ' . $info['pageLink'] . ' (' . $info['date'] . ')',
        };
    }

    /**
     * Normalisiert ein date()-Format:
     * - showTime=true  => ergänzt ' H:i', falls keine Zeitkomponenten vorhanden sind
     * - showTime=false => entfernt Zeitkomponenten (H/G/h/g, i, s, a/A) inkl. typischer Trenner
     */
    private function normalizeDateFormat(string $format, bool $showTime): string
    {
        $format = trim($format);

        if ($showTime) {
            // Wenn keine Stunden+Minuten enthalten sind, ergänzen wir " H:i".
            // (Sekunden optional, aber nicht automatisch ergänzt.)
            if (!preg_match('/[GgHh].*i|i.*[GgHh]/', $format)) {
                $format .= ' H:i';
            }
            return $format;
        }

        // showTime=false: Zeitbestandteile entfernen.
        // Ziel: robuste Entfernung typischer Zeitmuster, ohne Datumsbestandteile zu beschädigen.
        $original = $format;

        // Entferne typische Zeitteile, optional mit Trennern davor:
        // - " H:i", "H:i", " H:i:s", "H:i:s", " h:i a" etc.
        $format = preg_replace(
            '/\s*[:\-\.\/]?\s*[GgHh]\s*[:\-\.\/]?\s*i(?:\s*[:\-\.\/]?\s*s)?(?:\s*[aA])?/',
            '',
            $format
        ) ?? $format;

        // Falls nur Minuten/Sekunden/AMPM ohne Stunden übrig bleiben (ungewöhnlich, aber möglich):
        $format = preg_replace(
            '/\s*[:\-\.\/]?\s*i(?:\s*[:\-\.\/]?\s*s)?(?:\s*[aA])?/',
            '',
            $format
        ) ?? $format;

        $format = trim($format);

        // Wenn durch Removal alles leer wurde, fallback auf reines Datum.
        if ($format === '') {
            return 'd.m.Y';
        }

        // Optionales Feintuning: Doppelte Trennzeichen/Leerzeichen bereinigen, wenn Removal "Löcher" reißt.
        // (Sehr konservativ, damit keine ungewollten Änderungen passieren.)
        $format = preg_replace('/\s{2,}/', ' ', $format) ?? $format;
        $format = trim($format, " \t\n\r\0\x0B-:./");

        // Wenn das Feintuning zu aggressiv wäre und alles entfernt: fallback.
        if ($format === '') {
            return 'd.m.Y';
        }

        // Wenn das Ergebnis sich nicht verändert hat, ist das ebenfalls ok.
        return $format;
    }

    private function getLastChangedContentPageAndDate(): array
    {
        global $language, $CatPage;

        $latest = ['cat' => '', 'page' => '', 'time' => 0];

        $cats = $CatPage->get_CatArray(false, false, $this->includePages);
        if (!is_array($cats) || $cats === []) {
            // Keine Kategorien gefunden – defensiver Fallback.
            $time = time();
            $dt = (new DateTimeImmutable('@' . $time))->setTimezone(new DateTimeZone(date_default_timezone_get()));
            return [
                'pageText' => '',
                'pageLink' => '',
                'date'     => '<time datetime="' . $dt->format('c') . '">' . $dt->format($this->dateFormat) . '</time>',
            ];
        }

        foreach ($cats as $cat) {
            if (!is_string($cat) || $cat === '') {
                continue;
            }
            $dirInfo = $this->getLastChangeOfCat($cat);
            if (($dirInfo['time'] ?? 0) > $latest['time']) {
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
        $time = (int)($latest['time'] ?? 0);
        $time = $time > 0 ? $time : time();

        $dt = (new DateTimeImmutable('@' . $time))->setTimezone($tz);

        return [
            'pageText' => (string)$pageText,
            'pageLink' => (string)$pageLink,
            'date'     => '<time datetime="' . $dt->format('c') . '">' . $dt->format($this->dateFormat) . '</time>',
        ];
    }

    private function getLastChangeOfCat(string $cat): array
    {
        global $CatPage;

        $latest = ['page' => '', 'time' => 0];

        $pages = $CatPage->get_PageArray($cat, $this->includePages, true);
        if (!is_array($pages) || $pages === []) {
            return $latest;
        }

        foreach ($pages as $page) {
            if (!is_string($page) || $page === '') {
                continue;
            }
            $t = (int)$CatPage->get_Time($cat, $page);
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
            'showtime' => [
                'type' => 'checkbox',
                'description' => $lang_lastchange_admin->get('config_lastchange_showtime'),
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
            '3.0',
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
