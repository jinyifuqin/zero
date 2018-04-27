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
        $list = Cats::all();
        return view("admin@index/catList",['re'=>$list]);
    }

    public function addCat(){
        return view("admin@index/catAdd");
    }

    public function saveCat(Request $request){
        if($request->param('id')){
            $Cats = Cats::get($request->param('id'));
            $Cats->name = $request->param('name');
            $result = $Cats->save();
        }else{
            $data = array(
                'name'  =>  $request->param('name'),
            );
            $catsObj = new Cats($data);
            $result = $catsObj->save();
        }
//        echo "<pre>";var_dump($data);exit;

        if($result){
            $msg = array('status'=>'Success');
            echo json_encode($msg);
        }
//        echo "<pre>";var_dump($request->param());exit;
    }

    public function catEdit(Request $request){
        $id = $request->param('id');
        $re = Cats::get(['id' => $id]);
//        echo "<pre>";var_dump($re);exit;
        return view("admin@index/catEdit",['re'=>$re]);
    }

    public function catDelAll(Request $request){
        $ids = $request->param()['ids'];
        $re = Cats::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function catDelById(Request $request){
        $id = $request->param('id');
        $catInfo = Cats::get($id);
        $re = $catInfo->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

}