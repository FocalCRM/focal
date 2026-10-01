<?php

declare(strict_types=1);

arch('core domain remains strictly headless')
    ->expect('Focal\Core')
    ->not->toUse([
        'Filament',
        'Livewire',
    ]);

arch('core domain is fully isolated from all modular extensions')
    ->expect('Focal\Core')
    ->not->toUse([
        'Focal\Sales',
        'Focal\Service',
        'Focal\Marketing',
        'Focal\Filament',
    ]);

arch('no debug functions left in code')
    ->expect(['Focal\Core', 'Focal\Filament'])
    ->not->toUse([
        'dd',
        'dump',
        'ray',
        'var_dump',
    ]);

arch('all domain actions have an execute method')
    ->expect('Focal\Core\Actions')
    ->toHaveMethod('execute');

arch('all enums are string backed for database agnosticism')
    ->expect('Focal\Core\Enums')
    ->toBeStringBackedEnums();
