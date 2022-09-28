<?php

namespace Battis\CRUD\Utilities;

class Text
{
    public static function snake_case_to_PascalCase(string $snake_case): string
    {
        return join(
            array_map(
                fn($token) => strtoupper(substr($token, 0, 1)) .
                    substr($token, 1),
                explode("_", $snake_case)
            )
        );
    }

    public static function camelCase_to_snake_case(string $camelCase): string
    {
        $snake_case = $camelCase;
        foreach (
            [
                "/([^0-9])([0-9])/", // separate numeric phrases
                "/([A-Z])([A-Z][a-z])/", // separate trailing word from acronym
                "/([^A-Z])([A-Z])/", // separate end of word from trailing word,
                "/([^_])_+([^_])/", // minimize underscores
            ]
            as $regexp
        ) {
            $snake_case = preg_replace($regexp, "$1_$2", $snake_case);
        }
        return strtolower($snake_case);
    }

    public static function pluralize(string $singular): string
    {
        switch (substr($singular, -1)) {
            case "s":
            case "x":
            case "z":
                return "{$singular}es";
            case "S":
            case "X":
            case "Z":
                return "{$singular}ES";
            case "y":
                return substr($singular, 0, strlen($singular) - 1) . "ies";
            case "Y":
                return substr($singular, 0, strlen($singular) - 1) . "IES";
            default:
                switch (substr($singular, -2)) {
                    case "sh":
                    case "Sh":
                    case "ch":
                    case "Ch":
                        return "{$singular}es";
                    case "SH":
                    case "sH":
                    case "CH":
                    case "cH":
                        return "{$singular}ES";
                    default:
                        if (
                            substr($singular, -1) ===
                            strtolower(substr($singular, -1))
                        ) {
                            return "{$singular}s";
                        } else {
                            return "{$singular}S";
                        }
                }
        }
    }
}
