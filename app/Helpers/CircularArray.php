<?php

namespace App\Helpers;

class CircularArray
{
    protected $array;
    protected $index = 0;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function next()
    {
        $value = $this->array[$this->index];
        $this->index = ($this->index + 1) % count($this->array);
        return $value;
    }

    public function reset()
    {
        $this->index = 0;
    }
}
