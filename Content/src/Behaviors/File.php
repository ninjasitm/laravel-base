<?php

namespace Nitm\Content\Behaviors;

class File extends \October\Rain\Extension\ExtensionBase
{
    public function attributesToArray()
    {
        echo 'Hello from '.get_called_class();
        exit;
    }
}
