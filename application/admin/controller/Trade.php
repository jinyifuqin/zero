<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28 0028
 * Time: 下午 2:18
 */

namespace app\admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\Brands;
use app\admin\model\Cats;
use app\admin\model\Items;
use app\index\model\Addrs;
use app\index\model\Trades;
use \think\Controller;
use think\Request;

class Trade extends Controller
{
    public function index(){
        $trades = Trades::all();
        $delimiter = urlencode(',');
        foreach ($trades as $k=>&$v){
            $v['item_name'] = Items::where('id',$v['item_id'])->value('name');
            $v['address'] = preg_replace("/$delimiter/",' ',$v['address']);
        }

        return view("admin@trade/index",['trades'=>$trades]);
    }

    public function send(Request $request){
        $id = $request->param('id');
        $trade = Trades::get($id);

        if($trade->getData('type') == 0){
            $trade->type = 1;
        }else{
            $trade->type = 0;
        }
        $result = $trade->save();
        if($request){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

}