<?php
class IndexController extends Controller {
    public function index(){
        $attr = D('Attr');

        $attrs = $attr->getAll();
        var_dump($attrs);
    }
}