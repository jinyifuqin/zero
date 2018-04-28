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
use app\index\model\Trades;
use \think\Controller;
use think\Request;

class Trade extends Controller
{
    public function index(){
        $trades = Trades::all();
//        echo"<pre>";var_dump($trades);exit;
        return view("admin@trade/index",['trades'=>$trades]);
    }
}