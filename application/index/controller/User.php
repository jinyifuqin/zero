<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17 0017
 * Time: 上午 9:31
 */

namespace app\index\controller;
use app\admin\model\Brands;
use app\index\model\Addrs;
use app\index\model\Weixins;
use app\index\model\Points;
use app\index\model\Trades;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use app\admin\model\Items;
use think\Controller;
use think\Db;
use think\Request;

class User extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
        parent::__construct($request);
    }

    public function self_info(Request $request){
        if(array_key_exists('userinfo',$_SESSION)){
            $user = $_SESSION['userinfo'];
        }
        $userObj = Users::get($user->id);
        $desc = Addrs::where('id',$user->address)->value('desc');
        $desc = explode('%2C',$desc);
        $ssqdesc = array_filter($desc);
        $desc = array_pop($ssqdesc);
        $ssqdesc = implode(' ',$ssqdesc);
        $userObj->ssqdesc = $ssqdesc;
        $userObj->desc = $desc;
        $userObj->nickname = json_decode(urldecode($userObj->nickname));
        $url = url('/userInfo');
        $re['url'] = $url;
        $re['userInfo'] = $userObj;
        return view("index@user/selfInfo",['re'=>$re]);
    }

    public function save_head(Request $request){
        $headPic = $request->file('file');
        $userId = $_SESSION['userinfo']->id;
        $re = upload($headPic);
        $end = htmlspecialchars($re->getSaveName());
        if($re->getError() == ''){
            $logo = $end;
            $brandObj = new Users();
            $result = $brandObj->save([
                'pic'  =>  $logo,
            ],['id'=>$userId]);
            if($result){
                $_SESSION['userinfo']->pic = $logo;
                $msg = array('status'=>'Success','path'=>$logo);
            }
            echo json_encode($msg);
        }
    }

    public function save_addr(Request $request){
        $userId = $_SESSION['userinfo']->id;
        $addrId = $_SESSION['userinfo']->address;
        $addrObj = Addrs::get(['id' => $addrId,'default'=>1]);
        $addrSql = $addrObj->desc;
        $type = $request->param('type');
        $addr = explode(' ',$request->param('addr'));
        $delimiter = urlencode(',');
        if($type == 'before'){
            $addrArr = array_filter(explode('%2C',$addrSql));
            $jtAddr = end($addrArr);
            $addr[] = $jtAddr;
            $addrStr = implode($delimiter,$addr);
            $addrObj->desc = $addrStr;
            $re = $addrObj->save();
        }else{

        }
        if($re){
            $msg = array('status'=>'Success');
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($addr);
//        echo "<pre>";var_dump($addrStr);
    }

}