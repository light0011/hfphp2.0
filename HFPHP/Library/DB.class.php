<?php

//直接对数据库操作，并且采取单利模式
//只有Model类才能调用DB中的方法，防止被污染


class DB
{

    //存放实例化的对象
    static private $instance;

    //PDO对象
    private $pdo = null;

    //公共静态方法获取实例化的对象
    static protected function getInstance()
    {
        //判断self::$instance 是否已经被实例化
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // 查询表达式
    protected $selectSql = 'SELECT %FIELD% FROM %TABLE%%WHERE%%ORDER%%LIMIT%';

    //私有克隆，防止被克隆
    private function __clone()
    {

    }


    //私有构造
    protected function __construct()
    {

        try {
            $this->pdo = new PDO('mysql:host=' . C('DB_HOST') . ';dbname=' . C('DB_NAME'), C('DB_USER'), C('DB_PWD'), array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . C('DB_CHARSET')));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }


    //增加
    protected function insert($data=array(),$options=array()){


        $values = $fields = array();

        foreach($data as $key=>$value){
            $value = $this->parseValue($value);

            //过滤非标量数据
            if(is_scalar($value)){
                $values[] = $value;
                $fields[] = $this->parseKey($key);
            }
        }

        $sql= 'INSERT INTO '.$this->parseTable($options['table']).'('.implode(',',$fields).') VALUES ('.implode(',',$values).')';




        $res = $this->execute($sql);
        if($res !== false){
            return $this->pdo->lastInsertId();
        } else {
            return false;
        }

    }

    //修改
    //传入三个数组，分别是修改表名，修改的条件，修改的key与value
    protected function update($data,$options){

        $sql = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(isset($options['where']) ? $options['where'] : '');

        return $this->execute($sql);

    }

    //删除
    public function delete($options=array()){

        $sql = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(isset($options['where']) ? $options['where'] : '')
            .$this->parseOrder(isset($options['order']) ? $options['order'] : '')
            .$this->parseLimit(isset($options['limit']) ? $options['limit'] : '');

        return $this->execute($sql);


    }


    //判断某个数据是否存在
    protected function isOne($tables, Array $param)
    {
        $where = '';
        foreach ($param as $key => $value) {
            $where .= "$key = $value AND ";
        }
        $where = ' WHERE ' . substr($where, 0, -4);

        $sql = "SELECT id FROM $tables[0] $where LIMIT 1";
        return $this->execute($sql)->rowCount();
    }




    protected function select($options = array()){


        //得到sql语句
        $sql = $this->buildSelectSql($options);

        return $this->query($sql);


    }


    //生成查询SQL;
    public function buildSelectSql($options = array()){

        $sql = str_replace(array('%TABLE%','%FIELD%','%WHERE%','%ORDER%','%LIMIT%'),array(
            $this->parseTable($options['table']),
            $this->parseFiled(isset($options['field']) ? $options['field'] : ' * '),
            $this->parseWhere(isset($options['where']) ? $options['where'] : ''),
            $this->parseOrder(isset($options['order']) ? $options['order'] : ''),
            $this->parseLimit(isset($options['limit']) ? $options['limit'] : '')
        ),$this->selectSql);
        return $sql;
    }

    //limit分析
    private function parseLimit($limit){
        return !empty($limit) ? ' LIMIT '.$limit.' ':'';
    }

    //table分析（以后逐渐完善）

    private function parseTable($tables){
        return $tables;
    }

    //field分析（以后逐渐完善）
    private function parseFiled($fields){
        return $fields;
    }

    //where分析（待完善）
    private function parseWhere($where){
        return empty($where) ? '' : ' WHERE '.$where;
    }

    //order分析（待完善）
    private function parseOrder($order){
        return !empty($order) ? ' ORDER BY '.$order : '';
    }



    //总记录
    protected function total($tables,Array $param = array()){
        $where = '';
        if (isset($param['where'])) {
            foreach ($param['where'] as $key => $value) {
                $where .= $value.' AND ';
            }
            $where = substr($where,0,-4);
        }
        $sql = "SELECT COUNT(*) as count FROM $tables[0] $where";
        $stm = $this->execute($sql);
        return $stm->fetchObiect()->count;
    }

    //得到下一个ID
    protected function nextId($tables){
        $sql = "SHOW TABLE STATUS LIKE '$tables[0]' ";
        $stm = $this->execute($sql);
        return $stm->fetchObiect->Auto_increment;
    }



    //执行查询SQL语句
    private function query($sql){
        $res = $this->getResource($sql);

        $result = array();
        while (!!$obj = $res->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $obj;
        }

        return $result;

    }

    //执行增删改SQL语句
    private function execute($sql){

        return $this->getResource($sql)->rowCount();

    }

    //得到执行SQL语句后的资源句柄
    private function getResource($sql){
        try {
            $res = $this->pdo->prepare($sql);
            $res->execute();
        } catch (PDOException  $e) {
            exit('SQL语句：'.$sql.'<br />错误信息：'.$e->getMessage());
        }
        return $res;
    }

    //得到字段信息
    public function getFields($tableName){

        $result = $this->query('SHOW COLUMNS FROM '.$tableName);

        $info = array();

        if($result){
            foreach($result as $key => $value){
               $info[$value['Field']] = array(
                   'name' => $value['Field'],
                   'type' => $value['Type'],
                   'notnull' => (bool) ($value['Null'] === ''),
                   'default' => $value['Default'],
                   'primary' => (strtolower($value['Key']) == 'pri'),
                   'autoinc' => (strtolower($value['Extra']) == 'auto_increment'),
               );

            }

        }


        return $info;
    }


    //key分析
    private function parseKey($key){
        $key = trim($key);
        if(!preg_match('/[,\'\"\*\(\)`.\s`]/',$key)){
            $key = '`'.$key.'`';
        }
        return $key;
    }

    //value分析
    private function parseValue($value){
        if(is_string($value)){
            $value = '\''.$this->escapeString($value).'\'';
        }
        return $value;
    }


    //判断sql指令是否需要安全过滤，如需过滤便过滤
    private function escapeString($str){
        if(!get_magic_quotes_gpc()){
            $str = addslashes($str);
        }
        return $str;
    }


    //update中的set分析
    private function parseSet($data){
        foreach($data as $key=>$val){
            $value = $this->parseValue($val);
            //过滤非标量数据
            if(is_scalar($value)){
                $set[] = $this->parseKey($key).'='.$value;
            }

        }
        return ' SET '.implode(',',$set);
    }




}










?>