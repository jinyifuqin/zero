<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/4 0004
 * Time: 下午 3:43
 */

namespace app\admin\model;
use think\Model;

class Discounts extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

    public function adminusers()
    {
        return $this->hasOne('adminusers','id','service_cent_id');
    }

}