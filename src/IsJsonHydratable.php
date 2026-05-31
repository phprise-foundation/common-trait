<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\JsonHydratable;
use RuntimeException;

trait IsJsonHydratable
{
    use IsArrayHydratable;

    #[\Override]
    public static function fromJson(string $json, int $flags = self::USE_DEFAULTS, array $options = []): static
    {
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid JSON plain text.');
        }

        return static::fromArray($decoded, $flags, $options);
    }
}
