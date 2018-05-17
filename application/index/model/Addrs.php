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

    public function saveAddr($userid,$postInfo,$default){
        $re = $this->where('user_id', $userid)
            ->find();
        if($re == null){
            $default = 1;
        }

        if(array_key_exists('addrid',$postInfo)){
            $addrId = $postInfo['addrid'];
            if(count($postInfo) == 1){
                $this->save([
                    'default'  => $default,
                ],['id' => $addrId]);
            }else{
                $postInfo['name'] = urlencode(json_encode($postInfo['name']));
                $data = [
                    'default'  => $default,
                    'name'=>$postInfo['name'],
                    'desc'=>$postInfo['desc'],
                    'phone_num'=>$postInfo['phone_num'],
                    'detail_desc'=>$postInfo['detail_desc']
                ];

                $this->save($data,['id' => $addrId]);
            }

        }else{

            $this->data['name'] = urlencode(json_encode($postInfo['name']));
            $this->phone_num = $postInfo['phone_num'];
            $this->user_id = $userid;
            $this->detail_desc = $postInfo['detail_desc'];
            $this->desc = $postInfo['desc'];
            $this->default = $default;
//        echo "<pre>";var_dump($this);exit;
            $this->save();
            $addrId = $this->id;
//            echo "<pre>";var_dump(55);exit;
        }


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