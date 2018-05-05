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

    public function addPointByBuy($giveDiscount){
        $this->userId = $giveDiscount['userId'];
        $this->pointCount = $giveDiscount['pointCount'];
        $this->type = $giveDiscount['type'];
        $this->trade_number = $giveDiscount['trade_number'];

        $point = Points::create([
            'count'  =>  $this->pointCount,
            'user_id' =>  $this->userId,
            'type' =>   $this->type,
            'trade_number' => $this->trade_number,
            'future_count' => $this->pointCount*2
        ]);

//        $surpluspoint = SurplusPoints::create([
//            'userid' =>  $this->userId,
//            'point_count' =>   $this->pointCount,
//            'trade_number' => $this->trade_number,
//        ]);

    }

    public function delPointByTradeId($trade_number){
        Points::destroy(['trade_number' => $trade_number]);
    }


}