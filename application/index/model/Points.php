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
        $checktype = [0=>'购买获得',1=>'赠送获得',2=>'返利'];
        return $checktype[$value];
    }

}