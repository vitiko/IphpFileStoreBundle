<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Iphp\FileStoreBundle\File;


interface UploadedFileInterface extends FileInterface
{
    function isProtected();
    function setProtected($protected);

    function getClientOriginalName();
    function getClientMimeType();

}