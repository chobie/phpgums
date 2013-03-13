<?php
class Gum_Finder
{
    protected $home;

    public function __construct($home)
    {
        $this->home = $home;
    }

    public function find($spec_name)
    {
        $basedir = $this->home . DIRECTORY_SEPARATOR . "specifications";

        $dir = new DirectoryIterator($basedir);
        $specs = array();
        foreach ($dir as $file) {
            if (!$file->isDot()) {
                if(!$file->isDir()) {
                    if (strpos($file->getFileName(), $spec_name) !== false) {
                        $specs[] = $file->getPathname();
                    }
                }
            }
        }

        $spec_file = array_shift($specs);
        if (!$spec_file) {
            throw new Exception("can't find file. probably this is a bug");
        }

        require $spec_file;
        return array_pop(Gum::getSpecs());
    }
}