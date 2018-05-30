<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/30 0030
 * Time: 下午 4:32
 */

namespace app\index\model;
use think\Model;

class GiveGoods extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }
}