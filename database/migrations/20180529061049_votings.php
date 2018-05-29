<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Votings extends Migrator
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
        $table = $this->table('votings',array('engine'=>'MyISAM'));
        $table->addColumn('point_count', 'integer',array('limit' => 32,'default'=>0,'comment'=>'投票费用'))
            ->addColumn('voting_count', 'integer',array('limit' => 32,'default'=>0,'comment'=>'每位用户最多投票数'))
            ->addColumn('title', 'string',array('default'=>'','comment'=>'标题'))
            ->addColumn('desc', 'string',array('default'=>'','comment'=>'描述'))
            ->addColumn('content', 'string',array('default'=>'','comment'=>'内容'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->create();
    }
}
