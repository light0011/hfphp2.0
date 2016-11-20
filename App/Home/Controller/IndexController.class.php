<?php

namespace Home\Controller;
use Core\Controller;


class IndexController extends Controller {

    public function index(){

        $rule = array('date','Y-m-d');
        echo call_user_func_array($rule[0],(array)$rule[1]);

    }

    public static function test($str) {

        return $str.'ccc';

    }






}
