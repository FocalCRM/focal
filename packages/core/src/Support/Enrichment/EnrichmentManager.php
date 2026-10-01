<?php

declare(strict_types=1);

namespace Focal\Core\Support\Enrichment;

use Focal\Core\Contracts\EnrichmentDriver;
use InvalidArgumentException;

class EnrichmentManager
{
    /**
     * Registered custom drivers.
     *
     * @var array<string, EnrichmentDriver|callable(): EnrichmentDriver>
     */
    protected array $drivers = [];

    /**
     * Resolve an enrichment driver by name.
     */
    public function driver(?string $name = null): EnrichmentDriver
    {
        $name ??= $this->getDefaultDriver();

        if (isset($this->drivers[$name])) {
            $driver = $this->drivers[$name];

            return is_callable($driver) ? $driver() : $driver;
        }

        if ($name === 'heuristic') {
            return app(HeuristicEnrichmentDriver::class);
        }

        throw new InvalidArgumentException("Unsupported company enrichment driver [{$name}].");
    }

    /**
     * Register a custom enrichment driver.
     *
     * @param  EnrichmentDriver|callable(): EnrichmentDriver  $driver
     */
    public function extend(string $name, EnrichmentDriver|callable $driver): self
    {
        $this->drivers[$name] = $driver;

        return $this;
    }

    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return (string) config('focal-core.enrichment.driver', 'heuristic');
    }
}
