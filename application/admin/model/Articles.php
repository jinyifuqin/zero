<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/28 0028
 * Time: 下午 4:39
 */

namespace app\admin\model;
use think\Model;

class Articles extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }
}