<?php
namespace app\index\controller;
use app\admin\model\Brands;
use app\index\model\Addrs;
use app\index\model\Weixins;
use app\index\model\Points;
use app\index\model\Trades;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use app\admin\model\Items;
use think\Db;
use think\Request;
class Index
{
    public $wxObj;
    public $pagesize = 2;
    public function __construct()
    {
        session_start();

        $this->wxObj = new Wechat();
    }

    public function index(Request $request)
    {

//        session_destroy();
//        echo "<pre>";var_dump($_SESSION);exit;
        $_SESSION['url'] = $_SERVER['HTTP_HOST'];
        $serviceuserid = $request->param('userid');
        $memberid = $request->param('memberid');
        if(isset($serviceuserid)){
            $_SESSION['adminUserId'] = $serviceuserid;
        }
        if(isset($memberid)){
            $_SESSION['share_member_id'] = $memberid;
        }


        if(array_key_exists('userinfo',$_SESSION)){
            $id = $_SESSION['userinfo']->id;
            $userObj = Users::get($id);
//            echo "<pre>";var_dump($userObj->service_cent_id);exit;
            if(isset($serviceuserid) && $userObj->service_cent_id == 0){
                $userObj->service_cent_id = $serviceuserid;
            }
//            echo "<pre>";var_dump($memberid);exit;
            if(isset($memberid)){
                $userObj->share_member_id = $memberid;
            }
            $userObj->save();
            $re = Items::all();
            $curl = "index";

            $appid = WX_APPID;
            $wxObj = Weixins::all();
            $ticket = $wxObj[0]->ticket;
            $access_token_true = $wxObj[0]->access_token_true;
//            echo "<pre>";var_dump($_SESSION);exit;
//            $timestamp = $_SESSION['timestamp'];
            $signatureRe = $this->get_signature($ticket);
//            echo "1";exit;
            $signature = $signatureRe['signature'];
            $noncestr = $signatureRe['noncestr'];
//            echo "<pre>";var_dump($signatureRe);exit;
            $url = "http://".$_SERVER['HTTP_HOST'].'?memberid='.$id;
            $weixin = array(
                'appid'=>$appid,
                'access_token_true'=>$access_token_true,
                'ticket'=>$ticket,
                'timestamp'=>$signatureRe['timestamp'],
                'noncestr'=>$noncestr,
                'signature'=>$signature,
                'url'=>$url
            );
            $re = ['footType'=>$curl,'itemInfo'=>$re,'weixin'=>$weixin];
//            echo "<pre>";var_dump($weixin);exit;
            return view("index@index/index",['re'=>$re]);
        }
        $url = $this->wxObj->get_authorize_url(1);
        return redirect($url);

//        echo "<pre>";var_dump($request->param());exit;

    }

    public function buy(){
        $itemid = $_SESSION['itemid'];
        $item = Items::get(['id' => $itemid]);
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $user = Users::where('id',$userid)->find();
        $_SESSION['userinfo'] = $user;
        $addr = Addrs::where('id',$user->address)->find();
        $item->brandName = Brands::where('id',$item->brand_id)->value('name');
//        echo "<pre>";var_dump($item);exit;
        $data['item'] = $item;
        $data['userinfo'] = $userinfo;

//        echo "<pre>";var_dump($addr);exit;
        if($addr){
            $addr->desc = preg_replace('/%2C/',' ',$addr->desc);
            $data['addr'] = $addr;
        }



//        unset($_SESSION['userinfo']);
//        echo "<pre>";var_dump($data);exit;
        return view("index@item/buying",['data'=>$data]);
    }

    public function wxLogin(Request $request=null){
        if($request){
            $_SESSION['itemid'] = $request->param('id');
        }
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
//        echo "<pre>";var_dump($_SESSION);exit;
//        unset($_SESSION['userinfo']);
        if(array_key_exists('userinfo',$_SESSION) && $_SESSION['userinfo'] != null){

            return redirect('/buy');
        }
        $url = $this->wxObj->get_authorize_url(1);
//        unset($_SESSION['getinfo']);
//        unset($_SESSION['get_access_token']);
//        echo "<pre>";var_dump($url);exit;
        if($request){
            return redirect($url);
        }else{
            return $url;
        }
//
    }

    public function getInfo(Request $request){
        $get = $request->param();
        $_SESSION['getinfo'] = $get;
        $code = $get['code'];
        $wx = Weixins::all();

        if($wx == null){
            $result = $this->getWxAccessToken($code);
            $_SESSION['openid'] = $result['openid'];
            $data = array(
                'access_token'=>$result['access_token'],
                'access_token_true'=>$result['access_token_true'],
                'ticket'=>$result['ticket'],
                'create_time'=>date('Y-m-d H:i:s',time()+7000)
            );
            $wxObj = new Weixins($data);
            $wxObj->save();
        }else{
            $wx = $wx[0];
            $id = $wx->id;
            $create_time = $wx->create_time;
            if($create_time < time()){
                $result = $this->getWxAccessToken($code);
                $_SESSION['openid'] = $result['openid'];
                $data = array(
                    'access_token'=>$result['access_token'],
                    'access_token_true'=>$result['access_token_true'],
                    'ticket'=>$result['ticket'],
                    'create_time'=>date('Y-m-d H:i:s',time()+7000)
                );
                $wxObj = new Weixins;
                $wxObj->save($data,['id' => $id]);
            }else{
                $get_access_token = $this->wxObj->get_access_token($code);
                $openid = $get_access_token['openid'];
                $access_token = $get_access_token['access_token'];
                $data = $wx;
                $_SESSION['openid'] = $openid;
                $data['access_token'] = $access_token;
            }
//            echo "<pre>";var_dump($wx);exit;
        }

        $get_user_info = $this->wxObj->get_user_info($data['access_token'],$_SESSION['openid']);
//        echo "<pre>";var_dump($get_user_info);exit;
        $re = $this->createUser($get_user_info);
//        $dataAll = $this->get_signature($data['ticket']);
//        $_SESSION['signature'] = $dataAll['signature'];
//        $_SESSION['noncestr'] = $dataAll['noncestr'];
//        echo "<pre>";var_dump(222);exit;

        return redirect('/');
    }

    public function get_signature($ticket){
        $noncestr = $this->wxObj->randcode();
        $timestamp = time();
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

//        $string = sprintf("jsapi_ticket=%s&noncestr=%s×tamp=%s&url=%s", $ticket, $noncestr, $timestamp, $url);
//        $signature = sha1($string);

        $signature='jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url.'';
        $signature = sha1( $signature );

        $data = ['noncestr'=>$noncestr,'signature'=>$signature,'timestamp'=>$timestamp];
        return $data;
    }

    public function getWxAccessToken($code){
        $get_access_token = $this->wxObj->get_access_token($code);
        $openid = $get_access_token['openid'];
        $access_token = $get_access_token['access_token'];
        $token = $this->wxObj->wx_get_token();  //JS
        $ticket = $this->wxObj->wx_get_jsapi_ticket($token);
        $data = [
            'get_access_token'=>$get_access_token,
            'openid'=>$openid,
            'access_token'=>$access_token,
            'access_token_true'=>$token,
            'ticket'=>$ticket
        ];
        return $data;
    }

    public function createUser($get_user_info){
        $re = Users::where('openid',$get_user_info['openid'])->find();
        if(array_key_exists('adminUserId',$_SESSION)){
            $serviceCentId = $_SESSION['adminUserId'];
        }

        if(array_key_exists('share_member_id',$_SESSION)){
            $share_member_id = $_SESSION['share_member_id'];
        }
//        echo "<pre>";var_dump($serviceCentId);exit;
        if($re){
            $_SESSION['userinfo'] = $re;
            return true;
        }else{
            $data['openid'] = $get_user_info['openid'];
            $data['sex'] = $get_user_info['sex'];
            $pic = download($get_user_info['headimgurl']);
            $data['pic'] = $pic;
            if(array_key_exists('adminUserId',$_SESSION)){
                $data['service_cent_id'] = $serviceCentId;
            }
            if(array_key_exists('share_member_id',$_SESSION)){
                $data['share_member_id'] = $share_member_id;
            }
            $data['username'] = uniqid();
            $data['nickname'] = urlencode(json_encode($get_user_info['nickname']));
            $data['password'] = $get_user_info['openid'];
//            echo "<pre>";var_dump($data);exit;
            $catsObj = new Users($data);
            $result = $catsObj->save();
            $re = Users::where('openid',$data['openid'])->find();
            $_SESSION['userinfo'] = $re;
        }

        return $result;
    }

    public function user_info(){
//        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
        if(array_key_exists('userinfo',$_SESSION)){
            $userRe = $this->get_user_info();
            $curl = "userinfo";
            $re = ['footType'=>$curl,'userinfo'=>$userRe];
            return view("index@index/userInfo",['re'=>$re]);
        }
        $url = $this->wxObj->get_authorize_url(1);
        return redirect($url);

//        分割线
//        $userRe = $this->get_user_info();
//        $curl = "userinfo";
//        $re = ['footType'=>$curl,'userinfo'=>$userRe];
//
//        return view("index@index/userInfo",['re'=>$re]);
    }

    public function get_user_info(){
        if(array_key_exists('userinfo',$_SESSION)){
            $userInfo = $_SESSION['userinfo'];
        }else{
            $url = $this->wxLogin();
            return redirect($url);
        }
        $userId = $userInfo->id;
        $userRe = Users::get($userId);
        $nickname = json_decode(urldecode($userRe->getData('nickname')));
        $userRe->nickname = $nickname;
        $re = getPoint($userId);
        $true_count = round($re['true_cont'],6);
        $allPoint = $re['pointAll'];
        $userRe->allPoint = $allPoint;
        $userRe->allReturnMony = $true_count;
        return $userRe;
    }

    public function user_trade(Request $request){
//        echo "<pre>";var_dump($request->param());exit;
        $tradeObj = new Trades();
        $tradeType = $request->param('type');
        if($tradeType == null){
            $tradeType = "all";
        }
        $userRe = $this->get_user_info();

            if($tradeType == 'pd'){ // 待发货
                $trades = $tradeObj->where('user_id='.$userRe->id)
                    ->where('trade_type',0)
                    ->limit($this->pagesize)
                    ->select();
            }elseif($tradeType == 'as'){// 已发货Already shipped
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->where('trade_type',1)
                    ->limit($this->pagesize)
                    ->select();
            }elseif($tradeType == 'finish'){//已完成
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->where('trade_type',2)
                    ->limit($this->pagesize)
                    ->select();

            }else{
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->limit($this->pagesize)
                    ->select();
            }

//        echo "<pre>";var_dump($trades);exit;

        $curl = "userinfo";
        foreach ($trades as $k=>&$v){
            $v->item_name = Items::where('id',$v->item_id)->value('name');
            $v->pic = Items::where('id',$v->item_id)->value('pic');
            $v->trade_status = $v->getData('trade_type');
        }
//
        $re = ['footType'=>$curl,'userinfo'=>$userRe,'trade'=>$trades,'trade_type'=>$tradeType];
//        echo "<pre>";var_dump($re);exit;
        return view("index@index/trade",['re'=>$re]);
    }

    public function ajax_get_trade(Request $request){
        $post = $request->param();
        $macth = ['pd'=>0,'as'=>1,'finish'=>2,'all'=>null];
        $tradeObj = new Trades();
        $type = $macth[$post['type']];
        $userid = $post['userid'];
        $page = $post['page'];
        $pagesize = $this->pagesize;
        $start = ($page+1-1)*$pagesize;

        $page+=1;
        if($macth[$post['type']] !== null){
            $trades = $tradeObj
                ->where('user_id',$userid)
                ->where('trade_type',$type)
                ->limit($start,$pagesize)
                ->select();
        }else{
            $trades = $tradeObj
                ->where('user_id',$userid)
                ->limit($start,$pagesize)
                ->select();
        }

        foreach ($trades as $k=>&$v){
            $v->item_name = Items::where('id',$v->item_id)->value('name');
            $v->pic = Items::where('id',$v->item_id)->value('pic');
        }
        $data = ['page'=>$page,'trades'=>$trades];
        echo  json_encode($data);
    }

    public function trade_true_get(Request $request){
        $tradeId = $request->param('tradeId');
        $re = Trades::where('id', $tradeId)
            ->update(['trade_type' => 2]);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($tradeId);exit;
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
