<?php

namespace Iphp\FileStoreBundle\Tests;

use Doctrine\ORM\Proxy\Proxy;

/**
 * DummyEntityProxyORM.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class DummyEntityProxyORM extends DummyEntity implements Proxy
{
    public function __load() { }

    public function __isInitialized() { }
}
