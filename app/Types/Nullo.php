<?php

namespace App\Types;

use Override;

class Nullo extends Type
{
    #[Override]
    public function __toString()
    {
        return 'nullo';
    }
}
