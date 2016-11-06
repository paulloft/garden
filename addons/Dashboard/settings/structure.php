<?php
namespace Addons\Dashboard;
use Garden\Factory;
use Garden\Gdn;
$construct = Gdn::structure();
$permission = Factory::get('permission');

// User Table
$explicit = true;
$drop = false;

$construct->table('users');
$construct
    ->primary('id')
    ->column('login', 'varchar(50)', false, 'index')
    ->column('password', 'varbinary(100)') // keep this longer because of some imports.
    ->column('email', 'varchar(200)', false, 'index')
    ->column('name', 'varchar(50)', true)
    ->column('gender', array('u', 'm', 'f'), 'u')
    ->column('lastVisit', 'datetime', true, 'index')
    ->column('dateInserted', 'datetime', true, 'index')
    ->column('dateUpdated', 'datetime', true)
    ->column('dateDeleted', 'datetime', true)
    ->column('admin', 'tinyint(1)', '0')
    ->column('active', 'tinyint(1)', '1')
    ->column('deleted', 'tinyint(1)', '0')
    ->set($explicit, $drop);

$construct->table('users_groups');
$construct
    ->primary('id')
    ->column('userID', 'int', false, 'key')
    ->column('groupID', 'int', false, 'key')
    ->set($explicit, $drop);

$construct->table('groups');
$construct
    ->primary('id')
    ->column('name', 'varchar(100)')
    ->column('description', 'varchar(500)', true)
    ->column('sort', 'int', true)
    ->column('dateInserted', 'datetime', true, 'index')
    ->column('dateUpdated', 'datetime', true)
    ->column('dateDeleted', 'datetime', true)
    ->column('active', 'tinyint(1)', 1)
    ->column('deleted', 'tinyint(1)', 0)
    ->set($explicit, $drop);

$construct->table('permissions');
$construct
    ->primary('id')
    ->column('code', 'varchar(100)')
    ->column('def', 'tinyint(1)', true)
    ->column('sort', 'int', true)
    ->set($explicit, $drop);

$construct->table('groups_permissions');
$construct
    ->primary('id')
    ->column('groupID', 'int', false, 'key')
    ->column('permissionID', 'int', false, 'key')
    ->set($explicit, $drop);


$construct->table('session');
$construct
    ->primary('sessionID', 'char(32)')
    ->column('userID', 'int', false, 'key')
    ->column('lastActivity', 'datetime', true)
    ->column('dateInserted', 'datetime', true, 'index')
    ->column('expire', 'datetime', true)
    ->column('userAgent', 'varchar(250)', true)
    ->set($explicit, $drop);


$permission
    ->define('dashboard.user.view')
    ->define('dashboard.user.add')
    ->define('dashboard.user.edit')
    ->define('dashboard.user.delete')

    ->define('dashboard.group.view')
    ->define('dashboard.group.add')
    ->define('dashboard.group.edit')
    ->define('dashboard.group.delete')
;