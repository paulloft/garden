<?php
namespace Addons\Skeleton;
use Garden\Gdn;
$Construct = Gdn::Structure();

// User Table
$Construct->Table('User');

$Explicit = true; 
$Drop = false;

$Construct
    ->PrimaryKey('UserID')
    ->Column('Name', 'varchar(50)', true, 'key')
    ->Column('Password', 'varbinary(100)') // keep this longer because of some imports.
    ->Column('HashMethod', 'varchar(10)', TRUE)
    ->Column('Photo', 'varchar(255)', NULL)
    ->Column('Title', 'varchar(100)', NULL)
    ->Column('Location', 'varchar(100)', NULL)
    ->Column('About', 'text', TRUE)
    ->Column('Email', 'varchar(200)', FALSE, 'index')
    ->Column('ShowEmail', 'tinyint(1)', '0')
    ->Column('Gender', array('u', 'm', 'f'), 'u')
    ->Column('CountVisits', 'int', '0')
    ->Column('CountInvitations', 'int', '0')
    ->Column('CountNotifications', 'int', NULL)
    ->Column('InviteUserID', 'int', TRUE)
    ->Column('DiscoveryText', 'text', TRUE)
    ->Column('Preferences', 'text', TRUE)
    ->Column('Permissions', 'text', TRUE)
    ->Column('Attributes', 'text', TRUE)
    ->Column('DateSetInvitations', 'datetime', TRUE)
    ->Column('DateOfBirth', 'datetime', TRUE)
    ->Column('DateFirstVisit', 'datetime', TRUE)
    ->Column('DateLastActive', 'datetime', TRUE, 'index')
    ->Column('LastIPAddress', 'varchar(15)', TRUE)
    ->Column('AllIPAddresses', 'varchar(100)', TRUE)
    ->Column('DateInserted', 'datetime', FALSE, 'index')
    ->Column('InsertIPAddress', 'varchar(15)', TRUE)
    ->Column('DateUpdated', 'datetime', TRUE)
    ->Column('UpdateIPAddress', 'varchar(15)', TRUE)
    ->Column('HourOffset', 'int', '0')
    ->Column('Score', 'float', NULL)
    ->Column('Admin', 'tinyint(1)', '0')
    ->Column('Confirmed', 'tinyint(1)', '1') // 1 means email confirmed, otherwise not confirmed
    ->Column('Verified', 'tinyint(1)', '0') // 1 means verified (non spammer), otherwise not verified
    ->Column('Banned', 'tinyint(1)', '0') // 1 means banned, otherwise not banned
    ->Column('Deleted', 'tinyint(1)', '0')
    ->Column('Points', 'int', 0)
    ->Set($Explicit, $Drop);