<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\Action;
use League\Csv\Reader;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->action(function (array $data): void {
                    try {
                        // Get the file path from storage
                        $filePath = Storage::disk('public')->path($data['csv']);
                        
                        if (!file_exists($filePath)) {
                            throw new \Exception('File not found');
                        }

                        $csv = Reader::createFromPath($filePath);
                        $csv->setHeaderOffset(0);
                        
                        $headers = $csv->getHeader();
                        if (!in_array('product_name', $headers) || !in_array('product_image', $headers)) {
                            throw new \Exception('CSV must contain product_name and product_image columns');
                        }
                        
                        $records = $csv->getRecords();
                        $importCount = 0;
                        
                        foreach ($records as $record) {
                            static::$resource::getModel()::create([
                                'product_name' => $record['product_name'],
                                'product_image' => $record['product_image'],
                                'is_active' => true,
                            ]);
                            $importCount++;
                        }
                        
                        // Delete the temporary file after import
                        Storage::disk('public')->delete($data['csv']);
                        
                        Notification::make()
                            ->title("Successfully imported {$importCount} products")
                            ->success()
                            ->send();
                            
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->form([
                    \Filament\Forms\Components\FileUpload::make('csv')
                        ->label('CSV File')
                        ->acceptedFileTypes(['text/csv'])
                        ->required()
                        ->maxSize(5120) // 5MB max
                        ->disk('public')
                        ->directory('csv-imports') // Store in public/csv-imports
                ])
                ->modalHeading('Import Products CSV')
                ->modalDescription('Upload a CSV file containing product data. The CSV should have "product_name" and "product_image" columns.'),

            Actions\CreateAction::make(),
        ];
    }
}