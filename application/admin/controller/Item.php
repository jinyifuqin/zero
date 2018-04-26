<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/26 0026
 * Time: 上午 11:09
 */

namespace app\admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\Brands;
use app\admin\model\Cats;
use \think\Controller;
use think\Request;

class Item  extends Controller
{
    public function index(){
        return view("admin@index/item");
    }

    public function itemAdd(){

        return view("admin@index/itemAdd");
    }

    public function catList(){
        return view("admin@index/catList");
    }

    public function addCat(){
        return view("admin@index/catAdd");
    }

    public function saveCat(Request $request){
        $catsObj = new Cats([
            'name'  =>  $request->param('name'),
        ]);
        $result = $catsObj->save();
        if($result){
            $msg = array('status'=>'Success');
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($request->param());exit;
    }

}