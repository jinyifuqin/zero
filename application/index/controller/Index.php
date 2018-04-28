<?php
namespace app\index\controller;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use app\admin\model\Items;
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
        $re = Items::all();
//        echo password(123,456);exit;
        return view("index@index/index",['re'=>$re]);
    }

    public function buy(){
        $itemid = $_SESSION['itemid'];
        $re = Items::get(['id' => $itemid]);
        $userinfo = $_SESSION['userinfo'];
        $data['item'] = $re;
        $data['userinfo'] = $userinfo;
//        unset($_SESSION['userinfo']);
//        echo "<pre>";var_dump($userinfo);exit;
        return view("index@item/buy",['data'=>$data]);
    }

    public function wxLogin(Request $request){
        $_SESSION['itemid'] = $request->param('id');
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
//        unset($_SESSION['userinfo']);exit;
        if(array_key_exists('userinfo',$_SESSION)){

            return redirect('/buy');
        }
        $url = $this->wxObj->get_authorize_url(1);
//        unset($_SESSION['getinfo']);
//        unset($_SESSION['get_access_token']);
        return redirect($url);
    }

    public function getInfo(Request $request){
        $get = $request->param();
        $_SESSION['getinfo'] = $get;
        $code = $get['code'];

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

        $re = $this->createUser($get_user_info);

        return redirect($_SESSION['url']);
    }

    public function createUser($get_user_info){
        $re = Users::where('username',$get_user_info['nickname'])->find();
        if($re){
            $_SESSION['userinfo'] = $re;
            return true;
//            header("Location:/admin");
        }else{
            $data['openid'] = $get_user_info['openid'];
            $data['sex'] = $get_user_info['sex'];
            $pic = download($get_user_info['headimgurl']);
            $data['pic'] = $pic;
            $data['username'] = $get_user_info['nickname'];
            $data['password'] = $get_user_info['openid'];
            $catsObj = new Users($data);
            $result = $catsObj->save();
            $re = Users::where('username',$get_user_info['nickname'])->find();
            $_SESSION['userinfo'] = $re;
        }

        return $result;
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
