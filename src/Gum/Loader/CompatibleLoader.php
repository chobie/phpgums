<?php
class Gum_Loader_CompatibleLoader
{
    public static function load($orig_spec)
    {
        $spec_content = $orig_spec;
        if (!preg_match("/^<\?(php)?/",$spec_content)) {
            $spec_content = '<?php' . PHP_EOL . $spec_content;
        }

        $buffer = "";
        $tokens = token_get_all($spec_content);
        foreach ($tokens as $token) {
            if ($token == "[") {
                $token = "array(";
            } else if ($token == "]") {
                $token = ")";
            }
            if (is_array($token)) {
                $buffer .= $token[1];
            } else {
                $buffer .= $token;
            }
        }

        if (preg_match('/Gum::Specification\((function\(.+?\})\);/s', $buffer, $match, PREG_OFFSET_CAPTURE)) {
            $replacement = sprintf("'lambda_gumspec_func'");
            $new_buffer = preg_replace("/^function\(/", "function lambda_gumspec_func(", trim($match[1][0])) . PHP_EOL;
            $new_buffer .= substr_replace($buffer, $replacement, $match[1][1], strlen($match[1][0]));
            $new_buffer .= PHP_EOL;
            $new_buffer .= PHP_EOL;
        }
        return $buffer;
    }
}