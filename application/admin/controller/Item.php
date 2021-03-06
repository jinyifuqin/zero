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
use app\admin\model\PointItems;
use think\Config;
use \think\Controller;
use think\Request;

class Item  extends Controller
{
    public function __construct(Request $request = null)
    {
        session_start();
        $configName = 'config.xml';
        $this->configPath = APP_PATH.'admin'.DS.'config'.DS.$configName;
        Config::load($this->configPath);
        parent::__construct($request);
    }

    protected $beforeActionList = [
        'check_login',
    ];

    public function check_login(){
        $check = array_key_exists('adminUserInfo',$_SESSION);
        if(!$check)
            $this->redirect('/admin/login');
    }

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
        }
        return view("admin@index/item",['item'=>$items]);
    }

    public function point_items(){
        $items = PointItems::all();
        foreach ($items as $k=>&$v){
            if($v->cat_id){
                $catname = Cats::where('id',$v->cat_id)->column('name');
                $v->cat_name = $catname[0];
            }
            if($v->brand_id){
                $brandname = Brands::where('id',$v->brand_id)->column('name');
                $v->brand_name = $brandname[0];
            }
        }
        return view("admin@index/pointItems",['item'=>$items]);
    }

    public function itemAdd(){
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat];
        return view("admin@index/itemAdd",['data'=>$data]);
    }

    public function point_item_add(){
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat];
        return view("admin@index/pointItemAdd",['data'=>$data]);
    }

    public function itemEdit(Request $request){
        $id = $request->param('id');
        $item = Items::get($id);
//        echo "<pre>";var_dump($items);exit;
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat,'item'=>$item];
        $item->content = htmlspecialchars_decode($item->content);
//        echo "<pre>";var_dump($item->content);exit;
        $item->pic = preg_replace('/\\\\/','/',$item->pic);
        return view("admin@index/itemEdit",['data'=>$data]);
    }

    public function point_item_edit(Request $request){
        $id = $request->param('id');
        $item = PointItems::get($id);
//        echo "<pre>";var_dump($items);exit;
        $brand = Brands::all();
        $cat = Cats::all();
        $data = ['brand'=>$brand,'cat'=>$cat,'item'=>$item];
        $item->content = htmlspecialchars_decode($item->content);
//        echo "<pre>";var_dump($item->content);exit;
        $item->pic = preg_replace('/\\\\/','/',$item->pic);
        return view("admin@index/pointItemEdit",['data'=>$data]);
    }

    public function itemUpdate(Request $request){
//        echo "<pre>";var_dump($request->file());exit;
//        echo "<pre>";var_dump($request->param());exit;
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
        $prepareConten = $request->param('content');

//        echo"<pre>";var_dump($content);exit;
        $item = new Items();
        $creatTime = date('Y-m-d H:i:s',time());
        $id = $request->param('id');
        $name = $request->param('name');
        $desc = $request->param('desc');
        $content = htmlspecialchars($prepareConten);
        $price = $request->param('price');
        $a_price = $request->param('a_price');
        $b_price = $request->param('b_price');
        $c_price = $request->param('c_price');
        $d_price = $request->param('d_price');
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
                'content'=>$content,'price'=>$price,
                'a_price'=>$a_price,'b_price'=>$b_price,
                'c_price'=>$c_price,'d_price'=>$d_price,
                'status'=>$status,'cat_id'=>$catid,'brand_id'=>$brandid],
            ['id'=>$id]
        );

        $re = array('type'=>$result);
        echo json_encode($re);
    }

    public function point_item_update(Request $request){
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
        $prepareConten = $request->param('content');

//        echo"<pre>";var_dump($content);exit;
        $item = new PointItems();
        $creatTime = date('Y-m-d H:i:s',time());
        $id = $request->param('id');
        $name = $request->param('name');
        $desc = $request->param('desc');
        $content = htmlspecialchars($prepareConten);
        $price = $request->param('price');
        $status = $request->param('status');
        $catid = $request->param('cat_id');
        $brandid = $request->param('brand_id');
        $sort = $request->param('sort');
        $re = PointItems::get(['id' => $id]);
        if($check){
            $pic = $re->pic;
        }

        $result = $item->save(
            ['pic'=>$pic,'name'=>$name,'desc'=>$desc,'sort'=>$sort,'create_time'=>$creatTime,
                'content'=>$content,'price'=>$price,
                'status'=>$status,'cat_id'=>$catid,'brand_id'=>$brandid],
            ['id'=>$id]
        );

        $re = array('type'=>$result);
        echo json_encode($re);
    }

    public function itemSave(Request $request){
        $data = $request->param();
//        echo"<pre>";var_dump($data);exit;
        unset($data['/admin/itemSave']);
        unset($data['uploadfile']);
        $data['content'] = $data['editorValue'];
        unset($data['editorValue']);
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $file = $request->file('file-2');
        $re = upload($file);

        if($re == null){
            $itemObj = new Items($data);
            $result = $itemObj->save();
            if($result)
                return  redirect('/admin/items');
        }

        if($re->getError() == ''){
            $end = htmlspecialchars($re->getSaveName());
            $data['pic'] = $end;
            $itemObj = new Items($data);
            $result = $itemObj->save();
            if($result)
              return  redirect('/admin/items');
        }


    }

    public function point_item_save(Request $request){
        $data = $request->param();
        unset($data['/admin/pointItemSave']);
        unset($data['uploadfile']);
        $data['content'] = $data['editorValue'];
        unset($data['editorValue']);
        $data['create_time'] = date('Y-m-d H:i:s',time());
        $file = $request->file('file-2');
        $re = upload($file);

        if($re == null){
            $itemObj = new PointItems($data);
            $result = $itemObj->save();
            if($result)
                return  redirect('/admin/pointItems');
        }

        if($re->getError() == ''){
            $end = htmlspecialchars($re->getSaveName());
            $data['pic'] = $end;
            $itemObj = new PointItems($data);
            $result = $itemObj->save();
            if($result)
                return  redirect('/admin/pointItems');
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

    public function point_item_del(Request $request){
        $id = $request->param("id");
        $item = PointItems::get($id);
        $re = $item->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function itemStatus(Request $request){   //商品上下架
        $item = Items::get($request->param('id'));

        $item->status = $item->status == 0?1:0;
        $result = $item->save();
        if($result){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function point_item_status(Request $request){   //商品上下架
        $item = PointItems::get($request->param('id'));

        $item->status = $item->status == 0?1:0;
        $result = $item->save();
        if($result){
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

    public function point_item_delAll(Request $request){
//        echo "<pre>";var_dump($request);exit;
        $ids = $request->param()['ids'];
        $re = PointItems::destroy($ids);
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

    public function item_price(){
        $items = Items::all();
        $list['ALow'] = config('setALow');
        $list['BLow'] = config('setBLow');
        $list['BHeight'] = config('setBHeight');
        $list['CLow'] = config('setCLow');
        $list['CHeight'] = config('setCHeight');
        $list['DLow'] = config('setDLow');
        $list['DHeight'] = config('setDHeight');
        $info = $_SESSION['adminUserInfo'];
        $serverId = $info->id;
        $count = \think\Db::table('yzt_entrusts')
            ->where('service_id',$serverId)
            ->sum('count');

        if($list['DLow'] <= $count && $count <= $list['DHeight']){
            $belong = "D";
        }elseif($list['CLow'] <= $count && $count <= $list['CHeight']){
            $belong = "C";
        }elseif($list['BLow'] <= $count && $count <= $list['BHeight']){
            $belong = "B";
        }elseif($list['ALow'] <= $count){
            $belong = "A";
        }else{
            $belong = "null";
        }
        
        $re = ['items'=>$items,'level'=>$belong];
//        echo "<pre>";var_dump($list);exit;
        return view("admin@index/itemPrice",['re'=>$re]);
    }

}