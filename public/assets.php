<?php

namespace Garden;

use function define;
use function dirname;

define('PATH_PUBLIC', __DIR__);
define('PATH_ROOT', dirname(__DIR__));

require_once PATH_ROOT . '/bootstrap.php';

$request = new Request();

$addonName = $request->getQuery('addon');
$path = $request->getQuery('path');

$addon = Addons::enabled($addonName);

if ($addon) {
    $addonDir = val('dir', $addon);
    $filePath = "$addonDir/Assets/$path";
    $filePath = str_replace('../', '/', $filePath);
    if (file_exists($filePath)) {
        $pathinfo = pathinfo($filePath);

        switch ($pathinfo['extension']) {
            case 'css':
                $mime = 'text/css';
                break;
            case 'js':
                $mime = 'application/javascript';
                break;

            default:
                $mime = mime_content_type($filePath);
                break;
        }

        header('Content-Type: ' . $mime);

        $handle = fopen($filePath, 'rb');

        while (!feof($handle)) {
            echo fread($handle, 1024 * 1024);
            ob_flush();
            flush();
        }

        fclose($handle);

        exit;
    }
}

Application::instance()->run();