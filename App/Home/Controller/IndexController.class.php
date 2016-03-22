<?php


class IndexController extends Controller {

    public function index(){

        $cookie = Cookie::getInstance();

        $cookie->set('name','lijun');

        $cookie->get('name');

        $cookie->del('name');

        //清除所有的cookile
        $cookie->clear();

        //设定值时也可以传入配置参数数组，但是删除该值时也必须传入相同的配置参数数组方可删除。
        $config = array('path' => '/','domain' => 'hfphp.com');

        $cookie->set('name','lijun',$config);

        //在不符合设定时传入配置的条件下，即不属于该网站下或者路径下，将无法获得值
        $cookie->get('name');

        $cookie->del('name',$config);

        //清除所有的cookile,只是在相同的配置下
        $cookie->clear($config);




    }

    public function get(){

        echo 'aaa';
        
    }




}