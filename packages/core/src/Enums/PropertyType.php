<?php

declare(strict_types=1);

namespace Focal\Core\Enums;

enum PropertyType: string
{
    case Text = 'text';
    case Number = 'number';
    case Boolean = 'boolean';
    case Select = 'select';
    case MultiSelect = 'multi_select';
    case Date = 'date';
    case DateTime = 'datetime';
    case Json = 'json';

    /**
     * Get a human-readable label for the property type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Text => 'Single-line Text',
            self::Number => 'Number',
            self::Boolean => 'Boolean (True/False)',
            self::Select => 'Dropdown Select',
            self::MultiSelect => 'Multiple Checkboxes',
            self::Date => 'Date Picker',
            self::DateTime => 'Date and Time',
            self::Json => 'JSON Object',
        };
    }
}
