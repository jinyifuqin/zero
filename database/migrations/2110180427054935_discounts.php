<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Discounts extends Migrator
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
        $table = $this->table('discounts',array('engine'=>'MyISAM'));
        $table->addColumn('number', 'string',array('default'=>'','comment'=>'优惠券号码'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'创建时间'))
            ->addColumn('service_cent_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'服务中心ID'))
            ->create();
    }
}
