<?php
namespace Garden;

define('PATH_PUBLIC', __DIR__);
define('PATH_ROOT', realpath(PATH_PUBLIC.'/../'));
define('DEBUG', false);

require_once PATH_ROOT.'/bootstrap.php';

$request = new Request();

$addonName = $request->getQuery('addon');
$path = $request->getQuery('path');

$addon = Addons::enabled($addonName);

if ($addon) {
    $addonDir = val('dir', $addon);
    $filePath = $addonDir.'/assets/'.$path;
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

        header('Content-Type: '.$mime);
        
        $buffer = '';
        $handle = fopen($filePath, 'rb');

        while (!feof($handle)) {
            $buffer = fread($handle, (1024*1024));
            echo $buffer;
            ob_flush();
            flush();
        }

        fclose($handle);

        exit;
    }
}
    
Gdn::app()->run();