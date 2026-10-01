<?php

declare(strict_types=1);

namespace Focal\Core\Tests;

use Focal\Core\Enums\PropertyType;
use Focal\Core\Models\PropertyDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyDefinitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_property_definition(): void
    {
        $property = PropertyDefinition::create([
            'entity_type' => 'contact',
            'name' => 'lead_score',
            'label' => 'Lead Score',
            'type' => PropertyType::Number,
            'group_name' => 'qualification',
            'is_required' => false,
            'is_searchable' => true,
        ]);

        $this->assertDatabaseHas('focal_properties', [
            'entity_type' => 'contact',
            'name' => 'lead_score',
            'type' => PropertyType::Number->value,
        ]);

        $this->assertSame(PropertyType::Number, $property->type);
        $this->assertTrue($property->is_searchable);
    }

    public function test_can_scope_properties_for_specific_entity(): void
    {
        PropertyDefinition::create([
            'entity_type' => 'contact',
            'name' => 'job_title',
            'label' => 'Job Title',
            'type' => PropertyType::Text,
        ]);

        PropertyDefinition::create([
            'entity_type' => 'company',
            'name' => 'annual_revenue',
            'label' => 'Annual Revenue',
            'type' => PropertyType::Number,
        ]);

        $contactProps = PropertyDefinition::forEntity('contact')->get();
        $companyProps = PropertyDefinition::forEntity('company')->get();

        $this->assertCount(1, $contactProps);
        $this->assertSame('job_title', $contactProps->first()->name);

        $this->assertCount(1, $companyProps);
        $this->assertSame('annual_revenue', $companyProps->first()->name);
    }
}
