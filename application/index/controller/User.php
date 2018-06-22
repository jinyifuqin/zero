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
use app\admin\model\VotingList;
use app\admin\model\Votings;
use app\email\PHPMailer;
use app\email\Smtp;
use app\index\model\Entrusts;
use app\index\model\Addrs;
use app\index\model\PutForwards;
use app\index\model\Weixins;
use app\index\model\Points;
use app\index\model\Trades;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use app\admin\model\Items;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;

class User extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
        $this->wxObj = new Wechat();
        $configName = 'config.xml';
        $this->configPath = APP_PATH.'admin'.DS.'config'.DS.$configName;
        Config::load($this->configPath);
        parent::__construct($request);
    }

    public function login_check(Request $request){
        $username = $request->param('username');
        $password = $request->param('password');
        $userObj = Users::get(['username'=>$username,'password'=>$password]);
        if($userObj){
            if(array_key_exists('wxInfo',$_SESSION)){
                $flag = Users::get(['openid'=>$_SESSION['wxInfo']['openid']]);
                if(!$flag && $userObj->openid == $userObj->username){
                    $userObj->pic = download($_SESSION['wxInfo']['headimgurl']);
                    $userObj->nickname = urlencode(json_encode($_SESSION['wxInfo']['nickname']));
                    $userObj->openid = $_SESSION['wxInfo']['openid'];
                    $userObj->save();
                }
            }
            if(array_key_exists('share_member_id',$_SESSION) && $userObj->share_member_id == 0 && $_SESSION['share_member_id'] != $userObj->id){
                $userObj->share_member_id = $_SESSION['share_member_id'];
                $userObj->save();
            }
            if(array_key_exists('adminUserId',$_SESSION) && $userObj->service_cent_id == 0){
                $userObj->service_cent_id = $_SESSION['adminUserId'];
                $userObj->save();
            }

            $_SESSION['userinfo'] = $userObj;
            $msg = array('status'=>'Success','url'=>'/');
            echo json_encode($msg);
        }else{
            $msg = array('status'=>'error','msg'=>'账号密码错误，请重新输入！');
            echo json_encode($msg);
        }
    }

    public function register(){
        $url = '/';
        $re['url'] = $url;
        return view("index@user/register",['re'=>$re]);
    }

    public function forget(){
        $url = '/';
        $re['url'] = $url;
        return view("index@user/forget",['re'=>$re]);
    }

    public function send_email(Request $request){
        $email = $request->param('email');
        $content = '';
        for ($i=0;$i<5;$i++){
            $content.=mt_rand(0,9);
        }
        $t = time();
        $_SESSION['yzmEmail'] = [$t=>$content];
        $str = '【永之泰】,本次验证码为：'.$content.'。请勿告诉他人';
        $re = sendEmail($email,$str);
        if($re){
            $msg = array('status'=>'success','msg'=>'邮件发送成功，请注意查收！');
            echo json_encode($msg);
        }else{
            $msg = array('status'=>'Success','msg'=>'发送失败！');
            echo json_encode($msg);
        }
    }

    public function check_email(Request $request){
        $post = $request->param();
        $username = $post['username'];
        $email = $post['email'];
        $emailCode = $post['emailCode'];
        $yzm = $_SESSION['yzmEmail'];
        $getTime = key($yzm);
        $now = time();
        $filter = 3*60;
//        echo "<pre>";var_dump();
        if($yzm[$getTime] == $emailCode){
            if($filter < $now - $getTime){
                $msg = array('status'=>'error','msg'=>"验证码已过期，请重新获取！");
                echo json_encode($msg);exit;
            }
            $user = Users::get(['email'=>$email,'username'=>$username]);
            if($user){
                $id = $user->id;
                $msg = array('status'=>'success','msg'=>'邮箱验证成功，等待跳转！','url'=>"/changePsw/{$id}");
                echo json_encode($msg);
            }else{
                $msg = array('status'=>'error','msg'=>"邮箱与：$username 不匹配！");
                echo json_encode($msg);
            }
        }else{
            $msg = array('status'=>'error','msg'=>'邮箱验证码错误！');
            echo json_encode($msg);
        }
    }

    public function change_psw($id){
        return view("index@user/changePsw",['id'=>$id]);
    }

    public function save_c_psw(Request $request){
        $post = $request->param();
        $password = $post['password'];
        $id = $post['userId'];
        $uObj = Users::get($id);
        $uObj->password = $password;
        $re = $uObj->save();
        if($re){
            $msg = array('status'=>'success','msg'=>'密码重置成功，请重新登录！','url'=>"/userInfo");
            echo json_encode($msg);
        }else{
            $msg = array('status'=>'error','msg'=>"不能与之前的密码一样！");
            echo json_encode($msg);
        }
    }

    public function register_captcha(){
        return getCaptcha(120,40,20,30);
    }

    public function exit_login(){
        unset($_SESSION['userinfo']);
        $this->redirect('/userInfo');
    }

    public function save_user(Request $request){
        $post = $request->param();
        $post['captcha'] = strtolower($post['captcha']);
        if($_SESSION['captcha'] != $post['captcha']){
            $msg = array('status'=>'error','msg'=>'验证码输入错误，请重新输入！');
            echo json_encode($msg);
        }else{
            $data['username'] = $post['username'];
            $data['email'] = $post['email'];
            $data['password'] = $post['password'];
            $data['phone_number'] = $post['username'];
            $data['nickname'] = urlencode(json_encode($post['nickname']));
            $data['sex'] = $post['sex'];
            $data['truename'] = $post['truename'];
            $data['openid'] = $post['username'];
            if(array_key_exists('wxInfo',$_SESSION)){
                $flag = Users::get(['openid'=>$_SESSION['wxInfo']['openid']]);
                if(!$flag){
                    $data['pic'] = download($_SESSION['wxInfo']['headimgurl']);
                    $data['nickname'] = urlencode(json_encode($_SESSION['wxInfo']['nickname']));
                    $data['openid'] = $_SESSION['wxInfo']['openid'];
                }
            }
            $userObj = new Users();
            $userObj->data($data);
            $re = $userObj->save();
            $addrObj = new Addrs();
            $addrObj->phone_num = $post['username'];
            $addrObj->user_id = $userObj->id;
            $addrObj->default = 1;
            $addrObj->save();
            $userObj->address = $addrObj->id;
            if(array_key_exists('share_member_id',$_SESSION)){
                $userObj->share_member_id = $_SESSION['share_member_id'];
            }
            if(array_key_exists('adminUserId',$_SESSION)){
                $userObj->service_cent_id = $_SESSION['adminUserId'];
            }

            $userObj->save();
            $_SESSION['userinfo'] = Users::get($userObj->id);
            if($re){
                $msg = array('status'=>'Success','msg'=>'恭喜您注册成功！','url'=>'/');
                echo json_encode($msg);
            }else{
                $msg = array('status'=>'Success','msg'=>'注册失败！');
                echo json_encode($msg);
            }
        }

//        echo "<pre>";var_dump($_SESSION);
    }

    public function weixin_login(){
        if(array_key_exists('wxInfo',$_SESSION)){
            $userObj = Users::get(['openid'=>$_SESSION['wxInfo']['openid']]);
            if(!$userObj){
                $this->redirect('/register');
            }else{
                $_SESSION['userinfo'] = $userObj;
                $this->redirect('/');
            }
        }else{
            $url = $this->wxObj->get_authorize_url(1);
            return redirect($url);
        }
//        $this->redirect('/admin/login');
//        $url = $this->wxObj->get_authorize_url(1);
//        return redirect($url);
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
//        echo "<pre>";var_dump($userObj->truename);exit;
        $re['nameFlag'] = false;
        if($userObj->truename == ''){
            $re['nameFlag'] = true;
        }else{
            $re['nameFlag'] = false;
        }
        $url = url('/userInfo');
        $re['url'] = $url;
        $re['userInfo'] = $userObj;
//        echo "<pre>";var_dump($re);exit;
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
//        echo "<pre>";var_dump($_SESSION);exit;
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

    public function nickname(){
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->nickname = json_decode(urldecode($userObj->nickname));
        $url = url('/selfInfo');
        $re = ['url'=>$url,'info'=>$userObj];
        return view("index@user/nickname",['re'=>$re]);
    }

    public function save_nickname(Request $request){
        $name = $request->param('nickname');
        $name = urlencode(json_encode($name));
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->nickname = $name;
        $re = $userObj->save();
        if($re){
            $this->redirect('/selfInfo');
        }
    }

    public function truename(){
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $url = url('/selfInfo');
        $re = ['url'=>$url,'info'=>$userObj];
        return view("index@user/truename",['re'=>$re]);
    }

    public function save_truename(Request $request){
        $name = $request->param('truename');
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->truename = $name;
        $re = $userObj->save();
        if($re){
            $this->redirect('/selfInfo');
        }
    }
    
    public function openbank(){
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $url = url('/selfInfo');
        $re = ['url'=>$url,'info'=>$userObj];
//        echo "<pre>";var_dump($userObj);exit;
        return view("index@user/openbank",['re'=>$re]);
    }

    public function save_open_bank(Request $request){
        $name = $request->param('openbank');
        $userId = $_SESSION['userinfo']->id;
        $userObj = Users::get(['id' => $userId]);
        $userObj->open_bank = $name;
        $re = $userObj->save();
        if($re){
            $this->redirect('/selfInfo');
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
        $service_list = Adminusers::all(['type'=>0]);
        $re['list'] = $service_list;
        return view("index@user/chooseServiceCent",['re'=>$re]);
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

    public function my_qrcode(){
        if(!array_key_exists('userinfo',$_SESSION)){
            $this->redirect('/userInfo');
        }
        $memberid = $_SESSION['userinfo']->id;
        $url = "http://".$_SERVER['HTTP_HOST'].'?memberid='.$memberid;
        $link = "http://qr.liantu.com/api.php?text=$url";
        $backUrl = $_SERVER['HTTP_REFERER'];
        $re = ['url'=>$backUrl,'link'=>$link];
        return view("index@user/myQrcode",['re'=>$re]);
//        echo "<pre>";var_dump($_SERVER['HTTP_HOST']);exit;
    }

    public function give_point(){
        $setGivePointBrokerage = config('setGivePointBrokerage');
        $return = url('/userInfo');
        $curl = "userinfo";
        $re = ['url'=>$return,'footType'=>$curl,'brokerage'=>$setGivePointBrokerage];
        return view("index@user/givePoint",['re'=>$re]);
    }

    public function search_member(Request $request){
        $number = $request->param('content');
        $query = ['phone_number'=>$number];
        $re = Users::get(['phone_number'=>$number]);
        if($re){
            $re->nickname = json_decode(urldecode($re->nickname));
//            $userinfo =  json_encode($re);
            $msg = ['type'=>'success','user'=>$re];
            echo json_encode($msg);
        }else{
            $msg = ['type'=>'error'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($re);exit;
    }

    public function give_point_member(Request $request){
        $userId = $_SESSION['userinfo']->id;
        $getId = $request->param('getId');
        if($userId == $getId){
            $msg = ['type'=>'error','msg'=>'无法赠送自己！'];
            echo json_encode($msg);
        }
        $brokerageNum = $request->param('brokerageNum');
        $count = $request->param('number');
        $pointObj = new Points();
        $noUseAdd = $pointObj->where('user_id',"=",$userId)
            ->where('type',"=",1)
            ->where('frozen_flag',"=",0)
            ->sum('count');
        $noUseDel = $pointObj->where('user_id',"=",$userId)
            ->where('type',"=",0)
            ->where('frozen_flag',"=",0)
            ->sum('count');
        $end = $noUseAdd - $noUseDel;
//        echo "<pre>";var_dump($noUseDel);exit;

        if($end >= $count+$brokerageNum){
            $givePoint['user_id'] = $userId;
            $givePoint['count'] = $count+$brokerageNum;
            $givePoint['type'] = 0;
            $givePoint['get_type'] = 1;
            $givePoint['frozen_flag'] = 0;
            $givePoint['create_time'] = date('Y-m-d H:i:s',time());
            $getPoint['user_id'] = $getId;
            $getPoint['count'] = $count;
            $getPoint['type'] = 1;
            $getPoint['get_type'] = 1;
            $getPoint['frozen_flag'] = 0;
            $getPoint['create_time'] = date('Y-m-d H:i:s',time());
            $list[] = $givePoint;
            $list[] = $getPoint;

            $re = $pointObj->saveAll($list);
            if($re){
                $msg = ['type'=>'success','msg'=>'积分赠送成功！'];
                echo json_encode($msg);
            }
        }else{
            $msg = ['type'=>'error','msg'=>'抱歉，您的积分不足以赠送！'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($canUse);exit;
    }

    public function service_cent(){
        $userInfo = $_SESSION['userinfo'];
        $uId = $userInfo->id;
        $info = Users::get($uId);
        if($info->service_cent_id == 0){
            $list = Adminusers::all(['type'=>0]);
            $flag = 'true';
            $re = ['list'=>$list,'flag'=>$flag];
            return view("index@user/chooseServiceCent",['re'=>$re]);
        }else{
            $serviceId = $info->service_cent_id;
            $serviceInfo = Adminusers::get($serviceId);
            $return = url('/userInfo');
            $curl = "userinfo";
            $re = ['url'=>$return,'footType'=>$curl,'info'=>$serviceInfo];
//            echo "<pre>";var_dump($serviceInfo);exit;
            return view("index@user/showServiceInfo",['re'=>$re]);
        }
    }

    public function entrust(){
        $userInfo = $_SESSION['userinfo'];
        $uId = $userInfo->id;
        $info = Users::get($uId);
        if($info->service_cent_id == 0){
            $list = Adminusers::all(['type'=>0]);
            $flag = 'true';
            $re = ['list'=>$list,'flag'=>$flag];
            return view("index@user/chooseServiceCent",['re'=>$re]);
        }else{
            $serviceId = $info->service_cent_id;
            $serviceInfo = Adminusers::get($serviceId);
            $return = url('/userInfo');
            $curl = "userinfo";
            $enRe = Entrusts::get(['user_id'=>$uId]);
//            $wtFlag = Points::get(['user_id'=>$uId,'get_type'=>4]);
//            if($wtFlag && $enRe){
//                $msg = '已接收委托';
//            }elseif($wtFlag && !$enRe){
//                $msg = '等待接受';
//            }else{
//                $msg = '未申请';
//            }
//            echo "<pre>";var_dump($enRe);exit;
            $re = ['url'=>$return,'footType'=>$curl,'info'=>$serviceInfo];
//
            return view("index@user/entrust",['re'=>$re]);
        }
    }

    public function entrust_show(){
        $return = url('/userInfo');
        $userInfo = $_SESSION['userinfo'];
        $userId = $userInfo->id;
        $curl = "userinfo";
        $ens = Entrusts::all(['user_id'=>$userId]);
        $setGivePointBrokerage = config('setGivePointBrokerage');
//        echo "<pre>";var_dump($setGivePointBrokerage);exit;
        $re = ['url'=>$return,'footType'=>$curl,'list'=>$ens,'setGivePointBrokerage'=>$setGivePointBrokerage];
        return view("index@user/entrustShow",['re'=>$re]);
    }

    public function give_point_service(Request $request){
        $userInfo = $_SESSION['userinfo'];
        $userId = $userInfo->id;
        $info = Users::get($userId);
        $count = $request->param('number');
        $brokerage = $request->param('brokerage');
        $brokerageCount = round($count*$brokerage*0.01,3);
//        echo "<pre>";var_dump($brokerageCount);exit;
        $serviceId = $info->service_cent_id;
        $pObj = new Points();
        $canUseAdd = $pObj->where('user_id',"=",$userId)
            ->where('type',"=",1)
            ->where('frozen_flag',"=",0)
            ->sum('count');
        $canUseDel = $pObj->where('user_id',"=",$userId)
            ->where('type',"=",0)
            ->where('frozen_flag',"=",0)
            ->sum('count');
        $canUse = round($canUseAdd-$canUseDel,3);
        if($canUse > $count){
            $givePoint['user_id'] = $userId;
            $givePoint['count'] = $count+$brokerageCount;
            $givePoint['type'] = 0;
            $givePoint['get_type'] = 4;
            $givePoint['frozen_flag'] = 0;
            $givePoint['create_time'] = date('Y-m-d H:i:s',time());
            $re = $pObj->save($givePoint);
            $enObj = new Entrusts();
            $enList['service_id'] = $serviceId;
            $enList['count'] = $count;
            $enList['user_id'] = $userId;
            $enList['type'] = 0;
            $enList['point_id'] = $pObj->id;
            $re1 = $enObj->save($enList);
            if($re){
                $msg = ['type'=>'success','msg'=>'恭喜你，申请委托成功，等待服务中心确认！'];
                echo json_encode($msg);
            }else{
                $msg = ['type'=>'error','msg'=>'抱歉，申请委托失败！'];
                echo json_encode($msg);
            }
        }else{
            $msg = ['type'=>'error','msg'=>'抱歉，您的积分不足以委托！'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($canUse);exit;
    }

    public function put_forward(){
        $return = url('/userInfo');
        $re = ['url'=>$return];
        return view("index@user/putForward",['re'=>$re]);
    }

    public function pf_action(Request $request){
        $num = $request->param('num');
        $brokerage = $request->param('brokerage');
        $pfNum = $num+$brokerage;
        $user = $_SESSION['userinfo'];
        $userId = $user->id;
        $canUseAdd = \think\Db::table('yzt_points')
            ->where('user_id',$userId)
            ->where('type',1)
            ->where('frozen_flag',1)
            ->sum('count');

        $canUseDel = \think\Db::table('yzt_points')
            ->where('user_id',$userId)
            ->where('type',0)
            ->where('frozen_flag',1)
            ->sum('count');
        $canUse = $canUseAdd - $canUseDel;
//        echo "<pre>";var_dump($canUse);exit;
        if($canUse >= $pfNum){
            $collections = Users::where('id',$userId)->value('collections');
            $open_bank = Users::where('id',$userId)->value('open_bank');
            if($collections == '' || $open_bank == ''){
                $msg = ['type'=>'error','msg'=>'请在个人中心添加银行卡信息！'];
                echo json_encode($msg);exit;
            }
//            echo "<pre>";var_dump($open_bank);exit;
            $pfPoint['user_id'] = $userId;
            $pfPoint['count'] = $pfNum;
            $pfPoint['type'] = 0;
            $pfPoint['get_type'] = 5;
            $pfPoint['frozen_flag'] = 1;
            $pfPoint['create_time'] = date('Y-m-d H:i:s',time());
            $pointObj = new Points();
            $re = $pointObj->save($pfPoint);
            $pfArr['point_id'] = $pointObj->id;
            $pfArr['count'] = $num;
            $pfArr['user_id'] = $userId;
            $pfObj = new PutForwards();
            $pfObj->data($pfArr);
            $pfObj->save();
            if($re){
                $msg = ['type'=>'error','msg'=>'提现成功！'];
                echo json_encode($msg);
            }else{
                $msg = ['type'=>'error','msg'=>'提现失败！'];
                echo json_encode($msg);
            }
        }else{
            $msg = ['type'=>'error','msg'=>'您的积分不足以提现这么多！'];
            echo json_encode($msg);
        }
    }

    public function sure_part(){
        $user = $_SESSION['userinfo'];
        $userId = $user->id;
        $e = new Entrusts();
        $first = $e->where('user_id', $userId)
            ->limit(1)
            ->order('create_time', 'asc')
            ->column('create_time');
        if(empty($first)){
            $msg = ['type'=>'error','msg'=>'你们之间没有委托关系！'];
            echo json_encode($msg);exit;
        }
        $first = $first[0];
        $checkTime = strtotime("+3 months", strtotime($first));
        $count = $e->where('user_id',$userId)
            ->where('type','in','0,1')
            ->sum('count');

        if($checkTime < time()){
            $partPoint['user_id'] = $userId;
            $partPoint['count'] = $count;
            $partPoint['type'] = 1;
            $partPoint['get_type'] = 4;
            $partPoint['frozen_flag'] = 0;
            $partPoint['create_time'] = date('Y-m-d H:i:s',time());
            $pObj = new Points();
            $pObj->data($partPoint);
            $re = $pObj->save();
            $re2 = Entrusts::destroy(['user_id' => $userId]);
            if($re && $re2){
                $msg = ['type'=>'success','msg'=>'解除托管成功！'];
                echo json_encode($msg);
            }else{
                $msg = ['type'=>'error','msg'=>'解除托管失败！'];
                echo json_encode($msg);
            }
        }else{
            $msg = ['type'=>'error','msg'=>'观察期，三个月之后才可解除！'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($count);exit;
    }
    
    public function user_voting(){
        $v = Votings::all();
        $curl = "userinfo";
        $re = ['v'=>$v,'footType'=>$curl];
//        echo "<pre>";var_dump($v);exit;
        return view("index@user/userVoting",['re'=>$re]);
    }

    public function voting_detail($id){
        $info = Votings::get($id);
        $content = $info->content;
        $content = trim($content);
        $content = explode('|',$content);
        $info->content = $content;
        $curl = "userinfo";
        $re = ['info'=>$info,'footType'=>$curl];
//        echo "<pre>";var_dump($content);exit;
        return view("index@user/votingDetail",['re'=>$re]);
    }
    
    public function voting_action(Request $request){
        $user = $_SESSION['userinfo'];
        $userId = $user->id;
        $post = $request->param();
        $voting_count = $post['voting_count'];
        $vListObj = new VotingList();
//        echo "<pre>";var_dump($post);exit;
        $allCount = $vListObj->where('user_id','=',$userId)
            ->where('voting_id','=',$post['voId'])
            ->count();
        if($allCount >= $voting_count){
            $msg = ['status'=>'error','msg'=>"最多投票{$voting_count}次！"];
            echo json_encode($msg);exit;
        }

        $votingPoint['user_id'] = $userId;
        $votingPoint['count'] = $post['pointCount'];
        $votingPoint['type'] = 0;
        $votingPoint['get_type'] = 6;
        $votingPoint['frozen_flag'] = 0;
        $votingPoint['create_time'] = date('Y-m-d H:i:s',time());
        $p = new Points();
        $p->data($votingPoint);
        $re = $p->save();
        $voListObj = new VotingList();
        $voData['user_id'] = $userId;
        $voData['voting_id'] = $post['voId'];
        $voData['voting_info'] = $post['voting_info'];
        $voListObj->data($voData);
        $re2 = $voListObj->save();
        if($re && $re2){
            $msg = ['status'=>'success','msg'=>'投票成功！'];
            echo json_encode($msg);
        }else{
            $msg = ['status'=>'error','msg'=>'投票失败！'];
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($post);exit;
    }

    public function user_get(){
        $userId = $_SESSION['userinfo']->id;
        $pObj = new Points();
        $list = $pObj->where('user_id', $userId)
            ->where('type', '=',1)
            ->where('get_type', '=',1)
            ->where('frozen_flag', 0)
            ->limit(10)
            ->order('create_time', 'desc')
            ->select();
        $curl = "userinfo";
        $re = ['list'=>$list,'footType'=>$curl];
//        echo "<pre>";var_dump($list);exit;
        return view("index@user/userGet",['re'=>$re]);
    }

    public function my_share(){
        $user = $_SESSION['userinfo'];
        $userObj = Users::get($user->id);
        $shareInfo = $userObj->share_member_id;
        if($shareInfo != 0){
            $shareMem = Users::get($shareInfo);
            $shareMem->nickname = json_decode(urldecode($shareMem->nickname));
        }else{
            $shareMem = null;
        }
        $curl = "userinfo";
        $re = ['info'=>$shareMem,'footType'=>$curl];
//                echo "<pre>";var_dump($re);exit;
        return view("index@user/myShare",['re'=>$re]);

    }

}