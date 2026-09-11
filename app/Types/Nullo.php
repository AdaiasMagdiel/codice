<?php

namespace App\Types;

use Override;

class Nullo implements Type
{
    #[Override]
    public function __toString()
    {
        return 'nullo';
    }
}
