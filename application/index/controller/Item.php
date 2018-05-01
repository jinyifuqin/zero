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

        return view("index@item/addAddr");
    }

    public function saveAddr(Request $request){
        if($request->param('default')){
            $default = 1;
        }else{
            $default = 0;
        }
        $userinfo = $_SESSION['userinfo'];
        $userid = $userinfo->id;
        $addrsObj = new Addrs();
        $addrsObj->saveAddr($userid,$request->param('desc'),$default);

        return redirect($_SESSION['url']);
    }

    public function tradeCreate(Request $request){
        $post = $request->param();
        $trade = new Trades();
        $tradeNum = date('Ymd').str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $create_time = date("Y-m-d H:i:s");
        echo "<pre>";var_dump($post);exit;
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
            $this->success('订单生成成功！请等待服务中心发货');
        }

    }
}