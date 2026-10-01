<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\QuoteResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\QuoteResource;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;
}
