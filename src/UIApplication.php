<?php
/**
 * Created by PhpStorm.
 * User: egorov
 * Date: 12.12.2014
 * Time: 11:41
 */
namespace samsonos\cms\ui;

use samson\core\Event;
use samson\core\CompressableService;

/**
 * User interface SamsonCMS application
 * @package samsonos\cms\ui
 * @author Vitaly Iegorov <egorov@samsonos.com>
 */
class UIApplication extends CompressableService
{
    /** @var string Identifier */
    protected $id = 'ui';

    /** @var Container Pointer to current workspace container */
    protected $workspace;

    /** @var Container[] Collection of opened containers */
    protected $openedContainers = array();

    /*
     * Initialize UI service
     */
    public function init(array $params = array())
    {
        // Create top parent container
        $this->workspace = new Container($this);

        // Create main UI menu
        $menu = new Menu($this, $this->workspace);
        $menu->set('class', 'main-menu');

        // Create home item
        $homeItem = new MenuItem($menu);
        $homeItem->set('title', 'Home')
            ->set('class', 'btn-home')
            ->set('content', '<a href="/"><i class="sprite sprite-header_home" href="/"></i></a>')
        ;

        // Create site item
        $siteItem = new MenuItem($menu);
        $siteItem->set('title', t('Перейти на веб-сайт', true))
            ->set('class', 'btn-site')
            ->set('content', '<a href="/"><i class="sprite sprite-header_site" href="/"></i></a>')
        ;

        // Fire event that ui menu left container has been created
        Event::fire('cms_ui.mainmenu_leftcreated', array(&$menu, &$this));

        // Create exit item
        $exitItem = new MenuItem($menu);
        $exitItem->set('title', t('Выйти', true))
            ->set('class', 'btn-icon-right btn-logout')
            ->set('content', '<a href="signin/logout"><i class="sprite sprite-header_logout" href="/"></i></a>')
        ;

        // Create settings item
        $settingsItem = new MenuItem($menu);
        $settingsItem->set('title', t('Выйти', true))
            ->set('class', 'btn-icon-right btn-settings')
            ->set('content', '<a href="settings"><i class="sprite sprite-header_settings" href="/"></i></a>')
        ;

        // Create i18n menu
        $i18nMenu = new Menu($this, $menu);
        $i18nMenu->set('title', t('Выберите язык', true))
            ->set('class', 'i18n-list');
        // Iterate all supported locales
        foreach (\samson\core\SamsonLocale::get() as $locale) {
            $localeItem = new MenuItem($i18nMenu);
            $url = $locale == DEFAULT_LOCALE ? '' : '/'.$locale;

            $localeItem
                ->set('class', 'i18n_item-'.$locale.' '.($locale == \samson\core\SamsonLocale::current() ? 'i18n-active' : ''))
                ->set('content', '<a href="'.$url.'">'.$locale.'</a>');
        }

        // Fire event that ui menu container has been created
        Event::fire('cms_ui.mainmenu_created', array(&$menu, &$this));

        // Create main UI menu
        $subMenu = new Menu($this, $menu);
        $subMenu->set('class', 'sub-menu');

        // Fire event that ui sub-menu container has been created
        Event::fire('cms_ui.submenu_created', array(&$subMenu, &$this));

        // Create main-content panel
        $mainPanel = new Container($this, $this->workspace);
        $mainPanel->set('class', 'mainPanel '.(sizeof($subMenu->children()) ? 'with-sub-menu' : ''));

        /*// Create form with tabs
        $form = new Form($this, $mainPanel);

        // Create form tab view
        $tabs = new TabView($form);

        // Add tab
        $tab = new Tab($tabs);
        $tab->header->set('content', '<span>Описание</span>');

        // Create localized tabs
        foreach (\samson\core\SamsonLocale::get() as $locale) {
            (new Tab($tab))->header->set('content', '<span>'.$locale.'</span>');
        }*/

        // Fire event that ui workspace container has been created
        Event::fire('cms_ui.workspace_created', array(&$this->workspace, &$this));

        return parent::init($params);
    }

    /**
     * Render user interface
     */
    public function __handler()
    {
        // Set workspace template
        s()->template('www/index.vphp');

        // Render workspace
        $this->set('content_html', $this->workspace->render())
        ;
    }
}
