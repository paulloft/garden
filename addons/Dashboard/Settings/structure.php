<?php

use Addons\Dashboard\Models\Permission;
use Addons\Dashboard\Models\Db\Structure;

$construct = Structure::instance();

// User Table
$explicit = true;
$drop = false;

$construct->table('users');
$construct
    ->primary('id')
    ->column('login', 'varchar(50)', false, 'index')
    ->column('password', 'varbinary(100)')// keep this longer because of some imports.
    ->column('email', 'varchar(200)', false, 'index')
    ->column('name', 'varchar(50)', true)
    ->column('gender', ['u', 'm', 'f'], 'u')
    ->column('admin', 'tinyint(1)', '0')
    ->column('active', 'tinyint(1)', '1')
    ->column('last_visit', 'datetime', true, 'index')
    ->column('created_at', 'datetime', true, 'index')
    ->column('created_by', 'int(10)', true, 'index')
    ->column('updated_at', 'datetime', true)
    ->column('updated_by', 'int(10)', true)
    ->set($explicit, $drop);

$construct->table('users_groups');
$construct
    ->primary('id')
    ->column('user_id', 'int(200)', false, 'key')
    ->column('group_id', 'int(200)', false, 'key')
    ->set($explicit, $drop);

$construct->table('groups');
$construct
    ->primary('id')
    ->column('name', 'varchar(100)')
    ->column('description', 'varchar(500)', true)
    ->column('sort', 'int(10)', true)
    ->column('active', 'tinyint(1)', 1)
    ->column('created_at', 'datetime', true, 'index')
    ->column('created_by', 'int(10)', true, 'index')
    ->column('updated_at', 'datetime', true)
    ->column('updated_by', 'int(10)', true)
    ->set($explicit, $drop);

$construct->table('permissions');
$construct
    ->primary('id')
    ->column('code', 'varchar(100)')
    ->column('def', 'tinyint(1)', true)
    ->column('sort', 'int(10)', true)
    ->set($explicit, $drop);

$construct->table('groups_permissions');
$construct
    ->primary('id')
    ->column('group_id', 'int(200)', false, 'key')
    ->column('permission_id', 'int(200)', false, 'key')
    ->set($explicit, $drop);


$construct->table('session');
$construct
    ->primary('session_id', 'char(32)')
    ->column('user_id', 'int(200)', false, 'key')
    ->column('last_activity', 'datetime', true)
    ->column('last_ip', 'varchar(50)', true)
    ->column('expire', 'datetime', true)
    ->column('user_agent', 'varchar(250)', true)
    ->column('created_at', 'datetime', true, 'index')
    ->set($explicit, $drop);


Permission::instance()
    ->define('dashboard.user.view')
    ->define('dashboard.user.add')
    ->define('dashboard.user.edit')
    ->define('dashboard.user.delete')
    ->define('dashboard.group.view')
    ->define('dashboard.group.add')
    ->define('dashboard.group.edit')
    ->define('dashboard.group.delete');