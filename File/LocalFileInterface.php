<?php
/**
 * @author Vitiko <vitiko@mail.ru>
 */

namespace Iphp\FileStoreBundle\File;


interface LocalFileInterface extends FileInterface
{

    function isDeleted();

    function delete();

    function getFilename();

    function getMimeType();

    function getOriginalName();

    function getSaveSource();
} 