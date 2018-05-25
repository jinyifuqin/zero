<?php

use think\migration\Migrator;
use think\migration\db\Column;

class PutForwards extends Migrator
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
        $table = $this->table('put_forwards',array('engine'=>'MyISAM'));
        $table->addColumn('count', 'string',array('default'=>'','comment'=>'金额'))
            ->addColumn('user_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'用户ID'))
            ->addColumn('point_id', 'integer',array('default'=>0,'comment'=>'积分ID'))
            ->addColumn('type', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'0未体现/1已提现'))//0未提现1已提现
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
//            ->addIndex(array('trade_number'), array('unique' => true))
            ->create();
    }
}
