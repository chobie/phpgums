<?php
class Gum_Command_Search
{
    public static function run($name)
    {
        $config = Gum_Application::getConfig();
        $urls = array();
        if (is_array($config['sources'])) {
            foreach ($config['sources'] as $spec) {
                $urls[] = $spec['url'];
            }
        }

        $specs_array = array();
        foreach ($urls as $url) {
            $specs_array[] = json_decode(gzdecode(file_get_contents("$url/specs.1.0.gz")));
        }

        printf("\n");
        printf("*** REMOTE GUMS ***\n");

        foreach ($specs_array as $specs) {
            foreach ($specs as $spec) {
                if (strpos($spec[0], $name) !== false) {
                    printf("%s (%s)\n", $spec[0], $spec[1]);
                }
            }
        }
    }
}
