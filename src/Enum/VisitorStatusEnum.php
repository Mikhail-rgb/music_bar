<?php
declare(strict_types=1);


namespace App\Enum;


class VisitorStatusEnum
{
    public const CAME = "came to the bar";
    public const ENTERED = "entered into the bar";
    public const GONE = "left the bar";
    public const WANTS_MUSIC = "in line to order the music";
    public const WANTS_DRINK = "want to order some drink";
    public const DRINKING = "drinking";
    public const DANCING = "dancing";
}