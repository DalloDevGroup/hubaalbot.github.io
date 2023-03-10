<?php
// Set up the Telegram bot
$telegram_bot_token = '1967292402:AAH-fAZAs6zIDRCrbXrKXiicOLSthKvIDyg';
$telegram_bot_url = 'https://api.telegram.org/bot'.$telegram_bot_token;

// Set up the ChatGPT API endpoint and API key
$chatgpt_api_endpoint = 'https://api.chatgpt.com/v1/gpt';
$chatgpt_api_key = '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';

// Function to send messages to the ChatGPT API and return the response
function get_chatgpt_response($message) {
    global $chatgpt_api_endpoint, $chatgpt_api_key;
    $payload = array(
        'text' => $message,
        'api_key' => $chatgpt_api_key
    );
    $options = array(
        'http' => array(
            'header'  => 'Content-type: application/json',
            'method'  => 'POST',
            'content' => json_encode($payload),
        ),
    );
    $context  = stream_context_create($options);
    $response = file_get_contents($chatgpt_api_endpoint, false, $context);
    return json_decode($response, true)['response'];
}

// Function to handle incoming messages from the user
function handle_message($update) {
    global $telegram_bot_url;
    $message = $update['message']['text'];
    $chat_id = $update['message']['chat']['id'];

    // Send the "typing" animation to the user
    $data = array(
        'chat_id' => $chat_id,
        'action' => 'typing'
    );
    $options = array(
        'http' => array(
            'header'  => 'Content-type: application/json',
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($telegram_bot_url.'/sendChatAction', false, $context);

    // Get the response from the ChatGPT API and send it to the user
    $response = get_chatgpt_response($message);
    $data = array(
        'text' => $response,
        'chat_id' => $chat_id
    );
    $options = array(
        'http' => array(
            'header'  => 'Content-type: application/json',
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($telegram_bot_url.'/sendMessage', false, $context);
}

// Set up the Telegram bot's message handler
$update = json_decode(file_get_contents('php://input'), true);
if (isset($update['message'])) {
    handle_message($update);
}

// Set up the webhook to receive incoming messages
if (isset($_GET['set_webhook'])) {
    $webhook_url = 'https://somchatgtp.rf.gd/index.php';
    $data = array(
        'url' => $webhook_url
    );
    $options = array(
        'http' => array(
            'header'  => 'Content-type: application/json',
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($telegram_bot_url.'/setWebhook', false, $context);
    echo $result;
}
?>