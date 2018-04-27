<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 2:25
 */

namespace app\index\controller;
use app\admin\model\Items;
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
}