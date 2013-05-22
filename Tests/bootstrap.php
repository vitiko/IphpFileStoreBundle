<?php

$autoloadFiles = array ( __DIR__.'/../vendor/autoload.php', __DIR__.'/../../../../../autoload.php');

foreach ($autoloadFiles as $autoloadFile)
{
    if (!is_file($autoloadFile)) continue;

    require $autoloadFile;
    return;
}

throw new \LogicException('Could not find autoload.php in vendor/. Did you run "composer install --dev"?');