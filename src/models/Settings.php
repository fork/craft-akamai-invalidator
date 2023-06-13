<?php

namespace fork\akamaiinvalidator\models;

use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    public bool $invalidateOnSave = true;

    public bool $enableInvalidateAll = false;

    public string $network = 'staging';

    public string $edgeRcSection = 'default';

    public string $edgeRcPath = '@root/.edgerc';

    public function getInvalidateOnSave(): bool
    {
        return App::parseBooleanEnv($this->invalidateOnSave);
    }

    public function getEnableInvalidateAll(): bool
    {
        return App::parseBooleanEnv($this->enableInvalidateAll);
    }

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
            ['invalidateOnSave', 'boolean'],
            ['enableInvalidateAll', 'boolean'],
            ['network', 'string'],
            ['edgeRcSection', 'string'],
            ['edgeRcPath', 'string'],
        ];
    }
}
