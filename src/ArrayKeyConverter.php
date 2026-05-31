<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\ValueObject\StringObject;

trait ArrayKeyConverter
{
    protected function arrayKeysSnakeToCamel(array $array): array
    {
        $camelCaseArray = [];
        foreach ($array as $key => $value) {
            $this->convertSnakeToCamelKeyValue($camelCaseArray, $key, $value);
        }
        return $camelCaseArray;
    }

    private function convertSnakeToCamelKeyValue(array &$camelCaseArray, string|int $key, mixed $value): void
    {
        $camelCaseKey = (new StringObject((string) $key))->toCamel();
        if (is_array($value)) {
            $value = $this->arrayKeysSnakeToCamel($value);
        }
        $camelCaseArray[$camelCaseKey] = $value;
    }

    protected function arrayKeysCamelToSnake(array $array): array
    {
        $snakeCaseArray = [];
        foreach ($array as $key => $value) {
            $this->convertCamelToSnakeKeyValue($snakeCaseArray, $key, $value);
        }
        return $snakeCaseArray;
    }

    private function convertCamelToSnakeKeyValue(array &$snakeCaseArray, string|int $key, mixed $value): void
    {
        $snakeCaseKey = (new StringObject((string) $key))->toSnake();
        if (is_array($value)) {
            $value = $this->arrayKeysCamelToSnake($value);
        }
        $snakeCaseArray[$snakeCaseKey] = $value;
    }
}
