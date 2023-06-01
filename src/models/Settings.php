<?php

namespace fork\akamaiinvalidator\models;

use craft\base\Model;

class Settings extends Model
{
    /** @var string */
    public string $network = 'staging';

    /** @var string */
    public string $edgeRcSection = 'default';

    /** @var string */
    public string $edgeRcPath = '@root/.edgerc';

    public function defineRules(): array
    {
        return [
            ['network', 'string'],
            ['edgeRcSection', 'string'],
            ['edgeRcPath', 'string'],
        ];
    }
}
