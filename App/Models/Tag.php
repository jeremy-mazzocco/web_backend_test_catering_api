<?php

namespace App\Models;

use App\Plugins\Di\Injectable;
use PDO;
use App\Plugins\Http\Response as Status;
use App\Plugins\Http\Exceptions;
use App\Plugins\Http\ApiException;
use App\Plugins\Db\Db;
use Exception;

class Tag extends Injectable
{
    public $id;
    public $name;

    public function __construct($name, $id = null)
    {
        $this->name = $name;
        $this->id = $id;
    }
    
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
  
}


