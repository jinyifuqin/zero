<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Articles extends Migrator
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
        $table = $this->table('articles',array('engine'=>'MyISAM'));
        $table->addColumn('content', 'string',array('default'=>'','comment'=>'文章内容'))
            ->addColumn('sort', 'integer',array('limit' => 32,'default'=>0,'comment'=>'排序'))
            ->addColumn('title', 'string',array('limit' => 32,'default'=>'','comment'=>'文章标题'))
            ->addColumn('menu_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'栏目Id'))
            ->addColumn('give_good', 'integer',array('default'=>0,'comment'=>'点赞'))
            ->addColumn('author', 'string',array('default'=>"",'comment'=>'作者'))
            ->addColumn('status', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'发布状态'))//0发布1非发布
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->create();
    }
}
