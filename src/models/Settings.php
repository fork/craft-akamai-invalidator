<?php

namespace fork\akamaiinvalidator\models;

use craft\base\Model;

class Settings extends Model
{
    public $network = 'staging';

    public function defineRules(): array
    {
        return [
            ['network', 'string'],
        ];
    }
}
