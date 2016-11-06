<?php
if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID = false)
    {
        return Garden\Factory::get('permission')->check($permission, $userID);
    }
}