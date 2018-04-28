<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 2:25
 */

namespace app\index\controller;
use app\admin\model\Items;
use app\index\model\Trades;
use \think\Controller;
use think\Request;

class Item extends Controller
{
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

    public function tradeCreate(Request $request){
        $post = $request->param();
        $trade = new Trades();
        $tradeNum = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $create_time = date("Y-m-d H:i:s");
//        echo "<pre>";var_dump($create_time);exit;
        $trade->data([
            'name'  =>  $post['name'],
            'address'  =>  $post['address'],
            'item_id'  =>  $post['item_id'],
            'user_id'  =>  $post['userid'],
            'type'  =>  $post['type'],
            'buy_num'  =>  $post['number'],
            'phone_num'  =>  $post['phone_num'],
            'buy_num'  =>  $post['number'],
            'trade_number'  =>  $tradeNum,
            'create_time'  =>  $create_time,
        ]);
        $re = $trade->save();
        if($re){
            $this->success('订单生成成功！请等待服务中心发货');
        }

    }
}