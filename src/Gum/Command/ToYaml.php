<?php
class Gum_Command_ToYaml
{
    protected $home;
    protected $sugar_home;
    protected $sugar_cache;
    protected $sugar_bin;
    protected $sugar_tmp;
    protected $sugar_lib;
    protected $sugar_specs;

    protected $logger;
    protected $specs = array();

    public function __construct()
    {
        $this->home        = getenv("HOME");
        $this->sugar_home  = "$this->home/.gum";
        $this->sugar_cache = $this->sugar_home . "/cache";
        $this->sugar_bin   = $this->sugar_home . "/bin";
        $this->sugar_tmp   = $this->sugar_home . "/tmp";
        $this->sugar_lib   = $this->sugar_home . "/gums";
        $this->sugar_specs = $this->sugar_home . "/specifications";

        $this->logger = Gum_Logger::getInstance();
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function addSpecs($key, $value)
    {
        $this->specs[$key] = $value;
    }

    public function getSpecs()
    {
        return $this->specs;
    }

    public function hasSpec($key)
    {
        return isset($this->specs[$key]);
    }

    public function generateYaml($target, $opts = array())
    {

        $package = new Gum_Package($target);
        $spec  = $spec_src = Gum_Loader_CompatibleLoader::load($package->getMetaData());

        eval('?>' . $spec);

        $spec = array_pop(Gum::getSpecs());
        echo $spec->toYaml();
    }

    public function execute($target, $opts = array())
    {
        $this->generateYaml($target, $opts);
    }

    public static function run($target, $opts = array())
    {
        $instance = new Gum_Command_ToYaml();
        $instance->execute($target, $opts);
    }
}
