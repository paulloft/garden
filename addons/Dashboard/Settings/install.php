<?php

use Addons\Dashboard\Models\Groups;

$groupModel = Groups::instance();

$groupModel->insertOrUpdate(1, [
    'name' => t('Administrator'),
    'description' => 'Full access to all',
]);
