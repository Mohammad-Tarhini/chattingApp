<?php
require_once __DIR__ . '/../services/ChatService.php';


class ChatController{

    //send message;
    public function sendMessage(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("the user where");
            exit ;
        }
        $id=$_POST['user_id'];
        $user=self::autho($connect,$id);

        if(!isset($_POST['content']) && (isset($_POST['recieverId'])|| isset($_POST['recieverEmail']))){
            echo ResponseService::error("lack of data");
        }
        if(isset($_POST['reciverId'])){
            $reciverId=$_POST['recieverId'];
        }
        else if( isset($_POST['recieverEmail'])){
            $reciverId=ChatService::GetUserFromEmail($connection,$_POST['recieverEmail']);
            if(!is_int($reciverId)){
                echo ResponseService::erorr($reciverId);
                exit;
            }
        }
       $isInsert= ChatService::sendMessageService( $connection, $id,  $reciverId,$content );
        if(!$isInsert){
            echo ResponseService::erorr($isInsert);
            exit;
        }
        echo ResponseService::success("is correct");
    }

    public receivingNewMessages(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("the user where");
            exit ;
        }
        $id=$_POST['user_id'];
        self::autho($connection,$id);
        $newMessages=ChatService::receivingMessagesService($connection,$id);
        if(is_string($newMessages)){
            echo ResponseService::error($newMessages);
        }
        echo ResponseService::Success($newMessages);
    }
    public readMessage(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("where is the user");
            exit;
        }
        $id=$_POST['user_id'];
        self::autho($connection,$id);
        if(!isset($_post["message_id"])){
            echo ResponseService::error("where the message ");
            exit;
        }
        $messageId=$_POST['message_id'];
        $res=ChatService::readMessageService($connection,$id,$messageId)
        if(is_string($res)){
            echo ResponseService::error($res);
            exit;
        }
        if(!$res){
            echo ResponseService::error($res);
            exit;
        }
        echo ResponseService::error($res);  
    }
    public getAllMessageWithOtherUser(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("where is the user");
            exit;
        }
        $id=$_POST['user_id'];
        self::autho($connection,$id);
        if(!isset($_POST['otherUserEmail'])){
            $otherUserEmail=$_POST['otherUserEmail'];
        }
       $messages= ChatService::getAllMessageWithOtherUser($connection,$id,$otherUserEmail);
       if($messages===null){
        echo ResponseService::error("message is empty");
        exit;
       }
       echo ResponseService::success($messages);
    }

    public getAllMessageForUser(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("where is the user");
            exit;
        }
        $id=$_POST['user_id'];
        self::autho($connection,$id);
       
       $messages= ChatService::getAllMessageForUser($connection,$id);
       if($messages===null){
        echo ResponseService::error("message is empty");
        exit;
       }
       echo ResponseService::success($messages);
    }

    public CatchUpWithAI(){
        global $connection;

        if(!isset($_POST['user_id'])){
            echo ResponseService::error("where is the user");
            exit;
        }
        $id=$_POST['user_id'];
        self::autho($connection,$id);
        
    }



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