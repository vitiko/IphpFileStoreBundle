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
            array ('csrf_protection' => false))


       // $uploadForm = $this->createFormBuilder($photo)
            ->add('title')
            ->add('date','date')

            //Using standart field type
            ->add ('photo','file')
            ->getForm();

        if ($request->isMethod('POST')) {


           // print 'POOST! - ';

        //    print_r ($request->get('title'));


         //   print_r ($request);
         //   exit();

            $uploadForm->bind($request);



            if ($uploadForm->isValid()) {

              //  print ' VALID !';

               // print_r ($photo->getPhoto());

                //exit();

                $em->persist($photo);
                $em->flush();
                return $this->redirect($this->generateUrl('TestBundle'));
            }
            else print "\n\n".$uploadForm->getErrorsAsString();
        }

        return $this->render('TestBundle:Photo:index.html.twig', array(
            'uploadForm' => $uploadForm->createView(),
            'photos' => $em->getRepository ('TestBundle:Photo')->findAll()
        ));
    }
}
