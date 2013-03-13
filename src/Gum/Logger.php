<?php
class Gum_Logger
{
    protected static $instance;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new Gum_Logger();
        }

        return self::$instance;
    }

    public function log()
    {
        $args = func_get_args();
        $args[0] .= "\n";
        call_user_func_array("printf", $args);
    }
}