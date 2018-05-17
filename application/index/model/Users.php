<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/27 0027
 * Time: 下午 4:46
 */

namespace app\index\model;
use think\Model;

class Users extends Model
{
    public function getSexAttr($value)
    {
        $checktype = [0=>'女生',1=>'男生'];
        return $checktype[$value];
    }

}