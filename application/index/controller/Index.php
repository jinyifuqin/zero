<?php
namespace app\index\controller;
use app\admin\model\Brands;
use app\index\model\Addrs;
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

    public function index()
    {
        $re = Items::all();
        $curl = "index";
        $re = ['footType'=>$curl,'itemInfo'=>$re];
//        echo password(123,456);exit;
        return view("index@index/index",['re'=>$re]);
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
//        unset($_SESSION['userinfo']);
        if(array_key_exists('userinfo',$_SESSION)){

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
//        echo "<pre>";var_dump($get);exit;
        if(!array_key_exists('get_access_token',$_SESSION) || $_SESSION['get_access_token'] == false){
            $get_access_token = $this->wxObj->get_access_token($code);
            $_SESSION['get_access_token'] = $get_access_token;
//            echo "<pre>";var_dump($get_access_token);exit;
        }else{
            $get_access_token = $_SESSION['get_access_token'];
        }

        if(!array_key_exists('get_user_info',$_SESSION) || $_SESSION['get_user_info'] == false){
//            echo "<pre>";var_dump(22);exit;
            $get_user_info = $this->wxObj->get_user_info($get_access_token['access_token'],$get_access_token['openid']);
            $_SESSION['get_user_info'] = $get_user_info;
        }else{
            $get_user_info = $_SESSION['get_user_info'];
        }

        $re = $this->createUser($get_user_info);

        return redirect($_SESSION['url']);
    }

    public function createUser($get_user_info){
        $re = Users::where('openid',$get_user_info['openid'])->find();
        if($re){
            $_SESSION['userinfo'] = $re;
            return true;
        }else{
            $data['openid'] = $get_user_info['openid'];
            $data['sex'] = $get_user_info['sex'];
            $pic = download($get_user_info['headimgurl']);
            $data['pic'] = $pic;
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
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
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
        $allPoint = round(getPoint($userId),6);
        $userRe->allPoint = $allPoint;
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
        }
//        echo "<pre>";var_dump($trades);exit;
//        echo "<pre>";var_dump($page);exit;
        $data = ['page'=>$page,'trades'=>$trades];
        echo  json_encode($data);
//        echo "<pre>";var_dump($data);exit;
//        echo "<pre>";var_dump(json_encode($data));


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
