<?php
class IndexController  {
    public function index(){
        $user = new UserController();
        $user->say();
    }
}