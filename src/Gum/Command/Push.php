<?php
class Gum_Command_Push
{
    public static function run($file, $opts = array())
    {
        $config = Gum_Application::getConfig();
        if (empty($config['global']['api_key'])) {
            throw new Exception("you have to add\n[global]\napi_key='you-api-key' to your .gumrc");
        }
        $api_key = $config['global']['api_key'];

        $server = "http://phpgums.org";
        if (!empty($opts['source'])) {
            $server = rtrim($opts['source'], "/");
        }

        $data = file_get_contents($file);
        echo file_get_contents("$server/api/v1/gums", false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header' => "Content-Type: application/octet-stream\r\n".
                            "Authorization: {$api_key}\r\n".
                            "User-Agent: phpgums/<Version> (<platform>) <php>/<phpversion>\r\n".
                            "Content-Length: " . strlen($data),
                'content' => $data,
        ),
        )));
    }
}
