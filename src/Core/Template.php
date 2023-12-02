<?php

namespace Core;

class Template
{
    public static function include($template, $args = []): void
    {

        if (!empty($args) && is_array($args)) {
            extract($args);
        }

        if(function_exists('locate_template')) {
            $path = locate_template($template, false, false);
        } else {
            $path = sprintf('%s/%s', DON_THEME_PATH, $template);
        }

        include($path);

    }

    public static function get($template, $args = []): bool|string
    {
        ob_start();
        self::include($template, $args);
        return ob_get_clean();
    }

}
