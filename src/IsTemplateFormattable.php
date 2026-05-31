<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\TemplateFormattable;
use RuntimeException;
use Stringable;

trait IsTemplateFormattable
{
    use IsArrayable;

    #[\Override]
    public function format(
        array|string $queries,
        array $aliases = [],
        string $separator = PHP_EOL,
        string $marker = ':'
    ): string {
        if (is_array($queries)) {
            $queries = implode($separator, $queries);
        }

        $data = $this->toArray();

        if (empty($aliases)) {
            $propertyNames = array_keys($data);
            $aliases = array_combine($propertyNames, $propertyNames);
        }

        uksort($aliases, fn(string $a, string $b) => strlen($b) <=> strlen($a));

        $keys = array_map(fn(string|int $key) => $marker . $key, array_keys($aliases));
        $values = array_map(fn(string $value) => $this->getAttributeAsString($value), $aliases);

        return str_replace($keys, $values, $queries);
    }

    private function getAttributeAsString(string $attributeName): string
    {
        $getter = 'get' . ucfirst($attributeName);
        if (method_exists($this, $getter)) {
            return $this->getStringValue($this->$getter());
        }

        if (property_exists($this, $attributeName)) {
            return $this->getStringValue($this->$attributeName);
        }

        throw new RuntimeException(
            sprintf('Undefined attribute %s::%s', get_class($this), $attributeName)
        );
    }

    private function getStringValue(Stringable|\DateTimeInterface|\JsonSerializable $value): string
    {
        if ($value instanceof Stringable) {
            return (string) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $this->dateFormat($value);
        }

        if ($value instanceof \JsonSerializable) {
            $json = json_encode($value);
            if ($json === false) {
                throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
            }
            return $json;
        }

        return (string) $value;
    }
}
