<?php


class IndexController extends Controller {

    public function index(){

        $User = D("User");

        var_dump(json_encode($User));

        //var_dump($memcache->mget($key));

    }

    public function get(){
        $User = D("User"); // 实例化User对象

        if (!$User->create()){

// 如果创建失败 表示验证没有通过 输出错误提示信息

            exit($User->getError());

        }else{

// 验证通过 可以进行其他数据操作
            echo 'ok';
        }
        
    }




}