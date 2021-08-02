<?php
declare(strict_types=1);


namespace App\Validator;


use App\Enum\ErrorCodeEnum;
use RuntimeException;

class BarValidator
{
    public function checkBeforeCreation(array $inputProperties)
    {
        $inputKeys = array_keys($inputProperties);

        $allBarsProperties = [
            "title",
            "capacity",
            "amountOfVisitors",
            "amountOfBartenders",
            "currentGenre",
            "status",
            "repertoire",
            "visitors"
        ];
        $necessaryBarsProperties = ["title", "capacity", "repertoire"];

        foreach ($inputKeys as $inputKey) {
            if (!in_array($inputKey, $allBarsProperties)) {
                throw new RuntimeException(
                    'Unknown properties. Expected properties are: `title`, `capacity`, `amountOfVisitors`, 
                    `amountOfBartenders`, `currentGenre`, `status`, `repertoire`, `visitors`',
                    ErrorCodeEnum::UNKNOWN_PROPERTY
                );
            }

            if (!in_array($inputKey, $necessaryBarsProperties)) {
                throw new RuntimeException(
                    'Not enough properties. Expected properties: `title`, `capacity`, `repertoire`',
                    ErrorCodeEnum::CREATION_FAILED
                );
            }
        }
    }
}