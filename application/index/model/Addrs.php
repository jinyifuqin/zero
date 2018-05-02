<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/28 0028
 * Time: 上午 11:25
 */

namespace app\index\model;
use think\Db;
use think\Model;

class Addrs extends Model
{

    public function setDefault($addrId,$userid){
        return Db::table('yzt_addrs')->where('id','<>', $addrId)->where('user_id',$userid)->update(['default' => '0']);
    }

    public function saveAddr($userid,$desc,$default){
        $re = $this->where('user_id', $userid)
            ->find();
        if($re == null){
            $default = 1;
        }
        $this->user_id = $userid;
        $this->desc = $desc;
        $this->default = $default;
        $this->save();
        $addrId = $this->id;
        try{
            if($default){
                $this->setDefault($addrId,$userid);
                $userObj = Users::get($userid);
                $userObj->address = $addrId;
                $result = $userObj->save();
            }else{
                $result = true;
            }

            if(!$result){
                throw new \Exception("保存失败！");
            }
        }
        catch(\Exception $e)
        {
            echo 'Message: ' .$e->getMessage();
        }

        return $result;
//
    }
}