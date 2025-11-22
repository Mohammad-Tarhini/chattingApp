<?php

class ChatService{
    public static function GetUserFromEmail(mysqli $connection ,  $email){
        if(empty($email))
            return " empty";
        $user=User::findByColumn($connection,"email",$email);
        if($user===""){
            return " no users "
        }
        $userId=$user[0]->getId();
        return $userId;
    }

    public static function   sendMessageService(mysqli $connection,int $senderId, int $reciverId,$content ){

       if(User::findById($senderId)===null) return "sender is not user ";
       if(User::findById($reciverId)===null) return "reciever is not a user ";

        $message=new message(
            [
                'receiver_id'=>$reciverId,
                'sender_id'=>$senderId,
                'content'=>$content,
                
            ]
        )
      if( !$message->insert($connection)){
        return " eror on insert";
      }
      return true;
       

    }
    public static function receivingMessagesService(mysqli $connection ,int $receiverId){
          if(User::findById($recieverId)===null) return "recieverId is not user ";
         $messagesReceivedNow= User::findAllWhere($connection,['receiver_id'=>$receiverId,'status'=>'sent']);
         if($messagesReceivedNow===null)return "no new message";
         $newMessagesRecievedData=[];
          foreach($messagesReceivedNow as $messageRecived ){
            $messageRecived->setStatus("delivered");
            $messageRecived->setDeliveredAt(date("Y-m-d H:i:s"));
            $messageRecived->update($connection);
            $newMessagesRecievedData=[{"messageId":$messageRecived->getId(),"senderId":$messageRecived->getSenderId(),"Content":$messageRecived->getContent(),"sendAt":$messageRecived->getSendAt() }]
          }
          return  $newMessagesRecievedData;

    }

    public static function readMessageService(mysqli $connection,$receiverId, int $messageId){
        $message=message::findByID($messageId);
        if($message===null){
            return "no message for this id message";
        }
        if($message->getReceiverId() !== $receiverId){
            return " this user is not the reciever";
        }

        $message->setStatus("seen");
        $message->setReadAt(date("Y-m-d H:i:s"));
       if( $message->update($connection)) return true;
       return "errot";
    }
    public static function getAllMessageWithOtherUser(mysqli $connection,int $userId, $otherUserEmail){
        $OtherUser=User::findByColumn($connection,"email",$otherUserEmail);
        if(empty($OtherUser))return " the other is is not exist";
        if(empty($user_id)) return "the use is not exist";
        $messageSendByUserForThisOther=message::findAllWhere($connection,['sender_id'=>$userId,'receiver_id'=>$otherUserId]);
        $messagereceivedtoUserFromThisOther=message::findAllWHere($connection,['sender_id'=>$otherUserId,'receiver_id'=>$userId]);
        $messageBetweenThem=unionObjectById($messageSendByUserForThisOther,$messagereceivedtoUserFromThisOther);
        usort($messageBetweenThem, function($a, $b) {
             return $b->send_at <=> $a->send_at;
            }); 
        return $messageBetweenThem;
    }
    public static function getAllMessageForUser(mysqli $connection,int $userId){
        $messageSendByUser=message::findByColumn($connection,"sender_id",$userId);
        $messageReceivedByUser=message::findColumn($connection,"receiver_id",$user_id);
        $messagesForThisUser=unionObjectById($messageSendByUser,$messageReceivedByUser);
        unsort($messagesForThisUser,function($a,$b){
            return $b->send_at <=> $a->send_at;
        });
        return $messagesForThisUser;
    }
    public static function CatchUpWithAI(mysqli $connection,int $userId){
        $message=message::findAllWhere($connection,['receiver_id'=>$user_id,'status'=>'delivered']);
        if(count($message)>3){
            
        }
    }


    private static function unionObjectsById(array $array1, array $array2) {
    $result = [];

    foreach (array_merge($array1, $array2) as $obj) {
        $result[$obj->id] = $obj;   // overwrite duplicates automatically
    }

    return array_values($result);
}
}





?>