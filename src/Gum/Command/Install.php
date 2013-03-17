<?php
class Gum_Command_Install
{
    protected $home;
    protected $gum_home;
    protected $gum_cache;
    protected $gum_bin;
    protected $gum_tmp;
    protected $gum_lib;
    protected $gum_specs;

    protected $logger;
    protected $specs = array();

    public function __construct()
    {
        $this->home      = getenv("HOME");

        $this->gum_home  = ($gum_home = getenv("GUM_HOME")) ? $gum_home : $this->home . "/.gum";
        $this->gum_cache = $this->gum_home . "/cache";
        $this->gum_bin   = $this->gum_home . "/bin";
        $this->gum_tmp   = $this->gum_home . "/tmp";
        $this->gum_lib   = $this->gum_home . "/gums";
        $this->gum_specs = $this->gum_home . "/specifications";

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

    public function downloadGumFile($target, $url)
    {
        $data = file_get_contents(rtrim($url));
        if (file_put_contents($this->gum_cache . "/$target", $data)) {
            $this->getLogger()->log("download succeeded");
        }
    }

    public function installGum($target, $opts = array())
    {
        $package = new Gum_Package($this->gum_cache . DIRECTORY_SEPARATOR . $target);
        $spec  = $spec_src = Gum_Loader_CompatibleLoader::load($package->getMetaData());

        eval('?>' . $spec);

        $spec = array_pop(Gum::getSpecs());
        $this->getLogger()->log("# processing %s spec.", $spec->getName());

        // resolve section
        $finder = new Gum_Finder($this->gum_home);
        $this->getLogger()->log("# resolve dependencies...");
        foreach ($spec->getDependencies() as $key => $version) {
            $this->getLogger()->log("  checking %s...", $key);

            try {
                $s = $finder->find($key);
                $this->getLogger()->log("  OK. %s-%s has already installed.", $s->getName(), $s->getVersion());
            } catch (Exception $e) {
                // naipo
                $this->getLogger()->log("%s did not install. try to resolve dependency", $key);

                $this->execute($key, $opts);
                $s = $finder->find($key);
            }
        }

        $tmpfile = tmpfile();
        fwrite($tmpfile, gzdecode($package->getDataArchive()));
        fseek($tmpfile, 0, SEEK_SET);

        // install section
        $data_tar  = new Archive_Minitar_Reader($tmpfile);
        $spec_name = $spec->getName() . "-" . $spec->getVersion();
        @mkdir(sprintf("%s/%s-%s", $this->gum_lib, $spec->getName(), $spec->getVersion()));
        foreach ($spec->getFiles() as $file) {
            if (strpos($file, "/") !== false) {
                // we can't detect it.
                $dirs = explode("/", $file);
                array_pop($dirs);

                $tmpdir = array();
                foreach ($dirs as $dir) {
                    $tmpdir[] = $dir;
                    if (!file_exists("{$this->gum_lib}/$spec_name/" . join("/", $tmpdir))) {
                        @mkdir("{$this->gum_lib}/$spec_name/" . join("/", $tmpdir));
                    }
                }
            }

            $this->getLogger()->log("  copying file `$file` into {$this->gum_lib}/$spec_name");
            file_put_contents(join(DIRECTORY_SEPARATOR, array($this->gum_lib, $spec_name, $file)), $data_tar->getEntry($file)->getContent());
        }

        foreach ($spec->getExecutables() as $file) {
            if (empty($file)) {
                continue;
            }

            file_put_contents("{$this->gum_bin}/$file", $data_tar->getEntry("bin/".$file)->getContent());
            chmod("{$this->gum_bin}/$file", 0755);
        }

        fclose($tmpfile);

        file_put_contents(join(DIRECTORY_SEPARATOR, array($this->gum_specs, "{$spec_name}.gumspec")), $spec_src);
        $this->getLogger()->log("# %s-%s has been installed.", $spec->getName(), $spec->getVersion());
    }

    public function execute($target, $opts = array())
    {
        //sugar/{bin,cache,doc,gums,specifications}
        $home = getenv("HOME");
        $sugar_home  = "$home/.gum";
        $sugar_cache = $sugar_home . "/cache";

        $logger = Gum_Logger::getInstance();
        if (pathinfo($target, PATHINFO_EXTENSION) == "gum") {
            copy($target, $sugar_cache . "/$target");
        } else {
            $specs = array();
            if (!$this->hasSpec($opts['source'])) {
                $logger->log("fetching latest specs...");
                $data = file_get_contents(rtrim($opts['source'], "/") . "/specs.1.0");

                if ($data) {
                    $specs = json_decode($data);
                    $logger->log("  okay, loaded specs.");
                    $this->addSpecs($opts['source'], $specs);
                }
            }

            $result = array();
            foreach ($this->getSpecs() as $specs) {
                foreach ($specs as $spec) {
                    if ($spec[0] == $target) {
                        $result[] = $spec;
                    }
                }
            }

            // for now. install latest gum only
            $spec = array_pop($result);
            if (!empty($spec[0])) {
                $logger->log("found %s-%s. downloading file.", $spec[0], $spec[1]);
                $this->downloadGumFile($target, $opts['source'] . "/gums/" . $spec[0] . "-" . $spec[1] . ".gum");
            }
        }

        $this->installGum($target, $opts);
    }

    public static function run($target, $opts = array())
    {
        $instance = new Gum_Command_Install();
        $instance->execute($target, $opts);
    }
}
