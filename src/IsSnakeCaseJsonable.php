<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\SnakeCaseJsonable;
use RuntimeException;

trait IsSnakeCaseJsonable
{
    use IsSnakeCaseArrayable;

    #[\Override]
    public function toSnakeCaseJson(): string
    {
        $json = json_encode($this->toSnakeCaseArray());

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }
}
