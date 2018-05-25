<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/25 0025
 * Time: 下午 4:06
 */

namespace app\index\model;
use think\Db;
use think\Model;

class PutForwards extends Model
{
    public function users()
    {
        return $this->hasOne('Users','id','user_id');
    }

    public function getTypeAttr($value)
    {
        $checktype = [0=>'未批准提现',1=>'已批准提现'];
        return $checktype[$value];
    }

}