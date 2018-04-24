<?php
namespace app\Admin\controller;
use app\admin\model\Adminusers;
use \think\Controller;
use think\Request;

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

    public function checkUser(Request $request){
        session_start();
        $captcha = $_SESSION['captcha'];
        $username = $request->param('username');
        $password = $request->param('password');
        $userObj = new Adminusers();
        $re = $userObj->where('username', $username)->where('password',$password)
            ->find();
        if($re){
            $_SESSION['adminUserInfo'] = $re;
        }else{
            $url = url('admin/index/login');
            $this->redirect($url);
//            echo "<pre>";var_dump(2);
        }
//        echo "<pre>";var_dump($_SESSION);
    }

    public function getCaptcha(){
        getCaptcha();

    }

    public function createUser(){
        $user = new Adminusers([
            'username'  =>  'admin',
            'password' =>  'admin'
        ]);
        $user->save();
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
