<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 16/05/14
 * Time: 02:27
 */

class Connect {
    private  $server,$password,$username,$database;

    function  __construct($server,$password,$username)  //constructor
    {
        $this->server = $server;
        $this->password = $password;
        $this->username = $username;
        mysqli_connect($server,$username,$password) or die(mysql_error());
    }

    function setServer($new_server)
    {
        $this->server = $new_server;

    }
    function  getServer()
    {
        return $this->server;

    }
    function getPassword()
    {
        return $this->password;

    }
    function  setPassword($new_password)
    {
        $this->password = $new_password;
    }
    function setUsername($new_username)
    {
        $this->username = $new_username;
    }
    function getUsername()
    {
        return $this->username;
    }
    function setDatabase($new_database)
    {
        $this->database = $new_database;
    }
    function getDatabase()
    {
        return $this->database;
    }
    function postQuery($database,$query)
    {

        $link = mysqli_connect($this->server,$this->username,$this->password);
        mysqli_select_db($link,$database);
        if(mysqli_query($link,$query))
        {

            return true;
        }
        else
        {
            return false;
        }

    }

    function getQuery($database,$query)
    {
        global $array;
        $array = array();
        $link = mysqli_connect($this->server,$this->username,$this->password);
        mysqli_select_db($link,$database);

        $result = mysqli_query($link,$query);
        $i= 0;



        while($array1 = mysqli_fetch_array($result))
        {
           // print_r($array1);
            $array[$i] = $array1;
            $i++;
            //$array =  array_push($array,$array1);
        }

        return $array;

    }
    function recQuery($database,$query)
    {
        $i = 1;
        global $array;
        mysql_select_db($database);
        $result = mysql_query($query);
        while($array1 = mysql_fetch_array($result))
        {
            $i++;
            array_push($array,$i -> $array1);
        }
        return $array;
    }
    static function exists($array,$param)
    {
        if(count($array) > 0)
        {
            return true;
        }
        else
        {
            trigger_error("The following".$param." already in use");
            return false;
        }
    }
} 