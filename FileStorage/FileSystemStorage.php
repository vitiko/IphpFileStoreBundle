<?php

namespace Iphp\FileStoreBundle\FileStorage;

use Iphp\FileStoreBundle\FileStorage\FileStorageInterface;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\File;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

/**
 * FileSystemStorage.
 *
 * @author Vitiko <vitiko@mail.ru>
 */
class FileSystemStorage implements FileStorageInterface
{

    protected $webDir;

    /**
     * Constructs a new instance of FileSystemStorage.
     *
     * @param
     */
    public function __construct($webDir = null)
    {
        $this->webDir = $webDir;
    }

    public function setWebDir($webDir )
    {
        $this->webDir = $webDir;
    }



    protected function getOriginalName(File $file)
    {
        return $file instanceof UploadedFile ?
            $file->getClientOriginalName() : $file->getFilename();
    }


    protected function getMimeType(File $file)
    {
        return $file instanceof UploadedFile ?
            $file->getClientMimeType() : $file->getMimeType();
    }


    public function   isSameFile(File $file, PropertyMapping $mapping, $fileName = null)
    {
        return $file->getRealPath() == realpath($mapping->resolveFileName($fileName));
    }


    protected function copyFile($source, $directory, $name)
    {
        if (!is_dir($directory)) {
            if (false === @mkdir($directory, 0777, true)) {
                throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
            }
        } elseif (!is_writable($directory)) {
            throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
        }

        $target = $directory . DIRECTORY_SEPARATOR . basename($name);

        if (!@copy($source, $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s)', $source, $target, strip_tags($error['message'])));
        }

        @chmod($target, 0666 & ~umask());

        return new File($target);
    }

    /**
     * {@inheritDoc}
     * File may be \Symfony\Component\HttpFoundation\File\File or \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function upload(PropertyMapping $mapping, File $file)
    {
        $originalName = $this->getOriginalName($file);
        $mimeType = $this->getMimeType($file);

        //transform filename and directory name if namer exists in mapping definition
        list ($fileName, $webPath) = $mapping->prepareFileName($originalName, $this);


        $fullFileName = $mapping->resolveFileName($fileName);
        //check if file already placed in needed position
        if (!$this->isSameFile($file, $mapping, $fileName)) {
            $fileInfo = pathinfo($fullFileName);
            if ($file instanceof UploadedFile) $file->move($fileInfo['dirname'], $fileInfo['basename']);
            else  $this->copyFile($file->getPathname(), $fileInfo['dirname'], $fileInfo['basename']);
        }


        $fileData = array(
            'fileName' => $fileName,
            'originalName' => $originalName,
            'mimeType' => $mimeType,
            'size' => filesize($fullFileName),
            'path' => $webPath
        );


        // print_r ($fileData);
        //  exit();

        if (!$fileData['path'])
            $fileData['path'] = substr($fullFileName, strlen($this->webDir));

        if (in_array($fileData['mimeType'], array('image/png', 'image/jpeg', 'image/pjpeg'))
            && function_exists('getimagesize')
        ) {
            list($width, $height, $type) = @getimagesize($fullFileName);
            $fileData = array_merge($fileData, array(
                'width' => $width, 'height' => $height
            ));
        }

        return $fileData;
    }


    public function removeFile(PropertyMapping $mapping, $fileName = null)
    {
        $fullFileName = $mapping->resolveFileName($fileName);

        if ($fullFileName && file_exists($fullFileName)) {
            @unlink($fullFileName);
            return !file_exists($fullFileName);
        }
        return null;
    }


    public function fileExists(PropertyMapping $mapping, $fileName = null)
    {
        return file_exists($mapping->resolveFileName($fileName));
    }



}
