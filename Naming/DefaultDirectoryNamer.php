<?php

namespace Iphp\FileStoreBundle\Naming;
use Iphp\FileStoreBundle\Mapping\PropertyMapping;


/**
 * @author Vitiko <vitiko@mail.ru>
 */
class DefaultDirectoryNamer
{


    /**
     *
     */
    function propertyRename(PropertyMapping $propertyMapping, $fileName, $params)
    {

        if (isset($params['use_field_name']) && $params['use_field_name'])
            return $propertyMapping->getFileDataPropertyName();

        $obj = $propertyMapping->getObj();
        $field = isset($params['field']) && $params['field'] ? $params['field'] : 'id';


        $fields = explode('/', $field);
        $path = '';


        foreach ($fields as $f) {
            if (strpos($f, '.')) {
                $str = 'return $obj->get' . implode('()->get', array_map('ucfirst', explode('.', $f))) . '();';
                $fieldValue = eval ($str);
            } else $fieldValue = $obj->{'get' . ucfirst($f)}();
            $path .= ($path ? '/' : '') . $fieldValue;
        }

        return $path;
    }


    function entityNameRename(PropertyMapping $propertyMapping, $fileName, $params)
    {
        return implode('', array_slice(explode('\\', get_class($propertyMapping->getObj())), -1));
    }


    function replaceRename(PropertyMapping $propertyMapping, $name, $params)
    {
        return strtr($name, $params);
    }


    function dateRename(PropertyMapping $propertyMapping, $fileName, $params)
    {
        $obj = $propertyMapping->getObj();

        $field = isset($params['field']) && $params['field'] ? $params['field'] : 'id';
        $depth = isset($params['depth']) && $params['depth'] ? strtolower($params['depth']) : 'day';

        $date = $obj->{'get' . ucfirst($field)}();
        $date = $date ? $date->getTimestamp() : time();

        $tpl = "Y/m/d";
        if ($depth == 'month') $tpl = "Y/m";
        if ($depth == 'year') $tpl = "Y";

        $dirName = date($tpl, $date);

        return $dirName;
    }


}
