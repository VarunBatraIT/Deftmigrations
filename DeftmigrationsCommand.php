<?php

Yii::import('system.cli.commands.MigrateCommand');

/**
 * Class        DeftmigrationsCommand
 *
 * @description Dump table schema or data depending on functions used. This file is meant to use with Yii 1.1.13+. Currently it only supports MySQL since when I wrote it, I was working on project with MySQL database.
 * usage:
 * @tutorial README.md
 *
 * @author      Varun Batra <codevarun@gmail.com>
 * @copyright   Varun Batra
 * @since       5/8/13
 * @version     0.01
 * @todo Make a multi insert compatible, as of today, Yii doesn't support it.
 * @link  http://varunbatra.com/ Personal Website
 * @link  http://deftinfotech.com/ Company Website
 * @license http://opensource.org/licenses/MIT MIT
 */
class DeftmigrationsCommand extends MigrateCommand
{

    public $table, $database, $fks;
    public $skipTables = array();
    private $autoIncrement;

    public function beforeAction($action, $params)
    {
        if (parent::beforeAction($action, $params)) {
            $this->skipTables[] = $this->migrationTable;
            return true;
        }
    }

    private function getDBName()
    {
        $curdb = explode('=', Yii::app()->db->connectionString);
        return $curdb[2];
    }

    private function getCharacterSet($tableName)
    {
        $curdb = $this->getDBName();
        $results = Yii::app()->db->createCommand('
            SELECT  T.table_collation,CCSA.character_set_name FROM information_schema.`TABLES` T,
            information_schema.`COLLATION_CHARACTER_SET_APPLICABILITY` CCSA
            WHERE CCSA.collation_name = T.table_collation
            AND T.table_schema = "' . $curdb . '"
            AND T.table_name = "' . $tableName . '";
            SHOW CHARACTER SET FOR ' . $curdb . '.' . $tableName
        )->queryRow();
        return $results;
    }

    private function promptCommon($name = false, $tableName = null)
    {

        $All = false;
        if ($name == false) {
            $name = $this->prompt('Can you please at least tell me name of Migration?');
            if ($name == "") {
                echo "\nExiting No valid name is given\n";
                exit(1);
            }
        }
        if ($tableName == null) {
            $All = $this->prompt('Ok so you didn\'t give me table name, should I consider all tables. It may eat up your memory? yes/no ', 'no');
            $All = trim(strtolower($All));
            if ($All != 'no' && $All != 'yes') {
                echo "\nExiting No valid answer is given\n";
                exit(1);
            }
        }

        if ($All == 'no') {
            $tableName = $this->prompt('Please tell me table name');
        } elseif ($All == 'yes') {
            $tableName = true;
        }
        return array($name, $tableName);
    }

    public function actionTableSchema($name = false, $tableName = null)
    {
        list($name, $tableName) = $this->promptCommon($name, $tableName);
        $tables = array();
        if ($tableName === true) {
            $tables = Yii::app()->db->schema->getTables();
        } else {
            $tables = Yii::app()->db->schema->getTable($tableName);
            if (!$tables) {
                echo "\nSorry table name is invalid\n";
                exit(1);
            }
            $tables = array($tables);
        }

        $this->interactive = false;
        foreach ($tables as $table) {
            if (in_array($table->name, $this->skipTables)) {
                continue;
            }
            $result = $this->getTableStructure($table);
            $this->templateFile = $this->template($result);
            $this->actionCreate(array($name . '_' . $table->name));
        }
        if (count($this->fks) > 0) {
            echo "\n" . 'Waiting for 2 seconds to write Fks' . "\n";
            sleep(2);
        }
        foreach ($this->fks as $table => $Fks) {
            if ($Fks == '') {
                continue;
            }
            $this->templateFile = $this->template($Fks);
            $this->actionCreate(array($name . '_' . $table . 'Fks'));
        }
    }

    public function actionTableData($name = false, $tableName = null)
    {

        list($name, $tableName) = $this->promptCommon($name, $tableName);

        $AllTables = array();
        $dumpTable = '';

        $tables = Yii::app()->db->schema->getTables();
        foreach ($tables as $table) {
            if (in_array($table->name, $this->skipTables)) {
                continue;
            }
            $AllTables[] = $table->name;
        }
        if ($tableName !== true) {
            if (in_array($tableName, $AllTables)) {
                $AllTables = array();
                $AllTables[] = $tableName;
            }
        }
        $this->interactive = false;
        foreach ($AllTables as $table) {
            $dumpTable = $this->getTableDump($table);
            if ($dumpTable === false) {
                continue;
            }
            $this->templateFile = $this->template($dumpTable);
            $this->actionCreate(array($name . '_' . $table));
        }
    }

    public function actionTableDump($name = false, $tableName = null)
    {
        list($name, $tableName) = $this->promptCommon($name, $tableName);
        $this->actionTableSchema($name, $tableName);
        echo "\n" . 'Sleeping for 2 seconds before getting their Data' . "\n";
        sleep(2);
        $this->actionTableData($name, $tableName);
    }

    private function getColType($col, $findKey = true)
    {
        if ($col->isPrimaryKey && $findKey) {
            return "pk";
        }
        $result = $col->dbType;
        if (!$col->allowNull) {
            $result .= ' NOT NULL';
        }
        if ($col->defaultValue != null) {
            $result .= " DEFAULT '{$col->defaultValue}'";
        }
        if ($col->autoIncrement) {
            $this->autoIncrement = $col->name;
            $result .= " AUTO_INCREMENT";
        }
        return $result;
    }

    private function findConstraints($table)
    {
        if (!isset($this->fks[$table->name])) {
            $this->fks[$table->name] = '';
        }
        $i = 0;
        $fkName = $this->getFkNames($table->name);
        $Constraints = $this->findConstraintsMysql($table);
        foreach ($table->foreignKeys as $key => $value) {
            $onDelete = (isset($Constraints[0][$i]) && $Constraints[0][$i] != '') ? ($Constraints[0][$i]) : NULL;
            $onUpdate = (isset($Constraints[1][$i]) && $Constraints[1][$i] != '') ? ($Constraints[1][$i]) : NULL;
            if ($onDelete == NULL && $onDelete == Null) {
                $this->fks[$table->name] .= '$this->addForeignKey("' . $fkName[$i] . '", "' . $table->name . '", "' . $key . '", "' . $value[0] . '", "' . $value[1] . '");' . "\n";
            } else {
                $this->fks[$table->name] .= '$this->addForeignKey("' . $fkName[$i] . '", "' . $table->name . '", "' . $key . '", "' . $value[0] . '", "' . $value[1] . '","' . $onDelete . '","' . $onUpdate . '");' . "\n";
            }
            $i++;
        }
    }

    private function getFkNames($table)
    {
        return Yii::app()->db->createCommand("SELECT constraint_name FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY' AND table_schema = DATABASE() AND table_name = '$table'")->queryColumn();
    }

    private function findConstraintsMysql($table)
    {
        $onDelete = array();
        $onUpdate = array();
        $j = $i = 0;
        $row = Yii::app()->db->createCommand('SHOW CREATE TABLE ' . $table->rawName)->queryRow();
        $matches = array();
        $regexp = '/FOREIGN KEY\s+\(([^\)]+)\)\s+REFERENCES\s+([^\(^\s]+)\s*\(([^\)]+)\) ON DELETE (.*) ON UPDATE (.*),/mi';
        foreach ($row as $sql) {
            if (preg_match_all($regexp, $sql, $matches, PREG_SET_ORDER))
                break;
        }
        foreach ($matches as $match) {
            $onDelete[$i++] = $match[4];
            $onUpdate[$j++] = $match[5];
        }
        return array($onDelete, $onUpdate);
    }

    private function getTableDump($table)
    {
        $tableDump = "\n" .
            '$i=0;' .
            "\n" .
            '$sql = \'SET foreign_key_checks = 0\';' .
            "\n" .
            'Yii::app()->db->createCommand($sql)->execute();' .
            "\n" .
            '$sql = \'SET foreign_key_checks = 1\';' .
            "\n" .
            "Try{" .
            "\n";

        $data = Yii::app()->db->createCommand()->select()->from($table)->queryAll();
        if (!$data) {
            return false;
        }
        $tableDump .= "\n" . "/* Dumpling Structure for $table*/" . "\n";
        foreach ($data as $dataOne) {
            $tableDump .= "\n" . '$i++;' . "\n";
            foreach ($dataOne as $columns => $value) {
                $value = str_replace("'", "\'", $value);
                $value = str_replace("\\'", "'", $value);
                $tableDump .= '$toInsert[$i][\'' . $columns . '\'] = \'' . $value . '\'; ' . "\n";
            }
        }
        $tableDump .= '
foreach($toInsert as $insertRow){ ' . "\n" .
            '$this->insert(\'' . $table . '\', $insertRow);' . "\n" . '
}' . "\n\n" . '
unset($toInsert);
        ' . "\n" .
            '}Catch(Exception $e){' .
            "\n" .
            'Yii::app()->db->createCommand($sql)->execute();' .
            "\n" .
            'echo \'Sorry got some error\';' .
            "\n" .
            'return false;' .
            "\n" .
            "}" .
            "\n" .
            'Yii::app()->db->createCommand($sql)->execute();' .
            "\n" .
            "\n";

        return $tableDump;
    }

    private function getTableStructure($def)
    {

        $character = $this->getCharacterSet($def->name);
        $result = '';
        //Skipping Migrations
        if ($def->name == 'migration') {
            return false;
        }
        if (is_array($def->primaryKey)) {
            $findKey = false;
        } else {
            $findKey = true;
        }
        if (isset($def->primaryKey)) {
            $findKey = false;
        }
        if (isset($def->primaryKey) && !is_array($def->primaryKey)) {
            $primary_keys = array($def->primaryKey);
        }
        if (isset($def->primaryKey) && is_array($def->primaryKey)) {
            $primary_keys = $def->primaryKey;
        }

        $result .= '$this->createTable("' . $def->name . '", array(' . "\n";
        $this->autoIncrement = false;
        foreach ($def->columns as $col) {
            $result .= '    "' . $col->name . '"=>"' . $this->getColType($col, $findKey) . '",' . "\n";
        }
        if ($findKey === false && is_array($primary_keys)) {
            if ($this->autoIncrement !== false) {
                $primary_keys = array_diff($primary_keys, array($this->autoIncrement));
                array_unshift($primary_keys, $this->autoIncrement);
            }
            $result .= '"PRIMARY KEY (' . implode(',', $primary_keys) . ')"';
        }
        $result .= '), " DEFAULT CHARACTER SET ' . $character['character_set_name'] . ' COLLATE ' . $character['table_collation'] . '");' . "\n\n";
        $this->findConstraints($def);
        return $result;
    }

    private function template($up = "")
    {
        $file = <<<EOD
<?php

class {ClassName} extends CDbMigration
{
	public function up()
	{
	$up
	}

	public function down()
	{
		echo "{ClassName} does not support migration down.\\n";
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
EOD;
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'template.php', $file);
        return 'application.commands.template';
    }

}