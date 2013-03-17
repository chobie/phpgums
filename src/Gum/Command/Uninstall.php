<?php
class Gum_Command_Uninstall
{
    public static function run($target)
    {
        $home      = getenv("HOME");
        $gum_home  = ($gum_home = getenv("GUM_HOME")) ? $gum_home : $home . "/.gum";
        $gum_cache = $gum_home . "/cache";
        $gum_bin   = $gum_home . "/bin";
        $gum_tmp   = $gum_home . "/tmp";
        $gum_lib   = $gum_home . "/gums";
        $gum_specs = $gum_home . "/specifications";

        $basename = basename($target, ".gum");

        // Todo: read Spec and delete files (bin, lib, specifications)
        `rm -f {$gum_specs}/{$target}-*.gumspec`;
        `rm -rf {$gum_lib}/{$target}-*`;
    }
}