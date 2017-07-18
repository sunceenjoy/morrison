<?php

namespace Morrison\Core\Repository\Entity;

abstract class BaseEntity
{
    public function fromArray($array)
    {
        foreach ($array as $key => $value) {
            $this->$key = $value;
        }
    }

    public function toArray($c)
    {
        // http://symfony.com/doc/current/components/serializer.html
        $array = $c['entity.serializer']->normalize($this);
        return $array;
    }
}
