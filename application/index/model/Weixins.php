<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/11 0011
 * Time: 下午 2:33
 */

namespace app\index\model;
use think\Db;
use think\Model;

class Weixins extends Model
{
    public function getCreateTimeAttr($time)
    {
        return strtotime($time);
    }
}