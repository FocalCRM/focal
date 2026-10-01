<?php

declare(strict_types=1);

namespace Focal\Filament\Resources\MarketingTemplateResource\Pages;

use DoPHP\MailBuilder\Filament\Components\EmailSlotBuilder;
use DoPHP\MailBuilder\MailBuilder;
use Filament\Resources\Pages\CreateRecord;
use Focal\Filament\Resources\MarketingTemplateResource;

class CreateMarketingTemplate extends CreateRecord
{
    protected static string $resource = MarketingTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EmailSlotBuilder::applyPresetAction(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
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
