<?php

namespace Addons\Skeleton\Controllers;

use Garden\Form;
use Garden\Renderers\Template;
use Garden\Request;


class Skeleton {

    protected function template(): Template
    {
        $template = new Template('template');
        $template
            ->addJs('//ajax.googleapis.com/ajax/libs/jquery/2.1.0/jquery.min.js', false)
            ->addJs('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js', false)
            ->addCss('//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css', false)
            ->addCss('starter-template.css')
            ->meta('X-UA-Compatible', 'IE=edge,chrome=1', true)
            ->meta('viewport', 'width=device-width, initial-scale=1.0')
            ->setData('currentPath', Request::current()->getPath());

        return $template;
    }

    public function index(): Template
    {
        return $this->template()->setTitle('Hello World');
    }

    public function about()
    {
        return $this->template()
            ->setTitle('About Garden')
            ->setView('index');
    }

    public function contact()
    {
        $form = new Form();
        $form->validation()
            ->rule('name', 'required')
            ->rule('email', 'email');

        if ($form->submitted()) {
            $form->save();
        }

        return $this->template()
            ->setTitle('Contact form')
            ->setData('form', $form);
    }

}