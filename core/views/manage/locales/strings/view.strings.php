<?php

namespace Forge\Core\Views\Manage\Locales\Strings;

use Forge\Core\Abstracts\View;
use Forge\Core\App\App;
use Forge\Core\App\Auth;
use Forge\Core\Classes\Localization;
use Forge\Core\Classes\TableBar;
use Forge\Core\Classes\Utils;
use Forge\Core\Traits\ApiAdapter;

class StringsView extends View
{
    public $parent = 'manage';
    public $name = 'string-translation';
    public $permission = 'manage.locales.strings';
    public $languages = null;
    public $permissions = array(
        'manage.locales.strings.update'
    );
    private $apiMainListener = 'string-translations';

    use ApiAdapter;

    public function init()
    {
        $this->languages = Localization::getLanguages();
    }

    public function content($uri = array())
    {
        if (count($uri) > 0) {
            if ($uri[0] == 'update') {
                return $this->getSubview($uri, $this);
            }
            if ($uri[0] == 'translate') {
                return $this->getSubview($uri, $this);
            }
        } else {
            return $this->ownContent();
        }
    }

    private function ownContent()
    {
        return $this->app->render(CORE_TEMPLATE_DIR . "views/", "locales", array(
            'title' => i('String Translations'),
            'add' => i('Update Strings'),
            'add_permission' => Auth::allowed($this->permissions[0]),
            'add_url' => Utils::getUrl(array('manage', 'string-translation', 'update')),
            'table' => $this->stringsTable()
        ));
    }

    private function stringsTable()
    {
        $bar = new TableBar(Utils::url(['api', $this->apiMainListener]), 'string_translations_table');
        $bar->enableSearch();
        $bar->enableSorting([
            'default' => i('Default', 'core'),
            'domain_ASC' => i('Textdomain Ascending', 'core'),
            'domain_DESC' => i('Textdomain Descending', 'core')
        ]);
        $bar->addDirectFilter([
            'label' => i('Choose Textdomain', 'core'),
            'field' => 'domain',
            'values' => Localization::getTextDomains()
        ]);
        $bar->addDirectFilter([
            'label' => i('Status', 'core'),
            'field' => 'status',
            'values' => [
                'translated' => i('Translation complete', 'core'),
                'translation_missing' => i('Missing translations', 'core')
            ]
        ]);

        return $bar->render() . $this->app->render(CORE_TEMPLATE_DIR . "assets/", "table", array(
                'id' => "string_translations_table",
                'th' => array_merge(array(
                    Utils::tableCell(i('String')),
                    Utils::tableCell(i('Domain'))
                ), $this->getLanguageNameCells(), array(
                    Utils::tableCell(i('In use'), "center"),
                    Utils::tableCell(i('Translate'), "center")
                )),
                'td' => $this->getStringRows()
            ));
    }

    /**
     * API Search method for table
     * @return json tr's for the table
     */
    public function search()
    {
        $args = ['search' => $_GET['t']];
        $sort = ["used", "desc"];
        if ($_GET['s'] !== 'default' && $_GET['s'] !== '') {
            $sort = explode("_", $_GET['s']);
        }

        foreach ($_GET as $key => $value) {
            if (strstr($key, 'filter__')) {
                $k = explode("__", $key);
                $k = $k[1];
                $args['where'][$k] = $value;
            }
        }

        return json_encode([
            'newTable' => App::instance()->render(
                CORE_TEMPLATE_DIR . 'assets/',
                'table-rows',
                ['td' => $this->getStringRows($sort, $args)]
            )
        ]);
    }

    private function getLanguageNameCells()
    {
        $cells = array();
        foreach ($this->languages as $lang) {
            array_push($cells, Utils::tableCell($lang['name'], "center"));
        }
        return $cells;
    }

    private function getStringRows($sort = ["used", "desc"], $args = [])
    {
        $rows = [];
        foreach (Localization::getAllStrings($sort, $args) as $string) {
            $row = new \stdClass();
            $row->tds = array_merge(
                array(
                    Utils::tableCell(htmlentities($string['string'])),
                    Utils::tableCell(htmlentities($string['domain']))
                ),
                $this->getLanguageTranslationState($string),
                array(
                    $this->stringInUse($string),
                    $this->translateAction($string)
                )
            );
            array_push($rows, $row);
        }
        return $rows;
    }

    private function stringInUse($string)
    {
        return Utils::tableCell(
            $string['used'] == 1 ? Utils::icon("check") : Utils::icon("error_outline"),
            "center"
        );
    }

    private function translateAction($string)
    {
        return Utils::tableCell(App::instance()->render(CORE_TEMPLATE_DIR . "assets/", "table.actions", array(
            'actions' => array(
                array(
                    "url" => Utils::getUrl(array("manage", "string-translation", "translate", $string['id'])),
                    "icon" => "mode_edit",
                    "name" => i('Translate'),
                    "ajax" => true,
                    "confirm" => true
                )
            )
        )), "center", false, false, Utils::getUrl(array("manage", "string-translation", "translate", $string['id'])));
    }

    private function getLanguageTranslationState($string)
    {
        $cells = array();
        foreach (Localization::getLanguages() as $language) {
            $translated = Localization::stringTranslationState($string['string'], $string['domain'], $language['code']);
            if (!$translated) {
                array_push($cells, Utils::tableCell(Utils::icon("error_outline"), "center"));
            } else {
                array_push($cells, Utils::tableCell(Utils::icon("done"), "center"));
            }
        }
        return $cells;
    }
}
