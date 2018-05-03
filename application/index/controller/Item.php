<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 2:25
 */

namespace app\index\controller;
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
        $re = Items::all();
        return view("index@item/index",['re'=>$re]);
    }

    public function item(Request $request){
        $id = $request->param('id');
        $re = Items::get(['id' => $id]);
//        echo "<pre>";var_dump($re);exit;
        return view("index@item/item",['re'=>$re]);
    }

    public function addAddr(){
        $_SESSION['url'] = $_SERVER['HTTP_REFERER'];
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $addr = Addrs::all(['user_id'=>$userid]);
        $data['userid'] = $userid;
        foreach ($addr as $k=>&$v){
            $v['desc'] = preg_replace('/%2C/',' ',$v->desc);
        }

        $data['addr'] = $addr;

        return view("index@item/addAddrList",['re'=>$data]);
    }

    public function add_addr_info(){
        return view("index@item/addAddrInfo");
    }

    public function saveAddr(Request $request){
        $delimiter = urlencode(',');
        $desc = implode($delimiter,explode(' ',$request->param('desc'))).$delimiter.$request->param('desc1');
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
            'phone_num'=>$request->param('phone_num'),
        ];

        $addrsObj = new Addrs();
        $addrsObj->saveAddr($userid,$postInfo,$default);

        return redirect($_SESSION['url']);
    }

    public function tradeCreate(Request $request){
        $post = $request->param();
        $trade = new Trades();
        $tradeNum = date('Ymd').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $create_time = date("Y-m-d H:i:s");
        $numMatch = preg_match('/^\d+$/',$post['number']);
        $phoneMatch = preg_match('/^1[34578]\d{9}$/',$post['phone_num']);
        if(!$numMatch){
            $data = ['msg'=>"请输入正确的数量！",'type'=>"error"];
            return  json_encode($data);

        }
        if(!$phoneMatch){
            $data = ['msg'=>"请输入正确的手机号！",'type'=>"error"];
            return json_encode($data);

        }
        if($post['address'] == ''){
            $data = ['msg'=>"请添加收货地址！",'type'=>"error"];
            return json_encode($data);

        }
        $all = [
            'name'  =>  $post['name'],
            'address'  =>  $post['address'],
            'item_id'  =>  $post['item_id'],
            'user_id'  =>  $post['userid'],
            'type'  =>  $post['type'],
            'buy_num'  =>  $post['number'],
            'phone_num'  =>  $post['phone_num'],
            'trade_number'  =>  $tradeNum,
            'create_time'  =>  $create_time,
        ];
        $trade->data($all);
//        echo "<pre>";var_dump($all);exit;
        $re = $trade->save();
        if($re){
            $data = ['msg'=>"订单生成成功！请等待服务中心发货",'type'=>"success"];
            return json_encode($data);
        }else{
            $data = ['msg'=>"订单生成失败！",'type'=>"error"];
            return json_encode($data);
        }

    }
}