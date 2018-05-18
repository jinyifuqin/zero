<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Users extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('users',array('engine'=>'MyISAM'));
        $table
            ->addColumn('username', 'string',array('limit' => 64,'default'=>'','comment'=>'用户名，登陆使用'))
            ->addColumn('nickname', 'string',array('default'=>'','comment'=>'昵称'))
            ->addColumn('password', 'string',array('limit' => 32,'default'=>md5('123456'),'comment'=>'用户密码'))
            ->addColumn('openid', 'string',array('default'=>'','comment'=>'OpenId'))
            ->addColumn('pic', 'string',array('default'=>'','comment'=>'头像'))
            ->addColumn('phone_number', 'string',array('default'=>'','comment'=>'手机号'))
            ->addColumn('service_cent_id', 'integer',array('limit' => 15,'default'=>0,'comment'=>'服务中心推广人'))
            ->addColumn('share_member_id', 'integer',array('limit' => 15,'default'=>0,'comment'=>'分享的朋友'))
            ->addColumn('referee_id', 'integer',array('limit' => 15,'default'=>0,'comment'=>'推荐人'))
            ->addColumn('sex', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'性别'))
            ->addColumn('status', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'0普通会员1服务中心'))
            ->addColumn('id_card', 'integer',array('limit' => 18,'default'=>0,'comment'=>'身份证'))
            ->addColumn('address', 'string',array('default'=>'','comment'=>'收货地址'))
            ->addColumn('collections', 'string',array('default'=>0,'comment'=>'收款账号'))
            ->addColumn('login_status', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'登陆状态'))
            ->addColumn('login_code', 'string',array('limit' => 32,'default'=>0,'comment'=>'排他性登陆标识'))
            ->addColumn('last_login_time', 'datetime',array('default'=>0,'comment'=>'最后登录时间'))
            ->addIndex(array('openid'), array('unique' => true))
//            ->addIndex(array('phone_number'), array('unique' => true))
//            ->addIndex(array('id_card'), array('unique' => true))
            ->create();
    }
}
