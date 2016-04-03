<?php

namespace Home\Controller;
use Core\Controller;


class IndexController extends Controller {

    public function index(){
        $verify = new \Vendor\Verify();

        $verify->verify();




    }

    public function img() {


    }






}