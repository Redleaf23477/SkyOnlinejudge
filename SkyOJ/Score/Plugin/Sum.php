<?php namespace SkyOJ\Score\Plugin;

class Sum extends \SkyOJ\Score\ScoreMode
{
    const VERSION = '1.0';
    const NAME = 'Sum';
    const DESCRIPTION = 'Sum for all AC subtasks';
    const COPYRIGHT = 'redleaf23477';

    public static function patten():string
    {
        // don't care, copied from Average.php
        return "[[S1,D1],[S2,D2]]";
    }
    public static function is_match(string $scoretype):bool
    {
        // don't care, copied from Average.php
        return json_decode($scoretype);
    }
    public static function calculate(string $scoretype, $res)
    {
        $max = 100;
        $sum = 0;

        $json = json_decode($scoretype);

        $task_num = count($json->tasks);

        for($i = 0; $i < $task_num; ++$i)
        {
            if($res->tasks[$i]->result_code == 20)
                $sum += $json->weight[$i];
        }

        return min($sum, $max);
    }
}