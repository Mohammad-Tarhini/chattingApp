<?php
require_once __DIR__ . '/../services/UserService.php';
require_once __DIR__ .'/../services/ResponseService.php';
class UserController{


    public getUserInfo(){
        global $connection;

        if(!isset($_GET['user_id'])){
            echo ResponseService::error("the user where")
            exit ;
        }
        $id=$_GET['user_id'];
        $user=self::autho($connect,$id);
        echo ResponseService::success($user);
    }

    public updateUserInfo(){
        global $connection;
        if(!isset($_POST['user_id'])){
            echo ResponseService::error("where the user");
            exit;
        }
        if(!isset($_POST['name'],$_POST['password'],$_POST['email'])){
            echo ResponseService::error("no data to update");
            exit;
        }
        $id=$_POST['user_id'];
        $user=self::autho($connection,$id);
        
        $userUpdateArray=[
            "id"=>$id
            "name"=> !empty($_POST['name']) ? $_POST['name'] : $user->getname(),
            "password"=>!empty($_POST['password']) ? $_POST['password'] : $user->getPassword(),
            "email"=>!empty($_POST['email']) ? $_POST['email'] : $user->getEmail(),
        ];
        $res=UserService::UpdateUserInfoService($connection,$userUpdateArray);
        if($res !=""){
            echo Response::error($res);
            exit;
        }
        echo Response::success("correct ");
    }
    public getAllUsers(){
        global $connection;

        if(!isset($_GET['user_id'])){
            echo ResponseService::error("the user where")
            exit ;
        }
        $id=$_GET['user_id'];
        $res=UserService::GetAllUserService($connection);
        if(!$res){
            echo Response::error("no users");

        }
        echo Response::success($res);



    }
    // public getMySendersTo(){
    //     global $connection;

    //     if(!isset($_GET['user_id'])){
    //         echo ResponseService::error("the user where")
    //         exit ;
    //     }
    //     $id=$_GET['user_id'];
    //     $res=UserService::
    // }
    // public getRecievedFrom(){

    // }
    private static function autho($connection, $id)
    {
        if (empty($id)) {
            echo ResponseService::error("User ID missing");
            exit;
        }

        $user = Middleware::Authorization($connection, $id);

        if (empty($user)) {
            echo ResponseService::error("Unauthorized user");
            exit;
        }
        if(!$user){
            echo ResponseService::error("Unauthorized user");
            exit;       
        }
        return $user;

    }

}




?>