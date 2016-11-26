<?php
namespace Addons\Installer\Models;
use Garden\Gdn;

class Install extends \Garden\Plugin {

    public function saveConfig($data, $name, $rewrite = false)
    {
        $file = GDN_CONF . "/$name.php";
        $config = !$rewrite && file_exists($file) ? include($file) : [];
        $newConfig = array_merge($config, $data);

        array_save($newConfig, $file);
    }

    public function installAddons($addons)
    {
        $structure = Gdn::structure();
        $allAddons = \Garden\Addons::all();

        foreach ($allAddons as $addon => $options) {
            if (!val($addon, $addons)) continue;
            $installed = c("addons.$addon", null) !== null;
            $dir = val('dir', $options);

            $structureFile = $dir.'/settings/structure.php';
            if (file_exists($structureFile)) {
                call_user_func(function($structureFile) {
                    include_once $structureFile;
                }, $structureFile);
            }

            $installFile = $dir.'/settings/install.php';

            if (!$installed && file_exists($installFile)) {
                call_user_func(function($installFile) {
                    include_once $installFile;
                }, $installFile);
            }
        }
    }

    public function saveAddons($addons)
    {
        foreach ($addons as $name=>$enabled) {
            $addons[$name] = (bool)$enabled;
        }
        $this->saveConfig($addons, 'addons', true);
    }

    public function cacheDrivers()
    {
        $drivers = [];
        $files = scandir(GDN_SRC . '/Cache');

        foreach ($files as $driver) {
            $driver = rtrim_substr($driver, '.php');
            if ($driver == '.' || $driver == '..' || $driver == 'System') continue;
            $drivers[strtolower($driver)] = $driver == 'Dirty' ? 'Disabled' : $driver;
        }

        return $drivers;
    }

}