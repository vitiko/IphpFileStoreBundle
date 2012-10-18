<?php
/**
 * Created by Vitiko
 * Date: 08.08.12
 * Time: 16:59
 */

namespace Iphp\FileStoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Iphp\FileStoreBundle\File\File as IphpFile;

class FileDataViewTransformer implements DataTransformerInterface
{


        public function transform($fileDataFromDb)
        {

          //  print 'view transform';
    //    var_dump ($fileDataFromDb);
        //     print '<hr>';
            return $fileDataFromDb;
        }


        /**
         * array with 2 items - file (UploadedFile) and delete (checkbox)
         * @param $fileDataFromForm
         * @return int
         */
        public function reverseTransform($fileDataFromForm)
        {
      //   print 'reverseViewTransform';
            //            var_dump ($fileDataFromForm);
//
      //      print '<hr>';

            return $fileDataFromForm;

        }


}
