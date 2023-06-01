<?php

namespace fork\akamaiinvalidator\models;

use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    /** @var string */
    public string $network = 'staging';

    /** @var string */
    public string $edgeRcSection = 'default';

    /** @var string */
    public string $edgeRcPath = '@root/.edgerc';

    public function getNetwork(): string
    {
        return App::parseEnv($this->network);
    }

    public function getEdgeRcSection(): string
    {
        return App::parseEnv($this->edgeRcSection);
    }

    public function getEdgeRcPath(): string
    {
        return App::parseEnv($this->edgeRcPath);
    }

    public function defineRules(): array
    {
        return [
            ['network', 'string'],
            ['edgeRcSection', 'string'],
            ['edgeRcPath', 'string'],
        ];
    }
}
