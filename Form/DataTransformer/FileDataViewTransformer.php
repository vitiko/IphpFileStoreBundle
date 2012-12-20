<?php

namespace Iphp\FileStoreBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Iphp\FileStoreBundle\File\File as IphpFile;

/**
 * @author Vitiko <vitiko@mail.ru>
 */
class FileDataViewTransformer implements DataTransformerInterface
{


        public function transform($fileDataFromDb)
        {
            return $fileDataFromDb;
        }


        /**
         * array with 2 items - file (UploadedFile) and delete (checkbox)
         * @param $fileDataFromForm
         * @return int
         */
        public function reverseTransform($fileDataFromForm)
        {
            return $fileDataFromForm;
        }
}
