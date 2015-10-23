<?php
class IndexController extends Controller {
    public function index(){
        $attr = D('Attr');


        $data['name'] = '寒风';
        $data['info'] = '最是ssss疯狂';

        var_dump($attr->add($data));

    }
}