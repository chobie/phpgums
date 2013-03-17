<?php
class Gum_Specification
{
    public $name;
    public $version;
    public $authors = array();
    public $email = array();
    public $homepage;
    public $summary;
    public $description;
    public $files = array();
    public $test_files = array();
    public $executables = array();
    public $require_paths = array();
    public $develop_dependnecies = array();
    public $dependencies = array();
    public $licences = array();
    public $post_instal_message;
    public $autoload = array();

    public $specification_version;
    public $gum_version;
    public $required_gum_version;

    public function addDevelopmentDependency($sugar_name, $version = null)
    {
    }

    public function addEmail($email)
    {
        $this->email[] = $email;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function addAuthor($author)
    {
        $this->authors[] = $author;
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

    public function setName($name)
    {
        $this->name = $name;
    }

    public function addLicense($license)
    {
        $this->licences[] = $license;
    }

    public function setHomepage($homepage)
    {
        $this->homepage = $homepage;
    }

    public function getHomepage()
    {
        return $this->homepage;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
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


    public function outputSpec($overrides = array())
    {
        ob_start();
        ?>
Gum::Specification(function($s){
    $s->name        = "<?php echo $this->getName();?>";
    $s->version     = "<?php echo (empty($overrides['version'])) ? $this->getVersion(): $overrides['version'];?>";
    $s->authors     = array(
<?php
    foreach ($this->getAuthors() as $author) {
        echo "        \"$author\",\n";
    }
    ?>
    );
    $s->email       = array(
<?php
    foreach ($this->getEmails() as $author) {
        echo "        \"$author\",\n";
    }
    ?>
    );
    $s->homepage    = "<?php echo $this->homepage; ?>";
    $s->summary     = "<?php echo $this->summary; ?>";
    $s->description = "<?php echo $this->description; ?>";

<?php
if (!empty($overrides['files'])) {
    echo "    \$s->files       = {$overrides['files']}\n";
} else {
?>
    $s->files         = array(
<?php
    foreach ($this->getFiles() as $file) {
        echo "            \"$file\",\n";
    }
    echo "    );\n";
}
    ?>
<?php
    if (!empty($overrides['test_files'])) {
        echo "    \$s->test_files  = {$overrides['test_files']}\n";
} else {
?>
    $s->test_files  = array(
<?php
    foreach ($this->getTestFiles() as $file) {
        echo "            \"$file\",\n";
    }
    echo "    );\n";
}
?>
<?php
if (!empty($overrides['executables'])) {
    echo "    \$s->executables = {$overrides['executables']}\n";
} else {
?>
    $s->executables = array(
<?php
    foreach ($this->getExecutables() as $file) {
        echo "            \"$file\",\n";
    }
    echo "    );\n";
}
?>

<?php
    foreach ($this->getDependencies() as $key => $val) {
        echo "        \$s->addDependency(\"$key\", \"$val\");";
    }
    ?>

<?php
    foreach ($this->getAutoload() as $name => $path) {
        echo "    \$s->addAutoload(\"$name\", \"$path\");";
    }
    ?>

});
<?php

        return ob_get_clean();
    }

    public function toYaml()
    {
        $buffer = "";

        $buffer .= sprintf("name: %s\n", $this->getName());
        $buffer .= sprintf("version: %s\n", $this->getVersion());
        $buffer .= sprintf("authors:\n");
        foreach ($this->getAuthors() as $author) {
            $buffer .= sprintf("  - %s\n", $author);
        }
        $buffer .= sprintf("email:\n");
        foreach ($this->getEmails() as $author) {
            $buffer .= sprintf("  - %s\n", $author);
        }
        $buffer .= sprintf("homepage: %s\n", $this->homepage);
        $buffer .= sprintf("summary: |\n");
        foreach (preg_split("/\r?\n/", $this->summary) as $line) {
            $buffer .= "  " . $line . PHP_EOL;
        }
        $buffer .= sprintf("files:\n");
        foreach ($this->getFiles() as $file) {
            $buffer .= sprintf("  - %s\n", $file);
        }
        $buffer .= sprintf("test_files:\n");
        foreach ($this->getTestFiles() as $file) {
            $buffer .= sprintf("  - %s\n", $file);
        }
        $buffer .= sprintf("executables:\n");
        foreach ($this->getExecutables() as $file) {
            $buffer .= sprintf("  - %s\n", $file);
        }
        $buffer .= sprintf("require_paths:\n");
        foreach ($this->require_paths as $path) {
            $buffer .= sprintf("  - %s\n", $path);
        }
        $buffer .= sprintf("develop_dependencies:\n");
        foreach ($this->develop_dependnecies as $dependency => $version) {
            $buffer .= sprintf("  %s: '%s'\n", $dependency,$version);
        }
        $buffer .= sprintf("dependencies:\n");
        foreach ($this->dependencies as $dependency => $version) {
            $buffer .= sprintf("  %s: '%s'\n", $dependency, $version);
        }
        $buffer .= sprintf("licenses:\n");
        foreach ((array)$this->licences as $license) {
            $buffer .= sprintf("  - %s\n", $license);
        }
        $buffer .= sprintf("post_install_message: |\n");
        foreach (preg_split("/\r?\n/", $this->post_instal_message) as $line) {
            $buffer .= "  " . $line . PHP_EOL;
        }
        $buffer .= sprintf("autoload:\n");
        foreach ($this->autoload as $src => $path) {
            $buffer .= sprintf("  %s: %s\n", $src, $path);
        }

        $buffer .= sprintf("specification_version: %s\n", $this->specification_version);
        $buffer .= sprintf("gum_version: %s\n", $this->gum_version);
        $buffer .= sprintf("required_gum_version: %s\n", $this->required_gum_version);

        return $buffer;
    }

}
