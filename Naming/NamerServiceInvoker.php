<?php
namespace Iphp\FileStoreBundle\Naming;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class NamerServiceInvoker
{

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    public function rename($serviceName, $method, PropertyMapping $propertyMapping, $fileName, $args = array())
    {

        return call_user_func(
            array($this->container->get($serviceName), $method . 'Rename'),
            $propertyMapping,
            $fileName,
            $args);

    }


    public function resolveCollision ($serviceName, $fileName, $attempt)
    {
        return call_user_func(
            array($this->container->get($serviceName), 'resolveCollision'), $fileName, $attempt);
    }

}
