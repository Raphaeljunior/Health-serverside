<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 16/05/14
 * Time: 02:43
 */
include('User_Manager.php');

class Subscription
{
public  $user,$transaction,$validity,$start_date,$end_date,$license_type,$valid,$days_valid;
    function  getLicenseType()
    {
        return $this->license_type;
    }
    function setLicenseType($license)
    {
        $this->license_type = $license;
    }

    function setUser($id)
    {
        $user = new User_Manager(false,$id);
        $this->user= $user;
    }
    function getUser()
    {

        return $this->user;

    }
    function  setTransaction($t_ID)
    {
        $this->transaction = $t_ID;
    }
    function getTransacton()
    {

        return $this->transaction;;
    }
    function setValidity($valid)
    {
        $validity = new DateInterval('P'.$valid.'D');
        $this->validity = $validity;
    }
    function getValidity()
    {
        return $this->validity;
    }

    function detValidity($permit)
    {
        $query = "SELECT * FROM permits WHERE name = '$permit'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        return $result['0']['time'];
    }
    function getStart()
    {

        return $this->start_date;
    }
    function  setStart($date)
    {
        $start = date_create($date);
        $this->start_date = $start;
    }
    function getEnd()
    {
      return $this->end_date;
    }
    function setEnd($date)
    {
        $this->end_date = date_create($date);
    }
    function detEnd($string)
    {
        if($string)
        {
            $start = date_create(date("Y-m-d H:i:s"));
            date_add($start,$this->getValidity());
            return date_format($start,"Y-m-d H:i:s");
        }
        else
        {
            $start = $this->getStart();
            date_add($start,$this->getValidity());
            return $start;
        }


    }
    function  getDaysRemaining()
    {



        $start = date_create();

       $interval = $start->diff($end);
       $days =  $interval->format('%R%D');
        echo $days;
        $this->days_valid = intval($days);
        return  $this->days_valid;

    }
    function days()
    {
        $end1 = $this->getEnd();
        $start = new DateTime(date('Y-m-d H:i:s'));
        $end = new DateTime($end1->format('Y-m-d H:i:s'));
        $interval = $start->diff($end);
        $this->days_valid =  intval($interval->format('%R%a days'));
        return $this->days_valid;
    }
    function informSubscription()
    {
        $start = $this->getStart()->format("Y-m-d H:i:s");
        $end = $this->getEnd()->format("Y-m-d H:i:s");
        $mail = $this->getUser()->getEmail();
        $header = $this->license_type." renewal ";
        $message = " Dear ".$this->getUser()->getFname()."\n"." You have successfully acquired a ".$this->license_type." permit/license to operate from ".$start." to ".$end." . Thank you";
       if(mail($mail,$header,$message))
       {
           return true;
       }
       else
       {
           return false;
       }
    }

    function subscribe()
    {
      $valid = $this->getValidity()->format('%D');
        $t_id = $this->getTransacton();
        $start = date_format($this->getStart(),"Y-m-d H:i:s");
        $end = date_format($this->getEnd(),"Y-m-d H:i:s");
        $user = $this->getUser()->getUID();
        $license = $this->getLicenseType();
        $query = "INSERT INTO `subscription` (`transaction`, `validity`, `start`, `end`, `user`, `UID`, `license_type`) VALUES ('$t_id', '$valid', '$start', '$end', '$user', NULL, '$license')";
        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {

         $this->informSubscription();
        return true;
        }
        else
        {
            return false;
        }
      }
    function  is_Valid()
    {

        $days = $this->days();
        if($days <  0 || $days = 0)
        {

            $this->defaulter();
            $this->valid  =  false;
            return false;
        }
        else
        {

            $this->valid  = true;
            return true;
        }


    }
    function  defaulter()
    {
        $user = $this->getUser();
        $id = $user->getUID();
        $trans = $this->getTransacton();
        $tid = $trans->getTransaction_id();

        $query = "DELETE FROM `tax`.`subscription` WHERE `subscription`.`transaction` = '$tid'";

        $query1 = "INSERT INTO `defaulters` (`user`, `transaction`, `RID`) VALUES ('$id', '$tid', NULL)";

        $link = new Connect(SERVER,PASSWORD,USER);
       if($link->postQuery(DATABASE,$query1) )
       {
            $link1 = new Connect(SERVER,PASSWORD,USER);
           $link1->postQuery(DATABASE,$query);
           $this->inform();

       }

    }
    function  inform()
    {
        $mail = $this->getUser()->getEmail();
        $header= " License/Permit Renewal ";
        $message = " Dear ".$this->getUser()->getFname()." \n"." Your ".$this->getLicenseType()." has expired and requires renewal. You are currently being treated as a defaulter";
        if(mail($mail,$header,$message))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function __Construct($new,$tid)
    {
        if($new)
        {

        }
        else
        {
         $this->idSubscription($tid);
        }
    }
    function  idSubscription($tid)
    {

        $query = "SELECT * FROM subscription WHERE transaction  = '$tid'";

        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        if(count($result) == 0)
        {
            throw new Exception("The defaulter above DOES NOT exist in our system");
        }
        $this->setEnd($result['0']['end']);
        $this->setStart($result['0']['start']);
        $this->setLicenseType($result['0']['license_type']);
        $this->setTransaction($result['0']['transaction']);
        $this->setUser($result['0']['user']);
        $this->setValidity($result['0']['validity']);

    }
} 