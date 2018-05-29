<?php

class Man
{
    private $id;
    private $name;
    private $age;

    public function __construct($id, $name, $age)
    {
        $this->id = $id;
        $this->name = $name;
        $this->age = $age;
    }

    public function greeting()
    {
        return "My name is $this->name. I am $this->age years old.";
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}