<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 2:25
 */

namespace app\index\controller;
use app\admin\model\Adminusers;
use app\admin\model\Discounts;
use app\admin\model\Items;
use app\index\model\Addrs;
use app\index\model\Users;
use app\index\model\Trades;
use \think\Controller;
use think\Db;
use think\Request;

class Item extends Controller
{
    public function __construct(Request $request)
    {
        session_start();
    }

    public function itemList(){
//        echo "<pre>";var_dump($_SESSION);exit;
        $re = Items::all();
        $curl = "itemList";
        $re = ['footType'=>$curl,'itemList'=>$re];
        return view("index@item/index",['re'=>$re]);
    }

    public function item(Request $request){
        $id = $request->param('id');
        $result = Items::get(['id' => $id]);
//        echo "<pre>";var_dump($re);exit;
        $curl = "itemInfo";
        $re = ['footType'=>$curl,'itemInfo'=>$result];
//        echo "<pre>";var_dump($re);exit;
        return view("index@item/item",['re'=>$re]);
    }

    public function addAddr(){
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
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

//        echo "<pre>";var_dump($request->param());exit;
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
            $AddrObj = Addrs::get($postInfo['addrid']);
            if($AddrObj){
                $default = $AddrObj->default;
            }
        }
//        echo "<pre>";var_dump($postInfo);exit;


        $addrsObj = new Addrs();
        $addrsObj->saveAddr($userid,$postInfo,$default);

        return redirect('/addAddr');
    }

    public function check_discount(Request $request){
        $discountNum = $request->param('discountNum');
        $disObj = Discounts::get(['number'=>$discountNum]);
        if($disObj){
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
        if($re){
            $data = ['msg'=>"订单生成成功！请等待服务中心发货",'type'=>"success",'cent_name'=>$service_cent_name];
            return json_encode($data);
        }else{
            $data = ['msg'=>"订单生成失败！",'type'=>"error"];
            return json_encode($data);
        }

    }
}