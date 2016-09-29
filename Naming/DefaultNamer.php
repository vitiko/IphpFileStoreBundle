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
        $name = transliterator_transliterate("Any-Latin; Latin-ASCII; [\u0100-\u7fff] remove", $name);
        $name = preg_replace('/[^\\pL\d.]+/u', '-', $name);
        $name = preg_replace('/[-\s]+/', '-', $name);
        $name = strtolower(trim($name, '-'));

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
