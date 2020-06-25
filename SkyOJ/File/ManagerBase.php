<?php namespace SkyOJ\File;

class ManagerBase
{
    /**
     * directory under Path::base() to store stuffs
     */
    protected $subrootname = '';

    /**
     * get directory string to subroot
     * @return string
     *  Dir of subroot
     */
    function base():string
    {
        return Path::base().$this->subrootname;
    }

    /**
     * create directory under subroot if not exists,
     * @param string $path 
     *  Dir name to be created
     * @return bool
     *  Success or not
     */
    function mkdir($path):bool
    {
        $full = $this->base().$path;
        if( is_dir($full) ) return true;
        return mkdir($full,0777,true);
    }

    /**
     * read content of a file, throw exception if on error
     * @param string $path
     *  File to be read
     * @param bool $blank
     *  Whether the file is okay to not exist
     * @return string
     *  file content
     */
    function read(string $path,bool $blank = true)
    {
        $full = $this->base().$path;
        if( !file_exists($full) )
        {
            if( $blank ) return '';
            throw new ManagerBaseException('No Such File!');
        }
        $data = file_get_contents($full);
        if( $data === false )
            throw new ManagerBaseException('Get File Error!');
        return $data;
    }

    /**
     * write string to file
     * @param string $path
     *  File to be written
     * @param string $data
     *  Content to be written
     * @return bool
     *  Success or not
     */
    function write(string $path,string $data)
    {
        $full = $this->base().$path;
        return file_put_contents($full,$data) !== false;
    }

    /**
     * move a file from $source to $target, 
     * throw exception on error
     * @param string $source
     *  Dir of source file
     * @param string $target
     *  Dir of target file
     * @return null
     */
    function move($source,$target)
    {
        $source = $this->base().$source;
        $target = $this->base().$target;
        if( !rename($source,$target) )
            throw new ManagerBaseException('Move File Error!');
    }

    /**
     * copy a file from $source to $target, 
     * throw exception on error
     * @param string $source
     *  Dir of source file
     * @param string $target
     *  Dir of target file
     * @return null
     */
    function copy($source,$target)
    {
        $source = $this->base().$source;
        $target = $this->base().$target;
        if( !copy($source,$target) )
            throw new ManagerBaseException('Copy File Error!');
    }

    /**
     * copy a file from $source to $target in subroot, 
     * throw exception on error
     * @param string $source
     *  Dir of source file
     * @param string $target
     *  Dir of target file in subroot
     * @return null
     */
    function copyin($source,$target)
    {
        $target = $this->base().$target;
        if( !copy($source,$target) )
            throw new ManagerBaseException('Copy In File Error!');
    }
}

class ManagerBaseException extends \Exception { }
