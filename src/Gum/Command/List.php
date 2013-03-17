<?php
class Gum_Command_List
{
    public static function run()
    {
        $home        = getenv("HOME");
        $sugar_home  = "$home/.gum";
        $sugar_specs = $sugar_home . "/specifications";

        $logger = Gum_Logger::getInstance();
        $logger->log("# installed gum specs.");

        foreach(explode("\n",trim(`ls $sugar_specs`)) as $line) {
            $logger->log("  %s", $line);
        }
    }
}
