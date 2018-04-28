<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Trades extends Migrator
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
        $table = $this->table('trades');
        $table->addColumn('trade_number', 'integer',array('limit' => 32,'default'=>0,'comment'=>'订单号'))
            ->addColumn('user_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'用户ID'))
            ->addColumn('name', 'string',array('limit' => 32,'default'=>'','comment'=>'用户名'))
            ->addColumn('address', 'integer',array('default'=>0,'null'=>true,'comment'=>'收货地址ID'))
            ->addColumn('type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'类型'))
            ->addColumn('item_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'商品ID'))
            ->addColumn('buy_num', 'integer',array('default'=>1,'null'=>true,'comment'=>'购买数量'))
            ->addColumn('phone_num', 'integer',array('default'=>0,'null'=>true,'comment'=>'手机号'))
            ->addColumn('create_time', 'datetime',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->addForeignKey ('user_id' , 'users' , 'id' , [ 'delete' =>  'SET_NULL' , 'update' =>  'NO_ACTION' ])
            ->create();
    }
}
