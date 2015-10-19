<?php

class Model extends DB{

    //让前面穿的参数更灵活，在这里进行分析再传到DB进行执行

    protected $db = null;
    protected $tables = array();
    protected $fields = array();

    //查询表达式参数
    protected $options = array();

    //模型名称
    protected $name = '';

    protected function __construct(){
        $this->db = parent::getInstance();

        //设置模型名
        $this->name = substr(get_class($this),0,-5);
        //设置表前缀
        $this->tablePrefix = C('DB_PREFIX');



    }


    //利用_call实现连贯操作
    public function __call($method,$args){
        if(in_array(strtolower($method),array('field','table','where','order','limit'),true)){
            $this->options[strtolower($method)] = $args[0];
            return $this;
        }


    }


    protected function addData(Array $data){
        return $this->db->addDB($this->tables,$data);
    }


    protected function update(Array $where,Array $updateData){
        return $this->db->update($this->tables,$where,$updateData);
    }

    //查询语句
    protected function select($options = array()){

        $options = $this->parseOptions($options);

        return $this->db->select($options);
    }



    //分析options，得到组装sql语句的参数
    protected function parseOptions($options = array()){

        if(is_array($options))
            $options = array_merge($this->options,$options);

        //每次都先清空，以免影响下次查询
        $this->options = array();

        //获取表名
        if(!isset($options['table'])){
            $options['table'] = $this->getTableName();
        }

        //记录操作的模型名
        $options['model'] = $this->name;

        return $options;

    }


    //得到表名
    private function getTableName(){
        return $this->tablePrefix.strtolower($this->name);
    }











}













?>