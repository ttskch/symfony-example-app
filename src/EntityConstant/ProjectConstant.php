<?php

declare(strict_types=1);

namespace App\EntityConstant;

final class ProjectConstant
{
    const STATE_INITIAL = 'ProjectConstant.STATE_INITIAL';
    const STATE_WIP = 'ProjectConstant.STATE_WIP';
    const STATE_COMPLETE = 'ProjectConstant.STATE_COMPLETE';
    const STATE_CANCELED = 'ProjectConstant.STATE_CANCELED';

    public static function getValidStates(): array
    {
        return [
            self::STATE_INITIAL,
            self::STATE_WIP,
            self::STATE_COMPLETE,
            self::STATE_CANCELED,
        ];
    }
}
