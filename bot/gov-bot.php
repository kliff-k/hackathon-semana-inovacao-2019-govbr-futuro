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
$location   = $update['message']['location'];

// Main - Treats the input and returns answer plus keyboard layout
if($location)
{
    $response = 'Os seguintes serviços são os mais populares em sua localização:';
    $remove_keyboard = json_encode(["remove_keyboard" => TRUE]);
    file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

    $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-select.php?q=*:*&fl=id,nome_s&rows=4"),TRUE);
    $response = 'Esses são os serviços mais acessados ultimamente:';
    file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

    foreach ($result['response']['docs'] AS $value)
    {
        $response = '<a href="https://www.google.com">'.$value['nome_s'].'</a>';
        file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
    }

}
else
{
    switch ($text)
    {
        case '/start':
        case '/inicio':
        case '/oi':
            $response = 'Bem vindo à plataforma de serviços do governo brasileiro.';
            $remove_keyboard = json_encode(["remove_keyboard" => TRUE]);
            file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

            $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-select.php?q=*:*&fl=id,nome_s&rows=4"),TRUE);
            $response = 'Esses são os serviços mais acessados ultimamente:';
            file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

            foreach ($result['response']['docs'] AS $value)
            {
                $response = '<a href="https://www.google.com">'.$value['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
            }

            $response = 'Como posso te ajudar? Digite o nome do serviço que deseja encontrar. Ou envie uma localização para a lista de serviços mais populares na região.';
            file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

            break;
        default:
            $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-select.php?q=".urlencode($text)."&fl=id,nome_s&rows=4"),TRUE);
            $total = $result['response']['numFound'];

            $remove_keyboard = json_encode(["remove_keyboard" => TRUE]);

            if(!$total)
            {
                $response = "Não foram encontrados resultados para a sua busca.";
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");
            }

            if($total == 1)
            {
                $response = '<a href="https://www.google.com">'.$result['response']['docs'][0]['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
            }

            if($total > 1 )
            {
                $response = "Foram encontrados $total serviços.";
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&text=$response");

                $response = '<a href="https://www.google.com">'.$result['response']['docs'][0]['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
                $response = '<a href="https://www.google.com">'.$result['response']['docs'][1]['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
                $response = '<a href="https://www.google.com">'.$result['response']['docs'][2]['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");
                $response = '<a href="https://www.google.com">'.$result['response']['docs'][3]['nome_s'].'</a>';
                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=$remove_keyboard&parse_mode=HTML&text=$response");

                $result = json_decode(file_get_contents("https://hs2019st.com/govbr/solr-suggest.php?suggest=true&suggest.build=true&suggest.dictionary=nomeSuggester&wt=json&suggest.count=4&suggest.q=".urlencode($text)),TRUE);
                $response = 'Deseja refinar a sua busca? Tente um dos termos sugeridos:';

                $keyboard = [];

                foreach ($result['suggest']['nomeSuggester'][$text]['suggestions'] AS $value)
                {
                    $keyboard[] = [$value['term']];
                }

                $reply_keyboard = ['keyboard' => $keyboard, 'one_time_keyboard' => TRUE, 'resize_keyboard' => TRUE];

                file_get_contents($bot."/sendmessage?chat_id=$chat_id&reply_markup=".json_encode($reply_keyboard)."&text=$response");
            }

            break;
    }
}

// Log
file_put_contents(
    '../../bot_logs/log',
    $text.' -> '.$bot."/sendmessage?chat_id=$chat_id&reply_markup=".json_encode($reply_keyboard)."&text=$response"."\n-----\n",
    FILE_APPEND);
