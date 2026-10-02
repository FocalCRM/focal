<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CrmListResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Enums\ListType;
use Focal\Core\Models\CrmList;
use Focal\Filament\Resources\CrmListResource;
use Focal\Filament\Support\FocalAuthorization;

class ViewCrmList extends ViewRecord
{
    protected static string $resource = CrmListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Members')
                ->icon(Heroicon::ArrowPath)
                ->color('primary')
                ->visible(fn (): bool => $this->getRecord() instanceof CrmList && $this->getRecord()->type === ListType::Active)
                ->authorize(FocalAuthorization::forRecord('update', CrmListResource::class))
                ->action(function (): void {
                    /** @var CrmList $record */
                    $record = $this->getRecord();
                    $synced = $record->syncActiveMembers();

                    Notification::make()
                        ->title('List Synchronized')
                        ->body("Criteria evaluated and {$synced} members updated.")
                        ->success()
                        ->send();
                }),
            EditAction::make(),
        ];
    }
}
