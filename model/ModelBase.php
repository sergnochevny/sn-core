<?php

namespace sn\core\model;

use PDO;
use sn\core\App;
use sn\core\exceptions\BeginTransactionException;
use sn\core\exceptions\CommitTransactionException;
use sn\core\exceptions\ExecException;
use sn\core\exceptions\QueryException;
use sn\core\exceptions\RollBackTransactionException;

class ModelBase extends AbstractModel{

    protected static $table;

    public static $filter_exclude_keys = ['scenario', 'reset'];

    /**
     * @return null
     * @throws \Exception
     */
    public static function getFields(){
        $response = null;
        $query = "DESCRIBE " . static::$table;
        $result = static::Query($query);
        if($result) {
            while($row = static::FetchAssoc($result)) {
                $response[$row['Field']] = $row;
            }
        }

        return $response;
    }

    /**
     * @return bool
     * @throws \sn\core\exceptions\BeginTransactionException
     * @throws \PDOException
     */
    public static function BeginTransaction(){
        if(!static::$inTransaction) {
            static::$inTransaction = App::$app->getDBConnection(static::$connection)->BeginTransaction();
            if(!static::$inTransaction) {
                throw new BeginTransactionException(self::Error());
            }
        }

        return static::$inTransaction;
    }

    /**
     * @return bool
     * @throws \sn\core\exceptions\CommitTransactionException
     */
    public static function Commit(){
        $res = !static::$inTransaction;
        if(static::$inTransaction) {
            $res = App::$app->getDBConnection(static::$connection)->Commit();
            if(!$res) {
                throw new CommitTransactionException(self::Error());
            }
            static::$inTransaction = false;
        }

        return $res;
    }

    /**
     * @return bool
     * @throws \sn\core\exceptions\RollBackTransactionException
     */
    public static function RollBack(){
        $res = !static::$inTransaction;
        if(static::$inTransaction) {
            $res = App::$app->getDBConnection(static::$connection)->RollBack();
            if(!$res) {
                throw new RollBackTransactionException(self::Error());
            }
            static::$inTransaction = false;
        }

        return $res;
    }

    /**
     * @param $query
     * @param null $prms
     * @return mixed
     * @throws \sn\core\exceptions\QueryException
     */
    public static function Query($query, $prms = null){
        $res = App::$app->getDBConnection(static::$connection)->Query($query, $prms);

        if(!$res) {
            throw new QueryException(self::Error());
        }

        return $res;
    }

    /**
     * @param $query
     * @return mixed
     * @throws \sn\core\exceptions\ExecException
     */
    public static function Exec($query){
        $res = App::$app->getDBConnection(static::$connection)->Exec($query);

        if(!$res) {
            throw new ExecException(self::Error());
        }

        return $res;
    }

    /**
     * @return mixed
     */
    public static function Error(){
        return App::$app->getDBConnection(static::$connection)->Error();
    }

    /**
     * @return mixed
     */
    public static function LastId(){
        return App::$app->getDBConnection(static::$connection)->LastId();
    }

    /**
     * @param \PDOStatement $from
     * @return null
     */
    public static function FetchAssoc($from){
        return $from ? $from->fetch(PDO::FETCH_ASSOC) : null;
    }

    /**
     * @param \PDOStatement $from
     * @return null
     */
    public static function FetchAssocAll($from){
        return $from ? $from->fetchAll(PDO::FETCH_ASSOC) : null;
    }

    /**
     * @param \PDOStatement $from
     * @param int $result_type
     * @return mixed
     */
    public static function FetchArray($from, $result_type = PDO::FETCH_BOTH){
        return $from ? $from->fetch($result_type) : null;
    }

    /**
     * @param \PDOStatement $from
     * @param int $result_type
     * @return mixed
     */
    public static function FetchArrayAll($from, $result_type = PDO::FETCH_BOTH){
        return $from ? $from->fetchAll($result_type) : null;
    }

    /**
     * @param \PDOStatement $from
     * @return mixed|null
     */
    public static function FetchValue($from){
        return $from ? $from->fetch(PDO::FETCH_COLUMN) : null;
    }

    /**
     * @param \PDOStatement $from
     * @return int
     */
    public static function getNumRows($from){
        return $from ? $from->rowCount() : 0;
    }

    /**
     * @param \PDOStatement $from
     * @return int
     */
    public static function AffectedRows($from){
        return $from ? $from->rowCount() : 0;
    }

    /**
     * @param \PDOStatement $from
     */
    public static function FreeResult($from){
        if($from) $from->closeCursor();
    }
}