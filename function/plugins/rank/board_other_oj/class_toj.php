<?php
if(!defined('IN_SKYOJSYSTEM'))
{
  exit('Access denied');
}

class class_toj{
    public $version = '1.0';
    public $name = 'Toj capturer';
	public $description = 'TOJ capturer for test';
	public $copyright = 'test by LFsWang';
	public $pattern = "/^toj[0-9]+$/i";
	private $api = 'http://210.70.137.215/oj/be/api';
	private $useraclist = array();
	

	function install()
	{
	    $tb = DB::tname('ojlist');
	    DB::query("INSERT INTO `tojtest_ojlist`
	            (`id`, `class`, `name`, `description`, `available`) VALUES
	            (NULL,'class_toj','TNFSH Online Judge','TOJ uid',1)");
	    //set SQL
	}
	
	function checkid($id)
	{
	    $id = (string)$id;
	    return preg_match('/^[1-9]+[0-9]*$/',$id);
	}
	
	function post($data)
	{
	    $context['http'] = array (
			'timeout'   => 60,
			'method'	=> 'POST',
			'content'   => http_build_query($data, '', '&'),
		);
		$response = @file_get_contents('http://210.70.137.215/oj/be/api', false, stream_context_create($context));
		return $response;
	}
	
	function back($uid,$query)
	{
	    global $_E;
	    if( DB::loadcache("class_toj_work_$uid") ){
            exit('');
        }
	    $pid = pcntl_fork();
        if ( $pid==-1 ) return ;
        elseif($pid){
            return ;
        }
        else
        {
            DB::putcache("class_toj_work_$uid",'work',86400);
            if( $aclist = $this->post($query) )
            {
                
                $_E['template']['dbg'].="$uid download from toj<br>";
                $this->useraclist[$uid]  = json_decode($aclist)->ac;
                DB::putcache(   "class_toj_uid_$uid",
                                array(time()+rand(120,420),$this->useraclist[$uid]),86400);
                DB::deletecache("class_toj_work_$uid");
            }
            sleep(5);
            exit(0);
        }
	}
	function preprocess($userlist,$problist)
	{
	    global $_E;
	    $query = array('reqtype' => 'AC','acct_id' => 0 );
	    foreach($userlist as $uid)
	    {
	        $rebuild = true;
	        if(!$this->checkid($uid))
	            continue;
	        $query['acct_id'] = $uid;
	        $cache = DB::loadcache("class_toj_uid_$uid");
	        if( $cache !== false )
	        {
	            $_E['template']['dbg'].="$uid load form cache<br>";
	            $this->useraclist[$uid] = $cache[1];
	            if( $cache[0]>time())
	            {
	                $rebuild=false;
	            }
	        }
	        else
	        {
	            $this->useraclist[$uid] = array();
	        }
	        
	        if( $rebuild )
	        {
	            $_E['template']['dbg'].="$uid Build!<br>";
	            $this->back($uid,$query);
	        }
	    }
	}
	
	function query($uid,$pid)
	{
	    $pid = preg_replace('/[^0-9]*/','',$pid);
	    if(in_array($pid,$this->useraclist[$uid]))
	    {
	        return 9;
	    }
	    else
	    {
	        return 0;
	    }
	}
	
	function showname($str)
	{
	    $pid = preg_replace('/[^0-9]*/','',$str);
	    $str="<a style='color:rgb(255,246,157)' href='http://toj.tfcis.org/oj/pro/$pid/' target='_blank'>toj $pid</a>";
	    return $str;
	}
}