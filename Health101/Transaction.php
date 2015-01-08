<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 16/05/14
 * Time: 02:32
 */
include('User_Manager.php');
include('Subscription.php');
class Transaction
{
public $amount,$license_owner,$validity,$Transaction_ID,$date,$slip_no,$permit;
    function getPermit()
    {
        return $this->permit;
    }
    function  setPermit($permit)
    {
     $this->permit = $permit;
    }
    function getSlip()
    {
        return $this->slip_no;
    }
    function  setSlip($slip)
    {
    $this->slip_no = $slip;
    }
function setAmount($amount)
    {
       $this->amount = $amount;
    }
    function detAmount()
    {
        $permit = $this->getPermit();
        $query = "SELECT * FROM permits where name  = '$permit'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
         return $$result['0']['fees'];
    }
    function getAmount()
    {
        return $this->amount;
    }
    function getLicense_Owner()
    {
        return $this->license_owner;
    }
    function if_registered($license)
    {
        $query = "SELECT * FROM users WHERE uniqueID = '$license' ";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        if(count($result)>0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function set_license_owner($license)
    {
      if($this->if_registered($license))
      {
          $user = new User_Manager(false,$license);
          $this->license_owner = $user;
          return true;
      }
      else
      {
          return false;
      }
    }
    function get_license_owner()
    {

        return $this->license_owner;
    }


    function setValidity($days)
    {
        if(filter_var(filter_var($days,FILTER_SANITIZE_NUMBER_INT),FILTER_VALIDATE_INT))
        {
            $interval = new DateInterval("P".$days."D");
            $this->validity = $interval;
        }
    }
    function getValidity()
    {
        return $this->validity;
    }
    function detValidity()
    {
        $permit = $this->getPermit();
        $query = "SELECT * FROM permits where name  = '$permit' ";

        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);

        return  $result['0']['time'];
    }
    function setTransactionID($id)
    {

     $this->Transaction_ID = $id;

    }
    function generateID()
    {
        return uniqid("Tr-");
    }
    function getTransaction_id()
    {
        return $this->Transaction_ID;
    }
    function if_in_use($id)
    {
        $query = "SELECT * FROM transactions WHERE trensaction_id = '$id' ";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result  = $link->getQuery(DATABASE,$query);
        return Connect::exists($result," Transaction ID");
    }
    function setDate($date)
    {
        $this->date = $date;
    }
    function getDate()
    {
        return$this->date;
    }

    function subscribe()
    {

        $sub = new Subscription(true,"");
        $sub->setUser($this->get_license_owner()->getUID());
        $sub->setTransaction($this->getTransaction_id());
        $sub->setStart(date("Y-m-d H:i:s"));
        $sub->setValidity($sub->detValidity($this->getPermit()));
        $sub->setEnd($sub->detEnd(true));$sub->setLicenseType($this->getPermit());
         if($sub->subscribe())
         {

             return true;
         }
        else
        {
            return false;
        }

    }

    function inform($id,$amount,$validity)
    {
        $user = new User_Manager(true,"");
        $user->retrieveRecords($id);
        $email = $user->getEmail();
        $phone = $user->getPhone();
        $name = $user->getFname();
        $message = "Dear ".$name.". You have successfully subscribed to parking in the county. You paid ".$amount." to be subscribed for ".$validity." days";
        $head = " Parking Subscription";
        mail($email,$head,$message);
        Transaction::text($phone,$message);
    }
    static  function text($phone,$text)
    {

    }
    function createTransaction()
    {
       $date = $this->getDate();
        $license = $this->get_license_owner()->getUID();
        $transaction_id = $this->getTransaction_id();
        $validity = $this->getValidity()->format('%d');
        $slip = $this->getSlip();
        $amount = $this->getAmount();
        $permit = $this->getPermit();
        $query = "INSERT INTO `transactions` (`transaction_id`, `payslip_no`, `licese_owner`, `amount`, `validity`, `date`, `UID`,`permit`) VALUES ('$transaction_id', '$slip', '$license', '$amount', '$validity', '$date', NULL,'$permit')";
        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {

           if( $this->subscribe())
           {
               return true;
           }
           else
           {
              return false;
           }

        }
        else
        {
            return false;
        }
    }
    function __construct($new,$id = "")
    {
        if($new)
        {
          $this->setTransactionID($this->generateID());
        }
        else
        {
          $this->idTransaction($id);
        }

    }
    function idTransaction($id)
    {
        $query = "SELECT * FROM transactions where transaction_id = '$id'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        $this->setTransactionID($result['0']['transaction_id']);
        $this->setAmount($result['0']['amount']);
        $this->set_license_owner($result['0']['licese_owner']);
        $this->setDate($result['0']['date']);
        $this->setValidity($result['0']['validity']);
        $this->setSlip($result['0']['payslip_no']);

}
} 