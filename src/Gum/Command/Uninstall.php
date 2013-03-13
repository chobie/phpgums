<?php
class Gum_Command_Uninstall
{
    public static function run($target)
    {
        $home = getenv("HOME");
        $sugar_home  = "$home/.gum";
        $sugar_cache = $sugar_home . "/cache";
        $sugar_bin   = $sugar_home . "/bin";
        $sugar_tmp   = $sugar_home . "/tmp";
        $sugar_lib = $sugar_home . "/gums";
        $sugar_specs = $sugar_home . "/specifications";
        $basename = basename($target, ".gum");

        // Todo: read Spec and delete files (bin, lib, specifications)
        `rm -f $sugar_specs/{$target}-*.gumspec`;
        `rm -rf $sugar_lib/{$target}-*`;
    }
}