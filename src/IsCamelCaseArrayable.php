<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\CamelCaseArrayable;

trait IsCamelCaseArrayable
{
    use IsArrayable, ArrayKeyConverter;

    #[\Override]
    public function toCamelCaseArray(): array
    {
        $array = $this->toArray();
        return $this->arrayKeysSnakeToCamel($array);
    }
}
