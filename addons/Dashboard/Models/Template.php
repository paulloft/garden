<?php

namespace Addons\Dashboard\Models;

use Garden\Cache;
use Garden\Config;
use Garden\Event;
use Garden\Helpers\Date;
use Garden\Helpers\Text;
use Garden\Renderers\View;
use Garden\Translate;
use Smarty;

class Template {

    /**
     * @var Smarty
     */
    private static $smarty;

    /**
     * Get the default dashboard template
     *
     * @return \Garden\Renderers\Template
     */
    public static function get(): \Garden\Renderers\Template
    {
        $template = new \Garden\Renderers\Template('dashboard', 'templates', 'dashboard');
        self::assignResources($template);

        Event::fire('dashboard_template_init');

        return $template;
    }

    /**
     * Get empty template
     *
     * @return \Garden\Renderers\Template
     */
    public static function getEmpty(): \Garden\Renderers\Template
    {
        $template = new \Garden\Renderers\Template('empty', 'templates', 'dashboard');
        self::assignResources($template);

        Event::fire('empty_template_init');

        return $template;
    }

    /**
     * Assign default dashboard resources
     *
     * @param \Garden\Renderers\Template $template
     */
    public static function assignResources(\Garden\Renderers\Template $template)
    {
        $template->addCss('//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        $template->addCss('//fonts.googleapis.com/css?family=Open+Sans:300,400,400italic,600,700');
        $template->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css');
        $template->addCss('bootstrap.theme.css');
        $template->addCss('dashboard.css');

        $template->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js');
        $template->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js');
        $template->addJs('dashboard.js');
    }

    /**
     * Renderer for .tpl extentions
     * @param string $templatePath
     * @param array $data
     * @return string
     * @see View::registerExtRenderer()
     *
     */
    public static function smartRenderer(string $templatePath, array $data): string
    {
        $smarty = self::getSmarty();
        $smarty->setTemplateDir(pathinfo($templatePath, PATHINFO_DIRNAME));
        $smarty->assign($data);

        return $smarty->fetch($templatePath);
    }

    /**
     * Get the Smarty instance
     *
     * @return Smarty
     */
    public static function getSmarty(): Smarty
    {
        if (self::$smarty !== null) {
            return self::$smarty;
        }

        $smarty = new Smarty();
        $config = Config::get('dashboard.smarty');
        $smarty->caching = $config['caching'] ?? false;

        $smarty
            ->setCompileDir($config['compile_dir'] ? PATH_ROOT . $config['compile_dir'] : GDN_CACHE . '/smarty/')
            ->setCacheDir($config['cache_dir'] ? PATH_ROOT . $config['cache_dir'] : GDN_CACHE . '/smarty/');

        foreach (\Garden\Addons::enabled() as $addon) {
            $smarty->addPluginsDir("{$addon['dir']}/SmartyPlugins");
        }

        $smarty->registerClass('Date', Date::class);
        $smarty->registerClass('Text', Text::class);
        $smarty->registerPlugin('modifier', 'translate', [Translate::class, 'get']);
        $smarty->registerPlugin('modifier', 'config', [Config::class, 'get']);

        if (Cache::$clear) {
            $smarty->clearAllCache();
        }

        self::$smarty = $smarty;

        return self::$smarty;
    }
}