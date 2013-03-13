<?php
class Gum_Command_Search
{
    public static function run($name)
    {
        $specs = json_decode(gzdecode(file_get_contents("http://phpgums.org/specs.1.0.gz")));

        printf("\n");
        printf("*** REMOTE GUMS ***\n");

        foreach ($specs as $spec) {
            if (strpos($spec[0], $name) !== false) {
                printf("%s (%s)\n", $spec[0], $spec[1]);
            }
        }
    }
}
