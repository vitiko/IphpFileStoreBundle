<?php

namespace Iphp\FileStoreBundle\Tests\Functional\TestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Iphp\FileStoreBundle\Form\Type\FileType as IphpFileType;

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

        $uploadForm = $this->get('form.factory')->createNamedBuilder(null, FormType::class, $photo,
            array('csrf_protection' => false))
            ->add('title', TextType::class)
            ->add('date', DateType::class)
            //Using standart field type
            ->add('photo', FileType::class)
            ->getForm();

        $uploadForm->handleRequest($request);

        if ($uploadForm->isSubmitted() && $uploadForm->isValid()) {

            $em->persist($photo);
            $em->flush();
            return $this->redirect($this->generateUrl('photo_index'));

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
            ->add('title', TextType::class)
            ->add('date', DateType::class)
            //Using  field type with showing file/image info
            ->add('photo', IphpFileType::class)
            ->getForm();


        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em->persist($photo);
            $em->flush();
            return $this->redirect($this->generateUrl('photo_edit', array('id' => $photo->getId())));

        }

        return $this->render('TestBundle:Photo:edit.html.twig', array(

            'photo' => $photo,
            'editForm' => $editForm->createView(),
        ));

    }

}
