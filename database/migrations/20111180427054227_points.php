<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Points extends Migrator
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
        $table = $this->table('points',array('engine'=>'MyISAM'));
        $table->addColumn('count', 'string',array('default'=>'','comment'=>'积分数'))
            ->addColumn('user_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'用户ID'))
            ->addColumn('future_count', 'integer',array('limit' => 32,'default'=>0,'comment'=>'理应获得积分数'))
            ->addColumn('trade_number', 'string',array('default'=>"",'comment'=>'订单号'))
            ->addColumn('type', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'类型add/del'))
            ->addColumn('get_type', 'integer',array('limit' => 1,'default'=>0,'null'=>true,'comment'=>'获取方式')) //0购买获取积分，1赠送获取积分
            ->addColumn('frozen_flag', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'冻结标识'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->addIndex(array('trade_number'), array('unique' => true))
            ->create();
    }
}
