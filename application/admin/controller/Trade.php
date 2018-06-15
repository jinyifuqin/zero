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
use app\admin\model\PointItems;
use app\index\model\Addrs;
use app\index\model\Points;
use app\index\model\Trades;
use app\index\controller\Point;
use app\index\model\Users;
use think\Config;
use \think\Controller;
use think\Db;
use think\Request;

class Trade extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
        parent::__construct($request);
        $configName = 'config.xml';
        $this->configPath = APP_PATH.'admin'.DS.'config'.DS.$configName;
        Config::load($this->configPath);
    }

    public function choose_service(){
        $service = Adminusers::all(['type'=>0]);
        $data = ['service'=>$service];
        return view("admin@trade/choose_service",['data'=>$data]);
    }

    public function service_trade(Request $request){
        $id = $request->param('id');
        if($id == 0){
            $users = Users::all();
        }else{
            $users = Users::all(['service_cent_id'=>$id]);
        }
        $keys = [];
        foreach($users as $v){
            $keys[] = $v->id;
        }

        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        $delimiter = urlencode(',');
        $keys = implode(',',$keys);
        $tr = Db::table('yzt_trades')
            ->where('user_id','IN',$keys)
            ->where('item_type','IN',0)
            ->select();
        foreach ($tr as &$val){
            $val['item_name'] = Items::where('id',$val['item_id'])->value('name');
            $val['address'] = preg_replace("/$delimiter/",' ',$val['address']);

            if($type == 1){
                $val['trade_status'] = $val['admin_check_type'] == 1?"通过":"未通过";   //管理员审核状态
                $val['admin_get_bill_status'] = $val['admin_get_bill_type'] == 1?"通过":"未通过";
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }

            }else{
                $val['trade_status'] = $val['check_type'] == 1?"通过":"未通过";   //服务中心审核状态
                $val['get_bill_status'] = $val['get_bill_type'] == 1?"通过":"未通过";  //服务中心审核状态
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }
            }
        }
//        echo "<pre>";var_dump($tr);exit;
        $count = count($tr);
        $data = ['type'=>$type,'trades'=>$tr,'count'=>$count];
//        echo "<pre>";var_dump($data['trades']);exit;
        return view("admin@trade/index",['data'=>$data]);
    }

    public function index(){
        $delimiter = urlencode(',');
        $adminId = $_SESSION['adminUserInfo']->id;
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        $users = Users::all(['service_cent_id'=>$adminId]);
        $keys = [];
        foreach($users as $v){
            $keys[] = $v->id;
        }
        $keys = implode(',',$keys);
        $tr = Db::table('yzt_trades')
            ->where('user_id','IN',$keys)
            ->where('item_type',0)
            ->select();
        foreach ($tr as &$val){
            $val['item_name'] = Items::where('id',$val['item_id'])->value('name');
            $val['address'] = preg_replace("/$delimiter/",' ',$val['address']);
            if($type == 1){
                $val['trade_status'] = $val['admin_check_type'] == 1?"通过":"未通过";   //管理员审核状态
                $val['admin_get_bill_status'] = $val['admin_get_bill_type'] == 1?"通过":"未通过";
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }
            }else{
                $val['trade_status'] = $val['check_type'] == 1?"通过":"未通过";   //服务中心审核状态
                $val['get_bill_status'] = $val['get_bill_type'] == 1?"通过":"未通过";  //服务中心审核状态
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }
            }
        }
        $count = count($tr);
//        echo "<pre>";var_dump($tr);exit;
        $data = ['type'=>$type,'trades'=>$tr,'count'=>$count];
        return view("admin@trade/index",['data'=>$data]);
    }

    public function point_trade(){
        $delimiter = urlencode(',');
        $adminId = $_SESSION['adminUserInfo']->id;
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        $users = Users::all(['service_cent_id'=>$adminId]);
        $keys = [];
        foreach($users as $v){
            $keys[] = $v->id;
        }
        $keys = implode(',',$keys);
        $tr = Db::table('yzt_trades')
            ->where('user_id','IN',$keys)
            ->where('item_type',1)
            ->select();
        foreach ($tr as &$val){
            $val['item_name'] = PointItems::where('id',$val['item_id'])->value('name');
            $val['address'] = preg_replace("/$delimiter/",' ',$val['address']);
            if($type == 1){
                $val['trade_status'] = $val['admin_check_type'] == 1?"通过":"未通过";   //管理员审核状态
                $val['admin_get_bill_status'] = $val['admin_get_bill_type'] == 1?"通过":"未通过";
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }
            }else{
                $val['trade_status'] = $val['check_type'] == 1?"通过":"未通过";   //服务中心审核状态
                $val['get_bill_status'] = $val['get_bill_type'] == 1?"通过":"未通过";  //服务中心审核状态
                if($val['trade_type'] == 1){
                    $val['trade_type_status'] = '已发货';
                }elseif($val['trade_type'] == 2){
                    $val['trade_type_status'] = '已完成';
                }else{
                    $val['trade_type_status'] = '未发货';
                }
            }
        }
        $count = count($tr);
//        echo "<pre>";var_dump($tr);exit;
        $data = ['type'=>$type,'trades'=>$tr,'count'=>$count];
        return view("admin@trade/pointTrade",['data'=>$data]);
    }

    public function trade_del_by_id(Request $request){
        $id = $request->param('id');
        $trade = Trades::get($id);
        $re = $trade->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function trade_del_all(Request $request){
        $ids = $request->param()['ids'];
        $trades = Trades::all($ids);
        $flag = false;
        foreach ($trades as $v){
            if($v->getData('trade_type') != 0){
                $flag = true;
                break;
            }
        }
        if($flag){
            $msg = array('status'=>'fails');
            echo json_encode($msg);exit;
        }
//        echo "<pre>";var_dump($trades);exit;
        $re = Trades::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function bill_send_more(Request $request){
        $setPointCount = config('setPointCount');
        $arr = $request->param();
        $post = $arr['arr'];
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        foreach($post as $k=>$v){
            $trade = Trades::get($v);
            if($type){
                if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 0){
                    $msg = array('status'=>'fails','msg'=>'服务中心未确认，状态无法改变!');
                    return json_encode($msg);
                }
                if($trade->getData('admin_check_type') == 0){
                    $msg = array('status'=>'fails','msg'=>'订单未确认，状态无法改变!');
                    return json_encode($msg);
                }
                if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 1){
                    $trade->admin_get_bill_type = 1;
                    $giveDiscount['user_id'] = $trade->user_id;
                    $giveDiscount['count'] = $trade->buy_price*$setPointCount*0.01;
                    $giveDiscount['type'] = 1;
                    $giveDiscount['get_type'] = 0;
                    $giveDiscount['frozen_flag'] = 0;
                    $giveDiscount['create_time'] = date('Y-m-d H:i:s',time());
                    $giveDiscount['trade_number'] = $trade->trade_number;
                    $pointObj = new Points();
                    $pointObj->data($giveDiscount);
                    $pointObj->save();
                    unset($giveDiscount);
//                Points::get(['trade_number' => $trade_number]);
//                $pointObj->addPointByBuy($giveDiscount);
                }else{
                    $pObj = new Points();
                    $obj = $pObj->where('user_id', $trade->user_id)
                        ->where('trade_number', $trade->trade_number)
                        ->where('count', $trade->buy_price*$setPointCount*0.01)
                        ->limit(1)
                        ->select();
                    $obj[0]->delete();
                    $trade->admin_get_bill_type = 0;
                }
            }else{
                if($trade->getData('get_bill_type') == 0 && $trade->getData('check_type') == 0){
                    $msg = array('status'=>'fails','msg'=>'订单未确认，状态无法改变!');
                    return json_encode($msg);
                }
                if($trade->getData('get_bill_type') == 1 && ($trade->getData('admin_get_bill_type') == 1 || $trade->getData('admin_check_type') == 1)){
                    $msg = array('status'=>'fails','msg'=>'总管理已审核，状态无法改变!');
                    return json_encode($msg);
                }
                if($trade->getData('get_bill_type') == 0){
                    $trade->get_bill_type = 1;
                }else{
                    $trade->get_bill_type = 0;
                }
            }
            $result = $trade->save();
            if(!$result){
                $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                return json_encode($msg);
            }
        }
        $msg = array('status'=>'Success');
        return json_encode($msg);
//        echo "<pre>";var_dump($post);exit;


    }

    public function billSend(Request $request){
        $setPointCount = config('setPointCount');
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        if($type){
            if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 0){
                $msg = array('status'=>'fails','msg'=>'服务中心未确认，状态无法改变!');
                return json_encode($msg);
            }
            if($trade->getData('admin_check_type') == 0){
                $msg = array('status'=>'fails','msg'=>'订单未确认，状态无法改变!');
                return json_encode($msg);
            }
            if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 1){
                $trade->admin_get_bill_type = 1;
                $giveDiscount['user_id'] = $trade->user_id;
                $giveDiscount['count'] = $trade->buy_price*$setPointCount*0.01;
                $giveDiscount['type'] = 1;
                $giveDiscount['get_type'] = 0;
                $giveDiscount['frozen_flag'] = 0;
                $giveDiscount['create_time'] = date('Y-m-d H:i:s',time());
                $giveDiscount['trade_number'] = $trade->trade_number;
                $pointObj = new Points();
                $pointObj->data($giveDiscount);
                $pointObj->save();
//                Points::get(['trade_number' => $trade_number]);
//                $pointObj->addPointByBuy($giveDiscount);
            }else{
                $pObj = new Points();
                $obj = $pObj->where('user_id', $trade->user_id)
                    ->where('trade_number', $trade->trade_number)
                    ->where('count', $trade->buy_price*$setPointCount*0.01)
                    ->limit(1)
                    ->select();
                $obj[0]->delete();
                $trade->admin_get_bill_type = 0;
            }
        }else{
            if($trade->getData('get_bill_type') == 0 && $trade->getData('check_type') == 0){
                $msg = array('status'=>'fails','msg'=>'订单未确认，状态无法改变!');
                return json_encode($msg);
            }
            if($trade->getData('get_bill_type') == 1 && ($trade->getData('admin_get_bill_type') == 1 || $trade->getData('admin_check_type') == 1)){
                $msg = array('status'=>'fails','msg'=>'总管理已审核，状态无法改变!');
                return json_encode($msg);
            }
            if($trade->getData('get_bill_type') == 0){
                $trade->get_bill_type = 1;
            }else{
                $trade->get_bill_type = 0;
            }
        }
        $result = $trade->save();
        if($result){
            $msg = array('status'=>'Success');
            return json_encode($msg);
        }else{
            $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
            return json_encode($msg);
        }

    }

    public function send_more(Request $request){
        $arr = $request->param();
        $post = $arr['arr'];
        $sharePointFilterConfig = config('sharePointFilterConfig');
        $setPointCount = config('setPointCount');
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        $pO = new \app\index\model\Points();
        foreach($post as $k=>$v){
            $trade = Trades::get($v);
            if($trade->getData('trade_type') != 0 && $type !=1){
                $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                return json_encode($msg);
                continue;
            }
            $memberId = $trade->user_id;
            $memObj = Users::get($memberId);
            $shareId = $memObj->share_member_id;
            $parnetFlag = false;
            if($shareId != 0){
//                $shareBuyPrice = Trades::where(['user_id'=>$shareId,'check_type'=>1])->sum('buy_price');
                $shareBuyPriceAdd = $pO->where('user_id',"=",$shareId)
//                ->where('get_type',">",0)
                    ->where('type',"=",1)
                    ->where('frozen_flag',"=",0)
                    ->sum('count');
                $shareBuyPriceDel = $pO->where('user_id',"=",$shareId)
//                ->where('get_type',">",0)
                    ->where('type',"=",0)
                    ->where('frozen_flag',"=",0)
                    ->sum('count');
                $shareBuyPrice = $shareBuyPriceAdd - $shareBuyPriceDel;
                if($shareBuyPrice >= $sharePointFilterConfig){
                    $flag = true;
                    //                newAddStart
                    $memParentObj = Users::get($shareId);
                    $shareParentId = $memParentObj->share_member_id;
                    if($shareParentId != 0){
                        $sharePrentBuyPriceAdd = $pO->where('user_id',"=",$shareParentId)
                            ->where('type',"=",1)
                            ->where('frozen_flag',"=",0)
                            ->sum('count');
                        $shareParentBuyPriceDel = $pO->where('user_id',"=",$shareParentId)
                            ->where('type',"=",0)
                            ->where('frozen_flag',"=",0)
                            ->sum('count');
                        $shareParentBuyPrice = $sharePrentBuyPriceAdd - $shareParentBuyPriceDel;
                        if($shareParentBuyPrice >= $sharePointFilterConfig){
                            $parnetFlag = true;
                        }
                    }
//                echo "<pre>";var_dump($shareId);var_dump($shareParentId);exit;
//                new AddEnd
                }else{
                    $flag = false;
                }
            }else{
                $flag = false;
            }

            if($type){
                if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 0){
                    $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                    return json_encode($msg);
                }
                if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 1){
                    $admin_check_type = 1;
                }elseif($trade->getData('admin_check_type') == 1 && $trade->getData('admin_get_bill_type') != 1){
                    $admin_check_type = 0;
                }else{
                    $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                    return json_encode($msg);
                }
                $trade->admin_check_type = $admin_check_type;
            }else{
                if($trade->getData('check_type') == 0){
                    $check_type = 1;
                    $trade_type = 1;
                    if($trade->item_type == 0){
                        if($flag){
                            $giveSharePoint['user_id'] = $shareId;
                            $giveSharePoint['count'] = $trade->buy_price*0.035;
                            $giveSharePoint['type'] = 1;
                            $giveSharePoint['get_type'] = 3;
                            $giveSharePoint['frozen_flag'] = 1;
                            $giveSharePoint['create_time'] = date('Y-m-d H:i:s',time());
                            $pointObj = new Points();
                            $pointObj->data($giveSharePoint);
                            $pointObj->save();
                            unset($giveSharePoint);
                        }

                        if($parnetFlag){
                            $givePrentSharePoint['user_id'] = $shareParentId;
                            $givePrentSharePoint['count'] = $trade->buy_price*0.01;
                            $givePrentSharePoint['type'] = 1;
                            $givePrentSharePoint['get_type'] = 3;
                            $givePrentSharePoint['frozen_flag'] = 1;
                            $givePrentSharePoint['create_time'] = date('Y-m-d H:i:s',time());
                            $pointParentObj = new Points();
                            $pointParentObj->data($givePrentSharePoint);
                            $pointParentObj->save();
                        }
                    }

                }elseif($trade->getData('check_type') == 1 && $trade->getData('admin_check_type') != 1 && $trade->getData('admin_get_bill_type') != 1 && $trade->getData('get_bill_type') != 1 ){
                    $check_type = 0;
                    $trade_type = 0;
                }else{
                    $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                    return json_encode($msg);
                }
                $trade->check_type = $check_type;
                $trade->trade_type = $trade_type;
            }
            $result = $trade->save();
            if($type){
                if($trade->getData('admin_check_type') == 1 && $trade->item_type == 0){

                    $givePoint['user_id'] = $trade->user_id;
                    $givePoint['count'] = $trade->buy_price*$setPointCount*0.01;
                    $givePoint['type'] = 1;
                    $givePoint['get_type'] = 0;
                    $givePoint['frozen_flag'] = 0;
                    $givePoint['create_time'] = date('Y-m-d H:i:s',time());
                    $givePoint['trade_number'] = $trade->trade_number;
                    $pointObj = new Points();
                    $pointObj->data($givePoint);
                    $pointObj->save();
                    unset($givePoint);

                }else{
                    $pObj = new Points();
                    $obj = $pObj->where('user_id', $trade->user_id)
                        ->where('trade_number', $trade->trade_number)
                        ->where('count', $trade->buy_price*$setPointCount*0.01)
                        ->limit(1)
                        ->select();
                    $obj[0]->delete();
                }
            }

        }
        $msg = array('status'=>'Success');
        return json_encode($msg);
    }

    public function send(Request $request){
        $sharePointFilterConfig = config('sharePointFilterConfig');
        $setPointCount = config('setPointCount');
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        $pO = new \app\index\model\Points();
        if($trade->getData('trade_type') != 0 && $type !=1){
            $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
            return json_encode($msg);exit;
        }
        $memberId = $trade->user_id;
        $memObj = Users::get($memberId);
        $shareId = $memObj->share_member_id;
        $parnetFlag = false;
        if($shareId != 0){
//            $shareBuyPrice = Trades::where(['user_id'=>$shareId,'check_type'=>1])->sum('buy_price');
            $shareBuyPriceAdd = $pO->where('user_id',"=",$shareId)
//                ->where('get_type',">",0)
                ->where('type',"=",1)
                ->where('frozen_flag',"=",0)
                ->sum('count');
            $shareBuyPriceDel = $pO->where('user_id',"=",$shareId)
//                ->where('get_type',">",0)
                ->where('type',"=",0)
                ->where('frozen_flag',"=",0)
                ->sum('count');
            $shareBuyPrice = $shareBuyPriceAdd - $shareBuyPriceDel;
            if($shareBuyPrice >= $sharePointFilterConfig){
                $flag = true;
//                newAddStart
                $memParentObj = Users::get($shareId);
                $shareParentId = $memParentObj->share_member_id;
                if($shareParentId != 0){
                    $sharePrentBuyPriceAdd = $pO->where('user_id',"=",$shareParentId)
                        ->where('type',"=",1)
                        ->where('frozen_flag',"=",0)
                        ->sum('count');
                    $shareParentBuyPriceDel = $pO->where('user_id',"=",$shareParentId)
                        ->where('type',"=",0)
                        ->where('frozen_flag',"=",0)
                        ->sum('count');
                    $shareParentBuyPrice = $sharePrentBuyPriceAdd - $shareParentBuyPriceDel;
                    if($shareParentBuyPrice >= $sharePointFilterConfig){
                        $parnetFlag = true;
                    }
                }
//                echo "<pre>";var_dump($parnetFlag);exit;
//                new AddEnd
            }else{
                $flag = false;
            }
        }else{
            $flag = false;
        }

        if($type){
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 0){
                $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                return json_encode($msg);
            }
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 1){
                $admin_check_type = 1;
            }elseif($trade->getData('admin_check_type') == 1 && $trade->getData('admin_get_bill_type') != 1){
                $admin_check_type = 0;
            }else{
                $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                return json_encode($msg);
            }
            $trade->admin_check_type = $admin_check_type;
        }else{

            if($trade->getData('check_type') == 0){
                $check_type = 1;
                $trade_type = 1;
                if($trade->item_type == 0){
                    if($flag){
                        $giveSharePoint['user_id'] = $shareId;
                        $giveSharePoint['count'] = $trade->buy_price*0.035;
                        $giveSharePoint['type'] = 1;
                        $giveSharePoint['get_type'] = 3;
                        $giveSharePoint['frozen_flag'] = 1;
                        $giveSharePoint['create_time'] = date('Y-m-d H:i:s',time());
                        $pointObj = new Points();
                        $pointObj->data($giveSharePoint);
                        $pointObj->save();
                    }

                    if($parnetFlag){
                        $givePrentSharePoint['user_id'] = $shareParentId;
                        $givePrentSharePoint['count'] = $trade->buy_price*0.01;
                        $givePrentSharePoint['type'] = 1;
                        $givePrentSharePoint['get_type'] = 3;
                        $givePrentSharePoint['frozen_flag'] = 1;
                        $givePrentSharePoint['create_time'] = date('Y-m-d H:i:s',time());
                        $pointParentObj = new Points();
                        $pointParentObj->data($givePrentSharePoint);
                        $pointParentObj->save();
                    }
                }
            }elseif($trade->getData('check_type') == 1 && $trade->getData('admin_check_type') != 1 && $trade->getData('admin_get_bill_type') != 1 && $trade->getData('get_bill_type') != 1 ){
                $check_type = 0;
                $trade_type = 0;
            }else{
                $msg = array('status'=>'fails','msg'=>'抱歉，状态无法改变！');
                return json_encode($msg);
            }
            $trade->check_type = $check_type;
            $trade->trade_type = $trade_type;

        }


        $result = $trade->save();

        if($request){
            if($type){
                if($trade->getData('admin_check_type') == 1 && $trade->item_type == 0){

                    $givePoint['user_id'] = $trade->user_id;
                    $givePoint['count'] = $trade->buy_price*$setPointCount*0.01;
                    $givePoint['type'] = 1;
                    $givePoint['get_type'] = 0;
                    $givePoint['frozen_flag'] = 0;
                    $givePoint['create_time'] = date('Y-m-d H:i:s',time());
                    $givePoint['trade_number'] = $trade->trade_number;
                    $pointObj = new Points();
                    $pointObj->data($givePoint);
                    $pointObj->save();

                }else{

                    $pObj = new Points();
                    $obj = $pObj->where('user_id', $trade->user_id)
                        ->where('trade_number', $trade->trade_number)
                        ->where('count', $trade->buy_price*$setPointCount*0.01)
                        ->limit(1)
                        ->select();
                    $obj[0]->delete();
                }
            }
            $msg = array('status'=>'Success');
            return json_encode($msg);
        }else{
            $msg = array('status'=>'fails');
            json_encode($msg);
        }
    }

}