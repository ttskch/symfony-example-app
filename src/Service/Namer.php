<?php

declare(strict_types=1);

namespace App\Service;

class Namer
{
    public function beautify(?string $name): ?string
    {
        return $name === null ? null : mb_convert_kana(trim(preg_replace('/( |　)+/', ' ', $name)), 'a');
    }
}
