<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 上午 11:22
 */

namespace app\admin\model;
use think\Model;

class Items extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }
}