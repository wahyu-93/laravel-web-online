<?php

namespace App\Filament\Resources\PricingResource\Pages;

use App\Filament\Resources\PricingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManagePricings extends ManageRecords
{
    protected static string $resource = PricingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
