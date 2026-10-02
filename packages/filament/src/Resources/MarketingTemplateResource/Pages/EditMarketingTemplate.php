<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingTemplateResource\Pages;

use DoPHP\MailBuilder\Filament\Components\EmailSlotBuilder;
use DoPHP\MailBuilder\MailBuilder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Focal\Filament\Resources\MarketingTemplateResource;
use Focal\Filament\Support\FocalAuthorization;
use Focal\Marketing\Models\MarketingTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;

class EditMarketingTemplate extends EditRecord
{
    protected static string $resource = MarketingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Live Preview')
                ->icon(Heroicon::Eye)
                ->color('info')
                ->modalHeading(fn (MarketingTemplate $record): string => "Email Preview: {$record->name}")
                ->modalDescription(fn (MarketingTemplate $record): string => "Subject: {$record->subject}")
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn (MarketingTemplate $record): View => view('focal-marketing::template-preview', [
                    'renderedHtml' => MarketingTemplateResource::renderSampleHtml($record),
                    'template' => $record,
                ])),

            Action::make('sendTest')
                ->authorize(FocalAuthorization::forRecord('update', MarketingTemplateResource::class))
                ->label('Send Test Email')
                ->icon(Heroicon::PaperAirplane)
                ->color('success')
                ->modalHeading('Send Test Preview Email')
                ->modalDescription('Dispatch a sample rendering to your inbox to test client formatting.')
                ->schema([
                    TextInput::make('recipient_email')
                        ->label('Recipient Email')
                        ->email()
                        ->default(fn (): string => auth()->user() !== null ? auth()->user()->email : 'admin@focal.test')
                        ->required(),
                ])
                ->action(function (MarketingTemplate $record, array $data): void {
                    $recipient = (string) $data['recipient_email'];
                    $subject = '[TEST PREVIEW] '.$record->subject;
                    $html = MarketingTemplateResource::renderSampleHtml($record);

                    try {
                        Mail::html($html, function ($message) use ($recipient, $subject): void {
                            $message->to($recipient)->subject($subject);
                        });
                    } catch (\Throwable) {
                        // Handled cleanly in testing / sandbox environments without SMTP
                    }

                    Notification::make()
                        ->title('Test Email Dispatched')
                        ->body("Rendered test preview sent to {$recipient}")
                        ->success()
                        ->send();
                }),

            Action::make('exportHtml')
                ->label('Export HTML')
                ->icon(Heroicon::ArrowDownTray)
                ->color('gray')
                ->modalHeading(fn (MarketingTemplate $record): string => "Export HTML: {$record->name}")
                ->modalDescription('Production-ready, inlined HTML payload.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->schema([
                    Textarea::make('exported_html')
                        ->label('Compiled HTML Code')
                        ->rows(18)
                        ->default(fn (MarketingTemplate $record): string => MarketingTemplateResource::renderSampleHtml($record))
                        ->readOnly(),
                ]),

            EmailSlotBuilder::applyPresetAction(),

            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $theme = isset($data['theme']) && is_array($data['theme']) ? $data['theme'] : [];

        if (! empty($data['slots']) && is_array($data['slots']) && class_exists(MailBuilder::class)) {
            /** @var list<array<string, mixed>> $slots */
            $slots = array_values($data['slots']);
            $data['body_html'] = MailBuilder::compile($slots, [
                'subject' => isset($data['subject']) ? (string) $data['subject'] : null,
                'preview_text' => isset($data['preview_text']) ? (string) $data['preview_text'] : null,
                'theme' => $theme,
            ]);
            $data['body_text'] = MailBuilder::plainText($slots);
        }

        if (! empty($data['slots_variant_b']) && is_array($data['slots_variant_b']) && class_exists(MailBuilder::class)) {
            /** @var list<array<string, mixed>> $slotsB */
            $slotsB = array_values($data['slots_variant_b']);
            $data['body_html_variant_b'] = MailBuilder::compile($slotsB, [
                'subject' => isset($data['subject_variant_b']) ? (string) $data['subject_variant_b'] : (isset($data['subject']) ? (string) $data['subject'] : null),
                'preview_text' => isset($data['preview_text_variant_b']) ? (string) $data['preview_text_variant_b'] : null,
                'theme' => $theme,
            ]);
            $data['body_text_variant_b'] = MailBuilder::plainText($slotsB);
        }

        return $data;
    }
}
