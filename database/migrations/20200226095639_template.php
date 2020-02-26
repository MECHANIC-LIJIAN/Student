<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Template extends Migrator
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
    public function up()
    {
        $table = $this->table('templates');
        $table->addColumn('ifUseData', 'enum', ['limit' => 1,'default'=>'0','values' => '0,1','null' => false,'comment'=>'是否使用自定义数据,1-使用,0-不使用'])
        ->addColumn('myData', 'string', ['limit' => 5, 'null' => true,'comment'=>'使用的数据集的id'])
        ->save();
    }
}
