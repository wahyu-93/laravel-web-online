<?php

namespace App\Filament\Resources\SectionContentResource\Pages;

use App\Filament\Resources\SectionContentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageSectionContents extends ManageRecords
{
    protected static string $resource = SectionContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
