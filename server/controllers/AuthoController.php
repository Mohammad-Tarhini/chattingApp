<?php
require_once __DIR__ . '/../services/AuthoService.php';
require_once __DIR__ .'/../services/ResponseService.php';
require_once __DIR__ .'/../utils/headers.php';
require_once(__DIR__ .'/../connection/connection.php');




class AuthoController{

    public function signUp() {
    
        error_reporting(E_ERROR | E_PARSE);
        $_POST = json_decode(file_get_contents("php://input"), true);

        global $connection;

        // Validate POST data
        if (!isset($_POST['name'], $_POST['email'], $_POST['password'])) {
            echo ResponseService::error("Missing data from client side", 400);
            return;
        }

        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        // Create User object
        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role
        ]);

        // Call signUp service
        $signUpResult = AuthoService::signUp($user, $connection);

        if (is_int($signUpResult)) {
            echo ResponseService::success("saved correct");
        } else {
            echo ResponseService::error($signUpResult, 409);
        }
    }

    function signIn(){
        error_reporting(E_ERROR | E_PARSE);
        $_POST = json_decode(file_get_contents("php://input"), true);
        global $connection ;
        if (!isset($_POST['email'], $_POST['password'])) {
            echo ResponseService::error("Missing email or password", 400);
            return;
        }
        $email = $_POST['email'];
        $password = $_POST['password'];

        $signInResult=AuthoService::signIn($email,$password,$connection);
        if(is_int($signInResult)){
            echo ResponseService::success(["userId" => $signInResult]);
        }else{
            echo ResponseService::error($signInResult,409);
        }

    }

}

?>