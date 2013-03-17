<?php
require dirname(__FILE__) . DIRECTORY_SEPARATOR . "Gum" . DIRECTORY_SEPARATOR . "Autoloader.php";

$loader = Gum_Autoloader::getInstance();
$loader->registerNamespace("Gum", dirname(__FILE__));
unset($loader);

class Gum
{
    protected static $specs = array();

    public static function Specification($func)
    {
        $spec = new Gum_Specification();
        try {
            call_user_func_array($func, array($spec));
        } catch (Exception $e) {
        }

        self::$specs[] = $spec;

        return $spec;
    }

    public static function getSpecs()
    {
        return self::$specs;
    }
}

if (!function_exists("gzdecode")) {
    function gzdecode($data)
    {
        return gzinflate(substr($data,10,-8));
    }
}

function gum($spec, $version = null)
{
    return require_phpgum($spec);
}

function require_phpgum($spec, $version = null)
{
    static $finder = false;

    $home = getenv("HOME");
    $sugar_base = "$home/.gum";

    if (!$finder) {
        $finder = new Gum_Finder($sugar_base);
    }

    try {
        $spec = $finder->find($spec);
        $loader = Gum_Autoloader::getInstance();

        foreach ($spec->getDependencies() as $dependency => $version) {
            require_phpgum($dependency);
        }

        foreach ($spec->getAutoload() as $key => $path) {
            $target_path  = $sugar_base . DIRECTORY_SEPARATOR . "/gums/" . sprintf("%s-%s/%s", $spec->getName(), $spec->getVersion(), $path);
            $loader->registerNamespace($key, $target_path);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

gum("archive-minitar");
