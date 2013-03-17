<?php
class Gum_Package
{
    protected $filename;
    protected $validated = false;

    public function __construct($filename)
    {
        $this->filename = $filename;
        $this->tar = new Archive_Minitar_Reader($this->filename);
    }

    public function isValid()
    {
    }

    public function getDataArchive()
    {
        return $this->tar->getEntry("data.tar.gz")->getContent();
    }

    public function getMetaData()
    {
        $spec  = gzdecode($this->tar->getEntry("metadata.gz")->getContent());
        return $spec;
    }
}