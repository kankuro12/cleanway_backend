<?php

namespace App\Jobs;

use App\Domain\Properties\ResolvePropertyCoordinates;
use App\Models\Property;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GeocodeProperty implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(public readonly int $propertyId) {}

    public function handle(ResolvePropertyCoordinates $resolver): void
    {
        $property = Property::query()->find($this->propertyId);

        if (! $property || ! $property->active || $property->trashed()) {
            return;
        }

        $resolver->execute($property);
    }
}
