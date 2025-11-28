<?php if (!defined('IS_CMS')) die();

/***************************************************************
* 
* Breadcrumb für moziloCMS – PHP 8.1-kompatibel & korrektes Trenner-Handling
* 
***************************************************************/
class Breadcrumb extends Plugin {

    public Language $admin_lang;

    public $settings;

    function getContent($value): string {
        global $CMS_CONF, $language, $CatPage;

        $cat = $CatPage->get_HrefText(CAT_REQUEST ?? '', false);
        $page = $CatPage->get_HrefText(CAT_REQUEST ?? '', PAGE_REQUEST ?? '');

        // Vorsatztext (breadcrumb_text)
        $entry = '';
        $breadcrumbText = $this->settings->get("breadcrumb_text");
        if (!empty($breadcrumbText)) {
            $entry = htmlspecialchars($breadcrumbText, ENT_QUOTES, 'UTF-8') . ':';
        }

        // Startbezeichnung (first_entry), Standard "Start" wenn leer
        $start = $this->settings->get("first_entry");
        if (empty($start)) {
            $start = 'Start';
        }
        $start = htmlspecialchars($start, ENT_QUOTES, 'UTF-8');

        // Trennzeichen
        $separator = $this->settings->get("breadcrumb_divider");
        $separator = !empty($separator) ? htmlspecialchars($separator, ENT_QUOTES, 'UTF-8') : '&raquo;';

        $items = [];
        $position = 1;

        // Start-Link immer anzeigen (erstes Breadcrumb-Element)
        $items[] = $this->makeListItem('{BASE_URL}', $start, $position++);

        if (ACTION_REQUEST === "sitemap" || ACTION_REQUEST === "search") {
            $actionName = '';
            if (ACTION_REQUEST === "sitemap") {
                $actionName = $language->getLanguageValue("message_sitemap_0");
            } elseif (ACTION_REQUEST === "search") {
                $actionName = $language->getLanguageValue("message_search_0");
            }
            $items[] = $this->makeTextItem($actionName, $position++);
        }
        elseif ($CMS_CONF->get("hidecatnamedpages") === "true" && $cat === $page) {
    // Wenn nur ein Eintrag, diesen als Text ausgeben (kein Link)
    $items[] = $this->makeTextItem($cat, $position++);
} else {
    // Kategorie als Link
    $items[] = $this->makeListItem("{$cat}.html", $cat, $position++);
    // Letzter Eintrag als Text (kein Link)
    $items[] = $this->makeTextItem($page, $position++);
}


        $html = '<nav class="breadcrumb" aria-label="Breadcrumb">';
        $html .= !empty($entry) ? '<span class="breadcrumb-entry">' . $entry . '</span>' : '';
        $html .= '<ol itemscope itemtype="http://schema.org/BreadcrumbList">';

        // Trenner nur zwischen den Items einfügen
        for ($i = 0; $i < count($items); $i++) {
            if ($i > 0) {
                $html .= '<li class="breadcrumb-separator" aria-hidden="true">' . $separator . '</li>';
            }
            $html .= $items[$i];
        }

        $html .= '</ol></nav>';

        return $html;
    }

    private function makeListItem(string $url, string $name, int $position): string {
        return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' .
               '<a itemprop="item" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' .
               '<span itemprop="name">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>' .
               '</a><meta itemprop="position" content="' . $position . '">' .
               '</li>';
    }

    private function makeTextItem(string $name, int $position): string {
        return '<li itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">' .
               '<span itemprop="name">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</span>' .
               '<meta itemprop="position" content="' . $position . '">' .
               '</li>';
    }

    function getConfig(): array {
        return [
            'breadcrumb_text' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_breadcrumb_text'),
                'maxlength' => '20',
            ],
            'first_entry' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_first_entry'),
                'maxlength' => '20',
            ],
            'breadcrumb_divider' => [
                'type' => 'text',
                'description' => $this->admin_lang->getLanguageValue('config_breadcrumb_divider'),
                'maxlength' => '3',
            ],
        ];
    }

    function getInfo(): array {
        global $ADMIN_CONF;

        $lang = $ADMIN_CONF->get('language') ?? 'de';
        $this->admin_lang = new Language(PLUGIN_DIR_REL . 'Breadcrumb/sprachen/admin_language_' . $lang . '.txt');

        return [
            'Version 3.1',
            '2.0 / 3.0',
            $this->admin_lang->getLanguageValue('description'),
            'moziloCMS',
            'https://www.mozilo.de',
            ['{Breadcrumb}' => $this->admin_lang->getLanguageValue('placeholder')],
        ];
    }
}
