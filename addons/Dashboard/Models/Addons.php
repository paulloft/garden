<?php
namespace Addons\Dashboard\Models;

class Addons extends \Garden\Plugin {

    public $error;

    public function getAll()
    {
        return \Garden\Addons::all();
    }

    public function saveConfig($data, $name, $rewrite = false)
    {
        $file = GDN_CONF . "/$name.php";
        $config = !$rewrite && file_exists($file) ? include($file) : [];
        $newConfig = array_merge($config, $data);

        array_save($newConfig, $file);
    }

    public function install($name)
    {
        $allAddons = $this->getAll();
        $addon = val($name, $allAddons);
        if ($addon) {
            $installed = c("addons.$name", null) !== null;
            $dir = val('dir', $addon);

            try {
                $structureFile = $dir.'/Settings/structure.php';
                if (file_exists($structureFile)) {
                    call_user_func(function($structureFile) {
                        include_once $structureFile;
                    }, $structureFile);
                }

                $installFile = $dir.'/Settings/install.php';

                if (!$installed && file_exists($installFile)) {
                    call_user_func(function($installFile) {
                        include_once $installFile;
                    }, $installFile);
                }

                return true;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        } else {
            $this->error = t('Addon not found');
            return false;
        }
    }

    public function save($addon, $enable)
    {
        $this->saveConfig([$addon => (bool)$enable], 'addons');
    }
}