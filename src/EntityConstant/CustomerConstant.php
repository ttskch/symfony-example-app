<?php

declare(strict_types=1);

namespace App\EntityConstant;

final class CustomerConstant
{
    const STATE_INITIAL = 'CustomerConstant.STATE_INITIAL';
    const STATE_WIP = 'CustomerConstant.STATE_WIP';
    const STATE_COMPLETE = 'CustomerConstant.STATE_COMPLETE';
    const STATE_FAILED = 'CustomerConstant.STATE_FAILED';

    public static function getValidStates(): array
    {
        return [
            self::STATE_INITIAL,
            self::STATE_WIP,
            self::STATE_COMPLETE,
            self::STATE_FAILED,
        ];
    }
}
