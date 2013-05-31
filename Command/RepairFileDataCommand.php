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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = $input->getOption('entity');
        $field = $input->getOption('field');
        $force = $input->getOption('force') ? true : false;

        $maxResults = $input->getOption('maxresults');
        if (!$maxResults || !is_numeric($maxResults)) $maxResults = 1000;

        $webDir = $input->getOption('webdir');

        if (!$webDir && $this->getContainer()->has('iphp.web_dir'))
            $webDir = $this->getContainer()->getParameter('iphp.web_dir');

        if (!$webDir) $webDir = str_replace('\\', '/', realpath($this->getContainer()->getParameter('kernel.root_dir') . '/../web/'));

        $propertyMappingFactory = $this->getContainer()->get('iphp.filestore.mapping.factory');

        if (!$webDir) throw new \InvalidArgumentException ('
         For resolving IphpFileStoreBundle uploaded files need to set --webdir option');


        list($bundle, $entity) = explode(':', $entity);

        $repo = $this->getContainer()->get('doctrine')->getRepository($bundle . ":" . $entity);
        $em = $this->getContainer()->get('doctrine')->getManager();

        $ids = $em->createQuery(
            "SELECT e.id FROM " . $bundle . ":" . $entity . " e ORDER BY e.id ASC"
        )->setMaxResults($maxResults)->getArrayResult();


        foreach ($ids as $pos => $e) {

            $toFlush = false;
            $entity = $repo->findOneById($e['id']);
            $fileData = $entity->{'get' . ucfirst($field)}();

            if (!$fileData) continue;

            $fullFileNameByWebPath = $webDir . $fileData['path'];
            $fullFileNameByWebPathExists = file_exists($fullFileNameByWebPath);

            $propertyMapping = $propertyMappingFactory->getMappingFromField(
                $entity, new \ReflectionClass($entity), $field);

            $resolvedFileName = $propertyMapping->resolveFileName($fileData['fileName']);
            $resolvedFileNameExists = file_exists($resolvedFileName) ? 'exists' : 'NO';

            if (!$fullFileNameByWebPathExists && !$resolvedFileNameExists) {
                die ("can't find file ");
            }

            if ($fullFileNameByWebPathExists && $resolvedFileNameExists && !$force) continue;

            //uploadedFile because need to move to new destination (not copy)
            $file = new UploadedFile ($fullFileNameByWebPathExists ? $fullFileNameByWebPath : $resolvedFileNameExists,
                $fileData['originalName'], $fileData['mimeType'], null, null, true);

            $entity->{'set' . ucfirst($field)} ($file);

            $em->persist($entity);

            $toFlush = true;
            if ($pos % 20 == 0 && $toFlush) $em->flush();
            if ($pos % 100 == 0) $em->clear();
        }

        $em->flush();
    }
}
