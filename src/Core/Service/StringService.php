<?php

namespace Core\Service;

class StringService
{
    public const WEEKDAY_LABELS = [
        1 => 'Пн',
        2 => 'Вт',
        3 => 'Ср',
        4 => 'Чт',
        5 => 'Пт',
        6 => 'Сб',
        7 => 'Вс',
    ];

    public const MONTH_LABELS = [
        1  => [ 'январь', 'января' ],
        2  => [ 'февраль', 'февраля' ],
        3  => [ 'март', 'марта' ],
        4  => [ 'апрель', 'апреля' ],
        5  => [ 'май', 'мая' ],
        6  => [ 'июнь', 'июня' ],
        7  => [ 'июль', 'июля' ],
        8  => [ 'август', 'августа' ],
        9  => [ 'сентябрь', 'сентября' ],
        10 => [ 'октябрь', 'октября' ],
        11 => [ 'ноябрь', 'ноября' ],
        12 => [ 'декабрь', 'декабря' ],
    ];

    public static function getWeekdayLabelByNumber(int $number): string
    {
        return self::WEEKDAY_LABELS[ $number ] ?? '';
    }

    public static function geMonthLabelByNumber(int $number, ?int $type = 0): string
    {
        return self::MONTH_LABELS[ $number ][ $type ] ?? '';
    }

    public static function replaceOnCdnUrl(?string $url = null): ?string
    {
        if (empty($url) || !str_contains($url, 'wp-content/uploads') || str_contains($url, 'cdn.don-m.nl')) {
            return $url;
        }

        $upload_basedir = WP_CONTENT_DIR . '/uploads';

        $check_url = str_replace([ 'https://', 'http://' ], '', $url);
        $check_url = str_replace(str_replace([
            'https://',
            'http://'
        ], '', get_option('siteurl')), '', $check_url);
        $check_url = str_replace('/wp-content/uploads/', '', $check_url);

        if (wp_get_environment_type() !== 'production' && file_exists($upload_basedir . '/' . $check_url)) {
            return $url;
        }

        return '//cdn.don-m.nl/wp-content/uploads/' . $check_url;

    }

    public static function maybeUnserialize(mixed $data): mixed
    {
        if (self::isSerialized($data)) {
            return @unserialize(trim($data));
        }

        return $data;
    }

    public static function isSerialized($data, $strict = true): bool
    {
        // If it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' === $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace     = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace) {
                return false;
            }
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3) {
                return false;
            }
            if (false !== $brace && $brace < 4) {
                return false;
            }
        }
        $token = $data[0];
        switch ($token) {
            case 's':
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
                // Or else fall through.
                // no break
            case 'a':
            case 'O':
            case 'E':
                return (bool) preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b':
            case 'i':
            case 'd':
                $end = $strict ? '$' : '';

                return (bool) preg_match("/^{$token}:[0-9.E+-]+;$end/", $data);
        }

        return false;
    }

    public static function sanitize(string $title, bool $sanitize = true): string
    {

        $title = mb_strtolower($title);

        switch (get_site_option('rtl_standard')) {
            case 'off':
                return $title;
            case 'gost':
                $title = strtr($title, [
                    "Є" => "EH",
                    "І" => "I",
                    "і" => "i",
                    "№" => "#",
                    "є" => "eh",
                    "А" => "A",
                    "Б" => "B",
                    "В" => "V",
                    "Г" => "G",
                    "Д" => "D",
                    "Е" => "E",
                    "Ё" => "JO",
                    "Ж" => "ZH",
                    "З" => "Z",
                    "И" => "I",
                    "Й" => "JJ",
                    "К" => "K",
                    "Л" => "L",
                    "М" => "M",
                    "Н" => "N",
                    "О" => "O",
                    "П" => "P",
                    "Р" => "R",
                    "С" => "S",
                    "Т" => "T",
                    "У" => "U",
                    "Ф" => "F",
                    "Х" => "KH",
                    "Ц" => "C",
                    "Ч" => "CH",
                    "Ш" => "SH",
                    "Щ" => "SHH",
                    "Ъ" => "'",
                    "Ы" => "Y",
                    "Ь" => "",
                    "Э" => "EH",
                    "Ю" => "YU",
                    "Я" => "YA",
                    "а" => "a",
                    "б" => "b",
                    "в" => "v",
                    "г" => "g",
                    "д" => "d",
                    "е" => "e",
                    "ё" => "jo",
                    "ж" => "zh",
                    "з" => "z",
                    "и" => "i",
                    "й" => "jj",
                    "к" => "k",
                    "л" => "l",
                    "м" => "m",
                    "н" => "n",
                    "о" => "o",
                    "п" => "p",
                    "р" => "r",
                    "с" => "s",
                    "т" => "t",
                    "у" => "u",
                    "ф" => "f",
                    "х" => "kh",
                    "ц" => "c",
                    "ч" => "ch",
                    "ш" => "sh",
                    "щ" => "shh",
                    "ъ" => "",
                    "ы" => "y",
                    "ь" => "",
                    "э" => "eh",
                    "ю" => "yu",
                    "я" => "ya",
                    "—" => "-",
                    "«" => "",
                    "»" => "",
                    "…" => "",
                ]);
                break;
            default:
                $title = strtr($title, [
                    "Є" => "YE",
                    "І" => "I",
                    "Ѓ" => "G",
                    "і" => "i",
                    "№" => "#",
                    "є" => "ye",
                    "ѓ" => "g",
                    "А" => "A",
                    "Б" => "B",
                    "В" => "V",
                    "Г" => "G",
                    "Д" => "D",
                    "Е" => "E",
                    "Ё" => "YO",
                    "Ж" => "ZH",
                    "З" => "Z",
                    "И" => "I",
                    "Й" => "J",
                    "К" => "K",
                    "Л" => "L",
                    "М" => "M",
                    "Н" => "N",
                    "О" => "O",
                    "П" => "P",
                    "Р" => "R",
                    "С" => "S",
                    "Т" => "T",
                    "У" => "U",
                    "Ф" => "F",
                    "Х" => "X",
                    "Ц" => "C",
                    "Ч" => "CH",
                    "Ш" => "SH",
                    "Щ" => "SHH",
                    "Ъ" => "'",
                    "Ы" => "Y",
                    "Ь" => "",
                    "Э" => "E",
                    "Ю" => "YU",
                    "Я" => "YA",
                    "а" => "a",
                    "б" => "b",
                    "в" => "v",
                    "г" => "g",
                    "д" => "d",
                    "е" => "e",
                    "ё" => "yo",
                    "ж" => "zh",
                    "з" => "z",
                    "и" => "i",
                    "й" => "j",
                    "к" => "k",
                    "л" => "l",
                    "м" => "m",
                    "н" => "n",
                    "о" => "o",
                    "п" => "p",
                    "р" => "r",
                    "с" => "s",
                    "т" => "t",
                    "у" => "u",
                    "ф" => "f",
                    "х" => "x",
                    "ц" => "c",
                    "ч" => "ch",
                    "ш" => "sh",
                    "щ" => "shh",
                    "ъ" => "",
                    "ы" => "y",
                    "ь" => "",
                    "э" => "e",
                    "ю" => "yu",
                    "я" => "ya",
                    "—" => "-",
                    "«" => "",
                    "»" => "",
                    "…" => "",
                ]);
        }

        return $sanitize ? sanitize_title_with_dashes($title, '', 'save') : $title;
    }

    public static function makeRelativeUrl(?string $url = null): ?string
    {
        return preg_replace('#https?://[^/]+/#', '/', $url);
    }

}
