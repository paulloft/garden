<?php
namespace Addons\Skeleton;
use Garden\Gdn;
$construct = Gdn::structure();

// User Table
$construct->table('User');

$explicit = true; 
$drop = false;

$construct
    ->primaryKey('userID')
    ->column('name', 'varchar(50)', true, 'key')
    ->column('password', 'varbinary(100)') // keep this longer because of some imports.
    ->column('hashMethod', 'varchar(10)', true)
    ->column('photo', 'varchar(255)', null)
    ->column('title', 'varchar(100)', null)
    ->column('location', 'varchar(100)', null)
    ->column('about', 'text', true)
    ->column('email', 'varchar(200)', false, 'index')
    ->column('showEmail', 'tinyint(1)', '0')
    ->column('gender', array('u', 'm', 'f'), 'u')
    ->column('countVisits', 'int', '0')
    ->column('countInvitations', 'int', '0')
    ->column('countNotifications', 'int', null)
    ->column('inviteUserid', 'int', true)
    ->column('discoverytext', 'text', true)
    ->column('preferences', 'text', true)
    ->column('permissions', 'text', true)
    ->column('attributes', 'text', true)
    ->column('dateSetInvitations', 'datetime', true)
    ->column('dateOfBirth', 'datetime', true)
    ->column('dateFirstVisit', 'datetime', true)
    ->column('dateLastaCtive', 'datetime', true, 'index')
    ->column('lastIpAddress', 'varchar(15)', true)
    ->column('allIpAddresses', 'varchar(100)', true)
    ->column('dateInserted', 'datetime', false, 'index')
    ->column('insertIpAddress', 'varchar(15)', true)
    ->column('dateUpdated', 'datetime', true)
    ->column('updateIpAddress', 'varchar(15)', true)
    ->column('hourOffset', 'int', '0')
    ->column('score', 'float', null)
    ->column('admin', 'tinyint(1)', '0')
    ->column('confirmed', 'tinyint(1)', '1') // 1 means email confirmed, otherwise not confirmed
    ->column('verified', 'tinyint(1)', '0') // 1 means verified (non spammer), otherwise not verified
    ->column('banned', 'tinyint(1)', '0') // 1 means banned, otherwise not banned
    ->column('deleted', 'tinyint(1)', '0')
    ->column('points', 'int', 0)
    ->set($explicit, $drop);