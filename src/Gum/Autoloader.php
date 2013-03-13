<?php
class Gum_Autoloader
{
    protected static $instance;
    protected static $base_dir;

    protected $namespaces;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Gum_Autoloader();
            self::$instance->register();
        }

        return self::$instance;
    }

    public function registerNamespace($key, $value)
    {
        $this->namespaces[$key] = $value;
    }


    /**
     * register autoloader
     *
     * @param string $dirname base directory path.
     * @return void
     */
    public static function register()
    {
        $instance = self::getInstance();
        spl_autoload_register(array($instance, "autoload"));
    }

    /**
     * unregister Phive autoloader
     *
     * @return void
     */
    public static function unregister()
    {
        spl_autoload_unregister(array(__CLASS__, "autoload"));
    }

    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * autoloader implementation
     *
     * @param string $name class name
     * @return boolean return true when load successful
     */
    public function autoload($name)
    {
        $retval = false;
        foreach ($this->getNamespaces() as $namespace => $require_path) {
            if (strpos($name, $namespace) === 0) {
                $parts = explode("_",$name);
                $expected_path = join(DIRECTORY_SEPARATOR, array($require_path, join(DIRECTORY_SEPARATOR,$parts) . ".php"));

                if (is_file($expected_path) && is_readable($expected_path)) {
                    require $expected_path;
                    $retval = true;
                    break;
                }
            }
        }

        return $retval;
    }
}