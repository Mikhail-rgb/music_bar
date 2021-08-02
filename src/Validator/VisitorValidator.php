<?php
declare(strict_types=1);


namespace App\Validator;


use App\Enum\ErrorCodeEnum;
use RuntimeException;

class VisitorValidator
{

    public function checkBeforeCreation(array $inputProperties)
    {
        $allVisitorsProperties = ["name", "surname", "money", "status", "genre"];
        $necessaryVisitorProperties = ["money", "genre"];

        if (!in_array($inputProperties, $allVisitorsProperties)) {
            throw new RuntimeException(
                'Unknown properties. Expected properties are: `name`, `surname`, `money`, `status`, `genre`',
                ErrorCodeEnum::UNKNOWN_PROPERTY
            );
        }

        if (!in_array($inputProperties, $necessaryVisitorProperties)) {
            throw new RuntimeException(
                'Not enough properties. Expected properties: `money`, `genre`',
                ErrorCodeEnum::CREATION_FAILED
            );
        }
    }
}