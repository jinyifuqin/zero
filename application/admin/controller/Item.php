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
use app\admin\model\Items;
use \think\Controller;
use think\Request;

class Item  extends Controller
{
    public function index(){
        $items = Items::all();
        foreach ($items as $k=>&$v){
            if($v->cat_id){
                $catname = Cats::where('id',$v->cat_id)->column('name');
                $v->cat_name = $catname[0];
            }
            if($v->brand_id){
                $brandname = Brands::where('id',$v->brand_id)->column('name');
                $v->brand_name = $brandname[0];
            }

//            echo "<pre>";var_dump($v);
        }
//exit;
//        exit;
        return view("admin@index/item",['item'=>$items]);
    }

    public function itemAdd(){
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat];
        return view("admin@index/itemAdd",['data'=>$data]);
    }

    public function itemEdit(Request $request){
        $id = $request->param('id');
        $item = Items::get($id);
//        echo "<pre>";var_dump($items);exit;
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat,'item'=>$item];
        $item->pic = preg_replace('/\\\\/','/',$item->pic);
        return view("admin@index/itemEdit",['data'=>$data]);
    }

    public function itemUpdate(Request $request){
        if($request->file('file-2')){
            $file = $request->file('file-2');
            $fileRe = upload($file);
            $pic = htmlspecialchars($fileRe->getSaveName());
            $re = array('re'=>$pic);
            echo json_encode($re);
            return;
        }
        $pic = $request->param('logopath');
        $check = strrpos($pic,'uploads');

        $item = new Items();
        $creatTime = date('Y-m-d H:i:s',time());
        $id = $request->param('id');
        $name = $request->param('name');
        $desc = $request->param('desc');
        $content = $request->param('content');
        $price = $request->param('price');
        $status = $request->param('status');
        $catid = $request->param('cat_id');
        $brandid = $request->param('brand_id');
        $sort = $request->param('sort');
        $re = Items::get(['id' => $id]);
        if($check){
            $pic = $re->pic;
        }

        $result = $item->save(
            ['pic'=>$pic,'name'=>$name,'desc'=>$desc,'sort'=>$sort,'create_time'=>$creatTime,
                'content'=>$content,'price'=>$price,'status'=>$status,'cat_id'=>$catid,'brand_id'=>$brandid],
            ['id'=>$id]
        );

        $re = array('type'=>$result);
        echo json_encode($re);
    }

    public function itemSave(Request $request){
        $data = $request->param();
        unset($data['uploadfile']);
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $file = $request->file('file-2');
        $re = upload($file);
        if($re == null){
            $itemObj = new Items($data);
            $result = $itemObj->save();
            if($result)
                return  redirect('/admin/items');
        }
//        echo "<pre>";var_dump($re);exit;
        if($re->getError() == ''){
            $end = htmlspecialchars($re->getSaveName());
            $data['pic'] = $end;
            $itemObj = new Items($data);
            $result = $itemObj->save();
            if($result)
              return  redirect('/admin/items');
        }


    }

    public function itemDelById(Request $request){
        $id = $request->param("id");
        $item = Items::get($id);
        $re = $item->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);

    }

    public function itemDelAll(Request $request){
//        echo "<pre>";var_dump($request);exit;
        $ids = $request->param()['ids'];
        $re = Items::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
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