<?php
namespace app\Admin\controller;
use \think\Controller;
class Index extends Controller
{
    public function index()
    {

//        return $this->fetch('admin@index/login');
        return view();
    }

    public function welcome()
    {
        return view("admin@index/welcome");
    }

    public function login()
    {
//        echo 1;exit;
//        session_start();
//        echo "<pre>";var_dump($_SESSION);
//        return $this->fetch('admin@index/login');
        return view("login");
    }

    public function getEr(){
        getErweima();

    }



    public function ajax()
    {
//        echo password(123,456);exit;
        $arr = array(
            "a"=>"hello",
            "b"=>"world"
        );
        $re = json_encode($arr);
        echo $re;
//        return view();
    }
}
