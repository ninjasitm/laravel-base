<?php

namespace Nitm\Content\Behaviors;

class BaseAction extends \October\Rain\Extension\ExtensionBase
{
    public $owner;

    public function __construct($owner)
    {
        if (!$owner) {
            throw new \Exception('An owner is needed for this behavior');
        }
        $this->owner = $owner;
    }

    public function canAttach()
    {
        return strpos(get_class($this->owner), 'Related') === false;
    }
}
