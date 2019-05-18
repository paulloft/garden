<?php

namespace Addons\Installer\Models;

use Garden\Addons;
use Garden\Config;
use Garden\Db\Structure;
use Garden\Helpers\Arr;
use Garden\Helpers\Text;
use Garden\Traits\Instance;

class Install {

    use Instance;

    public function saveConfig($data, $name, $rewrite = false)
    {
        $file = GDN_CONF . "/$name.php";
        $config = !$rewrite && file_exists($file) ? include($file) : [];
        $newConfig = array_merge($config, $data);

        Arr::save($newConfig, $file);
    }

    public function installAddons($addons)
    {
        Structure::instance();
        $allAddons = Addons::all();

        foreach ($allAddons as $addon => $options) {
            if (!val($addon, $addons)) {
                continue;
            }
            $installed = Config::get("addons.$addon") !== null;
            $dir = val('dir', $options);

            $structureFile = "$dir/Settings/structure.php";
            if (file_exists($structureFile)) {
                call_user_func(function ($structureFile) {
                    include_once $structureFile;
                }, $structureFile);
            }

            $installFile = "$dir/Settings/install.php";

            if (!$installed && file_exists($installFile)) {
                call_user_func(function ($installFile) {
                    include_once $installFile;
                }, $installFile);
            }
        }
    }

    public function saveAddons($addons)
    {
        foreach ($addons as $name => $enabled) {
            $addons[$name] = (bool)$enabled;
        }
        $this->saveConfig($addons, 'addons', true);
    }

    public function cacheDrivers(): array
    {
        $drivers = [];
        $files = scandir(GDN_SRC . '/Cache');

        foreach ($files as $driver) {
            $driver = Text::rtrimSubstr($driver, '.php');
            if ($driver === '.' || $driver === '..' || $driver === 'System') {
                continue;
            }
            $drivers[strtolower($driver)] = $driver === 'Dirty' ? 'Disabled' : $driver;
        }

        return $drivers;
    }

}