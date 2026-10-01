<?php

declare(strict_types=1);

namespace Focal\Core\Enums;

enum AssociationCardinality: string
{
    case ManyToMany = 'many_to_many';
    case OneToMany = 'one_to_many';
    case OneToOne = 'one_to_one';

    /**
     * Get a human-readable label for the cardinality.
     */
    public function label(): string
    {
        return match ($this) {
            self::ManyToMany => 'Many to Many',
            self::OneToMany => 'One to Many',
            self::OneToOne => 'One to One',
        };
    }
}
