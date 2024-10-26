<?php

use App\Http\Controllers\ChartGeneratorController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Route::get('/{any}', function () {
//     return file_get_contents(public_path('react/public/index.html'));
// })->where('any', '.*');

Route::get('/symlink', function () {
    $result = Artisan::call('storage:link');
    
    if ($result === 0) {
        return 'Storage linked successfully!';
    } else {
        return 'Failed to link storage. Please check the logs for details.';
    }
});

Route::get('/', [ChartGeneratorController::class, 'index'])->name('chart.generator');
Route::post('/chart/generate', [ChartGeneratorController::class, 'generateChart'])->name('chart.generate');
Route::get('/admin/products', [ChartGeneratorController::class, 'fetchProducts'])->name('products.fetch');
Route::get('/api/all-products', [ChartGeneratorController::class, 'getAllProducts']);
