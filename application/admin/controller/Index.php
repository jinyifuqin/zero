<?php
namespace app\Admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\Brands;
use \think\Controller;
use think\Request;

class Index extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
//        unset($_SESSION['adminUserInfo']);
        parent::__construct($request);
    }

    public function index()
    {

        if(!isset($_SESSION['adminUserInfo'])){
            $this->redirect('/admin/login');
        }

//        return $this->fetch('admin@index/login');
        return view();
    }

    public function welcome()
    {
        return view("admin@index/welcome");
    }

    public function login()
    {
//        echo "<pre>";var_dump(isset($_SESSION['adminUserInfo']));exit;
        if(isset($_SESSION['adminUserInfo'])){
            $url = url('admin/index/index');
            return redirect($url);
        }

        return view("login");
    }

    public function checkUser(Request $request){
        $captcha = $_SESSION['captcha'];
        $username = $request->param('username');
        $password = $request->param('password');

        if(strtolower($request->param('captcha')) != $_SESSION['captcha']){
            $msg = array(
                "error"=>"验证码输入有误!",
            );
            return $msg;
        }
        $userObj = new Adminusers();
        $re = $userObj->where('username', $username)->where('password',$password)
            ->find();
        if($re){
            $_SESSION['adminUserInfo'] = $re;
            $url = url('admin/index/index');
            $msg = array(
                "url"=>$url,
            );
            return($msg);
        }else{
            $url = url('admin/index/login');
            $msg = array(
                "url"=>$url,
            );
            return $msg;
        }
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

    public function signOut(){
        unset($_SESSION['adminUserInfo']);
        $url = url('admin/index/login');
        return redirect($url);
    }

    public function brand(){
        $re = Brands::all();
        foreach ($re as $k=>&$v){
            $v->logo = preg_replace('/\\\\/','/',$v->logo);
//            echo "<pre>";var_export($a);exit;
        }

        return view("admin@index/brand",['re'=>$re]);
    }

    public function brandShow(){
        return view("admin@index/brandShow",'');
    }

    public function addbrand(Request $request){
        $file = $request->file('file-2');
        $name = $request->param('name');
        $sort = $request->param('sort');
        $creatTime = date('Y-m-d H:i:s',time());
        $re = upload($file);
        $end = htmlspecialchars($re->getSaveName());
//        echo "<pre>";var_export($end);exit;
        if($re->getError() == ''){
            $logo = $end;
            $brandObj = new Brands([
                'name'  =>  $name,
                'sort' =>  $sort,
                'logo' => $logo,
                'create_time' =>$creatTime
            ]);
            $brandObj->save();
        }


//        echo "<pre>";var_dump($re->getError());
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
