<?php
class Gum_Specification
{
    public $name;
    public $version;
    public $authors;
    public $email;
    public $homepage;
    public $summary;
    public $description;
    public $files;
    public $test_files;
    public $executables;
    public $require_paths;
    public $develop_dependnecies;
    public $dependencies = array();
    public $licences;
    public $post_instal_message;
    public $autoload = array();

    public $specification_version;
    public $phpsugar_version;
    public $required_phpsugar_version;

    public function addDevelopmentDependency($sugar_name, $version = null)
    {
    }

    public function addAutoload($name, $path)
    {
        $this->autoload[$name] = $path;
    }

    public function addDependency($sugar_name, $version = null)
    {
        $this->dependencies[$sugar_name] = $version;
    }

    public function getDependencies()
    {
        return $this->dependencies;
    }


    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }


    public function getExecutables()
    {
        return $this->executables;
    }


    public function getTestFiles()
    {
        return $this->test_files;
    }

    public function getFiles()
    {
        $result = array();
        foreach ($this->files as $file) {
            if (!empty($file)) {
                $result[] = $file;
            }
        }

        return $result;
    }

    public function getEmails()
    {
        return $this->email;
    }

    public function getAuthors()
    {
        return $this->authors;
    }

    public function getAutoload()
    {
        return $this->autoload;
    }


    public function outputSpec()
    {
        ob_start();
        ?>
        Gum::Specification(function($s){
        $s->name          = "<?php echo $this->getName();?>";
        $s->version       = "<?php echo $this->getVersion();?>";
        $s->authors       = array(
        <?php
        foreach ($this->getAuthors() as $author) {
            echo "            \"$author\",\n";
        }
        ?>

        );
        $s->email         = array(
        <?php
        foreach ($this->getEmails() as $author) {
            echo "            \"$author\",\n";
        }
        ?>

        );
        $s->homepage      = "<?php echo $this->homepage; ?>";
        $s->summary       = "TODO";
        $s->description   = "Description";

        $s->files         = array(
        <?php
        foreach ($this->getFiles() as $file) {
            echo "            \"$file\",\n";
        }
        ?>

        );
        $s->test_files    = array(
        <?php
        foreach ($this->getTestFiles() as $file) {
            echo "            \"$file\",\n";
        }
        ?>

        );
        $s->executables   = array(
        <?php
        foreach ($this->getExecutables() as $file) {
            echo "            \"$file\",\n";
        }
        ?>

        );
        $s->require_paths = array("lib");

        $s->addDevelopmentDependency("phpunit");

        <?php
        foreach ($this->getDependencies() as $key => $val) {
            echo "            \$s->addDependency(\"$key\", \"$val\");";
        }
        ?>



        <?php
        foreach ($this->getAutoload() as $name => $path) {
            echo "       \$s->addAutoload(\"$name\", \"$path\");";
        }
        ?>
        });
        <?php

        return ob_get_clean();
    }
}
