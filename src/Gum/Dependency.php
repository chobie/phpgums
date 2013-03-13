<?php
class Gum_Dependency
{
    const OP_EQUALS = 1;
    const OP_NOT_EQUALS = 2;
    const OP_GREATER_THAN = 3;
    const OP_GREATER_THAN_OR_EQUAL = 4;
    const OP_LESS_THAN = 5;
    const OP_LESS_THAN_OR_EQUAL = 6;
    const OP_APPROXIMATELY_GREATER_THAN = 7;


    const STATE_PARSE_OP = 0x00;
    const STATE_PARSE_VERSION = 0x01;

    protected $name;
    protected $dependencies = array();

    private function scan_state_get($c)
    {
        $c = (string)$c;
        if (ctype_digit($c)) {
            return 0;
        } else if (ctype_alpha($c)) {
            return 1;
        } else {
            return 2;
        }
    }

    private function parse_version_word($vsi)
    {
        $start = $end = $size =0;
        $max = strlen($vsi);
        $res = array();
        while($start < $max) {
            $current_state = $this->scan_state_get($vsi[$start]);
            if ($current_state == 2) {
                $start++;
                $end = $start;
                continue;
            }

            do {
                $end++;
                $next_char = @$vsi[$end];
                $next_state = $this->scan_state_get($next_char);
            } while($current_state == $next_state);
            $size = $end - $start;
            $res[] = substr($vsi, $start, $size);

            $start = $end;
        }
        return $res;
    }

    public function __construct($packagename)
    {
        $this->name = $packagename;
    }

    public function parseOperator($version_text)
    {
        $size = strlen($version_text);
        $status = 0;
        $operator = 0;

        for ($i = 0; $i< $size; $i++) {
            $char = $version_text[$i];
            $next_char = null;
            if ($i+1 <= $size) {
                $next_char = $version_text[$i+1];
            }

            if ($status = self::STATE_PARSE_OP) {
                switch ($char) {
                    case " ":
                        continue;
                        break;
                    case "=":
                        $operator = self::OP_EQUALS;
                        $state = self::STATE_PARSE_VERSION;
                        break;
                    case ">":
                        if ($next_char == "=") {
                            $operator = self::OP_GREATER_THAN_OR_EQUAL;
                        } else {
                            $operator = self::OP_GREATER_THAN;
                        }
                        $state = self::STATE_PARSE_VERSION;
                        break;
                    case "<":
                        if ($next_char == "=") {
                            $operator = self::OP_LESS_THAN_OR_EQUAL;
                        } else {
                            $operator = self::OP_LESS_THAN;
                        }
                        $state = self::STATE_PARSE_VERSION;
                        break;
                    case "!":
                        if ($next_char == "=") {
                            $operator = self::OP_NOT_EQUALS;
                        } else {
                            throw new Exception("unexpected char");
                        }
                        $state = self::STATE_PARSE_VERSION;
                        break;
                    case "~":
                        if ($next_char == "=") {
                            $operator = self::OP_APPROXIMATELY_GREATER_THAN;
                        } else {
                            throw new Exception("unexpected char");
                        }
                        $state = self::STATE_PARSE_VERSION;
                        break;
                    default:
                        throw new Exception("unexpected char");
                        break;
                }
            } else if ($status = self::STATE_PARSE_VERSION) {
                $version = trim(substr($version_text, $i+1));
                $versions = $this->parse_version_word($version);

                if (count($versions) < 3) {
                    throw new Exception("version must be <Major>.<Minor>.<Patch> style.");
                }

                break;
            } else {
                throw new Exception();
            }
        }

        return array(
            "operator" => $operator,
            "version" => $version,
            "version_words" => $versions,
        );
    }

    public function addDependency($package, $version)
    {
        $this->dependencies[$package] = $this->parse_version_word($version);
    }

    public function solved($specs)
    {
    }
}