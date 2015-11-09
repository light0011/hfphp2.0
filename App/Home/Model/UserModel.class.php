<?php

class UserModel extends Model{

    protected $validate  =  array(

        array('username','require','用户名必须！'), //默认情况下用正则进行验证
        array('password',array(1,2,3),'值的范围不正确！',2,'in')
    );




    public function getAll(){

        return $this->field('id,name,info')->order("id desc")->where("id = 1")->select();
    }

}








?>