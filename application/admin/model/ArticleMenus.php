<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/23 0023
 * Time: 下午 2:42
 */

namespace app\admin\model;
use think\Model;

class ArticleMenus extends Model
{
    public function getCreateTimeAttr($time)
    {
        return $time;
    }
}