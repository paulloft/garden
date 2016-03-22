<?php
return [
    "file" => [
        "defaultLifetime" => 3600,
        "cacheDir" => "cache"
    ],
    "memcache" => [
        "defaultLifetime" => 3600,
        "host" => "localhost",
        "port" => "11211",
        "persistent" => false
    ],
    "memcached" => [
        "defaultLifetime" => 3600,
        "host" => "localhost",
        "port" => "11211",
        "persistent" => false
    ]
];