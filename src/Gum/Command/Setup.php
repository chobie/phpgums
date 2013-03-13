<?php
class Gum_Command_Setup
{
    public static function run()
    {
        //sugar/{bin,cache,doc,gums,specifications}
        $home = getenv("HOME");

        @mkdir("$home/.gum/");
        @mkdir("$home/.gum/bin");
        @mkdir("$home/.gum/cache");
        @mkdir("$home/.gum/tmp");
        @mkdir("$home/.gum/doc");
        @mkdir("$home/.gum/gums");
        @mkdir("$home/.gum/specifications");
    }
}