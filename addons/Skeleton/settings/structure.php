<?php
namespace Addons\Skeleton;
use Garden\Gdn;
$construct = Gdn::structure();
$permission = Gdn::factory('permission');

// User Table

$explicit = true;
$drop = false;
/*
$construct->table('users');
$construct
    ->primary('id')
    ->column('login', 'varchar(50)', false, 'index')
    ->column('password', 'varbinary(100)') // keep this longer because of some imports.
    ->column('email', 'varchar(200)', false, 'index')
    ->column('name', 'varchar(50)')
    ->column('phone', 'varchar(100)')
    ->column('mobile', 'varchar(100)', true)
    ->column('gender', array('u', 'm', 'f'), 'u')
    ->column('lastVisit', 'datetime', true, 'index')
    ->column('dateInserted', 'datetime', true, 'index')
    ->column('dateUpdated', 'datetime', true)
    ->column('dateDeleted', 'datetime', true)
    ->column('admin', 'tinyint(1)', '0')
    ->column('active', 'tinyint(1)', '1')
    ->column('deleted', 'tinyint(1)', '0')
    ->set($explicit, $drop);
*/

/*
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
*/