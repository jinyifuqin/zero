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

}