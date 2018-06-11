<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/11 0011
 * Time: 上午 10:34
 */

namespace app\admin\model;
use think\Model;

class Notices extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }
}