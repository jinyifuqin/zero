<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 2:25
 */

namespace app\index\controller;
use app\admin\model\Adminusers;
use app\admin\model\ArticleMenus;
use app\admin\model\Articles;
use app\admin\model\Discounts;
use app\admin\model\Items;
use app\admin\model\PointItems;
use app\index\model\Addrs;
use app\index\model\Points;
use app\index\model\Users;
use app\index\model\Trades;
use \think\Controller;
use think\Db;
use think\Log;
use think\Request;

class Item extends Controller
{
    public $pages = 4;
    public function __construct(Request $request)
    {
        session_start();
    }

    public function itemList(){
//        Log::write('测试','notice');
        $itemObj = new Items();
        $re = $itemObj->where('status',1)
            ->order('sort', 'desc')
            ->limit($this->pages)
            ->select();
        $curl = "itemList";
        $re = ['footType'=>$curl,'itemList'=>$re,'page'=>1];
        return view("index@item/index",['re'=>$re]);
    }

    public function ajax_item($page){
        $start = ($page-1)*$this->pages;
        $itemObj = new Items();
        $re = $itemObj->where('status',1)
            ->order('sort', 'desc')
            ->limit($start,$this->pages)
            ->select();
        $info = ['items'=>$re,'pages'=>$page];
        echo json_encode($info);
    }

    public function item(Request $request){
        $id = $request->param('id');
        $result = Items::get(['id' => $id]);
        $artM = ArticleMenus::get(['title'=>'公告']);
        $artObj = new Articles();
        if($artM){
            $artMId = $artM->id;
            $gg = $artObj->where('menu_id',$artMId)
                ->where('status',1)
                ->order('give_good', 'desc')
                ->select();
        }
        $curl = "itemList";
        $re = ['footType'=>$curl,'itemInfo'=>$result];
        if($gg){
            $re['gg'] = $gg;
        }
//        echo "<pre>";var_dump($re);exit;
        return view("index@item/item",['re'=>$re]);
    }

    public function point_item($id){
        $result = PointItems::get(['id' => $id]);
        $artM = ArticleMenus::get(['title'=>'公告']);
        $artObj = new Articles();
        if($artM){
            $artMId = $artM->id;
            $gg = $artObj->where('menu_id',$artMId)
                ->where('status',1)
                ->order('give_good', 'desc')
                ->select();
        }
        $curl = "itemList";
        $re = ['footType'=>$curl,'itemInfo'=>$result];
        $_SESSION['pyHttp'] = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $re['url'] = $_SERVER['HTTP_REFERER'];
        if($gg){
            $re['gg'] = $gg;
        }
        return view("index@item/pointItem",['re'=>$re]);
    }

    public function addAddr(){
//        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
//        echo "<pre>";var_dump($_SESSION);exit;
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $addr = Addrs::all(['user_id'=>$userid]);
        $data['userid'] = $userid;
        foreach ($addr as $k=>&$v){
            $v['name'] = json_decode(urldecode($v['name']));
            $v['desc'] = preg_replace('/%2C/',' ',$v->desc);
        }

        $data['addr'] = $addr;
        if($_SESSION['itemType'] == 1){
            $data['url'] = '/pointBuy';
        }else{
            $data['url'] = '/buy';
        }

//        echo "<pre>";var_dump($_SERVER);exit;
        return view("index@item/addAddrList",['re'=>$data]);
    }

    public function add_addr_info(){
        return view("index@item/addAddrInfo");
    }

    public function change_addr(Request $request){
        $id = $request->param('id');
        $addr = new Addrs();
        $userId = $_SESSION['userinfo']->id;
        $postInfo = ['addrid'=>$id];
        $default = 1;
        $re = $addr->saveAddr($userId,$postInfo,$default);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function addr_edit(Request $request){
        $id = $request->param('id');
        $addr = Addrs::get($id);
        $delimiter = urlencode(',');
        $addr->name = json_decode(urldecode($addr->name ));
        $addr->desc = preg_replace("/$delimiter/",' ',$addr->desc);
        $addr->desc1 = $addr->detail_desc;

        return view("index@item/addrEdit",['re'=>$addr]);
    }

    public function saveAddr(Request $request){
        $desc = $request->param('desc');
        $detailDesc = $request->param('desc1');
        $delimiter = urlencode(',');
        $desc = implode($delimiter,explode(' ',$desc));
        if($request->param('default')){
            $default = 1;
        }else{
            $default = 0;
        }


        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $postInfo = [
            'user_id'=>$userid,
            'name'=>$request->param('name'),
            'desc'=>$desc,
            'detail_desc'=>$detailDesc,
            'phone_num'=>$request->param('phone_num'),
        ];
        if(array_key_exists('addrId',$request->param())){
            $postInfo['addrid']=$request->param('addrId');
//            $AddrObj = Addrs::get($postInfo['addrid']);
//            if($AddrObj){
//                $default = $AddrObj->default;
//            }
        }
//


        $addrsObj = new Addrs();
        $addrsObj->saveAddr($userid,$postInfo,$default);

        return redirect('/addAddr');
    }

    public function check_discount(Request $request){
        $discountNum = $request->param('discountNum');
        $disObj = Discounts::get(['number'=>$discountNum]);
        if($disObj && $disObj->can_use_count > $disObj->used_count){
            $zk = $disObj->zk;
            $msg = ['type'=>'success','zk'=>$zk];
        }else{
            $msg = ['type'=>'error'];
        }
        echo json_encode($msg);

//        echo "<pre>";var_dump($zk);exit;
    }

    public function tradeCreate(Request $request){
        $userid = $_SESSION['userinfo']->id;
        $userObj = Users::get($userid);
        $service_cent_id = $userObj->service_cent_id;
        $adminObj = Adminusers::get($service_cent_id);
        $service_cent_name = $adminObj->nickname;
        $post = $request->param();
        $discount = $post['discount'];
        $trade = new Trades();
        $tradeNum = date('Ymd').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $create_time = date("Y-m-d H:i:s");

        $numMatch = preg_match('/^\d+$/',$post['buynum']);
        if(array_key_exists('phone_num',$post) && $post['name'] != '' && $post['name'] != ' '){
            $phoneMatch = preg_match('/^1[34578]\d{9}$/',$post['phone_num']);
        }else{
            $data = ['msg'=>"请添加收货信息！",'type'=>"error"];
            return  json_encode($data);
        }

        if(!$numMatch){
            $data = ['msg'=>"请输入正确的数量！",'type'=>"error"];
            return  json_encode($data);
        }
        if(!$phoneMatch){
            $data = ['msg'=>"请输入正确的手机号！",'type'=>"error"];
            return json_encode($data);
        }
        if($post['address'] == '' || $post['address'] == ' '){
            $data = ['msg'=>"请添加收货地址！",'type'=>"error"];
            return json_encode($data);
        }
//        echo "<pre>";var_dump(($phoneMatch));exit;
        $delimiter = urlencode(',');
        $post['address'] = implode($delimiter,explode(' ',$post['address']));

        $all = [
            'name'  =>  $post['name'],
            'address'  =>  $post['address'],
            'item_id'  =>  $post['itemid'],
            'user_id'  =>  $userid,
            'check_type'  =>  0,
            'buy_num'  =>  $post['buynum'],
            'buy_price' => $post['totalprice'],
            'phone_num'  =>  $post['phone_num'],
            'trade_number'  =>  $tradeNum,
            'create_time'  =>  $create_time,
            'update_time'  =>  $create_time,
        ];
        $trade->data($all);
        $re = $trade->save();
        if($discount){
            $disRe = Db::table('yzt_discounts')->where('number', $discount)->setInc('used_count');
        }

        if($re){
            $data = ['msg'=>"订单生成成功！请等待服务中心发货",'type'=>"success",'cent_name'=>$service_cent_name];
            return json_encode($data);
        }else{
            $data = ['msg'=>"订单生成失败！",'type'=>"error"];
            return json_encode($data);
        }

    }

    public function point_trade_create(Request $request){
        $userid = $_SESSION['userinfo']->id;
        $userObj = Users::get($userid);
        $service_cent_id = $userObj->service_cent_id;
        $adminObj = Adminusers::get($service_cent_id);
        $service_cent_name = $adminObj->nickname;
        $post = $request->param();
//        $discount = $post['discount'];
        $trade = new Trades();
        $tradeNum = date('Ymd').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $create_time = date("Y-m-d H:i:s");

        $numMatch = preg_match('/^\d+$/',$post['buynum']);
        if(array_key_exists('phone_num',$post) && $post['name'] != '' && $post['name'] != ' '){
            $phoneMatch = preg_match('/^1[34578]\d{9}$/',$post['phone_num']);
        }else{
            $data = ['msg'=>"请添加收货信息！",'type'=>"error"];
            return  json_encode($data);
        }

        if(!$numMatch){
            $data = ['msg'=>"请输入正确的数量！",'type'=>"error"];
            return  json_encode($data);
        }
        if(!$phoneMatch){
            $data = ['msg'=>"请输入正确的手机号！",'type'=>"error"];
            return json_encode($data);
        }
        if($post['address'] == '' || $post['address'] == ' '){
            $data = ['msg'=>"请添加收货地址！",'type'=>"error"];
            return json_encode($data);
        }
        $noUseAdd = Points::where([
            'user_id' => $userid,
            'type'=>1,
            'frozen_flag'=>0
        ])->sum('count');

        $noUseDel = Points::where([
            'user_id' => $userid,
            'type'=>0,
            'frozen_flag'=>0
        ])->sum('count');
        $noUseEnd = $noUseAdd-$noUseDel;
        if($noUseEnd < $post['totalprice']){
            $data = ['msg'=>"您的积分不足！",'type'=>"error"];
            return json_encode($data);
        }
//        echo "<pre>";var_dump(($post['totalprice']));exit;
        $delimiter = urlencode(',');
        $post['address'] = implode($delimiter,explode(' ',$post['address']));
        $buyPoint['user_id'] = $userid;
        $buyPoint['count'] = $post['totalprice'];
        $buyPoint['type'] = 0;
        $buyPoint['get_type'] = 7;
        $buyPoint['frozen_flag'] = 0;
        $buyPoint['create_time'] = date('Y-m-d H:i:s',time());
        $pointObj = new Points();
        $pointObj->data($buyPoint);
        $res = $pointObj->save();
        if($res){
            $all = [
                'name'  =>  $post['name'],
                'address'  =>  $post['address'],
                'item_id'  =>  $post['itemid'],
                'user_id'  =>  $userid,
                'check_type'  =>  0,
                'buy_num'  =>  $post['buynum'],
                'buy_price' => $post['totalprice'],
                'phone_num'  =>  $post['phone_num'],
                'trade_number'  =>  $tradeNum,
                'create_time'  =>  $create_time,
                'update_time'  =>  $create_time,
                'item_type' => 1
            ];
            $trade->data($all);
            $re = $trade->save();
        }

//        if($discount){
//            $disRe = Db::table('yzt_discounts')->where('number', $discount)->setInc('used_count');
//        }

        if($re){
            $data = ['msg'=>"订单生成成功！请等待服务中心发货",'type'=>"success",'cent_name'=>$service_cent_name];
            return json_encode($data);
        }else{
            $data = ['msg'=>"订单生成失败！",'type'=>"error"];
            return json_encode($data);
        }

    }
}