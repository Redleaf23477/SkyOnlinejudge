<?php namespace SkyOJ\Core;
use SkyOJ\Core\DataBase\DB as DB;

class SKY_ERROR extends \SkyOJ\Helper\Enum
{
    const ERROR_NO = 0;

    const NO_SUCH_METHOD = 1;
    const NO_SUCH_ENUM_VALUE = 2;
    const NO_SUCH_DATA = 3;
    const SQL_OPERATOR_ERROR = 4;
    const UNKNOWN_ERROR = 9999;
}

class CommonObjectError extends \Exception
{
    public function __construct(string $msg,int $code = SKY_ERROR::UNKNOWN_ERROR , Exception $previous = null)
    {
        parent::__construct(SKY_ERROR::str($code).':'.$msg, $code, $previous);
    }
}


//TODO: rename it to SQLBaseObject
/**
 * Abstract that interacts with SQL database
 */
abstract class CommonObject
{
    
    protected static $table;
    protected static $prime_key;
    protected $sqldata = [];
    protected $lock = false;

    /**
     * Get database data with given index
     * @param string $name
     *  Name of index
     * @return any
     *  Data stored in database
     */
    public function __get(string $name)
    {
        return $this->sqldata[$name];
    }

    /**
     * Check whether database data has an index
     * @param string $name
     *  Name of index to be checked
     * @return bool
     *  Exist or not
     */
    public function __isset(string $name)
    {
        return isset($this->sqldata[$name]);
    }

    /**
     * Set value of database data with specific index
     * @param string $name
     *  Name of index
     * @param any $var
     *  Value to set
     * @return null
     */
    public function __set(string $name,$var):void
    {
        $called = "checkSet_".$name;
        if( !method_exists($this,$called) )
        {
            trigger_error("Set {$name} without Check is danger!");
        }
        else if ( !$this->$called($var) )
        {
            throw new CommonObjectError($called,SKY_ERROR::UNKNOWN_ERROR);
        }
        if( $this->sqldata[$name]!==$var )
            $this->UpdateSQLLazy($name,$var);
        $this->sqldata[$name] = $var;
    }
    
    /**
     * Load data from database table.  
     * Store it in ram and return.
     * Run callback function if there is one.
     * @param int $id
     *  Value of prime key
     * @return bool
     *  Success or not
     */
    public function load(int $id)
    {
        if( !isset(static::$table,static::$prime_key) )
        {
            throw new CommonObjectError($id,SKY_ERROR::UNKNOWN_ERROR);
        }
        $table = DB::tname(static::$table);
        $keyname = static::$prime_key;
        $data = DB::fetchEx("SELECT * FROM `{$table}` WHERE `{$keyname}`=?",$id);
        if( empty($data) ) return false;
        $this->sqldata = $data;
        if( method_exists($this,'afterLoad') )
            return $this->afterLoad();
        return true;
    }

    /**
     * Assign data, store in ram.
     * Run callback function if there is one.
     * @param array $data
     *  Data that is given
     * @return bool
     *  Success or not
     */
    public function loadByData(array $data)
    {
        $this->sqldata = $data;
        if( method_exists($this,'afterLoad') )
            return $this->afterLoad();
        return true;
    }

    /**
     * Load a range of data from database table and return.
     * @param int $id
     *  Value of prime key
     * @return array
     *  Data fetched from database
     */
    function loadRange(int $start,int $end)
    {
        if( !isset(static::$table,static::$prime_key) )
        {
            throw new CommonObjectError($called,SKY_ERROR::UNKNOWN_ERROR);
        }
        $table = DB::tname(static::$table);
        $keyname = static::$prime_key;
        $data = DB::fetchAllEx("SELECT * FROM `{$table}` WHERE `{$keyname}` BETWEEN  ? AND ?",$start,$end);
        $class = get_called_class();
        $res = [];
        foreach( $data as $row )
        {
            $p = new $class(null);
            if( $p->loadByData($row) );
                $res[] = $p;
        }
        return $res;
    }

    /**
     * Load from data from database table and return value of specific column.
     * @param int $id
     *  Value of prime key
     * @param string $col
     *  Name of column
     * @return bool
     *  Data of the column
     */
    static function fetchColByPrimeID(int $id,string $col)
    {
        $table = DB::tname(static::$table);
        $keyname = static::$prime_key;

        return DB::fetchEx("SELECT `$col` FROM `{$table}` WHERE `{$keyname}`=?",$id);
    }

    /**
     * Store ($col, $val) pair temporarily in $host in ram.
     * If $col === null, return data stored in $host and clear $host.
     * @param string $col
     *  Name of column
     * @param any $val
     *  Data of column
     * @return any
     *  null if storing value, data in $host if $col === null
     */
    protected function UpdateSQLLazy(string $col = null,$val = null)
    {
        static $host = [];
        if( $col === null ){
            $back = $host;
            $host = [];
            return $back;
        }
        $this->sqldata[$col] = $val;
        $host[] = [$col,$val];
    }

    /**
     * Store lazy updated data into database.
     * @return bool
     *  Success or not
     */
    public function save():bool
    {
        $table = DB::tname(static::$table);
        $prime_key = static::$prime_key;
        $data = $this->UpdateSQLLazy();

        if( empty($data) )
            return true;

        try{
            DB::$pdo->beginTransaction();

            foreach( $data as $d )
                if( DB::queryEx("UPDATE `{$table}` SET `{$d[0]}`= ? WHERE `{$prime_key}`=?",$d[1],$this->$prime_key) === false )
                    throw DB::$last_exception;
            DB::$pdo->commit();
            return true;
        }catch(\Exception $e){
            DB::$pdo->rollBack();
            #\Log::msg(\Level::Error,"UpdateSQL Transaction rollBack! :",$e->getMessage());
            return false;
        }
    }

    //TODO: implementation me
    private static function ensureTableColumnNameVaild( string $table )
    {
        
    }

    /**
     * Call sql INSERT INTO
     */
    protected static function insertInto( array $insert_data )
    {
        $table = DB::tname(static::$table);

        //mysql syntax : INSERT INTO `table` (`c1`,`c2`...`cn`) VALUES ('a','b','c')
        $insert_into_table = "INSERT INTO `{$table}`";
        $cols = "";
        $vals = "";
        $data = [];

        foreach( $insert_data as $col => $val )
        {
            self::ensureTableColumnNameVaild($col);
            $cols.="`{$col}`,";
            $vals.="?,";
            $data[] = $val;
        }

        $cols = rtrim($cols,",");
        $vals = rtrim($vals,",");

        $res = DB::query( "{$insert_into_table} ({$cols}) VALUES ({$vals})" , $data );

        if( $res === false )
            throw new CommonObjectError("insertInto",SKY_ERROR::SQL_OPERATOR_ERROR);
        
        return DB::lastInsertId(static::$prime_key);
    }
    //static abstract public function create(array $data):int;
}