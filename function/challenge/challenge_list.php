<?php namespace SKYOJ\Challenge;

if (!defined('IN_SKYOJSYSTEM')) {
    exit('Access denied');
}

require_once $_E['ROOT'].'/function/common/pagelist.php';
use  \SKYOJ\PageList;
function listHandle()
{
    global $SkyOJ,$_E;
    $page = $SkyOJ->UriParam(2)??'1';
    $uid = \SKYOJ\safe_get('uid')??null;
    $pid = \SKYOJ\safe_get('pid')??null;
    $result = \SKYOJ\safe_get('result')??null;

    file_put_contents('/var/www/html/skyoj/data/debug.txt', json_encode($uid).PHP_EOL, FILE_APPEND);
    file_put_contents('/var/www/html/skyoj/data/debug.txt', json_encode($pid).PHP_EOL, FILE_APPEND);
    file_put_contents('/var/www/html/skyoj/data/debug.txt', json_encode($result).PHP_EOL, FILE_APPEND);

    $conds = array();
    if( !empty($uid) ) $conds['uid'] = $uid;
    if( !empty($pid) ) $conds['pid'] = $pid;
    if( !empty($result) ) $conds['result'] = $result;

    if( !preg_match('/^[1-9][0-9]*$/',$page) )
        $page = '1';

    $pl = new PageList('challenge');
    $allpage = $pl->all();

    $relpage = $allpage - $page + 1;

    //$data = $pl->GetPageDataByPage($page,'cid','*','DESC');
    // $data = \SkyOJ\Challenge\Container::loadRange( ($relpage-1)*PageList::ROW_PER_PAGE , $relpage*PageList::ROW_PER_PAGE-1 );
    $data = \SkyOJ\Challenge\Container::loadRangeCond( ($relpage-1)*PageList::ROW_PER_PAGE , $relpage*PageList::ROW_PER_PAGE-1, $conds );

    //LOG::msg(Level::Debug, '', $data);
    $_E['template']['challenge_list_pagelist'] = $pl;
    $_E['template']['challenge_list_now'] = $page;
    $_E['template']['challenge_info'] = $data ? $data : [];

    \Render::render('challenge_list', 'challenge');
}
