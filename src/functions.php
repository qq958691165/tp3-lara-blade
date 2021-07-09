<?php
if (!function_exists('v')){
    function v($str,$filter=true){
        if ($filter){
            return htmlentities($str);
        }
        return $str;
    }
}