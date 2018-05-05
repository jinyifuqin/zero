<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28 0028
 * Time: 下午 1:38
 */

namespace app\index\model;
use think\Model;

class Trades extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    public function getCheckTypeAttr($value)
    {
        $checktype = [0=>'未通过',1=>'已通过'];
        return $checktype[$value];
    }

    public function getAdminCheckTypeAttr($value){
        $admincheck = [0=>'管理员未通过',1=>'管理员已通过'];
        return $admincheck[$value];
    }

//    public function items(){
//        return $this->hasOne('app\admin\model\Items','id','item_id');
//    }
//
//    public function addrs(){
//        return $this->hasOne('app\index\model\Addrs','id','address');
//    }
}