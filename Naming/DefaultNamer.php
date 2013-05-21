<?php
namespace Iphp\FileStoreBundle\Naming;

use Iphp\FileStoreBundle\Mapping\PropertyMapping;

/**
 * DefaultNamer
 * @author Vitiko <vitiko@mail.ru>
 */
class DefaultNamer
{


    /**
     * Filename translitaration renamin
     * @param $name
     * @return string
     */
    function translitRename(PropertyMapping $propertyMapping, $name)
    {

        $name = preg_replace('/[^\\pL\d.]+/u', '-', $name);


        $iso = array(
            "Є" => "YE", "І" => "I", "Ѓ" => "G", "і" => "i", "№" => "N", "є" => "ye", "ѓ" => "g",
            "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
            "Е" => "E", "Ё" => "YO", "Ж" => "ZH",
            "З" => "Z", "И" => "I", "Й" => "J", "К" => "K", "Л" => "L",
            "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
            "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "H",
            "Ц" => "C", "Ч" => "CH", "Ш" => "SH", "Щ" => "SHH", "Ъ" => "'",
            "Ы" => "Y", "Ь" => "", "Э" => "E", "Ю" => "YU", "Я" => "YA",
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
            "е" => "e", "ё" => "yo", "ж" => "zh",
            "з" => "z", "и" => "i", "й" => "j", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "c", "ч" => "ch", "ш" => "sh", "щ" => "shh", "ъ" => "",
            "ы" => "y", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", "«" => "", "»" => "", "—" => "-"
        );
        $name = strtr($name, $iso);
        // trim
        $name = trim($name, '-');

        // transliterate
        if (function_exists('iconv')) {
            $name = iconv('utf-8', 'us-ascii//TRANSLIT', $name);
        }
        return strtolower($name);
    }


    /**
     * Rename file name based on value of object property (default: id)
     * @param $name
     * @param $params
     * @return string
     */
    function propertyRename(PropertyMapping $propertyMapping, $name, $params)
    {

        $obj = $propertyMapping->getObj();

        $field = isset($params['field']) && $params['field'] ? $params['field'] : 'id';
        $fieldValue = $obj->{'get' . ucfirst($field)}();
        if (!$fieldValue) $fieldValue = $obj->getId();
        if ($fieldValue) $name = $fieldValue . substr($name, strrpos($name, '.'));
        return $name;
    }


    function propertyPrefixRename(PropertyMapping $propertyMapping, $name, $params)
    {
        $fieldValue = $this->getFieldValueByParam($propertyMapping, $params);
        $delimiter = isset($params['delimiter']) && $params['delimiter'] ? $params['delimiter'] : '-';

        return $fieldValue . $delimiter . $name;
    }

    protected function getFieldValueByParam(PropertyMapping $propertyMapping, $params)
    {
        $obj = $propertyMapping->getObj();

        $fieldValue = '';
        if (isset($params['use_field_name']) && $params['use_field_name']) {
            $fieldValue = $propertyMapping->getFileDataPropertyName();
        } else {
            $field = isset($params['field']) && $params['field'] ? $params['field'] : 'id';
            $fieldValue = $obj->{'get' . ucfirst($field)}();
        }

        if (!$fieldValue) $fieldValue = $obj->getId();
        return $fieldValue;
    }


    function propertyPostfixRename(PropertyMapping $propertyMapping, $name, $params)
    {
        $fieldValue = $this->getFieldValueByParam($propertyMapping, $params);
        $delimiter = isset($params['delimiter']) && $params['delimiter'] ? $params['delimiter'] : '-';

        $ppos = strrpos($name, '.');
        return substr($name, 0, $ppos) .  $delimiter . $fieldValue. '' . substr($name, $ppos);

    }


    function replaceRename (PropertyMapping $propertyMapping, $name, $params)
    {
       return strtr ($name, $params);
    }




    // Разрешение коллизий с одинаковыми названиями файлов
    function resolveCollision($name, $attempt = 1)
    {
        $addition = $attempt;
        if ($attempt > 10) $addition = date('Y_m_d_H_i_s');

        $ppos = strrpos($name, '.');

        return ($ppos === false ? $name : substr($name, 0, $ppos))
                . '_' . $addition . ''
                . ($ppos === false  ? '' : substr($name, $ppos));
    }


}
