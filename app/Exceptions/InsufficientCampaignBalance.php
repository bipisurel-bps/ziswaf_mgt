<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientCampaignBalance extends RuntimeException
{
    public function __construct(public readonly int $remaining, public readonly int $requested)
    {
        parent::__construct(sprintf(
            'Penyaluran Rp %s melebihi sisa dana kampanye Rp %s.',
            number_format($requested, 0, ',', '.'),
            number_format($remaining, 0, ',', '.')
        ));
    }
}
