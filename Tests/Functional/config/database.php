<?php

$path = $container->getParameter ('kernel.test_env_dir').'/db.sqllite';


$container->loadFromExtension('doctrine', array(
    'dbal' => array(
        'driver' => 'pdo_sqlite',
        'path' => $path,
    ),
));