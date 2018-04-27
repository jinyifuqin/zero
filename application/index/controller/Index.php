<?php
namespace app\index\controller;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use think\Request;
class Index
{
    public $wxObj;
    public function __construct()
    {
        session_start();
        $this->wxObj = new Wechat();
    }

    public function index()
    {
//        echo password(123,456);exit;
        return view();
    }

    public function wxLogin(){

        $url = $this->wxObj->get_authorize_url(1);
//        unset($_SESSION['getinfo']);
//        unset($_SESSION['get_access_token']);
//        echo "<pre>";var_dump($url);exit;
        return redirect($url);
    }

    public function getInfo(Request $request){
        $get = $request->param();
        $_SESSION['getinfo'] = $get;
        $code = $get['code'];
        $state = $get['state'];

//        echo "<pre>";var_dump($code);exit;
        if(!array_key_exists('get_access_token',$_SESSION)){
            $get_access_token = $this->wxObj->get_access_token($code);
            $_SESSION['get_access_token'] = $get_access_token;
        }else{
            $get_access_token = $_SESSION['get_access_token'];
        }

        if(!array_key_exists('get_user_info',$_SESSION)){
            $get_user_info = $this->wxObj->get_user_info($get_access_token['access_token'],$get_access_token['openid']);
            $_SESSION['get_user_info'] = $get_user_info;
        }else{
            $get_user_info = $_SESSION['get_user_info'];
        }

        $this->createUser($get_user_info);
    }

    public function createUser($get_user_info){
        $re = Users::where('username',$get_user_info['nickname'])->find();
        if($re){
            header("Location:/admin");
            exit;
        }

        $data['openid'] = $get_user_info['openid'];
        $data['sex'] = $get_user_info['sex'];
        $pic = download($get_user_info['headimgurl']);
        $data['pic'] = $pic;
        $data['username'] = $get_user_info['nickname'];
        $data['password'] = $get_user_info['openid'];
        $catsObj = new Users($data);
        $result = $catsObj->save();
        if($result){
            $url = url('/admin');
            return redirect('/admin');
        }
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
