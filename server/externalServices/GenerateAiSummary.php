<?php

require_once __DIR__ . "/CallOpenAI.php";

class GenerateAiSummary
{
    public static function summarizeContent($contents, $retry = 0, $error = null, $previous = null)
    {
        // MAX RETRY
        if ($retry > 4) {
            return [
                "success" => false,
                "error"   => "Failed to generate summary from AI"
            ];
        }

        // Convert data to JSON for AI input
        $jsonString = json_encode($contents, JSON_UNESCAPED_UNICODE);

        // --- FIRST ATTEMPT ---
        if ($retry === 0) {
            $instruction = <<<EOD
Summarize the following message in a small paragraph or a single sentence.

Data:
$jsonString
EOD;
        } 
        
        // --- RETRY WITH ERROR DETAILS ---
        else {
            $instruction = <<<EOD
Your previous summary response was invalid.

Error: $error
Previous AI Output:
$previous

Try again. Return ONLY plain text without JSON or formatting.

Data:
$jsonString
EOD;
        }

        // --- CALL AI ---
        $result = requestOpenAi($instruction);

        // Decode response
        $response = is_string($result) ? json_decode($result, true) : $result;

        // Invalid JSON received
        if ($response === null) {
            return [
                "success" => false,
                "error" => "Failed to decode AI response",
                "raw" => $result
            ];
        }

        // API returned an error
        if (isset($response["error"])) {
            return [
                "success" => false,
                "error" => $response["error"]["message"] ?? "Unknown AI error"
            ];
        }

        // Missing content from AI
        if (!isset($response["choices"][0]["message"]["content"])) {
            return [
                "success" => false,
                "error" => "AI returned invalid structure",
                "raw" => $response
            ];
        }

        $text = trim($response["choices"][0]["message"]["content"]);

        // Validate output
        if (strlen($text) < 5) {
            // Retry again
            return self::summarizeContent(
                $contents,
                $retry + 1,
                "AI returned empty or invalid summary",
                $text
            );
        }

        return [
            "success" => true,
            "summary" => $text
        ];
    }
}


// require_once __DIR__."/CallOpenAI.php";

// class GenerateAiSummary{
//     public static function summarizeCOntent($contents, $retry = 0, $error = null, $previous = null)
//     {
        
//     if ($retry > 4) {
//         return [
//             "success" => false,
//             "error"   => "Failed to generate summary from AI"
//         ];
//     }

//     $jsonString = json_encode($contents);

//     if ($retry === 0) {
//         $instruction = <<<EOD
// Summarize the following  message
// on small paragraph or sentence

// Data:
// $jsonString
// EOD;
//     } else {
//         $instruction = <<<EOD
// Your previous response was invalid.

// Error: $error
// Previous AI text:
// $previous

// Try again. Return ONLY plain text.

// Data:
// $jsonString
// EOD;
//     }

//     // --- CALL OPENAI ---
//     $result = requestOpenAi($instruction);

//     // If response is a string → decode JSON
//     if (is_string($result)) {
//         $response = json_decode($result, true);
//     } else {
//         $response = $result;
//     }

//     // If failed to decode JSON
//     if ($response === null) {
//         return [
//             "success" => false,
//             "error" => "Failed to decode AI response",
//             "raw" => $result
//         ];
//     }

//     // If API error returned
//     if (isset($response["error"])) {
//         return [
//             "success" => false,
//             "error" => $response["error"]["message"] ?? "Unknown AI error"
//         ];
//     }

//     // If AI structure is invalid
//     if (!isset($response["choices"][0]["message"]["content"])) {
//         return [
//             "success" => false,
//             "error" => "AI returned invalid structure",
//             "raw" => $response
//         ];
//     }

//     $text = $response["choices"][0]["message"]["content"];

//     // Validate summary
//     if (strlen(trim($text)) < 5) {
//         return self::summarizeJsonText(
//             $jsonData,
//             $retry + 1,
//             "AI returned empty summary",
//             $text
//         );
//     }

//     return [
//         "success" => true,
//         "summary" => $text
//     ];
//     }
// }




?>