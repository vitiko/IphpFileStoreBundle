<?php
namespace Iphp\FileStoreBundle\Tests\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class BaseTestCase extends WebTestCase
{
    static protected function createKernel(array $options = array())
    {
        return self::$kernel = new AppKernel(
            isset($options['config']) ? $options['config'] : 'default.yml'
        );
    }

    protected function setUp()
    {
        $fs = new Filesystem();
        $fs->remove(sys_get_temp_dir() . '/IphpFileStoreTestBundle/');
    }


    protected function getEntityManager()
    {
        return self::$kernel->getContainer()->get('doctrine.orm.entity_manager');
    }

    protected final function importDatabaseSchema()
    {
        $em = $this->getEntityManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($em);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);
        }
    }
}
