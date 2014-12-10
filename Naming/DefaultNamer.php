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
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMapping $propertyMapping
     * @param $name
     * @return string
     */
    public function translitRename(PropertyMapping $propertyMapping, $name)
    {
        $name = preg_replace('/[^\\pL\d.]+/u', '-', $name);

        $specChars = array ('Ь' => '','Ъ' => '', 'ъ' => '','ь' => '', '«' => '', '»' => '', '—' => '-' );
        $name = strtr($name,  $specChars );

        if (function_exists('transliterator_transliterate')) {
            $name = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower();', $name);
            $name = preg_replace('/[-\s]+/', '-', $name);
        } else {
            $iso = array(
                "Є" => "E", "І" => "I", "Ѓ" => "G", "і" => "i", "№" => "N", "є" => "e", "ѓ" => "g",
                "ç" => "c", "è" => "e","é" => "e",
                "А" => "A", "Б" => "B", "В" => "V", "Г" => "G", "Д" => "D",
                "Е" => "E", "Ё" => "e", "Ж" => "Z",
                "З" => "Z", "И" => "I", "Й" => "J", "К" => "K", "Л" => "L",
                "М" => "M", "Н" => "N", "О" => "O", "П" => "P", "Р" => "R",
                "С" => "S", "Т" => "T", "У" => "U", "Ф" => "F", "Х" => "H",
                "Ц" => "C", "Ч" => "C", "Ш" => "S", "Щ" => "S",
                "Ы" => "Y", "Э" => "E", "Ю" => "U", "Я" => "A",
                "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d",
                "е" => "e", "ё" => "e", "ж" => "z",
                "з" => "z", "и" => "i", "й" => "j", "к" => "k", "л" => "l",
                "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
                "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
                "ц" => "c", "ч" => "c", "ш" => "s", "щ" => "s",
                "ы" => "y",  "э" => "e", "ю" => "u", "я" => "a",
            );
            $name = strtr($name, $iso);
            $name = strtolower($name);
        }

        if (function_exists('iconv')) {
            $name = iconv('utf-8', 'us-ascii//TRANSLIT//IGNORE', $name);
        }
        $name = trim($name, '-');

        return $name;
    }

    /**
     * Rename file name based on value of object property (default: id)
     * @param \Iphp\FileStoreBundle\Mapping\PropertyMapping $propertyMapping
     * @param $name
     * @param $params
     * @return string
     */
    public function propertyRename(PropertyMapping $propertyMapping, $name, $params)
    {
        $fieldValue = $this->getFieldValueByParam($propertyMapping, $params);
        if ($fieldValue) $name = $fieldValue . substr($name, strrpos($name, '.'));
        return $name;
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

    public function propertyPrefixRename(PropertyMapping $propertyMapping, $name, $params)
    {
        $fieldValue = $this->getFieldValueByParam($propertyMapping, $params);
        $delimiter = isset($params['delimiter']) && $params['delimiter'] ? $params['delimiter'] : '-';

        return $fieldValue . $delimiter . $name;
    }

    public function propertyPostfixRename(PropertyMapping $propertyMapping, $name, $params)
    {
        $fieldValue = $this->getFieldValueByParam($propertyMapping, $params);
        $delimiter = isset($params['delimiter']) && $params['delimiter'] ? $params['delimiter'] : '-';

        $ppos = strrpos($name, '.');
        return substr($name, 0, $ppos) . $delimiter . $fieldValue . '' . substr($name, $ppos);

    }

    public function replaceRename(PropertyMapping $propertyMapping, $name, $params)
    {
        return strtr($name, $params);
    }

    /**
     * Разрешение коллизий с одинаковыми названиями файлов
     *
     * @param $name
     * @param int $attempt
     * @return string
     */
    public function resolveCollision($name, $attempt = 1)
    {
        $addition = $attempt;
        if ($attempt > 10) $addition = date('Y_m_d_H_i_s');

        $ppos = strrpos($name, '.');

        return ($ppos === false ? $name : substr($name, 0, $ppos))
        . '_' . $addition . ''
        . ($ppos === false ? '' : substr($name, $ppos));
    }
}
