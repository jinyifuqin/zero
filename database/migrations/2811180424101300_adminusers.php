<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Adminusers extends Migrator
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
        $table = $this->table('adminusers',array('engine'=>'MyISAM'));
        $table->addColumn('username', 'string',array('limit' => 15,'default'=>'','comment'=>'用户名，登陆使用'))
            ->addColumn('nickname', 'string',array('default'=>'','comment'=>'会员名称'))
            ->addColumn('password', 'string',array('limit' => 32,'default'=>md5('123456'),'comment'=>'用户密码'))
            ->addColumn('login_status', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'登陆状态'))
            ->addColumn('sex', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'性别'))
            ->addColumn('type', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'账号类型0服务中心1超级管理员'))
            ->addColumn('phone_number', 'string',array('default'=>0,'comment'=>'手机号'))
            ->addColumn('email', 'string',array('default'=>'','comment'=>'邮箱'))
            ->addColumn('last_login_ip', 'string',array('default'=>'','comment'=>'最后登录IP'))
            ->addColumn('last_login_time', 'datetime',array('default'=>0,'comment'=>'最后登录时间'))
            ->addIndex(array('username'), array('unique' => true))
            ->addIndex(array('phone_number'), array('unique' => true))
            ->addIndex(array('email'), array('unique' => true))
            ->create();
    }
}
