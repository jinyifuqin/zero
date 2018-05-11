<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Weixins extends Migrator
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
        $table = $this->table('weixins',array('engine'=>'MyISAM'));
        $table
            ->addColumn('access_token', 'string',array('default'=>'','comment'=>'微信授权'))
            ->addColumn('access_token_true', 'string',array('default'=>'','comment'=>'微信JStoken'))
            ->addColumn('ticket', 'string',array('default'=>'','comment'=>'ticket'))
            ->addColumn('create_time', 'timestamp',array('default'=>'CURRENT_TIMESTAMP','comment'=>'最后登录时间'))
            ->create();
    }
}
