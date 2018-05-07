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
use app\index\controller\Point;
use \think\Controller;
use think\Request;

class Trade extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
        parent::__construct($request);
    }

    public function index(){
        $trades = Trades::all();
        $delimiter = urlencode(',');
//        echo "<pre>";var_dump($_SESSION);exit;
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型

//        echo "<pre>";var_dump($type);exit;
        foreach ($trades as $k=>&$v){
            $v['item_name'] = Items::where('id',$v['item_id'])->value('name');
            $v['address'] = preg_replace("/$delimiter/",' ',$v['address']);
            if($type == 1){
                $v['trade_status'] = $v->getData('admin_check_type');   //管理员审核状态
                $v['admin_get_bill_status'] = $v->getData('admin_get_bill_type');
            }else{
                $v['trade_status'] = $v->getData('check_type');   //服务中心审核状态
                $v['get_bill_status'] = $v->getData('get_bill_type');   //服务中心审核状态
            }
//            echo "<pre>";var_dump($v['check_type']);exit;
        }
        
        $data = ['type'=>$type,'trades'=>$trades];



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
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
        if($type){
            if($trade->getData('admin_get_bill_type') == 0){
                $trade->admin_get_bill_type = 1;
            }else{
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
        $id = $request->param('id');
        $trade = Trades::get($id);
        $type = $_SESSION['adminUserInfo']->getData('type'); // 账号类型
//        echo "<pre>";var_dump($type);exit;
        if($type){
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 0){
                $msg = array('status'=>'fails');
                return json_encode($msg);
            }
            if($trade->getData('admin_check_type') == 0 && $trade->getData('check_type') == 1){
                $trade->admin_check_type = 1;
            }else{
                $trade->admin_check_type = 0;
            }
        }else{
            if($trade->getData('check_type') == 0){
                $trade->check_type = 1;
            }else{
                $trade->check_type = 0;
            }
        }

        $result = $trade->save();
//        echo "<pre>";var_dump(6);exit;
        if($request){
//            echo "<pre>";var_dump($trade);exit;
            if($type){
                if($trade->getData('admin_check_type') == 1){
                    $giveDiscount['userId'] = $trade->user_id;
                    $giveDiscount['pointCount'] = $trade->buy_price;
                    $giveDiscount['type'] = 1;
                    $giveDiscount['trade_number'] = $trade->trade_number;
                    $pointObj = new Point();
                    $pointObj->addPointByBuy($giveDiscount);

                }else{
                    $pointObj = new Point();
                    $pointObj->delPointByTradeId($trade->trade_number);
                }
            }
            $msg = array('status'=>'Success');
            return json_encode($msg);
        }else{
            $msg = array('status'=>'fails');
            json_encode($msg);
        }
//        echo json_encode($msg);
    }

}