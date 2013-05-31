<?php
namespace Iphp\FileStoreBundle\Tests\Functional;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class AppKernel extends Kernel
{
    protected $config;

    protected $testEnv;

    public function __construct($config, $testEnv = 'default')
    {
        //separate generated container
        parent::__construct($testEnv . '_' . substr(md5($config), 0, 3), true);

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($config)) {
            $config = __DIR__ . '/config/' . $config;
        }

        if (!file_exists($config)) {
            throw new \RuntimeException(sprintf('The config file "%s" does not exist.', $config));
        }

        $this->config = $config;
        $this->testEnv = $testEnv;
    }

    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),

            new  \Iphp\FileStoreBundle\IphpFileStoreBundle(),
            new  \Iphp\FileStoreBundle\Tests\Functional\TestBundle\TestBundle(),
            new  \Iphp\FileStoreBundle\Tests\Functional\TestXmlConfigBundle\TestXmlConfigBundle()
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->config);
    }

    public function getCacheDir()
    {
        return $this->getTestEnvDir() . '/app/cache/' . substr(md5($this->config), 0, 3) . '';
    }

    public function getConfig()
    {
        return $this->config;
    }


    public static function getTestBaseDir()
    {
      return sys_get_temp_dir() . '/IphpFileStoreTestBundle';
    }



    public function getTestEnvDir()
    {
        return self::getTestBaseDir().'/'. $this->testEnv;
    }


    public function makeTestEnvDir()
    {
        $fs = new Filesystem();
        $fs->remove($this->getTestEnvDir());
        $fs->mkdir($this->getTestEnvDir());
    }


    protected function getKernelParameters()
    {


        return array_merge(
            parent::getKernelParameters(), array(
                'kernel.test_env' => $this->testEnv,
                'kernel.test_env_dir' => $this->getTestEnvDir(),

            )
        );
    }

    public function getTestEnv()
    {
        return $this->testEnv;
    }


}