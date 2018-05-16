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
use app\index\model\Points;
use app\index\model\Trades;
use app\index\controller\Point;
use think\Config;
use \think\Controller;
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

    public function index(){
        $trades = Trades::all();
        $count = Trades::count();
        $delimiter = urlencode(',');
//        echo "<pre>";var_dump($count);exit;
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型

//        echo "<pre>";var_dump($type);exit;
        foreach ($trades as $k=>&$v){
            $v['item_name'] = Items::where('id',$v['item_id'])->value('name');
            $v['address'] = preg_replace("/$delimiter/",' ',$v['address']);
            if($type == 1){
                $v['trade_status'] = $v->getData('admin_check_type');   //管理员审核状态
                $v['admin_get_bill_status'] = $v->getData('admin_get_bill_type');
                $v['trade_type_status'] = $v->getData('trade_type');
            }else{
                $v['trade_status'] = $v->getData('check_type');   //服务中心审核状态
                $v['get_bill_status'] = $v->getData('get_bill_type');   //服务中心审核状态
                $v['trade_type_status'] = $v->getData('trade_type');
            }
//            echo "<pre>";var_dump($v['check_type']);exit;
        }
        
        $data = ['type'=>$type,'trades'=>$trades,'count'=>$count];



        return view("admin@trade/index",['data'=>$data]);
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
        $re = Trades::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function billSend(Request $request){
        $setDoublePointCount = config('setDoublePointCount');
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        if($type){
            if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 0){
                $msg = array('status'=>'fails');
                return json_encode($msg);
            }
            if($trade->getData('admin_get_bill_type') == 0 && $trade->getData('get_bill_type') == 1){
                $trade->admin_get_bill_type = 1;
                $giveDiscount['user_id'] = $trade->user_id;
                $giveDiscount['count'] = $setDoublePointCount;
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
                    ->where('count', $setDoublePointCount)
                    ->limit(1)
                    ->select();
                $obj[0]->delete();
                $trade->admin_get_bill_type = 0;
            }
        }else{
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
            $msg = array('status'=>'fails');
            return json_encode($msg);
        }

    }

    public function send(Request $request){
        $setPointCount = config('setPointCount');
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型

        if($type){
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 0){
                $msg = array('status'=>'fails');
                return json_encode($msg);
            }
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 1){
                $admin_check_type = 1;
            }else{
                $admin_check_type = 0;
            }
        }else{
            if($trade->getData('check_type') == 0){
                $check_type = 1;
                $trade_type = 1;
            }else{
                $check_type = 0;
                $trade_type = 0;
            }
            $trade->check_type = $check_type;
            $trade->trade_type = $trade_type;

        }
        $trade->admin_check_type = $admin_check_type;

        $result = $trade->save();
//        echo "<pre>";var_dump(6);exit;
        if($request){
            if($type){
                if($trade->getData('admin_check_type') == 1){

                    $giveDiscount['user_id'] = $trade->user_id;
                    $giveDiscount['count'] = $setPointCount;
                    $giveDiscount['type'] = 1;
                    $giveDiscount['get_type'] = 0;
                    $giveDiscount['frozen_flag'] = 0;
                    $giveDiscount['create_time'] = date('Y-m-d H:i:s',time());
                    $giveDiscount['trade_number'] = $trade->trade_number;
                    $pointObj = new Points();
                    $pointObj->data($giveDiscount);
                    $pointObj->save();

                }else{

                    $pObj = new Points();
                    $obj = $pObj->where('user_id', $trade->user_id)
                        ->where('trade_number', $trade->trade_number)
                        ->where('count', $setPointCount)
                        ->limit(1)
                        ->select();
                    $obj[0]->delete();
                }
            }
//            echo "<pre>";var_dump($giveDiscount);exit;
            $msg = array('status'=>'Success');
            return json_encode($msg);
        }else{
            $msg = array('status'=>'fails');
            json_encode($msg);
        }
//        echo json_encode($msg);
    }

}