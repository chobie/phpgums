<?php
class Gum_Command_Build
{
    public static function run($spec)
    {
        if (!is_readable($spec)) {
            throw new Exception("can't read {$spec}");
        }

        $spec_content = $orig_spec = file_get_contents($spec);
        $buffer = Gum_Loader_CompatibleLoader::load($spec_content);

        eval('?>' . $buffer);
        $spec = array_pop(Gum::getSpecs());

        $tar = new Archive_Minitar_Writer("{$spec->getName()}-{$spec->getVersion()}.gum", array("gzip" => true));

        // Todo: should use php://memory at here. but it seems can't use with gzopen, ughh.
        $data_tar = new Archive_Minitar_Writer("data.tar.gz");
        foreach ($spec->getFiles() as $file) {
            $data_tar->addFile($file);
        }

        $tar->addContent("data.tar.gz", (string)$data_tar);
        $tar->addContent("metadata.gz", gzencode($spec->outputSpec()));

        $tar->write();
    }
}