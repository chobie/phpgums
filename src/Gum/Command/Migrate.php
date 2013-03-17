<?php
class Gum_Command_Migrate
{
    public function __construct()
    {
    }

    public static function run($file, $opts = array())
    {
        $composer = json_decode(file_get_contents($file), true);

        $spec = new Gum_Specification();
        $spec->setName(str_replace("/", "-", $composer['name']));
        $spec->setDescription($composer['description']);
        $spec->setHomepage($composer['homepage']);
        $spec->addLicense($composer['license']);
        foreach ($composer['authors'] as $author) {
            $spec->addAuthor($author['name']);
            if (!empty($author['email'])) {
                $spec->addEmail($author['email']);
            }
        }
        foreach ($composer['autoload'] as $type => $map) {
            foreach ($map as $key => $path) {
                $spec->addAutoload($key, $path);
            }
        }

        echo $spec->outputSpec(array(
            "version" => $opts['version'],
            "files" => 'split("\n", `git ls-files`);',
            "test_files" => 'split("\n", `git ls-files -- {test,spec,features}/*`);',
            "executables" => 'array_map("basename", split("\n", `git ls-files -- bin/*`));',
        ));
    }
}
