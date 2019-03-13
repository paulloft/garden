<?php
return [
    "driver" => "memcached",
    "file" => [
        "defaultLifetime" => 3600,
        "cacheDir" => "cache"
    ],
    "memcache" => [
        "host" => "localhost",
        "port" => "11211",
        "keyPrefix" => "gdn_"
    ],
    "memcached" => [
        "host" => "localhost",
        "port" => "11211",
        "keyPrefix" => "gdn_"
    ],
    "redis" => [
        "defaultLifetime" => 3600,
        "host" => "localhost",
        "port" => "6379",
        "keyPrefix" => "gdn_",
        "timeout" => 0,
        "reserved" => null,
        "retry_interval" => 0
    ],
    "host" => "localhost",
    "database" => "",
    "username" => "",
    "password" => "",
    "tablePrefix" => ""
];