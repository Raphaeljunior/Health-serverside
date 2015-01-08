<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 17/05/14
 * Time: 09:34
 */
include('User_Manager.php');
$id = $_GET['user'];
$pass = $_GET['password'];
$user = new User_Manager(false,$id);
if($user->login($pass))
{
    echo 1;
}
else
{
    echo 0;
}
