<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Items extends Migrator
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
        $table  = $this->table('items',array('engine'=>'MyISAM'));
        $table->addColumn('sort', 'integer',array('limit' => 32,'default'=>0,'comment'=>'排序'))
            ->addColumn('pic', 'string',array('limit' => 64,'default'=>'','comment'=>'商品图'))
            ->addColumn('name', 'string',array('limit' => 32,'default'=>'','comment'=>'商品名称'))
            ->addColumn('desc', 'string',array('limit' => 32,'default'=>'','comment'=>'品牌描述'))
            ->addColumn('content', 'string', array('default'=>'','comment'=>'产品详情'))
            ->addColumn('price', 'integer',array('limit' => 32,'default'=>0,'signed'=>false,'comment'=>'商品价格'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'创建时间'))
            ->addColumn('status', 'boolean',array('limit' => 1,'default'=>0,'comment'=>'状态'))
            ->addColumn('cat_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'分类ID'))
            ->addColumn('brand_id', 'integer',array('default'=>0,'null'=>true,'comment'=>'品牌ID'))
            ->addIndex(array('name'), array('unique' => true))
            ->create();
    }



}
