<?php
require_once __DIR__ . '/../models/User.php';


class UserService{

    public static function UpdateUserInfoService(mysqli $connection, $userupdate){
        $user=new User($userupdate);
       if(! $user->update($connection)){
        return "error on update";
       }
       else{
        return "";
       }
       
    }
    public static function GetAllUserService(mysqli $connection){
       $users=User::findAll($connection);
       if(empty($user)){
        return false;
       }
       return $user;
    }
    // public static function GetSendersToService(mysqli $connection ,int $userId){

    //     if(empty($userId)){
    //         return  "empty user Id";
    //     }
    //     $sendersTo=User::
    // }


}








?>