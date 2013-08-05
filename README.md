Deftmigrations
==============

This reduces manual work of Yii developer by generating migrations containing table schema and/or table data.

You can entirely replace Yii default migration and use this one. In case you don't want to do it, just copy this class in protected\commands folder. It should be available automatically.

##Following commands are available:
```
yiic.bat deftmigrations TableSchema
```

Above command will create migration with Table Schema (definition)

```
yiic.bat deftmigrations TableData
```

Above command will create migration with Table Data

```
yiic.bat deftmigrations TableDump
```

Above command will create migration with Table Schema and Table Data.


##Passing Variables
Although it will prompt for anything is needed but you can pass name of migration (which is used as prefix) and table name in command line like this:

```
yiic.bat deftmigrations TableDump  --name=DEFT --tableName=countries
```

Above command will create migration with Table Schema and Table Data for countries table.


##Batch Command For All Tables

If you don't pass any Table name then it will ask for considring all tables, type 'yes' on prompt. Just use any of following:

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
 I have encountered many problems and eliminate it all like composite key, primary key was default as int(11) and many more. This class should be able to manage most of common databases & table. There is no guarantee that it will work for you.

 To avoid Fks error, I have disabled its' check. In error it only displays that Migration got error and then I have re-enabled check of FK whether or not migration was successful or not.
 To avoid weird sequence causing FKs error, there is a 2 seconds delay.
 
##Migrations Name
 
 Sample migration name when Name is giving as DEFT
 
 m130805_190610_DEFT_cities
 
 m130805_190610_DEFT_countries
 
 Where cities and countries are tables
 
 Thus migration name given is used as prefix with tables, Separate file was important to avoid memory problems during creation. You may still get memory problem during applying migrations.
 
##Important
 Currently for each insert, one query is executing. Yii as of 1.1.13 doesn't support multi-insertion but hopefully next release does. So you might end up many queries running. Well, this is for one time only. 