<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Cast\TypeCoercerInterface;
use Phprise\Common\Contract\ArrayHydratable;
use ReflectionClass;
use ReflectionProperty;

trait IsArrayHydratable
{
    use ArrayKeyConverter;

    public const int USE_DEFAULTS = 0;
    public const int USE_STRICT = 1;

    private static array $defaultCoercers = [];
    private static array $reflectionCache = [];

    #[\Override]
    public static function fromArray(array $array, int $flags = self::USE_DEFAULTS, array $options = []): static
    {
        $camelCaseArray = self::arrayKeysSnakeToCamel($array);
        $reflectionClass = self::getReflectionClass(static::class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $properties = $reflectionClass->getProperties();

        foreach ($properties as $property) {
            self::hydratePropertyFromCamelArray($instance, $property, $camelCaseArray, $flags, $options);
        }

        return $instance;
    }

    private static function hydratePropertyFromCamelArray(
        object $instance,
        ReflectionProperty $property,
        array $camelCaseArray,
        int $flags,
        array $options
    ): void {
        $propertyName = $property->getName();
        $value = $camelCaseArray[$propertyName] ?? null;

        self::hydrateProperty($instance, $property, $value, $flags, $options);
    }

    private static function hydrateProperty(object $instance, ReflectionProperty $property, mixed $value, int $flags, array $options): void
    {
        if ($value === null) {
            return;
        }

        $type = $property->getType();

        if (!$type instanceof \ReflectionNamedType) {
            return;
        }

        $typeName = $type->getName();

        if (is_array($value) && is_subclass_of($typeName, ArrayHydratable::class)) {
            $value = $typeName::fromArray($value, $flags, $options);
        }

        $coercedValue = self::coerceValue($value, $type, $flags, $options);

        self::setPropertyValue($instance, $property, $coercedValue);
    }

    private static function setPropertyValue(object $object, \ReflectionProperty $property, mixed $value): void
    {
        $name = $property->getName();
        $setter = 'set' . ucfirst($name);

        if (method_exists($object, $setter)) {
            $object->$setter($value);
            return;
        }

        $property->setValue($object, $value);
    }

    private static function getReflectionClass(string $className): ReflectionClass
    {
        if (!isset(self::$reflectionCache[$className])) {
            self::$reflectionCache[$className] = new ReflectionClass($className);
        }

        return self::$reflectionCache[$className];
    }

    private static function getDefaultCoercers(): array
    {
        if (empty(self::$defaultCoercers)) {
            self::$defaultCoercers = [
                new \Phprise\Common\Cast\IntCoercer(),
                new \Phprise\Common\Cast\FloatCoercer(),
                new \Phprise\Common\Cast\BoolCoercer(),
                new \Phprise\Common\Cast\StringCoercer(),
                new \Phprise\Common\Cast\DateTimeImmutableCoercer(),
                new \Phprise\Common\Cast\DateTimeMutableCoercer(),
            ];
        }

        return self::$defaultCoercers;
    }

    private static function coerceValue(mixed $value, \ReflectionNamedType $type, int $flags, array $options): mixed
    {
        if ($value === null) {
            return null;
        }

        $typeName = $type->getName();

        if (gettype($value) === $typeName || $value instanceof $typeName) {
            return $value;
        }

        $typeCoercers = $options['typeCoercers'] ?? [];

        if ($flags === self::USE_DEFAULTS) {
            $typeCoercers = array_merge($typeCoercers, self::getDefaultCoercers());
        }

        $applicableCoercers = array_filter(
            $typeCoercers,
            fn(TypeCoercerInterface $coercer) => $coercer->supports($typeName)
        );

        return array_reduce(
            $applicableCoercers,
            fn(mixed $currentValue, TypeCoercerInterface $coercer): mixed => $coercer->coerce($currentValue),
            $value
        );
    }
}
