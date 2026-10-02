<?php

declare(strict_types=1);

namespace Odden\Filament\Resources\CompanyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Odden\Filament\Resources\CompanyResource;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
