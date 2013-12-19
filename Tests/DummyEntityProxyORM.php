<?php

namespace Iphp\FileStoreBundle\Tests;

use Closure;
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

    /**
     * Sets the callback to be used when cloning the proxy. That initializer should accept
     * a single parameter, which is the cloned proxy instance itself.
     *
     * @param Closure|null $cloner
     *
     * @return void
     */
    public function __setCloner(Closure $cloner = null)
    {
        // TODO: Implement __setCloner() method.
    }

    /**
     * Retrieves the callback to be used when cloning the proxy.
     *
     * @see __setCloner
     *
     * @return Closure|null
     */
    public function __getCloner()
    {
        // TODO: Implement __getCloner() method.
    }

    /**
     * Marks the proxy as initialized or not.
     *
     * @param boolean $initialized
     *
     * @return void
     */
    public function __setInitialized($initialized)
    {
        // TODO: Implement __setInitialized() method.
    }

    /**
     * Retrieves the list of lazy loaded properties for a given proxy
     *
     * @return array Keys are the property names, and values are the default values
     *               for those properties.
     */
    public function __getLazyProperties()
    {
        // TODO: Implement __getLazyProperties() method.
    }

    /**
     * Retrieves the initializer callback used to initialize the proxy.
     *
     * @see __setInitializer
     *
     * @return Closure|null
     */
    public function __getInitializer()
    {
        // TODO: Implement __getInitializer() method.
    }

    /**
     * Sets the initializer callback to be used when initializing the proxy. That
     * initializer should accept 3 parameters: $proxy, $method and $params. Those
     * are respectively the proxy object that is being initialized, the method name
     * that triggered initialization and the parameters passed to that method.
     *
     * @param Closure|null $initializer
     *
     * @return void
     */
    public function __setInitializer(Closure $initializer = null)
    {
        // TODO: Implement __setInitializer() method.
    }


}
