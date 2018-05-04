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

    public function getTypeAttr($value)
    {
        $type = [0=>'未发货',1=>'已发货'];
        return $type[$value];
    }

//    public function items(){
//        return $this->hasOne('app\admin\model\Items','id','item_id');
//    }
//
//    public function addrs(){
//        return $this->hasOne('app\index\model\Addrs','id','address');
//    }
}