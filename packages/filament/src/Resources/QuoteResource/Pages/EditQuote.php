<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\QuoteResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\QuoteResource;

class EditQuote extends EditRecord
{
    protected static string $resource = QuoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
