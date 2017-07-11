<?php
namespace GPRU\DB;

use GPRU\Settings;

class Connection
{
    public static function dbh($dbname, $strict = true)
    {
        static $dbh_cache = array();

        $DBINFO = Settings::get('db');
        $dbinfo = @$DBINFO[$dbname];
        if (!$dbinfo) {
            throw new ErrorException("no such db $dbname");
        }

        $cache_key = $dbname.@$strict;
        if (isset($dbh_cache[$cache_key])) {
            return $dbh_cache[$cache_key];
        }

        $dbh = null;
        if ($dbinfo['driver'] == 'mysql') {
            $dbh = self::mysql_dbh($dbinfo, $strict);
        } elseif($dbinfo['driver'] == 'access') {
            $dbh = self::access_dbh($dbinfo, $strict);
        } else {
            throw new ErrorException("invalid database driver '".@$dbinfo['driver']."'");
        }

        if ($dbh == null) {
            throw new ErrorException("Could not create database handler");
        }

        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $dbh->exec("SET NAMES utf8");

        $dbh_cache[$cache_key] = $dbh;

        return $dbh;
    }

    private static function mysql_dbh($dbinfo, $strict) {
        $dbh = new \PDO('mysql:host='.$dbinfo['host'].';port='.$dbinfo['port'].';dbname='.$dbinfo['dbname'].';charset=utf8', $dbinfo['username'], $dbinfo['password']);

        if ($strict) {
            $dbh->exec("SET sql_mode = 'TRADITIONAL'");
        }

        return $dbh;
    }

    private static function access_dbh($dbinfo, $strict) {
        $db_path_ansi = mb_convert_encoding($dbinfo['db_path'], 'cp1251');
        $dbh = new \PDO('odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq='.$db_path_ansi, $dbinfo['username'], $dbinfo['password']);

        return $dbh;
    }

    // проверка существования таблицы
    public function tableExists($tbl,$dbh)
    {
        $arrAllTables=$dbh->query("SHOW TABLES")->fetchAll();
        $table_exists=false;
        foreach ($arrAllTables as $arrOneTable)
        {
            if (in_array($tbl,$arrOneTable)) {$table_exists=true;}
        }
        return($table_exists);
    }

    public function quote_identifier($i) {
        return '`'.str_replace('`', '``', $i).'`';
    }
}
