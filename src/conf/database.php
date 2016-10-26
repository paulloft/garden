<?php
return [
    "driver" => "PDO",
    "type" => "mysql",
    "host" => "localhost",
    "database" => "test",
    "username" => "test",
    "password" => "test",
    "charset" => "utf8",
    "caching" => false,
    "tablePrefix" => "",
    "options" => [
        "12" => false,
        "1000" => true,
        "1002" => "set names 'utf8'" 
    ],
    "identifier" => '`',
    "storageEngine" => "innodb"
];