<?php
namespace Addons\Dashboard;

$groupModel = Models\Groups::instance();

$groupModel->insertOrUpdate(1, [
    'name' => t('Administrator'),
    'description' => 'Full access to all',
]);

