<?php

declare(strict_types=1);

namespace Phprise\Common\Trait;

use Phprise\Common\Contract\CamelCaseJsonable;
use RuntimeException;

trait IsCamelCaseJsonable
{
    use IsCamelCaseArrayable;

    #[\Override]
    public function toCamelCaseJson(): string
    {
        $json = json_encode($this->toCamelCaseArray());

        if ($json === false) {
            throw new RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        return $json;
    }
}
