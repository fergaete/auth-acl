<?php
return array(
    'auth.database' => array(
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost',
        'dbname'   => 'api',
        'user'     => 'root',
        'password' => 'toor',
        'charset'  => 'utf8',
        'driverOptions' => array(1002 => 'SET NAMES utf8')
    ),
    'auth.debug'         => false,
    'auth.api.version'   => '1',
    'auth.date.timezone' => 'America/Santiago'
);