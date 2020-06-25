<?php namespace SkyOJ\File;

class Path
{
    const DIR_SPILT_CHAR = '/';

    /**
     * Stores a path in Linux style
     */
    private static $base;

    /**
     * Turn Windows sytle path to Linux style path if needed.
     * Stores it in $base.
     * @param string $s
     *  Path to be converted
     * @return null
     */
    static function initialize(string $s)
    {
        if( substr($s,-1)!==self::DIR_SPILT_CHAR )
        {
            if( substr($s,-1)=== '\\' ) #On Windows
                $s = rtrim($s,"\\");
            $s.= self::DIR_SPILT_CHAR;
        }
        Path::$base = $s;
    }

    /**
     * get base
     * @return string
     */
    static public function base():string
    {
        return Path::$base;
    }

    static public function idhash(int $id)
    {
        $hex = strtoupper( str_repeat("0",8).dechex($id) );
        $hex = substr($hex,-8);
        return $hex;
    }

    static public function id2folder(int $id):string
    {
        $path = implode(self::DIR_SPILT_CHAR,str_split(self::idhash($id),2));
        return $path.self::DIR_SPILT_CHAR;
    }
}

class PathException extends \Exception {};