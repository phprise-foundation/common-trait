<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\SnakeCaseArrayable;

trait IsSnakeCaseArrayable
{
    use IsArrayable, ArrayKeyConverter;

    #[\Override]
    public function toSnakeCaseArray(): array
    {
        $array = $this->toArray();
        return $this->arrayKeysCamelToSnake($array);
    }
}
