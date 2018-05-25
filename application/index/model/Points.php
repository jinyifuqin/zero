<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/5 0005
 * Time: 上午 10:46
 */

namespace app\index\model;
use think\Db;
use think\Model;

class Points extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    public function getGetTypeAttr($value)
    {
        $checktype = [0=>'购买获得',1=>'积分赠送',2=>'积分变现',3=>'推广积分',4=>'委托申请',5=>'提现'];
        return $checktype[$value];
    }

}