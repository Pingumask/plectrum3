<?php

namespace Pingumask\Plectrum\Model;

class Config
{
    private static mixed $config = null;
    public static function get(string $section, string $line): ?string
    {
        if (is_null(self::$config)) {
            self::$config = parse_ini_file("../config.ini", true, INI_SCANNER_NORMAL);
        }
        if (self::$config === false) {
            return null;
        }
        return self::$config[$section][$line] ?? null;
    }
}
