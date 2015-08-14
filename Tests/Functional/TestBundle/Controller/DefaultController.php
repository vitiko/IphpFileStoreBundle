<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Iphp\FileStoreBundle\Tests\Functional\TestBundle\Entity\Photo;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = new Photo();

        $uploadForm = $this->get('form.factory')->createNamedBuilder(null, 'form', $photo,
            array('csrf_protection' => false))
            ->add('title')
            ->add('date', 'date')
        //Using standart field type
            ->add('photo', 'file')
            ->getForm();

        if ($request->isMethod('POST')) {
            $uploadForm->bind($request);

            if ($uploadForm->isValid()) {

                $em->persist($photo);
                $em->flush();
                return $this->redirect($this->generateUrl('photo_index'));
            } else print "\n\n" . $uploadForm->getErrorsAsString();
        }

        return $this->render('TestBundle:Photo:index.html.twig', array(
            'uploadForm' => $uploadForm->createView(),
            'photos' => $em->getRepository('TestBundle:Photo')->findAll()
        ));
    }


    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('TestBundle:Photo')->findOneById($id);

        $editForm = $this->createFormBuilder($photo)
            ->add('title')
            ->add('date', 'date')
        //Using  field type with showing file/image info
            ->add('photo', 'iphp_file')
            ->getForm();


        if ($request->isMethod('POST')) {

            $editForm->bind($request);
 
            if ($editForm->isValid()) {
                $em->persist($photo);
                $em->flush();
                return $this->redirect($this->generateUrl('photo_edit', array('id' => $photo->getId())));
            } else print "\n\n" . $uploadForm->getErrorsAsString();
        }

        return $this->render('TestBundle:Photo:edit.html.twig', array(

            'photo' => $photo,
            'editForm' => $editForm->createView(),
        ));

    }

}
