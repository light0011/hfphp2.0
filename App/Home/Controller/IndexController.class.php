<?php
class IndexController extends Controller {
    public function index(){

        $this->display();

    }

    public function get(){
        $user = M('user');
        $user->create();
        $user->save();
    }




}