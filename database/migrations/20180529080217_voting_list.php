<?php

use think\migration\Migrator;
use think\migration\db\Column;

class VotingList extends Migrator
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
        $table = $this->table('voting_list',array('engine'=>'MyISAM'));
        $table->addColumn('user_id', 'integer',array('limit' => 32,'default'=>0,'comment'=>'用户ID'))
            ->addColumn('voting_id', 'integer',array('limit' => 32,'default'=>0,'comment'=>'投票ID'))
            ->addColumn('voting_info', 'string',array('limit' => 32,'default'=>0,'comment'=>'投票内容'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->create();
    }
}
