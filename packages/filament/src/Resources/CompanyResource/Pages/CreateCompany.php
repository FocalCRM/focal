<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CompanyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\CompanyResource;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;
}
