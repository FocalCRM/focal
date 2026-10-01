<?php

declare(strict_types=1);

arch('no debug functions are left in the code')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();
