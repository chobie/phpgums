<?php
class Gum_Command_Install
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

    public function downloadGumFile($target, $url)
    {
        $data = file_get_contents(rtrim($url));
        if (file_put_contents($this->sugar_cache . "/$target", $data)) {
            $this->getLogger()->log("download succeeded");
        }
    }

    public function installGum($target, $opts = array())
    {
        //sugar/{bin,cache,doc,gums,specifications}
        $home = getenv("HOME");
        $sugar_home  = "$home/.gum";
        $sugar_cache = $sugar_home . "/cache";
        $sugar_bin   = $sugar_home . "/bin";
        $sugar_tmp   = $sugar_home . "/tmp";
        $sugar_lib = $sugar_home . "/gums";
        $sugar_specs = $sugar_home . "/specifications";
        $basename = basename($target, ".gum");


        $tar  = new Archive_Minitar_Reader($sugar_cache . DIRECTORY_SEPARATOR . $target);
        $spec  = gzdecode($tar->getEntry("metadata.gz")->getContent());
        $spec  = $spec_src = Gum_Loader_CompatibleLoader::load($spec);

        eval('?>' . $spec);

        $spec = array_pop(Gum::getSpecs());
        $this->getLogger()->log("# processing %s spec.", $spec->getName());

        $home = getenv("HOME");
        $sugar_base = "$home/.gum";

        // resolve section
        $finder = new Gum_Finder($sugar_base);
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

        // tar library can't
        $tmpfile = tmpfile();
        fwrite($tmpfile, gzdecode($tar->getEntry("data.tar.gz")->getContent()));
        fseek($tmpfile, 0, SEEK_SET);
        $data_tar  = new Archive_Minitar_Reader($tmpfile);

        // install section
        $spec_name = $spec->getName() . "-" . $spec->getVersion();
        @mkdir(sprintf("%s/%s-%s", $sugar_lib, $spec->getName(), $spec->getVersion()));
        foreach ($spec->getFiles() as $file) {
            if (strpos($file, "/") !== false) {
                // we can't detect it.
                $dirs = explode("/", $file);
                array_pop($dirs);

                $tmpdir = array();
                foreach ($dirs as $dir) {
                    $tmpdir[] = $dir;
                    if (!file_exists("$sugar_lib/$spec_name/" . join("/", $tmpdir))) {
                        @mkdir("$sugar_lib/$spec_name/" . join("/", $tmpdir));
                    }
                }
            }

            $this->getLogger()->log("  copying file `$file` into $sugar_lib/$spec_name");
            file_put_contents(join(DIRECTORY_SEPARATOR, array($sugar_lib, $spec_name, $file)), $data_tar->getEntry($file)->getContent());
        }

        foreach ($spec->getExecutables() as $file) {
            if (empty($file)) {
                continue;
            }

            file_put_contents("$sugar_bin/$file", $data_tar->getEntry("bin/".$file)->getContent());
            chmod("$sugar_bin/$file", 0755);

            if (!empty($opts['copy_bin']) && $opts['copy_bin']) {
                file_put_contents("/usr/bin/$file", $data_tar->getEntry("bin/".$file)->getContent());
                chmod("/usr/bin/$file", 0755);
            }
        }

        fclose($tmpfile);

        file_put_contents(join(DIRECTORY_SEPARATOR, array($sugar_specs, "{$spec_name}.gumspec")), $spec_src);
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
            $logger->log("found %s-%s. downloading file.", $spec[0], $spec[1]);
            $this->downloadGumFile($target, $opts['source'] . "/" . "/gums/" . $spec[0] . "-" . $spec[1] . ".gum");
        }

        $this->installGum($target, $opts);
    }

    public static function run($target, $opts = array())
    {
        $instance = new Gum_Command_Install();
        $instance->execute($target, $opts);
    }
}
