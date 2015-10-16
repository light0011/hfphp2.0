<?php

//HFPHP控制器基类  抽象类

abstract class Controller{

    //视图实例对象
    protected $view = null;


    public function __construct(){
        $this->view = Templates::getInstance();
    }

    protected function display($tplFile=''){
        $this->view->display($tplFile);
    }

    protected function assign($var,$value){
        $this->view->assign($var,$value);
    }



}













?>

