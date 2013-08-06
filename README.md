Deftmigrations
==============

Deftmigrations is coded to be used with Yii framework to generate migration files for table structure and or table data in other words, it can dump whole database.
This reduces manual work of Yii developers.
You can entirely replace Yii default migration and use this one. In case you don't want to do it, just copy this class in protected\commands folder. It should be available automatically.

##Following commands are available:
```
yiic.bat deftmigrations TableSchema
```

Above command will create migration with Table Schema/Structure only (definition)

```
yiic.bat deftmigrations TableData
```

Above command will create migration with Table Data only

```
yiic.bat deftmigrations TableDump
```

Above command will create migration with Table Schema and Table Data Both.


##Passing Variables
Although it will prompt for anything which is needed but you can pass name of migration (which is used as prefix) and table name in command line like this:

```
yiic.bat deftmigrations TableDump  --name=DEFT --tableName=countries
```

Above command will create migration with Table Schema and Table Data for countries table.


##Batch Command For All Tables

If you don't pass Table name then it will ask for considering all tables, then just type 'yes' on prompt. Just use any of following:

```
yiic.bat deftmigrations TableSchema
```
```
yiic.bat deftmigrations TableData
```
```
yiic.bat deftmigrations TableDump
```


##Please Note:
 I have encountered many problems and eliminated it all like composite key, primary key was default as int(11), even order of primary keys in case of composite keys and many more. This class should be able to manage most of common databases & tables. Hey! There is no guarantee that it will work for you. If it doesn't work, fix it and make a pull request :P

To avoid Fks error, I have disabled its' check in insertion. In error it only displays that Migration got error and then I have re-enabled check of FK whether or not migration was successful.

To avoid weird sequence causing FKs error, there is a 2 seconds delay same for shifting to data of tables in case of TableDump command.
 
##Migrations Name
 
 Sample migration name when Name is giving as DEFT
 
 m130805_190610_DEFT_cities
 
 m130805_190610_DEFT_countries
 
 Where cities and countries are tables
 
 Thus migration name given is used as prefix with tables, Separate file was important to avoid memory problems during creation. You may still get memory problem during applying migrations.
 
##Important
 Currently for each insert, one query is executing. Yii as of 1.1.13 does not support multi-insertion but hopefully next release does. So you might end up many queries running. Well, this is for one time only. 
