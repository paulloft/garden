<?php
if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID = false)
    {
        return Garden\Gdn::factory('permission')->check($permission, $userID);
    }
}