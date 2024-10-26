<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\SimpleType\TblWidth;


class WordChart
{
    private $phpWord;
    private $section;
    private $siteName;
    private $products;
    private $month;
    private $year;
    private $mainTable;

    public function __construct($siteName, $products)
    {
        $this->phpWord = new PhpWord();
        $this->siteName = $siteName;
        $this->month = strtoupper(date('F')); // Dynamic month in uppercase
        $this->year = date('Y'); // Dynamic year
        $this->products = $products;
        
        // Set default font
        $this->phpWord->setDefaultFontName('Arial');
        $this->phpWord->setDefaultFontSize(11);
        
        // Add section with specific margins
        $this->section = $this->phpWord->addSection([
            'pageSizeH' => Converter::inchToTwip(11.69),
            'pageSizeW' => Converter::inchToTwip(16.54),
            'orientation' => 'portrait',
            'marginLeft' => Converter::cmToTwip(1),
            'marginRight' => Converter::cmToTwip(1),
            'marginTop' => Converter::cmToTwip(0.79),
            'marginBottom' => Converter::cmToTwip(1)
        ]);
    }

    public function generate()
    {
        $this->addHeaderContent();
        $this->addHeader();
        
        $this->addProductRows();
        $this->addFooter();

        return $this->phpWord;
    }

    private function addHeader()
    {
        // Create header table with 2 columns
        $header = $this->section->addHeader();
    
        // Create header table with 2 columns
        $headerTable = $header->addTable([
            'cellMargin' => 0,
            'cellSpacing' => 0,
            'width' => 100 * 50, // Table width in twips (100%)
        ]);
        
        $row = $headerTable->addRow();
    
        // Left column for logo
        $logoCell = $row->addCell(7000, [
            'valign' => 'center',
            'alignment' => Jc::END
        ]);
        
        if (file_exists(public_path('images/logo.png'))) {
            $logoCell->addImage(
                public_path('images/logo.png'),
                [
                    'width' => Converter::inchToPoint(4.26),   
                    'height' => Converter::inchToPoint(1.3),
                    'alignment' => Jc::END 
                ]
            );
        }
    
        // Right column for site name and date
        $infoCell = $row->addCell(8000, [
            'valign' => 'center'
        ]);
        
        $infoCell->addText(
            $this->siteName, 
            [
                'bold' => true,
                'size' => 32,
                'name' => 'Century Gothic'
            ], 
            [
                'alignment' => Jc::RIGHT
            ]
        );
        
        $infoCell->addText(
            "{$this->month} {$this->year}", 
            [
                'size' => 11,
                'bold' => true,
                'name' => 'Century Gothic'
            ], 
            [
                'alignment' => Jc::RIGHT
            ]
        );
    
        // Untuk memastikan styling yang konsisten
        $this->section->setStyle([
            'marginTop' => Converter::inchToTwip(0.5), // Memberikan ruang untuk header
            'headerHeight' => Converter::inchToTwip(0.5)
        ]);
    
        // Add space after header
        $this->section->addTextBreak(1);
    }

    private function addHeaderContent()
{
    // Tambahkan ke header section
    $header = $this->section->addHeader();
    
    // WALL CHART
    $header->addText(
        'WALL CHART',
        [
            'bold' => true, 
            'size' => 26, 
            'color' => '1F3864', 
            'name' => 'Calibri'
        ],
        [
            'alignment' => Jc::CENTER,
            'spaceBefore' => 100,
            'spaceAfter' => 100
        ]
    );

    // Description
    $header->addText(
        'Carefully read product label before use. Refer to Safety Data Sheet (SDS) for information on first aid, storage and handling and other safety precautions.',
        [
            'size' => 11,
            'name' => 'Calibri', 
            'color' => '1F3864'
        ],
        [
            'alignment' => Jc::CENTER, 
            'spaceBefore' => 50, 
            'spaceAfter' => 50
        ]
    );

    // Hazard Ratings
    $textRun = $header->addTextRun(['alignment' => Jc::CENTER, 'spaceAfter' => 100]);
    $textRun->addText('Hazard ratings: ', ['size' => 12, 'bold' => true, 'color' => '1F3864', 'name' => 'Calibri']);
    $textRun->addText('Low = Non-hazardous    ', ['size' => 12, 'color' => '1F3864', 'name' => 'Calibri']);
    $textRun->addText('Moderate = Signal word = WARNING    ', ['size' => 12, 'color' => '1F3864', 'name' => 'Calibri']);
    $textRun->addText('High = Signal word = DANGER', ['size' => 12, 'color' => '1F3864', 'name' => 'Calibri']);

    // Table Header
    $table = $header->addTable([
        'alignment' => Jc::CENTER,
        'width' => 100 * 45,
        'unit' => TblWidth::PERCENT,
        'spaceAfter' => 0,
        'spaceBefore' => 0
    ]);
    
    $row = $table->addRow();
    
    // Product Name Column
    $cell1 = $row->addCell(null, [
        'bgColor' => '1F3864',
        'valign' => 'center',
        'width' => 60 * 50,
        'unit' => TblWidth::PERCENT
    ]);
    $cell1->addText(
        'PRODUCT NAME                 APPLICATION AND AREA OF USE',
        [
            'bold' => true,
            'color' => 'FFFFFF',
            'size' => 12,
            'name' => 'Century Gothic'
        ],
        ['alignment' => Jc::CENTER]
    );
    
    // Hazard Column
    $cell2 = $row->addCell(null, [
        'bgColor' => '1F3864',
        'valign' => 'center',
        'width' => 25 * 35,
        'unit' => TblWidth::PERCENT
    ]);
    $cell2->addText(
        'HAZARD',
        [
            'bold' => true,
            'color' => 'FFFFFF',
            'size' => 12,
            'name' => 'Century Gothic'
        ],
        [
            'alignment' => Jc::LEFT,
            'indentLeft' => 200
        ]
    );
    
    // PPE Required Column
    $cell3 = $row->addCell(null, [
        'bgColor' => '1F3864',
        'valign' => 'center',
        'width' => 20 * 45,
        'unit' => TblWidth::PERCENT
    ]);
    $cell3->addText(
        'PPE REQUIRED',
        [
            'bold' => true,
            'color' => 'FFFFFF',
            'size' => 12,
            'name' => 'Century Gothic'
        ],
        ['alignment' => Jc::LEFT]
    );
}
    private function addProductRows()
    {
        try {
            // Buat tabel untuk produk tanpa border
            $productTable = $this->section->addTable([
                'alignment' => Jc::CENTER,
                'width' => 100 * 45,
                'unit' => TblWidth::PERCENT
            ]);
        
            // Tambahkan produk-produk
            foreach ($this->products as $product) {
                try {
                    // Row untuk gambar
                    if (!empty($product['product_image'])) {
                        $imageRow = $productTable->addRow();
                        $imageCell = $imageRow->addCell(null, [
                            'valign' => 'center',
                        ]);
        
                        $imagePath = storage_path('app/public/' . $product['product_image']);
                        
                        if (file_exists($imagePath)) {
                            try {
                                $imageCell->addImage(
                                    $imagePath,
                                    [
                                        'width' => 710, // Sesuaikan ukuran gambar
                                        'height' => 140,
                                        'alignment' => Jc::CENTER,
                                        'wrappingStyle' => 'inline',
                                        'spaceAfter' => 0,
                                        'spaceBefore' => 0
                                    ]
                                );
                            } catch (\Exception $e) {
                                Log::error("Failed to add product image", [
                                    'product' => $product['name'],
                                    'image_path' => $imagePath,
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }
                    }
        
                    // Tambahkan sedikit spasi setelah setiap produk
                    $spaceRow = $productTable->addRow();
                    $spaceCell = $spaceRow->addCell();
        
                } catch (\Exception $e) {
                    Log::error("Error adding product", [
                        'product' => $product['name'] ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }
        
        } catch (\Exception $e) {
            Log::error("Error creating product table", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }

        $this->section->addTextBreak(1);
    }

    private function addFooter()
{
    // Mendapatkan section dan menambahkan footer
    $footer = $this->section->addFooter();
    
    // Buat tabel footer tanpa border dengan lebar penuh
    $footerTable = $footer->addTable([
        'alignment' => Jc::CENTER,
        'width' => 100 * 48, // Menggunakan lebar penuh
        'unit' => TblWidth::PERCENT
    ]);

    // Baris footer
    $footerRow = $footerTable->addRow();

    // Kolom pertama untuk teks deskripsi dan website (sebelah kiri)
    $leftCell = $footerRow->addCell(null, [
        'valign' => 'center',
        'width' => 100 * 30, // Sesuaikan lebar
        'unit' => TblWidth::PERCENT,
    ]);
    
    // Tambahkan teks deskripsi dan website dengan alignment kiri
    $leftCell->addText(
        "Lorem Ipsum is simply dummy text of the printing",
        [
            'color' => 'A6ACAF',
            'size' => 10,
            'name' => 'Arial'
        ],
        ['alignment' => Jc::LEFT,  'spaceAfter' => 0  ]
    );
    
    $leftCell->addText(
        "www.simplydummy.com",
        [
            'color' => 'A6ACAF',
            'size' => 10,
            'name' => 'Arial'
        ],
        [
            'alignment' => Jc::LEFT,
            'spaceBefore' => 00 // Menambah jarak vertikal
        ]
    );

    // Kolom kedua untuk nomor telepon dan ABC (tengah)
    $middleCell = $footerRow->addCell(null, [
        'valign' => 'center',
        'width' => 100 * 30,
        'unit' => TblWidth::PERCENT
    ]);
    
    $middleCell->addText(
        "P: 1300 123 456",
        [
            'color' => 'A6ACAF',
            'size' => 10,
            'name' => 'Arial'
        ],
        [
            'alignment' => Jc::LEFT,
            'spaceAfter' => 0  
        ]
    );
    
    $middleCell->addText(
        "ABC: 12 345 678",
        [
            'color' => 'A6ACAF',
            'size' => 10,
            'name' => 'Arial'
        ],
        [
            'alignment' => Jc::LEFT,
            'spaceBefore' => 0 // Menambah jarak vertikal
        ]
    );

    // Kolom ketiga untuk informasi pusat racun (kanan)
    $rightCell = $footerRow->addCell(null, [
        'valign' => 'center',
        'width' => 100 * 40,
        'unit' => TblWidth::PERCENT
    ]);
    
    $rightCell->addText(
        "Poisons Information Centre: 1800 123 456",
        [
            'color' => 'FF5733', // Warna teks merah
            'size' => 10,
            'name' => 'Arial'
        ],
        [
            'alignment' => Jc::RIGHT // Mengubah ke rata kanan
        ]
    );
}

}