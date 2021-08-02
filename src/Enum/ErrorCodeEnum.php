<?php
declare(strict_types=1);


namespace App\Enum;


class ErrorCodeEnum
{
    public const CREATION_FAILED = 1;
    public const PROPERTY_NOT_SPECIFIED = 2;
    public const NEGATIVE_AMOUNT = 3;
    public const UNKNOWN_PROPERTY = 4;
    public const NOT_FOUND = 5;
    public const CANNOT_OPEN_BAR = 6;
    public const CANNOT_CLOSE_BAR = 7;
    public const BAR_IS_FULL = 8;
    public const UNEXPECTED_PROPERTY = 9;
    public const INCORRECT_REQUEST = 10;
}