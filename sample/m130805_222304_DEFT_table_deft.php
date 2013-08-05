<?php

class m130805_222304_DEFT_table_deft extends CDbMigration
{
	public function up()
	{
	$this->createTable("table_deft", array(
    "id"=>"int(2) NOT NULL",
    "id2"=>"int(11) NOT NULL AUTO_INCREMENT",
    "text"=>"varchar(230)",
"PRIMARY KEY (id2,id)"), " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");


	}

	public function down()
	{
		echo "m130805_222304_DEFT_table_deft does not support migration down.\n";
		return false;
	}

	/*
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	}

	public function safeDown()
	{
	}
	*/
}