<?php

use think\migration\Migrator;
use think\migration\db\Column;

class ArticleTypes extends Migrator
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
        $table = $this->table('article_types',array('engine'=>'InnoDB'));
        $table->addColumn('name', 'string',array('limit' => 32,'default'=>0,'comment'=>'类型名称'))
            ->addColumn('create_time', 'datetime',array('default'=>'CURRENT_TIMESTAMP','comment'=>'时间'))
            ->create();
    }
}
