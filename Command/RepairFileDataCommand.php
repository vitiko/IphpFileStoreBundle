<?php

namespace Iphp\FileStoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class RepairFileDataCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->setName('iphp:filestore:repair')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name (shortcut notation)')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'The field with file data')
            ->addOption('maxresults', null, InputOption::VALUE_OPTIONAL, 'Max results limitation')
            ->addOption('webdir', null, InputOption::VALUE_OPTIONAL, 'Web dir for searching file')
            ->addOption('force', null, InputOption::VALUE_OPTIONAL, 'Force reupload and rename files');

    }


    protected function getPropertyMappingFactory()
    {
        return $this->getContainer()->get('iphp.filestore.mapping.factory');
    }


    protected function getWebDir(InputInterface $input)
    {
        $webDir = $input->getOption('webdir');

        if (!$webDir && $this->getContainer()->has('iphp.web_dir'))
            $webDir = $this->getContainer()->getParameter('iphp.web_dir');

        if (!$webDir) $webDir = str_replace('\\', '/', realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web/'));

        if (!$webDir) throw new \InvalidArgumentException ('
         For resolving IphpFileStoreBundle uploaded files need to set --webdir option');

        return $webDir;
    }


    function getMaxResults(InputInterface $input)
    {
        $maxResults = $input->getOption('maxresults');
        if (!$maxResults || !is_numeric($maxResults)) $maxResults = 1000;

        return $maxResults;
    }


    function getEntityManager()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }

    function getMappingFromField($entity, $field)
    {
        return $this->getPropertyMappingFactory()->getMappingFromField($entity, new \ReflectionClass($entity), $field);
    }


    function  getRepository($entityFullName)
    {
        return $this->getEntityManager()->getRepository($entityFullName);
    }

    function getEntityIds($entityFullName, $maxResults)
    {
        return $this->getEntityManager()->createQuery(
            "SELECT e.id FROM " . $entityFullName . " e ORDER BY e.id ASC"
        )->setMaxResults($maxResults)->getArrayResult();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entityFullName = $input->getOption('entity');
        $field = $input->getOption('field');
        $force = $input->getOption('force') ? true : false;
        $webDir = $this->getWebDir($input);


        foreach ($this->getEntityIds($entityFullName, $this->getMaxResults($input)) as $pos => $e) {

            $toFlush = false;
            $entity = $this->getRepository($entityFullName)->findOneById($e['id']);
            $fileData = $entity->{'get' . ucfirst($field)}();

            if (!$fileData) continue;

            $fileNameByWebPath = $webDir . $fileData['path'];
            $fileNameByWebPathExists = file_exists($fileNameByWebPath);

            $resolvedFileName = $this->getMappingFromField($entity, $field)->resolveFileName($fileData['fileName']);
            $resolvedFileNameExists = file_exists($resolvedFileName) ? 'exists' : 'NO';

            if (!$fileNameByWebPathExists && !$resolvedFileNameExists) {
                $output->writeln("can't find file ");
                continue;
            }

            if ($fileNameByWebPathExists && $resolvedFileNameExists && !$force) continue;

            //uploadedFile because need to move to new destination (not copy)
            $file = new UploadedFile ($fileNameByWebPathExists ? $fileNameByWebPath : $resolvedFileNameExists,
                $fileData['originalName'], $fileData['mimeType'], null, null, true);

            $entity->{'set' . ucfirst($field)} ($file);

            $this->getEntityManager()->persist($entity);

            $toFlush = true;
            if ($pos % 20 == 0 && $toFlush) $this->getEntityManager()->flush();
            if ($pos % 100 == 0) $this->getEntityManager()->clear();
        }

        $this->getEntityManager()->flush();
    }
}
