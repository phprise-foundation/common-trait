<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\Jsonable;
use RuntimeException;

trait IsJsonable
{
    use IsArrayable;

    #[\Override]
    public function toJson(): string
    {
        $json = json_encode($this->toArray());

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }
}
