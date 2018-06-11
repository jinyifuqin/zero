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
use app\admin\model\Articles;
use app\admin\model\Brands;
use app\admin\model\Discounts;
use app\admin\model\Notices;
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
        $file = $request->file('file');
        if($file){
            $re = upload($file);
            $data['pic'] = htmlspecialchars($re->getSaveName());
        }
        if(array_key_exists('id',$post)){
            $data['id'] = $post['id'];
        }
        $data['title'] = $post['name'];
        $data['sort'] = $post['sort'];
        $artObj = new ArticleMenus();
        if(array_key_exists('id',$post)){
            $id = $post['id'];
            $re = $artObj->save($data,['id' => $id]);
        }else{
            $re = $artObj->save($data);
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

    public function article(){
        $arts = Articles::all();
        foreach($arts as &$v){
            $v->menu_name = ArticleMenus::where('id',$v->menu_id)->value('title');
        }
        return view("admin@article/article",['re'=>$arts]);
    }

    public function add_article(){
        $menus = ArticleMenus::all();
        return view("admin@article/addArticle",['data'=>$menus]);
    }

    public function article_save(Request $request){
        $post = $request->param();
        $artObj = new Articles();
        $data['sort'] = $post['sort'];
        $data['title'] = $post['title'];
        $data['menu_id'] = $post['menu_id'];
        $data['author'] = $post['author'];
        $data['status'] = $post['status'];
        $data['content'] = htmlspecialchars($post['editorValue']);
//        echo "<pre>";var_dump($post);exit;
        if(array_key_exists('id',$post)){
            $id = $post['id'];
            $data['id'] = $id;
            $re = $artObj->save($data,['id'=>$id]);
        }else{
            $artObj->data($data);
            $re = $artObj->save();
        }
        if($re){
            $msg = ['status'=>'success','msg'=>'状态更改成功！'];
        }else{
            $msg = ['status'=>'success','msg'=>'状态更改失败！'];
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($data);exit;
    }

    public function article_status($id){
        $artObj = Articles::get($id);
        if($artObj->status == 1){
            $artObj->status = 0;
        }else{
            $artObj->status = 1;
        }
        $re = $artObj->save();
        if($re){
            $msg = ['status'=>'success','msg'=>'状态更改成功！'];
        }else{
            $msg = ['status'=>'success','msg'=>'状态更改失败！'];
        }
        echo json_encode($msg);
//        echo "<pre>";var_dump($id);exit;
    }

    public function article_edit($id){
        $art = Articles::get($id);
        $menus = ArticleMenus::all();
        $art->content = htmlspecialchars_decode($art->content);
        $data = ['info'=>$art,'menu'=>$menus];
        return view("admin@article/articleEdit",['data'=>$data]);
    }

    public function article_del($id){
        $re = Articles::destroy($id);
        if($re){
            $msg = array('status'=>'success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function article_del_all(Request $request){
        $ids = $request->param()['ids'];
        $re = Articles::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function notice(){   //公告
        $arts = Notices::all();
        return view("admin@article/notice",['re'=>$arts]);
    }

    public function add_notice(){   //公告
        return view("admin@article/addNotice");
    }

    public function notice_save(Request $request){
        $post = $request->param();
        $notObj = new Notices();
        $data['sort'] = $post['sort'];
        $data['title'] = $post['title'];
        $data['status'] = $post['status'];
        $data['content'] = htmlspecialchars($post['editorValue']);
//        echo "<pre>";var_dump($post);exit;
        if(array_key_exists('id',$post)){
            $id = $post['id'];
            $data['id'] = $id;
            $re = $notObj->save($data,['id'=>$id]);
        }else{
            $notObj->data($data);
            $re = $notObj->save();
        }
        if($re){
            $msg = ['status'=>'success','msg'=>'状态更改成功！'];
        }else{
            $msg = ['status'=>'success','msg'=>'状态更改失败！'];
        }
        echo json_encode($msg);
    }

    public function notice_edit($id){
        $art = Notices::get($id);
        $art->content = htmlspecialchars_decode($art->content);
        $data = ['info'=>$art];
//                echo "<pre>";var_dump($data);exit;
        return view("admin@article/noticeEdit",['data'=>$data]);
    }

    public function notice_del($id){
        $re = Notices::destroy($id);
        if($re){
            $msg = array('status'=>'success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function notice_del_all(Request $request){
        $ids = $request->param()['ids'];
        $re = Notices::destroy($ids);
        if($re){
            $msg = array('status'=>'Success');
        }else{
            $msg = array('status'=>'fails');
        }
        echo json_encode($msg);
    }

    public function notice_status($id){
        $artObj = Notices::get($id);
        if($artObj->status == 1){
            $artObj->status = 0;
        }else{
            $artObj->status = 1;
        }
        $re = $artObj->save();
        if($re){
            $msg = ['status'=>'success','msg'=>'状态更改成功！'];
        }else{
            $msg = ['status'=>'success','msg'=>'状态更改失败！'];
        }
        echo json_encode($msg);
    }

}