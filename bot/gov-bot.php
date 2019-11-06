<?php

// Set webhook
// https://api.telegram.org/bot1017664403:AAFv3yj797EAHdleCSecDDKZuRJxJVUc3kc/setWebhook?url=https://hs2019st.com/govbr/bot/gov-bot.php

// Query info
// https://api.telegram.org/bot1017664403:AAFv3yj797EAHdleCSecDDKZuRJxJVUc3kc/getWebhookInfo

// Declare main variables
$token = "1017664403:AAFv3yj797EAHdleCSecDDKZuRJxJVUc3kc";
$bot = "https://api.telegram.org/bot".$token;

// Get request contents and relevant data
$content    = file_get_contents("php://input");
$update     = json_decode($content, true);
$update_id  = $update['update_id'];
$text       = $update['message']['text'];
$chat_date  = $update['message']['date'];
$message_id = $update['message']['message_id'];
$chat_id    = $update['message']['chat']['id'];
$first_name = $update['message']['chat']['first_name'];
$chat_type  = $update['message']['chat']['type'];

// Main - Treats the input and returns answer plus keyboard layout
switch ($text)
{
    case '/start':
    case '/inicio':
        $response = 'Bem vindo à plataforma de serviços do governo brasileiro.';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&text=$response");

        $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-select.php?q='".$text."'&fl=id,nome_s&rows=4"),TRUE);
        $response = 'Esses são os serviços mais acessados ultimamente:';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&text=$response");

        $response = '<a href="https://'.$result['response']['docs'][0]['id'].'">'.$result['response']['docs'][0]['nome_s'].'</a>';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&parse_mode=HTML&text=$response");
        $response = '<a href="https://'.$result['response']['docs'][1]['id'].'">'.$result['response']['docs'][1]['nome_s'].'</a>';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&parse_mode=HTML&text=$response");
        $response = '<a href="https://'.$result['response']['docs'][2]['id'].'">'.$result['response']['docs'][2]['nome_s'].'</a>';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&parse_mode=HTML&text=$response");
        $response = '<a href="https://'.$result['response']['docs'][3]['id'].'">'.$result['response']['docs'][3]['nome_s'].'</a>';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&parse_mode=HTML&text=$response");

        $response = 'Como posso te ajudar? Digite o nome do serviço que deseja encontrar.';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&text=$response");

        break;
    default:
        $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-select.php?q='".$text."'&fl=id,nome_s&rows=4"),TRUE);
        $response = $result['response']['docs'][0]['nome_s'];
        break;
}

// Log
file_put_contents(
    '../../bot_logs/log',
    $text.' -> '.$response."\n-----\n",
    FILE_APPEND);

// Response
file_get_contents($bot."/sendmessage?chat_id=$chat_id&text=$response");
