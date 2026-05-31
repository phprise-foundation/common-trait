<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use DateTimeInterface;
use Phprise\Common\Contract\Arrayable;
use ReflectionClass;
use ReflectionProperty;

trait IsArrayable
{
    private static array $reflectionCache = [];

    #[\Override]
    public function toArray(): array
    {
        $reflectionClass = self::getReflectionClass(static::class);
        $properties = $reflectionClass->getProperties();

        return $this->extractProperties($properties);
    }

    private static function getReflectionClass(string $className): ReflectionClass
    {
        if (!isset(self::$reflectionCache[$className])) {
            self::$reflectionCache[$className] = new ReflectionClass($className);
        }

        return self::$reflectionCache[$className];
    }

    private function extractProperties(array $properties): array
    {
        $data = [];
        foreach ($properties as $property) {
            $this->extractProperty($property, $data);
        }
        return $data;
    }

    private function extractProperty(ReflectionProperty $property, array &$data): void
    {
        if (!$property->isInitialized($this)) {
            return;
        }

        $name = $property->getName();
        $value = $property->getValue($this);

        if ($value instanceof Arrayable) {
            $data[$name] = $value->toArray();
            return;
        }

        if ($value instanceof DateTimeInterface) {
            $data[$name] = $this->dateFormat($value);
            return;
        }

        $data[$name] = $value;
    }

    private function dateFormat(DateTimeInterface $date): string
    {
        return $date->format(\DateTime::RFC3339);
    }
}
