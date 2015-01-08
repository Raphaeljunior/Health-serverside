<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 17/05/14
 * Time: 10:51
 */
include "Subscription.php";
$tid = $_GET['license'];
try
{
    $subscription = new Subscription(false,$tid);
    if($subscription->is_Valid())
    {
        echo json_encode($subscription);
    }
    else
    {
        echo "DEFAULTER";
    }
}
catch (Exception $e)
{
     echo $e->getMessage();
}
