<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Iphp\FileStoreBundle\File;


interface FileInterface
{
    function isProtected();
    function setProtected($protected);

}