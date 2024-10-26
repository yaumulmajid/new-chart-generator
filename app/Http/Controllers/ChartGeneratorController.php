<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\WordChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class ChartGeneratorController extends Controller
{
    public function index()
    {
        return view('chart-generator');
    }

    public function fetchProducts()
    {
        try {
            // Jika menggunakan database lokal
            $products = Product::select('id', 'name', 'product_name', 'product_image')
                             ->get();
            return response()->json(['data' => $products]);
            
            // Jika menggunakan API eksternal
            /*
            $response = Http::get('baseurl/admin/products');
            return response()->json($response->json());
            */
        } catch (\Exception $e) {
            Log::error('Failed to fetch products', [
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to fetch products'], 500);
        }
    }

    public function generateChart(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required',
            'products.*.name' => 'required|string',
            'products.*.product_image' => 'nullable|string'
        ]);

        try {
            Log::info('Starting chart generation', [
                'site_name' => $request->site_name,
                'products' => $request->products
            ]);

            // Fetch products from database
            $products = Product::whereIn('id', collect($request->products)->pluck('id'))
                             ->get()
                             ->map(function($product) {
                                 return [
                                     'id' => $product->id,
                                     'name' => $product->name ?? $product->product_name,
                                     'product_image' => $product->product_image,
                                     'hazard_level' => $product->hazard_level ?? 'N/A',
                                     'ppe_required' => $product->ppe_required ?? 'N/A'
                                 ];
                             })
                             ->toArray();

            if (empty($products)) {
                throw new \Exception('No valid products found');
            }

            // Generate Word document
            $wordChart = new WordChart($request->site_name, $products);
            $phpWord = $wordChart->generate();

            $fileName = Str::slug($request->site_name) . '_wall_chart.docx';
            $tempFile = tempnam(sys_get_temp_dir(), 'word_') . '.docx';

            // Save document
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($tempFile);

            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception('Generated file is invalid');
            }

            Log::info('Chart generated successfully', [
                'file_size' => filesize($tempFile),
                'file_name' => $fileName
            ]);

            return response()->download($tempFile, $fileName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Chart generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'error' => true,
                'message' => app()->environment('local') 
                    ? 'Failed to generate chart: ' . $e->getMessage()
                    : 'Failed to generate chart. Please try again.'
            ], 500);
        }
    }
    public function getAllProducts()
    {
        $products = Product::all();
        return response()->json([
            'status' => 'success',
            'data' => $products
        ], Response::HTTP_OK);
    }
}