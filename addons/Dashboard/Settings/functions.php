<?php

if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID = false)
    {
        return Garden\Gdn::permission()->check($permission, $userID);
    }
}
