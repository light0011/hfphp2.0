<?php

namespace Home\Controller;
use Core\Controller;

class IndexController extends Controller {

    public function index(){


        $user = A('User');

        $user->say();


    }






}