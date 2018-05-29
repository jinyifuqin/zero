<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/29 0029
 * Time: 下午 2:52
 */

namespace app\admin\model;
use think\Model;

class Votings extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }

}