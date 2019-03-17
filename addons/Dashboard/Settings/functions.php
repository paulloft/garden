<?php

if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID = false)
    {
        return \Addons\Dashboard\Models\Permission::instance()->check($permission, $userID);
    }
}
