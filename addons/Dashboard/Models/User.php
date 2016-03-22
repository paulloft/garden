<?php
namespace Addons\Dashboard\Models;

/**
* 
*/
class User extends \Garden\Model
{
    public $table = "user";
    
    function __construct()
    {
        parent::__construct($this->table);
    }
}