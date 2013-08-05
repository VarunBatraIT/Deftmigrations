<?php

class m130805_222308_DEFT_table_deft extends CDbMigration
{
	public function up()
	{
	
$i=0;
$sql = 'SET foreign_key_checks = 0';
Yii::app()->db->createCommand($sql)->execute();
$sql = 'SET foreign_key_checks = 1';
Try{

/* Dumpling Structure for table_deft*/

$i++;
$toInsert[$i]['id'] = '1'; 
$toInsert[$i]['id2'] = '1'; 
$toInsert[$i]['text'] = 'text1'; 

$i++;
$toInsert[$i]['id'] = '1'; 
$toInsert[$i]['id2'] = '2'; 
$toInsert[$i]['text'] = 'text2'; 

foreach($toInsert as $insertRow){ 
$this->insert('table_deft', $insertRow);

}


unset($toInsert);
        
}Catch(Exception $e){
Yii::app()->db->createCommand($sql)->execute();
echo 'Sorry got some error';
return false;
}
Yii::app()->db->createCommand($sql)->execute();


	}

	public function down()
	{
		echo "m130805_222308_DEFT_table_deft does not support migration down.\n";
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