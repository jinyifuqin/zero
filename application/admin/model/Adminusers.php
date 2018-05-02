<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/24 0024
 * Time: 下午 6:16
 */

namespace app\admin\model;
use think\Model;

class Adminusers extends Model
{
    public function getTypeAttr($value)
    {
        $type = [0=>'服务中心账号',1=>'超级管理员'];
        return $type[$value];
    }
}