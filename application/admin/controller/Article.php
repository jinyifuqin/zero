<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23 0023
 * Time: 下午 2:16
 */

namespace app\admin\controller;
use app\admin\model\Adminusers;
use app\admin\model\ArticleMenus;
use app\admin\model\Brands;
use app\admin\model\Discounts;
use app\index\model\Addrs;
use app\index\model\Trades;
use app\index\model\Users;
use think\Config;
use \think\Controller;
use think\Db;
use think\db\Query;
use think\Request;

class Article extends Controller
{
    public function article_menu(){
        $list = ArticleMenus::all();
        return view("admin@article/menus",['list'=>$list]);
    }

    public function add_article_menus(){
        return view("admin@article/menusAdd");
    }

    public function save_article_menus(Request $request){
        $post = $request->param();
        $title = $post['name'];
        $sort = $post['sort'];
        $artObj = new ArticleMenus();
        if(array_key_exists('id',$post)){
            $id = $post['id'];
            $artObj->id = $id;
            $re = $artObj->save([
                'title' => $title,
                'sort' => $sort
            ],['id' => $id]);
        }else{
            $artObj->sort = $sort;
            $artObj->title = $title;
            $re = $artObj->save();
        }
//        echo "<pre>";var_dump($post);exit;

        if($re){
            $msg = ['type'=>'success','msg'=>'文章栏目添加成功！'];
            echo json_encode($msg);
        }else{
            $msg = ['type'=>'error','msg'=>'文章栏目添加失败！'];
            echo json_encode($msg);
        }
    }

    public function article_menu_edit(Request $request){
        $id = $request->param('id');
        $info = ArticleMenus::get(['id'=>$id]);
        return view("admin@article/articleMenuEdit",['re'=>$info]);
//        echo "<pre>";var_dump($id);exit;
    }

    public function article_menu_del(Request $request){
        $id = $request->param('id');
        $artObj = ArticleMenus::get($id);
        $re = $artObj->delete();
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function article_menu_del_all(Request $request){
        $ids = $request->param()['ids'];
        $re = ArticleMenus::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

}