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

class FileDataTransformer implements DataTransformerInterface
{


        public function transform($fileDataFromDb)
        {


         //  print 'transform';
         //  var_dump ($fileDataFromDb);
            return $fileDataFromDb;
        }


        /**
         * array with 2 items - file (UploadedFile) and delete (checkbox)
         * @param $fileDataFromForm
         * @return int
         */
        public function reverseTransform($fileDataFromForm)
        {
        // print 'reverseTransform';
              //          var_dump ($fileDataFromForm);

//exit();

///            if (isset($fileDataFromForm['fileName']) && !isset($fileDataFromForm['file'])) return $fileDataFromForm;


            if  ($fileDataFromForm['delete'])
            {


                if (isset($fileDataFromForm['dir']))
                {


                  //File may no exists
                  try
                  {
                   $file = new IphpFile ($fileDataFromForm['dir'].'/'.$fileDataFromForm['fileName'],'Dummy');
                   return $file->delete();
                  }
                  catch (\Exception $e) {}
                }
            }

            return $fileDataFromForm['file'];

        }


}
