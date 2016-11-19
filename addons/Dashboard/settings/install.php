<?php
namespace Addons\Dashboard;

$groupModel = Models\Groups::instance();

$groupModel->save([
    'name' => t('Administrator'),
    'description' => 'Full access to all',
], 1);