<?php
namespace GPRU;

class Settings
{
    private static $settings;
    private static $settings_filename;

    private static function getSettings()
    {
        if (isset(self::$settings)) {
            return self::$settings;
        }

        if (file_exists(@$_SERVER['HOME'].'/etc/PHP/gpru_settings.php')) {
            self::$settings_filename = @$_SERVER['HOME'].'/etc/PHP/gpru_settings.php';
        } elseif (file_exists(@$_SERVER['GPRU_SETTINGS'])) {
            self::$settings_filename = $_SERVER['GPRU_SETTINGS'];
        } else {
            throw new \ErrorException("Settings file is not defined");
        }

        require_once self::$settings_filename;
        self::$settings = $_GPRU_SETTINGS;
        return self::$settings;
    }

    public static function get($setting, $default = null)
    {
        $settings = self::getSettings();

        $parts = explode('.', $setting);
        for ($i = 0; $i < count($parts); ++$i) {
            $n = $parts[$i];
            if (isset($settings[$n])) {
                if ($i < (count($parts) - 1)) {
                    $settings = $settings[$n];
                } else {
                    return $settings[$n];
                }
            } else {
                break;
            }
        }

        if (isset($default)) {
            return $default;
        }

        throw new \ErrorException("Setting $setting is not defined in ".self::$settings_filename);
    }
}
