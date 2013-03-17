<?php

class Gum_Command_GenerateIndex
{
    public static function run()
    {
        $pwd = $_SERVER['PWD'];
        echo "# generating index\n";

        $dir = new DirectoryIterator($pwd . DIRECTORY_SEPARATOR . "gums");

        $gums = array();
        foreach ($dir as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                if (strpos($file->getFileName(), ".gum") !== false) {
                    $gums[] = $file->getPathname();
                }
            }
        }

        foreach ($gums as $gum) {
            $package = new Gum_Package($gum);
            eval($package->getMetaData());
        }

        $result = array();
        foreach (Gum::getSpecs() as $spec)  {
            $result[] = array($spec->getName(), $spec->getVersion(), "php");
        }

        // TODO
        //asort($result);
        file_put_contents("specs.1.0", json_encode($result));
        file_put_contents("specs.1.0.gz", gzencode(json_encode($result)));
    }
}