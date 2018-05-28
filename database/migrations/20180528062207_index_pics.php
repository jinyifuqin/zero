<?php

use think\migration\Migrator;
use think\migration\db\Column;

class IndexPics extends Migrator
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
        $table = $this->table('index_pics',array('engine'=>'MyISAM'));
        $table->addColumn('sort', 'integer',array('limit' => 32,'default'=>0,'comment'=>'排序'))
            ->addColumn('pic', 'string',array('limit' => 64,'default'=>'','comment'=>'LOGO'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'创建时间'))
            ->addColumn('update_time', 'timestamp',array('comment'=>'更新时间'))
            ->create();
    }
}
