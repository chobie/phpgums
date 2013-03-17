<?php
class Gum_Application
{
    protected static $config;

    public static function getConfig()
    {
        if (!self::$config) {
            $home = getenv("HOME");
            if (is_file($home . DIRECTORY_SEPARATOR . ".gumrc")) {
                self::$config = parse_ini_file($home . DIRECTORY_SEPARATOR . ".gumrc", true);
            } else {

                self::$config = array(
                    "sources" => array(
                        "url" => array(
                            "http://phpgums.org"
                        ),
                    ),
                );
            }
        }

        return self::$config;
    }

    public static function run()
    {
        $home = getenv("HOME");

        $argv = $_SERVER['argv'];
        array_shift($argv);
        $command = array_shift($argv);

        switch ($command) {
            case "build":
                $spec = array_shift($argv);
                Gum_Command_Build::run($spec);
                break;
            case "update":
                $spec = array_shift($argv);

                $target = null;
                $opts = array();
                while ($opt = array_shift($argv)) {
                    if (is_null($target)) {
                        switch($opt) {
                            case "--system":
                                $spec = "gum";
                                break;
                            default:
                                throw new Exception("option $opt is not allowed");
                        }
                    } else {
                        $opts[$target] = $opt;
                        $target = null;
                    }
                }

                Gum_Command_Install::run("gum", array("source" => "http://localhost:8888"));
                break;

            case "install":
                $spec = array_shift($argv);

                $target = null;
                $opts = array();
                while ($opt = array_shift($argv)) {
                    if (is_null($target)) {
                        switch($opt) {
                            case "--source":
                                $target = "source";
                                break;
                            default:
                                throw new Exception("option $opt is not allowed");
                        }
                    } else {
                        $opts[$target] = $opt;
                        $target = null;
                    }
                }

                Gum_Command_Install::run($spec, $opts);
                break;
            case "uninstall":
                $spec = array_shift($argv);
                Gum_Command_Uninstall::run($spec);
                break;
            case "list":
                Gum_Command_List::run();
                break;
            case "generate_index":
                Gum_Command_GenerateIndex::run();
                break;
            case "setup":
                Gum_Command_Setup::run();
                break;
            case "toyaml":
                $spec = array_shift($argv);
                Gum_Command_ToYaml::run($spec);
                break;
            case "push":
                $spec = array_shift($argv);

                $target = null;
                $opts = array();
                while ($opt = array_shift($argv)) {
                    if (is_null($target)) {
                        switch($opt) {
                            case "--source":
                                $target = "source";
                                break;
                            default:
                                throw new Exception("option $opt is not allowed");
                        }
                    } else {
                        $opts[$target] = $opt;
                        $target = null;
                    }
                }

                Gum_Command_Push::run($spec, $opts);
                break;
            case "migrate":
                $spec = array_shift($argv);

                $target = null;
                $opts = array();
                while ($opt = array_shift($argv)) {
                    if (is_null($target)) {
                        switch($opt) {
                            case "-v":
                                $target = "version";
                                break;
                            default:
                                throw new Exception("option $opt is not allowed");
                        }
                    } else {
                        $opts[$target] = $opt;
                        $target = null;
                    }
                }

                Gum_Command_Migrate::run($spec, $opts);
                break;
            case "search":
                $name = array_shift($argv);
                Gum_Command_Search::run($name);
                break;
            case "server":
                $php_bin = trim(`php-config --php-binary`);
                pcntl_exec("{$php_bin}", array("-S", "localhost:8888"));
                break;
            case "help":
            default:
                echo "PHPGums is a sophisticated package manager for PHP. This is a
basic help message containing pointers to more information.

  Usage:
    gum -h/--help
    gum -v/--version
    gum command [arguments...] [options...]
";
                break;
        }
    }
}