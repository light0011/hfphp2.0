<?php

class Model extends Db{

    protected $db = null;
    protected $tables = array();
    protected $fields = array();



    protected function __construct(){
        $this->db = parent::getInstance();

    }

    protected function addData(Array $data){
        return $this->db->addDB($this->tables,$data);
    }


    protected function update(Array $where,Array $updateData){
        return $this->db->update($this->tables,$where,$updateData);
    }

    protected function select(Array $fields,Array $param = array()){
        return $this->db->select($this->tables,$fields,$param);
    }













}













?>