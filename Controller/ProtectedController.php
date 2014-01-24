<?php

namespace Iphp\FileStoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;


class ProtectedController extends Controller
{

    public function downloadAction(Request $request, $path)
    {
        $mappingConfig = $this->get('iphp.filestore.mapping.factory')
            ->getMappingConfig($request->get('_iphpfilestore_mapping'));


        if (!$mappingConfig) throw $this->createNotFoundException('File not found');


        $protectedFileName = $mappingConfig['protected_dir'] . '/' . $path;


        if (!file_exists($protectedFileName))
            throw $this->createNotFoundException('File ' . $mappingConfig['upload_path'] . '/' . $path . ' not found');


        if (!is_readable($protectedFileName))
            throw $this->createNotFoundException('File ' . $mappingConfig['upload_path'] . '/' . $path . ' not readable');



        if ($this->getUser() && array_intersect($mappingConfig['protected_roles'],$this->getUser()->getRoles()))
        {
            $response = new BinaryFileResponse($protectedFileName);
            return $response;
        }
        else
        {


           return $this->noAccessToFileAction();


        }



    }





    protected function noAccessToFileAction ()
    {
        if (!$this->getUser()) return $this->unauthorizedAction();

        else return $this->insufficientRightsAction();
    }



    protected function unauthorizedAction()
    {
        throw new HttpException(401, 'Unauthorized access.' );

        return array();
    }


    protected function insufficientRightsAction()
    {
        return $this->render ("IphpFileStoreBundle:Protected:insufficient.html.twig", array ())->setStatusCode(Response::HTTP_FORBIDDEN);
    }
}
