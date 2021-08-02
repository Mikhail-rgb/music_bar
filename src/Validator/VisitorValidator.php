<?php
declare(strict_types=1);


namespace App\Validator;


use App\Enum\ErrorCodeEnum;
use RuntimeException;

class VisitorValidator
{

    public function checkBeforeCreation(array $inputProperties)
    {
        if (!isset($inputProperties['visitors'])) {
            throw new RuntimeException(
                'Incorrect format of request. Should be array `visitors`',
                ErrorCodeEnum::INCORRECT_REQUEST
            );
        }

        $propertiesForCheck = $inputProperties['visitors'];
        $inputKeys = array_keys($propertiesForCheck);

        $allVisitorsProperties = ["name", "surname", "money", "status", "genre"];
        $necessaryVisitorProperties = ["money", "genre"];

        foreach ($inputKeys as $inputKey) {
            if (!in_array($inputKey, $allVisitorsProperties)) {
                throw new RuntimeException(
                    'Unknown properties. Expected properties are: `name`, `surname`, `money`, `status`, `genre`',
                    ErrorCodeEnum::UNKNOWN_PROPERTY
                );
            }

            if (!in_array($inputKey, $necessaryVisitorProperties)) {
                throw new RuntimeException(
                    'Not enough properties. Expected properties: `money`, `genre`',
                    ErrorCodeEnum::CREATION_FAILED
                );
            }
        }
    }
}