<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\CampaignResource\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Focal\Core\Models\Contact;
use Focal\Filament\Resources\CampaignResource;
use Focal\Filament\Resources\ContactResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Marketing\Actions\SendCampaignProofAction;
use Focal\Marketing\Enums\CampaignStatus;
use Focal\Marketing\Models\Campaign;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestEmail')
                ->label('Send Test')
                ->authorize(FocalAuthorization::forRecord('update', CampaignResource::class))
                ->icon(Heroicon::PaperAirplane)
                ->color('info')
                ->modalHeading('Send Proof / Test Email')
                ->modalDescription('Dispatch an immediate test email to internal reviewers with personalized sample merge tags.')
                ->form([
                    TextInput::make('recipient_emails')
                        ->label('Reviewer Email Address(es)')
                        ->placeholder('reviewer@example.com, marketing-team@example.com')
                        ->helperText('Comma-separated list of email addresses to receive the test broadcast.')
                        ->default(fn (): string => auth()->user()->email ?? 'test@focal.test')
                        ->required(),
                    Select::make('sample_contact_id')
                        ->label('Simulate Merge Tags As Contact (Optional)')
                        ->options(fn (): array => FocalAuthorization::query(ContactResource::class, Contact::class)->limit(50)->pluck('first_name', 'id')->map(function ($name, $id): string {
                            $contact = Contact::find($id);

                            return "{$name} {$contact?->last_name} ({$contact?->email})";
                        })->all())
                        ->searchable()
                        ->helperText('Uses this contact\'s real CRM attributes (name, company) for merge tag substitutions.'),
                ])
                ->action(function (array $data): void {
                    /** @var Campaign $campaign */
                    $campaign = $this->getRecord();
                    /** @var Contact|null $sampleContact */
                    $sampleContact = ! empty($data['sample_contact_id']) ? FocalAuthorization::query(ContactResource::class, Contact::class)->find($data['sample_contact_id']) : null;
                    $result = app(SendCampaignProofAction::class)->execute($campaign, (string) $data['recipient_emails'], $sampleContact);

                    if ($result['success']) {
                        Notification::make()
                            ->title('Test Email Dispatched')
                            ->body($result['message'])
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Failed to Dispatch Test')
                            ->body($result['message'])
                            ->danger()
                            ->send();
                    }
                }),
            Action::make('duplicate')
                ->label('Duplicate')
                ->authorize(fn (): bool => FocalAuthorization::allows('create', Campaign::class, CampaignResource::class))
                ->icon(Heroicon::DocumentDuplicate)
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Duplicate Campaign')
                ->modalDescription('Create a draft copy of this campaign with all targeting, UTM tracking, and template configurations preserved.')
                ->action(function (): void {
                    /** @var Campaign $record */
                    $record = $this->getRecord();
                    $replica = $record->replicate([
                        'sent_at',
                        'delivered_count',
                        'total_recipients',
                        'unique_opens_count',
                        'unique_clicks_count',
                        'bounces_count',
                        'unsubscribes_count',
                        'ab_winner_variant',
                    ]);
                    $replica->name = "Copy of {$record->name}";
                    $replica->status = CampaignStatus::Draft;
                    $replica->sent_at = null;
                    $replica->delivered_count = 0;
                    $replica->total_recipients = 0;
                    $replica->unique_opens_count = 0;
                    $replica->unique_clicks_count = 0;
                    $replica->bounces_count = 0;
                    $replica->unsubscribes_count = 0;
                    $replica->save();

                    Notification::make()
                        ->title('Campaign Duplicated')
                        ->body("Created draft '{$replica->name}'.")
                        ->success()
                        ->send();

                    $this->redirect(CampaignResource::getUrl('edit', ['record' => $replica]));
                }),
            DeleteAction::make(),
        ];
    }
}
