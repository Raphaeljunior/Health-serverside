<?php
/**
 * Created by PhpStorm.
 * User: CODING_MOVART
 * Date: 16/05/14
 * Time: 02:29
 */

include('Connect.php');
include ('Constants.php');

class User_Manager {

    public  $email,$fname,$lname,$phoneno,$uid,$rights;
    private $salt,$state,$pasword;
    function setRights($no)
    {
        if(filter_var(filter_var($no,FILTER_SANITIZE_NUMBER_INT),FILTER_VALIDATE_INT))
        {
            $this->rights = $no;
        }

    }
    function getRights()
    {
        return $this->rights;
    }
    function getEmail()
    {
        return $this->email;
    }
    function  setEmail($email)
    {
        if(User_Manager::validate_email($email))
        {
            $this->email = $email;
        }
     }
    function if_exists($unique)
    {
        $query = "SELECT * FROM users WHERE uniqueID = '$unique'";
        $link = new Connect(SERVER,PASSWORD,USER);

            $link->setDatabase(DATABASE);
            $result = $link->getQuery(DATABASE,$query);

            if(count($result)== 0)

            {

                return true;
            }
            else
            {
                return false;
            }


    }
    static function validate_email($mail)
    {
        if(filter_var(filter_var($mail,FILTER_SANITIZE_EMAIL),FILTER_VALIDATE_EMAIL))
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    function getPassword()
    {
        return $this->pasword;
    }
    function setPassword($pass)
    {


        $hash = $this->hashSSHA($pass);
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"];
        $this->pasword = $encrypted_password;
        $this->salt = $salt;

    }
    public function hashSSHA($password)
    {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
    public function checkhashSSHA($salt, $password)
    {

        $hash = base64_encode(sha1($password . $salt, true) . $salt);

        return $hash;
    }
    static function check_password_strength($pass)
    {
        $u = 0;
        $d = 0;
        $p = 0;
        $l = 0;
        $li = 0;


        if (ctype_upper($pass))
        {
            $u = 1;
        }

        if (ctype_digit($pass))
        {
            $d = 1;
        }

        if (ctype_punct($pass))
        {
            $p = 1;
        }

        if (ctype_lower($pass))
        {
            $l = 1;
        }

        if ((strlen($pass)>8))
        {
            $li = 1;
        }




        $str = compact($u,$d,$p,$l,$li);
        return User_Manager::calculate($str);

    }
    static  function calculate($param)
    {
        $i = 0;
        foreach($param as $key => $value)
        {
            if($value == 1)
            {
                $i = $i +20;
            }
        }
        if ($i > 50)
        {
            return true;
        }
        else
        {
            return false;
        }
    }




    function setFname($fname)
    {
        $this->fname = ucfirst(filter_var($fname,FILTER_SANITIZE_STRING));
    }
    function getFname()
    {
        return $this->fname;
    }
    function setLname($lname)
    {
        $this->lname = ucfirst(filter_var($lname,FILTER_SANITIZE_STRING));
    }
    function getLname()
    {
        return $this->lname;
    }
    function setPhone($phone)
    {
        $this->phoneno = filter_var($phone,FILTER_SANITIZE_NUMBER_INT);
    }
    function getPhone()
    {
        return $this->phoneno;
    }
    function retrievePassword()
    {
        $email = $this->email;

        $query = "SELECT * FROM users WHERE  email = '$email'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        return $result;

    }
    function  recoverPassword()
    {
        $unique = uniqid();
        $header = "Password recovery";
        $message= " Thank you for using our automated password recovery system. Your unique user ID is ".$unique;
        $email = $this->email;

        $query = "INSERT INTO recovery (email, uniqueid, UID) VALUES ('$email', '$unique', NULL)";
        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {
            if(mail($email,$header,$message))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
    }
    function  verifyCode($email,$code)
    {
        if($this->retrieveCode($email)== $code)
        {
            return true;

        }

    }
    private  function retrieveCode($email)
    {
        $query = "SELECT * FROM recovery where email = '$email'";
        $link = new Connect(SERVER,PASSWORD,DATABASE);
        $result = $link->getQuery(DATABASE,$query);
        return $result['uniqueid'];

    }
    function  updatePassword($new)
    {
        $credentials = $this->hashSSHA($new);

        $new1 = $credentials['encrypted'];
        $salt = $credentials['salt'];
        $email = $this->email;

        $query = "UPDATE `users` SET `password` = '$new1', `salt` = '$salt' WHERE `users`.`email` = '$email'";

        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {

            return true;

        }
        else
        {
            return false;
        }

    }
    function login($input)
    {
        $credentials =$this->retrievePassword();
        $encrypted_password = $credentials['0']['password'];
        $salt = $credentials['0']['salt'];
        $hash = $this->checkhashSSHA($salt,$input);
        if($hash == $encrypted_password)
        {
            return true;
        }
        else
        {
           throw new Exception("Invalid username or password");
        }

    }
    function register()
    {
        $id = $this->uid;
        $fname = $this->fname;
        $lname  = $this->lname;
        $email = $this->email;
        $password = $this->pasword;
        $phone = $this->phoneno;
        $rights = $this->rights;
        $salt = $this->salt;

        $query = "INSERT INTO `tax`.`users` (`uniqueID`, `fname`, `lname`, `email`, `password`, `salt`, `phone`, `UID`, `rights`) VALUES ('$id', '$fname', '$lname', '$email', '$password', '$salt', '$phone', NULL, '$rights')";
        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {

            return true;
        }
        else
        {
            return false;
        }

    }

    function __construct($new,$id)
    {
        if($new)
        {
          $this->uid = $this->generateUID();
        }
        else
        {
            $this->identify($id);
        }

    }
    private function identify($id)
    {
        $query = "SELECT *  FROM users WHERE uniqueID = '$id'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $details = $link->getQuery(DATABASE,$query);
        $this->fname = $details['0']['fname'];
        $this->lname = $details['0']['lname'];

        $this->email = $details['0']['email'];
        $this->pasword = $details['0']['password'];
        $this->phoneno = $details['0']['phone'];
        $this->rights= $details['0']['rights'];
        $this->uid = $details['0']['uniqueID'];

    }
    function retrieveRecords($id)
    {
        $query = "SELECT * FROM users WHERE uniqueID = '$id'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $details = $link->getQuery(DATABASE,$query);
        $this->fname = $details['0']['fname'];
        $this->lname = $details['0']['lname'];

        $this->email = $details['0']['email'];
        $this->pasword = $details['0']['password'];
        $this->phoneno = $details['0']['phone'];
        $this->rights= $details['0']['rights'];
        $this->uid = $details['0']['uniqueID'];
    }

    static function retrieveEmail($phone)
    {
        $query = "SELECT * FROM users WHERE phonenumber = '$phone'";
        $link = new Connect(SERVER,PASSWORD,USER);
        $result = $link->getQuery(DATABASE,$query);
        return $result['0']['email'];


    }
    function  getState()
    {
        return $this->state;
    }
    function  setState($state)
    {
        $this->state = $state;
    }
    function  log($uname)
    {
        $query = "CREATE TABLE $uname (meetingID Text)";
        $link = new Connect(SERVER,PASSWORD,USER);
        if($link->postQuery(DATABASE,$query))
        {
            return true;
        }
        else
        {
            return false;
        }



    }
    function setUID($id)
    {
        $this->uid = $id;
    }
    function  getUID()
    {
        return $this->uid;
    }
    function generateUID()
    {
        return uniqid("US-");
    }

}
