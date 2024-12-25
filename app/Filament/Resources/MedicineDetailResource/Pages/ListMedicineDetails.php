<?php

namespace App\Filament\Resources\MedicineDetailResource\Pages;

use App\Filament\Resources\MedicineDetailResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMedicineDetails extends ListRecords
{
    protected static string $resource = MedicineDetailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
