<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/15 0015
 * Time: 下午 3:45
 */

namespace app\index\controller;
use app\weixin\controller\Wechat;
use think\Controller;

class Common extends Controller
{
    protected function _initialize(){
        session_start();
        $this->wxObj = new Wechat();
//        $assign = ['a'=>'hello','b'=>'world'];
//        echo "<pre>";var_dump($assign);exit;
//        $this->assign($assign);
    }
}