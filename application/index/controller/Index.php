<?php
namespace app\index\controller;
use app\admin\model\Adminusers;
use app\admin\model\ArticleMenus;
use app\admin\model\Articles;
use app\admin\model\Brands;
use app\admin\model\IndexPics;
use app\admin\model\Notices;
use app\admin\model\PointItems;
use app\index\model\Addrs;
use app\index\model\GiveGoods;
use app\index\model\Talks;
use app\index\model\Weixins;
use app\index\model\Points;
use app\index\model\Trades;
use app\weixin\controller\Wechat;
use app\index\model\Users;
use app\admin\model\Items;
use think\Db;
use think\Request;
class Index extends Common
{
    public $wxObj;
    public $pagesize = 2;
    private $user;
    public function __construct()
    {
        session_start();
        $this->wxObj = new Wechat();
    }

    public function index(Request $request)
    {
//        session_destroy()
        $_SESSION['url'] = $_SERVER['HTTP_HOST'];
        $serviceuserid = $request->param('userid');
        $memberid = $request->param('memberid');
        if(isset($serviceuserid)){
            $_SESSION['adminUserId'] = $serviceuserid;
        }
        if(isset($memberid)){
            $_SESSION['share_member_id'] = $memberid;
        }

        $wxLook = check_login_type();
        if($wxLook){
            if(!array_key_exists('wxInfo',$_SESSION)){
                $url = $this->wxObj->get_authorize_url(1);
                return redirect($url);
            }
        }
        $itemObj = new Items();
        $re = $itemObj->where('status',1)
            ->limit(2)
            ->order('sort', 'desc')
            ->select();
        $pointItem = PointItems::all();
        $indePic = IndexPics::all();
        foreach($indePic as &$v){
            $v->pic = addslashes($v->pic);
        }
        $noticeObj = new Notices();
        $artObj = new Articles();
        $gg = $noticeObj->where('status',1)
            ->order('sort', 'desc')
            ->select();
        if(!empty($gg)){
            foreach($gg as $val){
                $val->create_time = date('Y-m-d',strtotime($val->create_time));
            }
        }
        $hotArt = $artObj->where('status',1)
            ->limit(4)
            ->order('give_good', 'desc')
            ->select();

        if(!empty($hotArt)){
            foreach($hotArt as $value){
                $value->create_time = date('Y-m-d',strtotime($value->create_time));
            }
        }


        $serviceMid = ArticleMenus::where('title','服务中心')->value('id');
        $serviceArt = $artObj->where('menu_id',$serviceMid)
            ->where('status',1)
            ->limit(4)
            ->order('give_good', 'desc')
            ->select();

        if(!empty($serviceArt)){
            foreach($serviceArt as $vv){
                $vv->create_time = date('Y-m-d',strtotime($vv->create_time));
            }
        }

        $curl = "index";
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        if(array_key_exists('userinfo',$_SESSION)){
            $id = $_SESSION['userinfo']->id;
            $userObj = Users::get($id);
            if(isset($serviceuserid) && $userObj->service_cent_id == 0){
                $userObj->service_cent_id = $serviceuserid;
            }
            if(isset($memberid) && $userObj->share_member_id == 0 && $memberid != $id){
                $userObj->share_member_id = $memberid;
            }

            if(isset($memberid) || isset($serviceuserid)){
                $userObj->save();
            }
            $weixin = wechat_js($id,$url);
//            echo "<pre>";var_dump($userObj->id);exit;
            $re = ['footType'=>$curl,'itemInfo'=>$re,'weixin'=>$weixin,'indexPic'=>$indePic,'pItem'=>$pointItem];
            if(!empty($gg)){
                $re['gg'] = $gg;
            }
            if(!empty($hotArt)){
                $re['hotArt'] = $hotArt;
            }
            if(!empty($serviceArt)){
                $re['serArt'] = $serviceArt;
            }
            return view("index@index/index",['re'=>$re]);
        }else{
            $weixin = wechat_js(null,$url);
            $re = ['footType'=>$curl,'itemInfo'=>$re,'weixin'=>$weixin,'indexPic'=>$indePic,'pItem'=>$pointItem];
            if(!empty($gg)){
                $re['gg'] = $gg;
            }
            if(!empty($hotArt)){
                $re['hotArt'] = $hotArt;
            }
            if(!empty($serviceArt)){
                $re['serArt'] = $serviceArt;
            }
            return view("index@index/index",['re'=>$re]);
//            $url = $this->wxObj->get_authorize_url(1);
//            return redirect($url);

        }

    }

    public function buy(){
        $itemid = $_SESSION['itemid'];
        $item = Items::get(['id' => $itemid]);
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $user = Users::where('id',$userid)->find();
        $service_cent_id = $user->service_cent_id;
        if($service_cent_id == 0){
            $flag = 'error';
        }else{
            $flag = 'success';
        }
        $_SESSION['userinfo'] = $user;
        $addr = Addrs::where('id',$user->address)->find();
        $item->brandName = Brands::where('id',$item->brand_id)->value('name');
        $data['item'] = $item;
        $data['userinfo'] = $user;

        if($addr){
            $addr->name = json_decode(urldecode($addr->name));
            $addr->desc = preg_replace('/%2C/',' ',$addr->desc);
            $data['addr'] = $addr;
        }
        $data['flag'] = $flag;
        $_SESSION['itemType'] = 0;
//        echo "<pre>";var_dump($data['service_list']);exit;
        return view("index@item/buying",['data'=>$data]);
    }

    public function point_buy(){
        $itemid = $_SESSION['itemid'];
        $item = PointItems::get(['id' => $itemid]);
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $user = Users::where('id',$userid)->find();
        $service_cent_id = $user->service_cent_id;
        if($service_cent_id == 0){
            $flag = 'error';
        }else{
            $flag = 'success';
        }
        $_SESSION['userinfo'] = $user;
        $addr = Addrs::where('id',$user->address)->find();
        $item->brandName = Brands::where('id',$item->brand_id)->value('name');
        $data['item'] = $item;
        $data['userinfo'] = $user;

        if($addr){
            $addr->name = json_decode(urldecode($addr->name));
            $addr->desc = preg_replace('/%2C/',' ',$addr->desc);
            $data['addr'] = $addr;
        }
        $data['flag'] = $flag;
        $_SESSION['itemType'] = 1;
        $data['url'] = $_SESSION['pyHttp'];
//        echo "<pre>";var_dump($_SERVER);exit;
        return view("index@item/pointBuy",['re'=>$data]);
    }

    public function wxLogin(Request $request=null){
        if($request){
            $_SESSION['itemid'] = $request->param('id');
        }
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
        if(array_key_exists('userinfo',$_SESSION) && $_SESSION['userinfo'] != null){

            return redirect('/buy');
        }else{
            return redirect('/userInfo');
        }
    }

    public function point_wx_login(Request $request=null){
        if($request){
            $_SESSION['itemid'] = $request->param('id');
        }
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
        if(array_key_exists('userinfo',$_SESSION) && $_SESSION['userinfo'] != null){

            return redirect('/pointBuy');
        }else{
            return redirect('/userInfo');
        }
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
        $_SESSION['wxInfo'] = $get_user_info;

//        $re = $this->createUser($get_user_info);

//        echo "<pre>";var_dump(222);exit;

        return redirect('/');
    }

    public function get_signature($ticket){
        $noncestr = $this->wxObj->randcode();
        $timestamp = time();
        $url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
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
//        echo "<pre>";var_dump($_SESSION);exit;
        if(array_key_exists('userinfo',$_SESSION)){
            $userRe = $this->get_user_info();
            $flag = Points::get(['user_id'=>$userRe->id]);
            if($flag){
                $allPoint = 500000000;
                $pO = new Points();
                $noUseAdd = $pO->where('type',"=",1)
                    ->where('frozen_flag',"=",0)
                    ->sum('count');

//                $noUseDel = $pO->where('type',"=",0)
//                    ->where('frozen_flag',"=",0)
//                    ->sum('count');
//                $shareGet = $pO->where('type',"=",1)
//                    ->where('get_type',"=",3)
//                    ->where('frozen_flag',"=",1)
//                    ->sum('count');
//                $prepareCount = $noUse + $shareGet;buy
                $prepareCount = $noUseAdd;
                $userRe->allSystemCount = $allPoint;
                $userRe->prepareCount = $prepareCount;
                $userRe->surplusCount = $allPoint - $prepareCount;
            }

//            echo "<pre>";var_dump($prepareCount);exit;
            $curl = "userinfo";
            $re = ['footType'=>$curl,'userinfo'=>$userRe,'flag'=>$flag];
            return view("index@index/userInfo",['re'=>$re]);
        }else{
            $url = '/';
            $re['url'] = $url;
            return view("index@index/login",['re'=>$re]);
        }
//        $url = $this->wxObj->get_authorize_url(1);
//        return redirect($url);
    }

    public function get_user_info(){
//        session_destroy();
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
        $canUse = $re['canUse'];
        $noUse = $re['noUse'];
//        echo "<pre>";var_export($noUse);var_export("<br>");var_export($canUse);exit;
        $userRe->allPoint = $noUse;
        $userRe->allReturnMony = $canUse;
        return $userRe;
    }

    public function show_no_use_point_list(){
        $userId = $_SESSION['userinfo']->id;
        $pObj = new \app\index\model\Points();
        $list = $pObj->where('user_id', $userId)
//            ->where('get_type', '>',0)
            ->where('frozen_flag', 0)
            ->limit(10)
            ->order('create_time', 'desc')
            ->select();
        $url = '/userInfo';
        $curl = "userinfo";
        $re = ['footType'=>$curl,'list'=>$list,'url'=>$url];
//        echo "<pre>";var_dump($list);exit;
        return view("index@index/showFrozenPointList",['re'=>$re]);

    }

    public function show_point_list(){
        $userId = $_SESSION['userinfo']->id;
        $pObj = new \app\index\model\Points();
        $list = $pObj->where('user_id', $userId)
            ->where('get_type', '>',0)
            ->where('frozen_flag', 1)
            ->limit(10)
            ->order('create_time', 'desc')
            ->select();

//        $list = \app\index\model\Points::all([
//            'user_id' => $userId,
//            'get_type'=>2,
////            'type'=>1,
//            'frozen_flag'=>1
//        ]);
        $url = '/userInfo';
        $curl = "userinfo";
        $re = ['footType'=>$curl,'list'=>$list,'url'=>$url];
//        echo "<pre>";var_dump($list);exit;
        return view("index@index/showPointList",['re'=>$re]);

    }

    public function user_trade(Request $request){
        $tradeObj = new Trades();
        $tradeType = $request->param('type');
        if($tradeType == null){
            $tradeType = "all";
        }
        $userRe = $this->get_user_info();

            if($tradeType == 'pd'){ // 待发货
                $trades = $tradeObj->where('user_id='.$userRe->id)
                    ->where('trade_type',0)
                    ->order('create_time', 'desc')
                    ->limit($this->pagesize)
                    ->select();
            }elseif($tradeType == 'as'){// 已发货Already shipped
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->where('trade_type',1)
                    ->order('create_time', 'desc')
                    ->limit($this->pagesize)
                    ->select();
            }elseif($tradeType == 'finish'){//已完成
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->where('trade_type',2)
                    ->order('create_time', 'desc')
                    ->limit($this->pagesize)
                    ->select();

            }else{
                $trades = $tradeObj->where('user_id',$userRe->id)
                    ->order('create_time', 'desc')
                    ->limit($this->pagesize)
                    ->select();
            }

        $curl = "userinfo";
        foreach ($trades as $k=>&$v){
            if($v->item_type == 0){
                $v->item_name = Items::where('id',$v->item_id)->value('name');
                $v->pic = Items::where('id',$v->item_id)->value('pic');
            }else{
                $v->item_name = PointItems::where('id',$v->item_id)->value('name');
                $v->pic = PointItems::where('id',$v->item_id)->value('pic');
            }

            $v->trade_status = $v->getData('trade_type');
        }
//
        $re = ['footType'=>$curl,'userinfo'=>$userRe,'trade'=>$trades,'trade_type'=>$tradeType];
//        echo "<pre>";var_dump($trades);exit;
        return view("index@index/trade",['re'=>$re]);
    }

    public function trade_detail($id,$type){
        $trade = Trades::get($id);
        $trade->address = preg_replace('/%2C/',' ',$trade->address);
        $trade->onePrice = $trade->buy_price/$trade->buy_num;
        if($type == 0){
            $item = Items::get($trade->item_id);
        }else{
            $item = PointItems::get($trade->item_id);
        }

        $url = '/myTrade';
        $re = ['trade'=>$trade,'item'=>$item,'url'=>$url];

//        echo "<pre>";var_dump($trade);exit;
        return view("index@index/tradeDetail",['re'=>$re]);
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
                ->order('create_time', 'desc')
                ->limit($start,$pagesize)
                ->select();
        }else{
            $trades = $tradeObj
                ->where('user_id',$userid)
                ->order('create_time', 'desc')
                ->limit($start,$pagesize)
                ->select();
        }

        foreach ($trades as $k=>&$v){
            if($v->item_type == 0){
                $v->item_name = Items::where('id',$v->item_id)->value('name');
                $v->pic = Items::where('id',$v->item_id)->value('pic');
            }else{
                $v->item_name = PointItems::where('id',$v->item_id)->value('name');
                $v->pic = PointItems::where('id',$v->item_id)->value('pic');
            }

//            $v->item_name = Items::where('id',$v->item_id)->value('name');
//            $v->pic = Items::where('id',$v->item_id)->value('pic');
        }
        $data = ['page'=>$page,'trades'=>$trades];
        echo  json_encode($data);
    }

    public function trade_true_get(Request $request){
        $tradeId = $request->param('tradeId');
        $tradeInfo = Trades::get(['id' => $tradeId]);
        $re = false;
        if($tradeInfo->getData('trade_type') != 0){
            $re = Trades::where('id', $tradeId)
                ->update(['trade_type' => 2]);
        }

        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($tradeId);exit;
    }

    public function trade_del(Request $request){
        $tradeId = $request->param('tradeId');
        $re = Trades::destroy($tradeId);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }


    public function community($id='')
    {
        if(array_key_exists('userinfo',$_SESSION)){
            $user = $_SESSION['userinfo'];
            $userId = $user->id;
        }


        if($id == ''){
            $menuId = ArticleMenus::get(['title'=>'服务中心']);
            $menuId = $menuId->id;
        }else{
            $menuId = $id;
        }
        $art = Articles::all(['menu_id'=>$menuId]);

        foreach ($art as &$v){
            $v->content = htmlspecialchars_decode($v->content);
            $v->talkCount = Talks::where('art_id','=',$v->id)->count();
            if(isset($userId)){
                $gObj = GiveGoods::get(['user_id'=>$userId,'gooded_id'=>$v->id,'type'=>0]);
                $talkFlag = Talks::get(['art_id'=>$v->id,'user_id'=>$userId]);
            }

//            echo "<pre>";var_dump($talkFlag);exit;
            if(isset($talkFlag)){
                $v->talkFlag = true;
            }else{
                $v->talkFlag = false;
            }
            if(isset($gObj)){
                $v->artFlag = true;
            }else{
                $v->artFlag = false;
            }
//            echo "<pre>";var_dump($a);exit;
        }
        $menu = ArticleMenus::all();
        $curl = "community";
        $re = ['footType'=>$curl,'art'=>$art,'menu'=>$menu];
        return view("index@index/community",['re'=>$re]);
    }

    public function notice(){
        $notice = Notices::all(['status'=>1]);
        $curl = 'index';
        $re = ['footType'=>$curl,'list'=>$notice];
        return view("index@index/notice",['re'=>$re]);
    }

    public function notice_detail($id){
        $notice = Notices::get($id);
        $notice->content = htmlspecialchars_decode($notice->content);
        $url = $_SERVER['HTTP_REFERER'];
        $re = ['url'=>$url,'content'=>$notice];
//        echo "<pre>";var_dump($url);exit;
        return view("index@index/noticeDetail",['re'=>$re]);


    }

    public function article_detail($id){
        if(array_key_exists('userinfo',$_SESSION)){
            $user = $_SESSION['userinfo'];
            $userId = $user->id;
            $agObj = GiveGoods::get(['user_id'=>$userId,'gooded_id'=>$id,'type'=>0]);
        }

        $art = Articles::get($id);
        $art->content = htmlspecialchars_decode($art->content);

        if(isset($agObj)){
            $art->artFlag = true;
        }else{
            $art->artFlag = false;
        }
        $talk = Talks::all(['art_id'=>$id]);
        foreach($talk as &$v){
            $v->pic = Users::where('id',$v->user_id)->value('pic');
            $nickname = Users::where('id',$v->user_id)->value('nickname');
            $v->nickname = json_decode(urldecode($nickname));
            if(isset($userId)){
                $pgObj = GiveGoods::get(['user_id'=>$userId,'gooded_id'=>$v->id,'type'=>1]);
            }
            if(isset($pgObj)){
                $v->plFlag = true;
            }else{
                $v->plFlag = false;
            }
        }
        $url = '/community';
//        $curl = "userinfo";
        $re = ['url'=>$url,'talk'=>$talk,'art'=>$art];
//        $re = ['talk'=>$talk,'art'=>$art];
        return view("index@index/articleDetail",['re'=>$re]);
    }

    public function artTalk(Request $request){
        $post = $request->param();
        $data['art_id'] = $post['artId'];
        if(!array_key_exists('userinfo',$_SESSION)){
            $msg = array('url'=>'/userInfo');
            echo json_encode($msg);exit;
        }
        $user = $_SESSION['userinfo'];
        $data['user_id'] = $user->id;

        $data['user_talk'] = $post['userTalk'];
        $talk = new Talks();
        $talk->data($data);
        $re = $talk->save();
        if($re){
            $msg = array('status'=>'success');
        }else{
            $msg = array('status'=>'error');
        }
        echo json_encode($msg);
    }

    public function art_give_good($id){
        $giveDoodsObj = new GiveGoods();
        if(!array_key_exists('userinfo',$_SESSION)){
            $msg = array('url'=>'/userInfo');
            echo json_encode($msg);exit;
        }
        $user = $_SESSION['userinfo'];
        $userId = $user->id;
        $gObj = GiveGoods::get(['user_id'=>$userId,'gooded_id'=>$id,'type'=>0]);

        if(!$gObj){
            $re1 = Db::table('yzt_articles')->where('id', $id)->setInc('give_good');
            $data['type'] = 0;
            $data['user_id'] = $userId;
            $data['gooded_id'] = $id;
            $giveDoodsObj->data($data);
            $re = $giveDoodsObj->save();
            if($re && $re1){
                $msg = array('status'=>'success','msg'=>'点赞成功！');
            }else{
                $msg = array('status'=>'error','msg'=>'点赞失败！');
            }
        }else{
            $msg = array('status'=>'error','msg'=>'您已经点过赞了！');
        }


        echo json_encode($msg);
    }

    public function pl_give_good($id){
        $giveDoodsObj = new GiveGoods();
        if(!array_key_exists('userinfo',$_SESSION)){
            $msg = array('url'=>'/userInfo');
            echo json_encode($msg);exit;
        }
        $user = $_SESSION['userinfo'];
        $userId = $user->id;
        $gObj = GiveGoods::get(['user_id'=>$userId,'gooded_id'=>$id,'type'=>1]);

        if(!$gObj){
            $re = Db::table('yzt_talks')->where('id', $id)->setInc('give_good');
            $data['type'] = 1;
            $data['user_id'] = $userId;
            $data['gooded_id'] = $id;
            $giveDoodsObj->data($data);
            $re = $giveDoodsObj->save();
            if($re){
                $msg = array('status'=>'success','msg'=>'点赞成功！');
            }else{
                $msg = array('status'=>'error','msg'=>'点赞失败！');
            }
        }else{
            $msg = array('status'=>'error','msg'=>'您已经点过赞了！');
        }

        echo json_encode($msg);
    }

}
