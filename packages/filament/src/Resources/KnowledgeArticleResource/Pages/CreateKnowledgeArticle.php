<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\KnowledgeArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\KnowledgeArticleResource;

class CreateKnowledgeArticle extends CreateRecord
{
    protected static string $resource = KnowledgeArticleResource::class;
}
