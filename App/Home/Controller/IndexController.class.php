<?php
class IndexController extends Controller {
    public function index(){
        $attr = D('Attr');

        $data['id'] = 24;

        $num = mt_rand(1,1000);

        $data['name'] = $num;

        $data['info'] = 'ThinkPHP@gmail.com';

        var_dump($attr->save($data));

    }
}