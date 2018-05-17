<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Addrs extends Migrator
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
        $table = $this->table('addrs',array('engine'=>'MyISAM'));
        $table->addColumn('user_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'用户ID'))
            ->addColumn('name', 'string',array('default'=>'','comment'=>'收货人名字'))
            ->addColumn('desc', 'string',array('default'=>'','comment'=>'收货地址'))
            ->addColumn('detail_desc', 'string',array('default'=>'','comment'=>'收货详细地址'))
            ->addColumn('phone_num', 'string',array('default'=>'','null'=>true,'comment'=>'手机号'))
            ->addColumn('default', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'默认标识'))
            ->create();
    }
}
