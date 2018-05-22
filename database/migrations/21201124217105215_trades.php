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
        $table = $this->table('trades',array('engine'=>'MyISAM'));
        $table->addColumn('trade_number', 'string',array('default'=>"",'comment'=>'订单号'))
            ->addColumn('user_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'用户ID'))
            ->addColumn('name', 'string',array('default'=>'','comment'=>'用户名'))
            ->addColumn('address', 'string',array('null'=>true,'comment'=>'收货地址'))
            ->addColumn('check_type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'服务中心审核状态1通过0未通过'))
            ->addColumn('trade_type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'订单状态0未发货1已发货2完成订单'))
            ->addColumn('admin_check_type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'管理员审核状态1通过0未通过'))
            ->addColumn('get_bill_type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'服务中心确认发票1通过0未通过'))
            ->addColumn('admin_get_bill_type', 'integer',array('limit' => 1,'default'=>0,'comment'=>'管理员审核发票状态1通过0未通过'))
            ->addColumn('item_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'商品ID'))
            ->addColumn('buy_num', 'integer',array('null'=>true,'comment'=>'购买数量'))
            ->addColumn('buy_price', 'string',array('null'=>true,'comment'=>'订单金额'))
            ->addColumn('phone_num', 'string',array('default'=>'','null'=>true,'comment'=>'手机号'))
            ->addColumn('create_time', 'datetime',array('comment'=>'最后登录时间'))
            ->addColumn('update_time', 'datetime',array('comment'=>'更新时间'))
            ->create();
    }
}
