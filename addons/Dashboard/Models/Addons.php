<?php

namespace Addons\Dashboard\Models;

use Garden\Config;
use Garden\Exception\Error;
use Garden\Helpers\Arr;
use Garden\Helpers\Files;
use Garden\Traits\Instance;
use Garden\Translate;

class Addons {

    use Instance;

    /**
     * @param string $name
     * @param array $data
     * @param bool $rewrite
     */
    public function saveConfig(string $name, array $data, bool $rewrite = false)
    {
        $file = GDN_CONF . "/$name.php";
        $config = !$rewrite && file_exists($file) ? Arr::load($file) : [];
        $newConfig = array_merge($config, $data);

        Arr::save($newConfig, $file);
    }

    /**
     * Addon instalation
     *
     * @param string $name
     * @throws Error
     */
    public function install(string $name)
    {
        $allAddons = \Garden\Addons::all();
        $addon = $allAddons[$name] ?? null;

        if (!$addon) {
            throw new Error(Translate::get('Addon not found'));
        }

        $installed = Config::get("addons.$name") !== null;


        $structureFile = "{$addon['dir']}/Settings/structure.php";
        if (file_exists($structureFile)) {
            Files::getInclude($structureFile);
        }

        $installFile = "{$addon['dir']}/Settings/install.php";

        if (!$installed && file_exists($installFile)) {
            Files::getInclude($installFile);
        }

    }

    /**
     * form save
     *
     * @param $addon
     * @param $enable
     */
    public function save($addon, $enable)
    {
        $this->saveConfig('addons', [$addon => (bool)$enable]);
    }
}