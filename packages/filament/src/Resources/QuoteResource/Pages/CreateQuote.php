<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\QuoteResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\QuoteResource;

class CreateQuote extends CreateRecord
{
    protected static string $resource = QuoteResource::class;
}
