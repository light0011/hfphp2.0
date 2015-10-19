<?php

class AttrModel extends Model{

    public function __construct(){
        parent::__construct();

    }

    public function getAll(){

        return $this->field('id,name,info')->order("id desc")->where("id = 1")->select();
    }

}








?>