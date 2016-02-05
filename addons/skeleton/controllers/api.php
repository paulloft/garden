<?php
namespace Garden;

echo 'open_';

/**
* 
*/
class ApiController extends Controller
{
    
    function __construct()
    {
        echo 'construct_';
    }

    public function index()
    {
        echo 'index method_';
    }
}