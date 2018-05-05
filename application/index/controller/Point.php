<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/5 0005
 * Time: 上午 9:53
 */

namespace app\index\controller;
use app\admin\model\Items;
use app\index\model\Addrs;
use app\index\model\SurplusPoints;
use app\index\model\Users;
use app\index\model\Trades;
use app\index\model\Points;
use \think\Controller;
use think\Db;
use think\Request;

class Point  extends Controller
{
    private $userId;
    private $pointCount;
    private $type;
    private $getType;
    private $frozenFlag;

    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function addDiscountByBuy($giveDiscount){
        $this->userId = $giveDiscount['userId'];
        $this->pointCount = $giveDiscount['pointCount'];
        $this->type = $giveDiscount['type'];

        $point = Points::create([
            'count'  =>  $this->pointCount,
            'user_id' =>  $this->userId,
            'type' =>   $this->type
        ]);

        $surpluspoint = SurplusPoints::create([
            'userid' =>  $this->userId,
            'point_count' =>   $this->pointCount,
        ]);



    }

    public function startUpDiscount(){
        ignore_user_abort(); //即使Client断开(如关掉浏览器)，PHP脚本也可以继续执行.
        set_time_limit(0); // 执行时间为无限制，php默认执行时间是30秒，可以让程序无限制的执行下去
        $interval=24*60*60; // 每隔一天运行一次
//        do{
//            sleep($interval); // 按设置的时间等待一小时循环执行
//            $sql="update blog set time=now()";
//            ...... //其他操作
//        }while(true);
    }
}