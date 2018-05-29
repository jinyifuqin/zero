<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Talks extends Migrator
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
        $table = $this->table('talks',array('engine'=>'MyISAM'));
        $table->addColumn('art_id', 'integer',array('limit' => 32,'default'=>0,'comment'=>'文章ID'))
            ->addColumn('user_id', 'integer',array('limit' => 32,'default'=>0,'comment'=>'用户ID'))
            ->addColumn('user_talk', 'string',array('default'=>'','comment'=>'用户回复'))
            ->addColumn('admin_talk', 'string',array('default'=>'','comment'=>'管理员回复'))
            ->addColumn('give_good', 'integer',array('default'=>0,'comment'=>'点赞'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->create();
    }
}
