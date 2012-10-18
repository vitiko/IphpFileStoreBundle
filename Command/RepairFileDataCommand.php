<?php

namespace Iphp\FileStoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Sensio\Bundle\GeneratorBundle\Command\Validators;

class RepairFileDataCommand extends GenerateDoctrineCommand
{

    protected function configure()
    {
        $this->setName('iphp:filestore:repair')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'The entity class name (shortcut notation)')
            ->addOption('field', null, InputOption::VALUE_REQUIRED, 'The field with file data');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $dialog = $this->getDialogHelper();


        $entity = Validators::validateEntityName($input->getOption('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);


        $field = $input->getOption('field');

        print $bundle . ":" . $entity . "->" . $field;

        $repo = $this->getContainer()->get('doctrine')->getRepository($bundle . ":" . $entity);
        $em = $this->getContainer()->get('doctrine')->getManager();

        $ids = $em->createQuery(
            "SELECT e.id FROM " . $bundle . ":" . $entity . " e ORDER BY e.id ASC"
        )->setMaxResults(16000)->getArrayResult();


        foreach ($ids as $pos => $e) {

            $toFlush = false;
            $entity = $repo->findOneById($e['id']);


            $fileData = $entity->{'get' . ucfirst($field)}();


            if (!$fileData) continue;
            $filename = $fileData['dir'] . '/' . $fileData['fileName'];

            if (file_exists($filename)) {
                //print "\n exists " . $filename;
                $byFileNameExists = true;

            } else {
                //print "\n NO exists " . $filename;
                $byFileNameExists = false;
            }


            $webDir = $this->getContainer()->getParameter('iphp.web_dir');
            $filenameByPath = $webDir . $fileData['path'];


            if (file_exists($filenameByPath)) {
              //  print "\n By Path exists " . $filenameByPath;

                $byFilePathExists = true;
            } else {
              //  print "\n By Path NO exists " . $filenameByPath;
                $byFilePathExists = false;
            }


            print "\n\n ".$entity->getId().' '.(string) $entity;

            if ($byFilePathExists && $byFileNameExists)
            {
                print ": NO PROBLEM";
                continue;
            }

            if (!$byFilePathExists && $byFileNameExists )
            {


                print  "\nresave ".$filename;
                print file_exists($filename) ? " EXISTS":" NO EXISTS";

                $fileObj = new \Symfony\Component\HttpFoundation\File\File($filename);

                print "\n Obj created";
                $entity->{'set' . ucfirst($field)} ( $fileObj);
                $em->persist ($entity);
                $toFlush = true;
                print 'saved';
            }



           if ($pos%20 == 0 && $toFlush) $em->flush();

            if ($pos%100 == 0) $em->clear();
        }
    }


    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }
}
