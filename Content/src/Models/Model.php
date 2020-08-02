<?php

namespace Nitm\Content\Models;

use Nitm\Content\Traits\HasFaqs;
use Nitm\Content\Traits\HasDeliverables;
use Nitm\Content\Traits\ChatForContent;

class Model extends BaseModel
{
    use HasFaqs,
        HasDeliverables,
        ChatForContent;
}
