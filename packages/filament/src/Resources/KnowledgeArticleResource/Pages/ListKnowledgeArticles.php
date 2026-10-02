<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\KnowledgeArticleResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Odden\Filament\Resources\KnowledgeArticleResource;

class ListKnowledgeArticles extends ListRecords
{
    protected static string $resource = KnowledgeArticleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
