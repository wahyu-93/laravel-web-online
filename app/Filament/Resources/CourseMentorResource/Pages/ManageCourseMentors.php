<?php

namespace App\Filament\Resources\CourseMentorResource\Pages;

use App\Filament\Resources\CourseMentorResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCourseMentors extends ManageRecords
{
    protected static string $resource = CourseMentorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
