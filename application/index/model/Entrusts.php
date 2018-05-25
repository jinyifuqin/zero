<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24 0024
 * Time: 下午 6:32
 */

namespace app\index\model;
use think\Model;

class Entrusts extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    public function getTypeAttr($value)
    {
        $checktype = [0=>'未接受',1=>'已接受',2=>'已拒绝'];
        return $checktype[$value];
    }

    public function users()
    {
        return $this->hasOne('Users','id','user_id');
    }

}