<?php

require_once __DIR__ . '/../services/ChatService.php';
require_once __DIR__ . '/../services/ResponseService.php';
require_once __DIR__ . '/../middleware.php'; // if you have it
require_once __DIR__ .'/../utils/headers.php';
require_once(__DIR__ .'/../connection/connection.php');



class ChatController
{
    // send message
    public function sendMessage()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        $user = self::autho($connection, $id);

        // require content and either recieverId or recieverEmail
        if (!isset($_POST['content']) || (!isset($_POST['recieverId']) && !isset($_POST['recieverEmail']))) {
            echo ResponseService::error("Lack of data: content and receiver required");
            exit;
        }

        $content = $_POST['content'];

        // determine receiver id
        if (isset($_POST['recieverId'])) {
            $recieverId = (int)$_POST['recieverId'];
        } elseif (isset($_POST['recieverEmail'])) {
            $recieverResult = ChatService::GetUserFromEmail($connection, $_POST['recieverEmail']);
            if (!is_int($recieverResult)) {
                // GetUserFromEmail returns error string on failure
                echo ResponseService::error($recieverResult);
                exit;
            }
            $recieverId = $recieverResult;
        } else {
            echo ResponseService::error("Receiver not provided");
            exit;
        }

        $isInsert = ChatService::sendMessageService($connection, (int)$id, $recieverId, $content);
        if ($isInsert !== true) {
            echo ResponseService::error($isInsert);
            exit;
        }

        echo ResponseService::success("Message sent successfully");
    }

    // receive new messages (mark delivered)
    public function receivingNewMessages()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        self::autho($connection, $id);

        $newMessages = ChatService::receivingMessagesService($connection, (int)$id);
        if (is_string($newMessages)) {
            // service returns error string
            echo ResponseService::error($newMessages);
            exit;
        }

        echo ResponseService::success($newMessages);
    }

    // mark a message as read
    public function readMessage()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        self::autho($connection, $id);

        if (!isset($_POST['message_id'])) {
            echo ResponseService::error("Message id is missing");
            exit;
        }

        $messageId = (int)$_POST['message_id'];
        $res = ChatService::readMessageService($connection, (int)$id, $messageId);
        if (is_string($res)) {
            echo ResponseService::error($res);
            exit;
        }

        // success
        echo ResponseService::success("Message marked as read");
    }

    // get all messages between current user and another user (by email)
    public function getAllMessageWithOtherUser()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        self::autho($connection, $id);

        if (!isset($_POST['otherUserEmail'])) {
            echo ResponseService::error("Other user's email is missing");
            exit;
        }

        $otherUserEmail = $_POST['otherUserEmail'];
        $messages = ChatService::getAllMessageWithOtherUser($connection, (int)$id, $otherUserEmail);

        if (is_string($messages)) {
            echo ResponseService::error($messages);
            exit;
        }

        echo ResponseService::success($messages);
    }

    // get all messages for current user (sent + received)
    public function getAllMessageForUser()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        self::autho($connection, $id);

        $messages = ChatService::getAllMessageForUser($connection, (int)$id);

        if (is_string($messages)) {
            echo ResponseService::error($messages);
            exit;
        }

        echo ResponseService::success($messages);
    }

    // ask AI to summarize delivered messages
    public function CatchUpWithAI()
    {
        global $connection;

        if (!isset($_POST['user_id'])) {
            echo ResponseService::error("User id is missing");
            exit;
        }

        $id = $_POST['user_id'];
        self::autho($connection, $id);

        $res = ChatService::CatchUpWithAI($connection, (int)$id);
        if (!is_array($res) || !isset($res['success'])) {
            echo ResponseService::error("Unexpected summary result");
            exit;
        }

        if (!$res['success']) {
            echo ResponseService::error($res['message'] ?? 'Unknown error from summarizer');
            exit;
        }

        // return summary
        echo ResponseService::success($res['summary'] ?? $res['message'] ?? '');
    }

    private static function autho($connection, $id)
    {
        if (empty($id)) {
            echo ResponseService::error("User ID missing");
            exit;
        }

        // Middleware::Authorization should return user object or null/false
        $user = Middleware::Authorization($connection, $id);

        if (empty($user) || $user === false) {
            echo ResponseService::error("Unauthorized user");
            exit;
        }

        return $user;
    }
}




// require_once __DIR__ . '/../services/ChatService.php';
// require_once __DIR__ .'/../services/ResponseService.php';


// class ChatController{

//     //send message;
//     public function sendMessage(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("the user where");
//             exit ;
//         }
//         $id=$_POST['user_id'];
//         $user=self::autho($connect,$id);

//        if (!isset($_POST['content']) || (!isset($_POST['recieverId']) && !isset($_POST['recieverEmail']))){
//             echo ResponseService::error("lack of data");
//         }
//         if(isset($_POST['reciverId'])){
//             $reciverId=$_POST['recieverId'];
//         }
//         else if( isset($_POST['recieverEmail'])){
//             $reciverId=ChatService::GetUserFromEmail($connection,$_POST['recieverEmail']);
//             if(!is_int($reciverId)){
//                 echo ResponseService::erorr($reciverId);
//                 exit;
//             }
//         }
//        $isInsert= ChatService::sendMessageService( $connection, $id,  $reciverId,$content );
//         if(!$isInsert){
//             echo ResponseService::erorr($isInsert);
//             exit;
//         }
//         echo ResponseService::success("is correct");
//     }

//     public receivingNewMessages(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("the user where");
//             exit ;
//         }
//         $id=$_POST['user_id'];
//         self::autho($connection,$id);
//         $newMessages=ChatService::receivingMessagesService($connection,$id);
//         if(is_string($newMessages)){
//             echo ResponseService::error($newMessages);
//         }
//         echo ResponseService::Success($newMessages);
//     }
//     public readMessage(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("where is the user");
//             exit;
//         }
//         $id=$_POST['user_id'];
//         self::autho($connection,$id);
//         if(!isset($_post["message_id"])){
//             echo ResponseService::error("where the message ");
//             exit;
//         }
//         $messageId=$_POST['message_id'];
//         $res=ChatService::readMessageService($connection,$id,$messageId)
//         if(is_string($res)){
//             echo ResponseService::error($res);
//             exit;
//         }
//         if(!$res){
//             echo ResponseService::error($res);
//             exit;
//         }
//         echo ResponseService::error($res);  
//     }
//     public getAllMessageWithOtherUser(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("where is the user");
//             exit;
//         }
//         $id=$_POST['user_id'];
//         self::autho($connection,$id);
//         if(!isset($_POST['otherUserEmail'])){
//             $otherUserEmail=$_POST['otherUserEmail'];
//         }
//        $messages= ChatService::getAllMessageWithOtherUser($connection,$id,$otherUserEmail);
//        if($messages===null){
//         echo ResponseService::error("message is empty");
//         exit;
//        }
//        echo ResponseService::success($messages);
//     }

//     public getAllMessageForUser(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("where is the user");
//             exit;
//         }
//         $id=$_POST['user_id'];
//         self::autho($connection,$id);
       
//        $messages= ChatService::getAllMessageForUser($connection,$id);
//        if($messages===null){
//         echo ResponseService::error("message is empty");
//         exit;
//        }
//        echo ResponseService::success($messages);
//     }

//     public CatchUpWithAI(){
//         global $connection;

//         if(!isset($_POST['user_id'])){
//             echo ResponseService::error("where is the user");
//             exit;
//         }
//         $id=$_POST['user_id'];
//         self::autho($connection,$id);

//         $res=ChatService::CatchUpWithAI($connection,$id);
//         if(!$res['success']){
//             echo ResponseService::error($res['message']);
//             exit;
//         }
//         echo ResponseService::success($res['summary']);
        
//     }



//      private static function autho($connection, $id)
//     {
//         if (empty($id)) {
//             echo ResponseService::error("User ID missing");
//             exit;
//         }

//         $user = Middleware::Authorization($connection, $id);

//         if (empty($user)) {
//             echo ResponseService::error("Unauthorized user");
//             exit;
//         }
//         if(!$user){
//             echo ResponseService::error("Unauthorized user");
//             exit;       
//         }
//         return $user;

//     }

// }

?>