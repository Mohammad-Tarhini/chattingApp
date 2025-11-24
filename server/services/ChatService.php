<?php

require_once __DIR__ . '/../externalServices/GenerateAiSummary.php';
require_once __DIR__ . '/../models/message.php';
require_once __DIR__ . '/../models/User.php';

class ChatService
{
    public static function GetUserFromEmail(mysqli $connection, $email)
    {
        if (empty($email)) {
            return "Email is empty";
        }

        // User::findByColumn should return array of users or empty array/string depending on your model
        $user = User::findByColumn($connection, "email", $email);
        if (empty($user) || !is_array($user)) {
            return "No user found with that email";
        }

        // assume first match
        $userId = $user[0]->getId();
        return (int)$userId;
    }

    public static function sendMessageService(mysqli $connection, int $senderId, int $reciverId, $content)
    {
        // Check users exist (update signatures if your User::findById needs connection param)
        if (User::findById($connection, $senderId) === null) {
            return "Sender is not a user";
        }
        if (User::findById($connection, $reciverId) === null) {
            return "Receiver is not a user";
        }

        $message = new message(
            [
                'receiver_id' => $reciverId,
                'sender_id'   => $senderId,
                'content'     => $content,
                'status'      => 'sent',
                'send_at'     => date("Y-m-d H:i:s")
            ]
        );

        if (!$message->insert($connection)) {
            return "Error on insert";
        }

        return true;
    }

    public static function receivingMessagesService(mysqli $connection, int $receiverId)
    {
        // find all messages for that receiver with status 'sent' (not delivered yet)
        $messagesReceivedNow = message::findAllWhere($connection, ['receiver_id' => $receiverId, 'status' => 'sent']);
        if ($messagesReceivedNow === null || empty($messagesReceivedNow)) {
            return "No new messages";
        }

        $newMessagesRecievedData = [];

        foreach ($messagesReceivedNow as $messageRecived) {
            // mark delivered
            $messageRecived->setStatus("delivered");
            $messageRecived->setDeliveredAt(date("Y-m-d H:i:s"));
            $messageRecived->update($connection);

            $newMessagesRecievedData[] = [
                "messageId" => $messageRecived->getId(),
                "senderId"  => $messageRecived->getSenderId(),
                "content"   => $messageRecived->getContent(),
                "sendAt"    => $messageRecived->getSendAt()
            ];
        }

        return $newMessagesRecievedData;
    }

    public static function readMessageService(mysqli $connection, $receiverId, int $messageId)
    {
        // find message by id (adjust signature if your method requires $connection)
        $message = message::findByID($connection, $messageId);
        if ($message === null) {
            return "No message for this id";
        }

        if ($message->getReceiverId() !== (int)$receiverId) {
            return "This user is not the receiver";
        }

        $message->setStatus("seen");
        $message->setReadAt(date("Y-m-d H:i:s"));

        if ($message->update($connection)) {
            return true;
        }

        return "Error updating message status";
    }

    public static function getAllMessageWithOtherUser(mysqli $connection, int $userId, $otherUserEmail)
    {
        // find other user by email
        $OtherUser = User::findByColumn($connection, "email", $otherUserEmail);
        if (empty($OtherUser) || !is_array($OtherUser)) {
            return "The other user does not exist";
        }

        $otherUserId = $OtherUser[0]->getId();

        if (empty($userId)) {
            return "The user does not exist";
        }

        $messageSendByUserForThisOther = message::findAllWhere($connection, [
            'sender_id'   => $userId,
            'receiver_id' => $otherUserId
        ]);

        $messagereceivedtoUserFromThisOther = message::findAllWhere($connection, [
            'sender_id'   => $otherUserId,
            'receiver_id' => $userId
        ]);

        $messageBetweenThem = self::unionObjectsById(
            is_array($messageSendByUserForThisOther) ? $messageSendByUserForThisOther : [],
            is_array($messagereceivedtoUserFromThisOther) ? $messagereceivedtoUserFromThisOther : []
        );

        // sort by send_at ascending or descending - choose descending (newest first)
        usort($messageBetweenThem, function ($a, $b) {
            // use getters if available
            $aSend = method_exists($a, 'getSendAt') ? $a->getSendAt() : ($a->send_at ?? null);
            $bSend = method_exists($b, 'getSendAt') ? $b->getSendAt() : ($b->send_at ?? null);
            return strcmp($bSend, $aSend);
        });

        return $messageBetweenThem;
    }

    public static function getAllMessageForUser(mysqli $connection, int $userId)
    {
        $messageSendByUser = message::findByColumn($connection, "sender_id", $userId);
        $messageReceivedByUser = message::findByColumn($connection, "receiver_id", $userId);

        $messagesForThisUser = self::unionObjectsById(
            is_array($messageSendByUser) ? $messageSendByUser : [],
            is_array($messageReceivedByUser) ? $messageReceivedByUser : []
        );

        usort($messagesForThisUser, function ($a, $b) {
            $aSend = method_exists($a, 'getSendAt') ? $a->getSendAt() : ($a->send_at ?? null);
            $bSend = method_exists($b, 'getSendAt') ? $b->getSendAt() : ($b->send_at ?? null);
            return strcmp($bSend, $aSend);
        });

        return $messagesForThisUser;
    }

    public static function CatchUpWithAI(mysqli $connection, int $userId)
    {
        // get messages that are delivered (or whichever status you want to summarize)
        $messages = message::findAllWhere($connection, ['receiver_id' => $userId, 'status' => 'delivered']);
        if ($messages === null || count($messages) <= 3) {
            return ["success" => false, "message" => "There are not more than 3 delivered messages to summarize"];
        }

        $contents = [];
        foreach ($messages as $message) {
            $contents[] = ['content' => $message->getContent()];
        }

        // call summarizer - adapt to how your GenerateAiSummary provides functionality
        if (function_exists('summarizeContent')) {
            $res = summarizeContent($contents);
        } elseif (class_exists('GenerateAiSummary') && method_exists('GenerateAiSummary', 'summarize')) {
            $res = GenerateAiSummary::summarize($contents);
        } else {
            return ["success" => false, "message" => "Summarizer service not available"];
        }

        if (!is_array($res) || !isset($res['success'])) {
            return ["success" => false, "message" => "Unexpected summarizer response"];
        }

        if (!$res['success']) {
            return ["success" => false, "message" => $res['error'] ?? 'Summary generation failed'];
        }

        // Return summary (use 'summary' key if available)
        return ["success" => true, "summary" => $res['summary'] ?? ($res['message'] ?? '')];
    }

    private static function unionObjectsById(array $array1, array $array2)
    {
        $result = [];

        foreach (array_merge($array1, $array2) as $obj) {
            // try to get id using getter first
            if (method_exists($obj, 'getId')) {
                $id = $obj->getId();
            } elseif (isset($obj->id)) {
                $id = $obj->id;
            } else {
                // fallback to spl_object_hash to avoid collision
                $id = spl_object_hash($obj);
            }
            $result[$id] = $obj; // duplicates overwritten, keeps latest occurrence
        }

        return array_values($result);
    }
}




// require_once __DIR__ . '/../externalService/GenerateAiSummary.php';
// require_once __DIR__ . '/../models/message.php';
// require_once __DIR__ . '/../models/User.php';

// class ChatService{
//     public static function GetUserFromEmail(mysqli $connection ,  $email){
//         if(empty($email))
//             return " empty";
//         $user=User::findByColumn($connection,"email",$email);
//         if($user===""){
//             return " no users "
//         }
//         $userId=$user[0]->getId();
//         return $userId;
//     }

//     public static function   sendMessageService(mysqli $connection,int $senderId, int $reciverId,$content ){

//        if(User::findById($senderId)===null) return "sender is not user ";
//        if(User::findById($reciverId)===null) return "reciever is not a user ";

//         $message=new message(
//             [
//                 'receiver_id'=>$reciverId,
//                 'sender_id'=>$senderId,
//                 'content'=>$content,
                
//             ]
//         )
//       if( !$message->insert($connection)){
//         return " eror on insert";
//       }
//       return true;
       

//     }
//     public static function receivingMessagesService(mysqli $connection ,int $receiverId){
//           if(User::findById($recieverId)===null) return "recieverId is not user ";
//          $messagesReceivedNow= User::findAllWhere($connection,['receiver_id'=>$receiverId,'status'=>'sent']);
//          if($messagesReceivedNow===null)return "no new message";
//          $newMessagesRecievedData=[];
//           foreach($messagesReceivedNow as $messageRecived ){
//             $messageRecived->setStatus("delivered");
//             $messageRecived->setDeliveredAt(date("Y-m-d H:i:s"));
//             $messageRecived->update($connection);
//             $newMessagesRecievedData=[{"messageId":$messageRecived->getId(),"senderId":$messageRecived->getSenderId(),"Content":$messageRecived->getContent(),"sendAt":$messageRecived->getSendAt() }]
//           }
//           return  $newMessagesRecievedData;

//     }

//     public static function readMessageService(mysqli $connection,$receiverId, int $messageId){
//         $message=message::findByID($messageId);
//         if($message===null){
//             return "no message for this id message";
//         }
//         if($message->getReceiverId() !== $receiverId){
//             return " this user is not the reciever";
//         }

//         $message->setStatus("seen");
//         $message->setReadAt(date("Y-m-d H:i:s"));
//        if( $message->update($connection)) return true;
//        return "errot";
//     }
//     public static function getAllMessageWithOtherUser(mysqli $connection,int $userId, $otherUserEmail){
//         $OtherUser=User::findByColumn($connection,"email",$otherUserEmail);
//         if(empty($OtherUser))return " the other is is not exist";
//         if(empty($user_id)) return "the use is not exist";
//         $messageSendByUserForThisOther=message::findAllWhere($connection,['sender_id'=>$userId,'receiver_id'=>$otherUserId]);
//         $messagereceivedtoUserFromThisOther=message::findAllWHere($connection,['sender_id'=>$otherUserId,'receiver_id'=>$userId]);
//         $messageBetweenThem=unionObjectById($messageSendByUserForThisOther,$messagereceivedtoUserFromThisOther);
//         usort($messageBetweenThem, function($a, $b) {
//              return $b->send_at <=> $a->send_at;
//             }); 
//         return $messageBetweenThem;
//     }
//     public static function getAllMessageForUser(mysqli $connection,int $userId){
//         $messageSendByUser=message::findByColumn($connection,"sender_id",$userId);
//         $messageReceivedByUser=message::findColumn($connection,"receiver_id",$user_id);
//         $messagesForThisUser=unionObjectById($messageSendByUser,$messageReceivedByUser);
//         unsort($messagesForThisUser,function($a,$b){
//             return $b->send_at <=> $a->send_at;
//         });
//         return $messagesForThisUser;
//     }
//     public static function CatchUpWithAI(mysqli $connection,int $userId){
//         $messages=message::findAllWhere($connection,['receiver_id'=>$user_id,'status'=>'delivered']);
//         if(count($messages)<=3){
//             return ["success"=>false,"message"=>"there are no read more then 3"];
//         }
//         $contents=[];
//         foreach($messages as $message){
//             $contents=[{'content'=>$message->getContent()}];
//         }
//         $res=summarizeCOntent($contents);
//         if(!$res["success"]){
//             return [ "success"=>false,"message"=>$res["error"]];
//         }
//         return ["success"=>true ,"message"=>$res["summary"]];
//     }


//     private static function unionObjectsById(array $array1, array $array2) {
//     $result = [];

//     foreach (array_merge($array1, $array2) as $obj) {
//         $result[$obj->id] = $obj;   // overwrite duplicates automatically
//     }

//     return array_values($result);
// }
// }





?>