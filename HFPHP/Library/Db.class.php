<?php

//直接对数据库操作，并且采取单利模式
//只有Model类才能调用DB中的方法，防止被污染


class DB{

    //存放实例化的对象
    static private $instance;

    //PDO对象
    private $pdo = null;

    //公共静态方法获取实例化的对象
    static protected function getInstance(){
        //判断self::$instance 是否已经被实例化
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    //私有克隆，防止被克隆
    private function __clone(){

    }


    //私有构造
    protected  function __construct(){
        echo 'a';
        try {
            $this->pdo = new PDO('mysql:host='.C('DB_HOST').';dbname='.C('DB_NAME'), C('DB_USER'), C('DB_PWD'), array(PDO::MYSQL_ATTR_INIT_COMMAND=>'SET NAMES '.C('DB_CHARSET')));
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            exit($e->getMessage());
        }
    }


    //增加
    protected function addDB($tables,Array $addData){
        $addFileds = array_keys($addData);
        $addValues = array_values($addData);

        $addFileds = implode(',',$addFileds);
        $addValues = implode("','", $addValues);

        $sql = "INSERT INTO $tables[0] ($addFileds) VALUES ('$addValues');";

        return $this->execute($sql)->rowCount();

    }

    //修改
    //传入三个数组，分别是修改表名，修改的条件，修改的key与value
    protected function update($tables,Array $param,Array $updateData){
        $where  = $setData = '';

        foreach ($param as $key => $value) {
            $where .= $key.' = '.$value.' AND ';
        }

        $where = ' WHERE '.substr($where,0,-4);

        foreach ($updateData as $key => $value) {
            $setData .= " $key = '$value',";
        }

        $setData = substr($setData, 0,-1);

        $sql = "UPDATE $tables[0] SET $setData $where";

        return $this->execute($sql)->rowCount();
    }


    //判断某个数据是否存在
    protected function isOne($tables,Array $param){
        $where = '';
        foreach ($param as $key => $value) {
            $where .= "$key = $value AND ";
        }
        $where = ' WHERE '.substr($where,0,-4);

        $sql = "SELECT id FROM $tables[0] $where LIMIT 1";
        return $this->execute($sql)->rowCount();
    }


    //删除某个数据
    protected function delete($tables,Array $param){
        $where = '';
        foreach ($param as $key => $value) {
            $where .= "$key = $value AND ";
        }
        $where = ' WHERE '.substr($where,0,-4);

        $sql = "DELETE  FROM $tables[0] $where LIMIT 1";

        return $this->execute($sql)->rowCount();
    }


    //查询
    protected function select($tables,Array $fields,Array $param = array()){
        $limit = $where = $like = $order = '';

        if (is_array($param) && count($param) != 0) {
            $limit = isset($param['limit']) ? $param['limit'] : '';
            $order = isset($param['order']) ? 'ORDER BY '.$param['order'] : '';

            if(isset($param['where'])){
                foreach ($param['where'] as $key => $value) {
                    $where .= " $value AND ";
                }
                $where = ' WHERE '.substr($where,0,-4);
            }

            if (isset($param['like'])) {
                foreach ($param['like'] as $key => $value) {
                    $like .= "$key LIKE '%$value%' AND";
                }
                $like = ' WHERE '.substr($like,0,-4);
            }
        }

        $selectFields = implode(',',$fields);
        $table = isset($tables[1]) ? $tables[0].','.$tables[1] : $tables[0];
        $sql = "SELECT $selectFields FROM $table $where $like $order $limit;";
        $stm = $this->execute($sql);
        $result = array();
        while (!!$obj = $stm->fetch(PDO::FETCH_ASSOC)) {
            $result[] = $obj;
        }

        return $result;

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



    //执行SQL语句
    private function execute($sql){
        try {
            $res = $this->pdo->prepare($sql);
            $res->execute();
        } catch (PDOException $e) {
            exit('SQL语句:'.$sql.'错误，错误信息是:'.$e->getMessage());
        }
        return $res;
    }







}










?>