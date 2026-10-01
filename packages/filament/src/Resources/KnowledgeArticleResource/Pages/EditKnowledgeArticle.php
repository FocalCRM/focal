<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\KnowledgeArticleResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Focal\Filament\Resources\KnowledgeArticleResource;

class EditKnowledgeArticle extends EditRecord
{
    protected static string $resource = KnowledgeArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
