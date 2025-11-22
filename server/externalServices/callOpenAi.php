<?php 

require_once __DIR__ . '/../utils/config.php';  // <-- FIXED

function requestOpenAi($instruction){

    $req = json_encode([
        "model" => GPT_VER,
        "messages" => [
            [
                "role" => "system",
                "content" => $instruction
            ]
        ],
        "temperature" => 0.3,
        "max_tokens" => MAX_Tokens,
        "frequency_penalty" => 0.3,
        "presence_penalty" => 0
    ], JSON_UNESCAPED_UNICODE);

    $authorization = "Authorization: Bearer " . OPEN_AI_KEY;

    $ch = curl_init();
    curl_setopt($ch , CURLOPT_URL , "https://api.openai.com/v1/chat/completions");
    curl_setopt($ch,CURLOPT_POST , true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$req);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST , 2);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER , 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch , CURLOPT_HTTPHEADER , [
        'Content-Type: application/json',
        $authorization
    ]);

    $res = curl_exec($ch);

    if(!$res){
        return json_encode(["error" => "CURL ERROR: " . curl_error($ch)]);
    }

    curl_close($ch);
    return $res;
}
?>