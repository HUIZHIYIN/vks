<?php

if($_SERVER['SERVER_NAME'] == 'localhost'){
    //local environment

    define('DB_SERVER','localhost');
    define('DB_SERVER_USERNAME','world');
    define('DB_SERVER_PASSWORD','world');
    define('DB_DATABASE','world');

}else{

}


if(!defined('MYSQL_WAIT_TIMEOUT_ERROR_NO1'))
{
    define('MYSQL_WAIT_TIMEOUT_ERROR_NO1', 2013);
}

if(!defined('MYSQL_WAIT_TIMEOUT_ERROR_NO2'))
{
    define('MYSQL_WAIT_TIMEOUT_ERROR_NO2', 2006);
}

if(!defined('TIME_BETWEEN_CONNECT_FAILS_IN_SEC'))
{
    define('TIME_BETWEEN_CONNECT_FAILS_IN_SEC','1');
}

if(!defined('MAX_ATTEMPTS_CONNECT_FAILS'))
{
    define('MAX_ATTEMPTS_CONNECT_FAILS','3');
}


?>