<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\KnowledgeArticleResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\KnowledgeArticleResource;

class CreateKnowledgeArticle extends CreateRecord
{
    protected static string $resource = KnowledgeArticleResource::class;
}
