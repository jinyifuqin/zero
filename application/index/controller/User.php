<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17 0017
 * Time: 上午 9:31
 */

namespace app\index\controller;
use app\admin\model\Adminusers;
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
        $desc = Addrs::get(['id'=>$userObj->address]);
        if($desc != null){
            $ssq = preg_replace('/%2C/',' ',$desc->desc);
            $detailDesc = $desc->detail_desc;
            $userObj->ssqdesc = $ssq;
            $userObj->desc = $detailDesc;
        }
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
        $userObj = Users::get($userId);
        $addrId = $userObj->address;
        $addrObj = Addrs::get(['id' => $addrId,'default'=>1]);
        $delimiter = urlencode(',');
        $addr = explode(' ',$request->param('addr'));
        $addr = implode($delimiter,array_filter($addr));
        if($addrObj == null){
            $addrObj = new Addrs();
            $name = $userObj->nickname;
            $addrObj->name = $name;
            $addrObj->desc = $addr;
            $addrObj->user_id = $userId;
            $addrObj->default = 1;
            $re = $addrObj->save();
            $userObj = Users::get($userId);
            $userObj->address = $addrObj->id;
            $userObj->save();
        }else{
            $addrObj->desc = $addr;
            $re = $addrObj->save();
        }
        if($re){
            $msg = array('status'=>'Success');
            echo json_encode($msg);
        }
    }

    public function self_detail_addr(){
        $addrId = $_SESSION['userinfo']->address;
        $addrObj = Addrs::get(['id' => $addrId,'default'=>1]);

        if($addrObj != null){
            $detaiDesc = $addrObj->detail_desc;
        }else{
            $detaiDesc = '';
        }
        $url = url('/selfInfo');
        $re = ['url'=>$url,'detail'=>$detaiDesc];

        return view("index@user/detailAddr",['re'=>$re]);
    }

    public function save_detail_addr(Request $request){
        $detailAddr = $request->param('detaiAddr');
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get($userId);
        $addrId = $userObj->address;
        $addrObj = Addrs::get(['id' => $addrId,'default'=>1]);
        if($addrObj == null){
            $msg = array('status'=>'fails','msg'=>'请先选择省市区！');
            echo json_encode($msg);
        }else{
            $addrObj->detail_desc = $detailAddr;
            $re = $addrObj->save();
            if($re){    //selfInfo
                $msg = array('status'=>'Success','url'=>'/selfInfo');
                echo json_encode($msg);
            }
        }

    }

    public function account_number(){
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $accountNumber = $userObj->collections;
        $url = url('/selfInfo');
        $re = ['url'=>$url,'collections'=>$accountNumber];
        return view("index@user/accountNumber",['re'=>$re]);
    }

    public function save_account_number(Request $request){
        $collections = $request->param('collections');
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->collections = $collections;
        $re = $userObj->save();
        if($re){
            $this->redirect('/selfInfo');
        }
    }

    public function phone_num(){
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $url = url('/selfInfo');
        $re = ['url'=>$url,'phoneNum'=>$userObj->phone_number];

        return view("index@user/phoneNum",['re'=>$re]);
    }

    public function save_phone_num(Request $request){
        $phoneNum = $request->param('phoneNum');
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->phone_number = $phoneNum;
        $addrObj = Addrs::get(['user_id'=>$userId,'default'=>1]);
        if($addrObj != null){
            $addrObj->phone_num = $phoneNum;
            $addrObj->save();
        }else{
            $addrObj = new Addrs();
            $addrObj->phone_num = $phoneNum;
            $addrObj->user_id = $userId;
            $addrObj->default = 1;
            $addrObj->name = $userObj->nickname;
            $re = $addrObj->save();
            $userObj->address = $addrObj->id;
            if($re){    //selfInfo
                $msg = array('status'=>'Success','url'=>'/selfInfo');
                echo json_encode($msg);
            }
        }
        $re = $userObj->save();
        if($re){
            $this->redirect('/selfInfo');
        }
    }

    public function choose_service_cent(){
        $service_list = Adminusers::all();
        return view("index@user/chooseServiceCent",['list'=>$service_list]);
    }

    public function save_service_cent(Request $request){
        $id = $request->param('id');
        if(array_key_exists('userinfo',$_SESSION)){
            $userId = $_SESSION['userinfo']->id;
        }
        $userObj = Users::get($userId);
        $userObj->service_cent_id = $id;
        $re = $userObj->save();
        if($re){
            $msg = array('status'=>'Success');
            echo json_encode($msg);
        }else{
            $msg = array('status'=>'error');
            echo json_encode($msg);
        }
    }

}