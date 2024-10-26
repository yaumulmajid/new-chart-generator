<!DOCTYPE html>
<html>
<head>
    <title>GM Chemicals - Chart Generator</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/x-icon"/>
</head>
<body class="bg-gray-50">
    <div class="max-w-4xl mx-auto p-4 sm:p-6">
        <div class="text-center mb-8">
            <img src="/images/logo.png" alt="Logo" class="h-12 sm:h-16 mx-auto mb-4 rounded-lg"/>
            <h2 class="text-xl sm:text-2xl font-bold">Chart Generator</h2>
        </div>

        <div class="w-full mx-auto p-4 bg-white rounded-lg shadow-md">
            <form id="chartForm">
                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Site Name</label>
                    <input type="text" class="w-full p-2 border rounded" name="site_name" required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 mb-2">Select products</label>
                    <input type="text" 
                           id="productSearch" 
                           class="w-full p-2 border rounded mb-4" 
                           placeholder="Type product name to search">
                    
                    <div id="productContainer" class="mb-6">
                        <div id="productList" class="mt-4">
                        </div>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="button" 
                            id="downloadButton"
                            class="w-full sm:w-auto text-white px-4 sm:px-6 py-2 rounded flex items-center justify-center hover:bg-blue-600 transition-colors"
                            style="background-color: #00A2FF;">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span class="button-text">Generate Chart</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Loading overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white p-4 rounded-lg flex items-center">
            <svg class="animate-spin h-5 w-5 mr-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Generating chart...</span>
        </div>
    </div>

    <script>
        let allProducts = [];
        let selectedProducts = new Set();
        let selectedProductsOrder = [];
        let currentPage = 1;
        const ITEMS_PER_PAGE = 60;
        const productList = document.getElementById('productList');
        const searchInput = document.getElementById('productSearch');
        const loadingOverlay = document.getElementById('loadingOverlay');
        let totalPages = 1;

        function showLoading() {
            loadingOverlay.classList.remove('hidden');
        }

        function hideLoading() {
            loadingOverlay.classList.add('hidden');
        }

        async function fetchProducts() {
            try {
                showLoading();
                const response = await fetch('/api/all-products');
                const data = await response.json();
                
                if (Array.isArray(data)) {
                    allProducts = data;
                } else if (data.data && Array.isArray(data.data)) {
                    allProducts = data.data;
                }

                totalPages = Math.ceil(allProducts.length / ITEMS_PER_PAGE);
                displayProducts(getPaginatedProducts());
                renderPaginationControls();
            } catch (error) {
                console.error('Error fetching products:', error);
                alert('Failed to load products. Please refresh the page.');
            } finally {
                hideLoading();
            }
        }

        function getPaginatedProducts(products = allProducts) {
            const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
            const endIndex = startIndex + ITEMS_PER_PAGE;
            return products.slice(startIndex, endIndex);
        }

        function displayProducts(products) {
            productList.innerHTML = '';
            
            // Create columns container
            const columnsContainer = document.createElement('div');
            columnsContainer.className = 'grid grid-cols-2 lg:grid-cols-3 gap-x-8';

            // Calculate items per column
            const totalItems = products.length;
            const numColumns = window.innerWidth >= 1024 ? 3 : 2; // 3 columns for desktop, 2 for mobile/tablet
            const itemsPerColumn = Math.ceil(totalItems / numColumns);

            // Create columns array
            const columns = Array(numColumns).fill().map(() => {
                const column = document.createElement('div');
                column.className = 'flex flex-col gap-y-2';
                return column;
            });

            // Distribute items vertically first, then move to next column
            products.forEach((product, index) => {
                const columnIndex = Math.floor(index / itemsPerColumn);
                if (columnIndex < numColumns) {
                    const productElement = createProductElement(product);
                    columns[columnIndex].appendChild(productElement);
                }
            });

            // Append all columns to container
            columns.forEach(column => columnsContainer.appendChild(column));
            productList.appendChild(columnsContainer);
        }

        function createProductElement(product) {
            const div = document.createElement('div');
            const productName = product.product_name || product.name || product.title || 'Unknown Product';
            const productId = product.id || product.product_id;

            div.className = 'flex items-start';
            div.innerHTML = `
                <div class="w-5 flex-shrink-0 pt-0.5">
                    <input type="checkbox" 
                        id="product-${productId}" 
                        name="products[]" 
                        value="${productId}"
                        data-name="${productName}"
                        ${selectedProducts.has(productId.toString()) ? 'checked' : ''}
                        class="form-checkbox h-4 w-4 text-blue-500 border-gray-300 rounded 
                            cursor-pointer transition-colors duration-200 
                            focus:ring-2 focus:ring-blue-200
                            hover:border-blue-500">
                </div>
                <label for="product-${productId}" 
                    class="text-sm cursor-pointer ml-3 flex-1 text-gray-700 hover:text-gray-900 transition-colors duration-200 leading-5">${productName}</label>
            `;

            const checkbox = div.querySelector(`#product-${productId}`);
            
            if (selectedProducts.has(productId.toString())) {
                const existingIndex = selectedProductsOrder.findIndex(p => p.id === productId);
                if (existingIndex === -1) {
                    selectedProductsOrder.push({
                        id: productId,
                        name: productName,
                        product_image: product.product_image
                    });
                }
            }

            checkbox.addEventListener('change', (e) => {
                const productId = e.target.value;
                if (e.target.checked) {
                    selectedProducts.add(productId);
                    selectedProductsOrder.push({
                        id: productId,
                        name: e.target.getAttribute('data-name'),
                        product_image: product.product_image
                    });
                } else {
                    selectedProducts.delete(productId);
                    selectedProductsOrder = selectedProductsOrder.filter(p => p.id !== productId);
                }
            });

            return div;
        }

        function renderPaginationControls() {
            const paginationContainer = document.createElement('div');
            paginationContainer.className = 'flex justify-center items-center mt-4 space-x-1 text-sm';
            
            // Previous button
            const prevButton = document.createElement('button');
            prevButton.innerHTML = '←';
            prevButton.className = `px-2 py-1 rounded-md ${
                currentPage === 1 
                    ? 'text-gray-300 cursor-not-allowed' 
                    : 'text-gray-600 hover:bg-gray-100'
            }`;
            prevButton.disabled = currentPage === 1;
            prevButton.onclick = () => {
                if (currentPage > 1) {
                    currentPage--;
                    displayProducts(getPaginatedProducts());
                    renderPaginationControls();
                }
            };

            const pageInfo = document.createElement('div');
            pageInfo.className = 'flex items-center space-x-1';
            
            let startPage = Math.max(1, currentPage - 1);
            let endPage = Math.min(totalPages, currentPage + 1);
            
            if (startPage > 1) {
                const firstPage = createPageButton(1);
                pageInfo.appendChild(firstPage);
                
                if (startPage > 2) {
                    const dots = document.createElement('span');
                    dots.className = 'text-gray-400 px-1';
                    dots.textContent = '...';
                    pageInfo.appendChild(dots);
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = createPageButton(i);
                pageInfo.appendChild(pageButton);
            }
            
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.className = 'text-gray-400 px-1';
                    dots.textContent = '...';
                    pageInfo.appendChild(dots);
                }
                const lastPage = createPageButton(totalPages);
                pageInfo.appendChild(lastPage);
            }

            const nextButton = document.createElement('button');
            nextButton.innerHTML = '→';
            nextButton.className = `px-2 py-1 rounded-md ${
                currentPage === totalPages 
                    ? 'text-gray-300 cursor-not-allowed' 
                    : 'text-gray-600 hover:bg-gray-100'
            }`;
            nextButton.disabled = currentPage === totalPages;
            nextButton.onclick = () => {
                if (currentPage < totalPages) {
                    currentPage++;
                    displayProducts(getPaginatedProducts());
                    renderPaginationControls();
                }
            };

            paginationContainer.appendChild(prevButton);
            paginationContainer.appendChild(pageInfo);
            paginationContainer.appendChild(nextButton);

            const existingPagination = document.querySelector('.pagination-controls');
            if (existingPagination) {
                existingPagination.remove();
            }

            paginationContainer.classList.add('pagination-controls');
            const productContainer = document.getElementById('productContainer');
            productContainer.appendChild(paginationContainer);
        }

        function createPageButton(pageNum) {
            const button = document.createElement('button');
            button.textContent = pageNum;
            button.className = `px-2 py-1 rounded-md min-w-[24px] ${
                pageNum === currentPage
                    ? 'bg-blue-50 text-blue-600 font-medium'
                    : 'text-gray-600 hover:bg-gray-100'
            }`;
            button.onclick = () => {
                if (pageNum !== currentPage) {
                    currentPage = pageNum;
                    displayProducts(getPaginatedProducts());
                    renderPaginationControls();
                }
            };
            return button;
        }

        // Search functionality
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const filteredProducts = allProducts.filter(product => {
                const name = product.product_name || product.name || product.title || '';
                return name.toLowerCase().includes(searchTerm);
            });
            currentPage = 1;
            totalPages = Math.ceil(filteredProducts.length / ITEMS_PER_PAGE);
            displayProducts(getPaginatedProducts(filteredProducts));
            renderPaginationControls();
        });

        // Download button functionality
        document.getElementById('downloadButton').addEventListener('click', async function() {
            const button = this;
            try {
                const siteName = document.querySelector('input[name="site_name"]').value;

                if (!siteName) {
                    alert('Please enter site name');
                    return;
                }

                if (selectedProductsOrder.length === 0) {
                    alert('Please select at least one product');
                    return;
                }

                button.disabled = true;
                showLoading();

                const response = await fetch('/chart/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        site_name: siteName,
                        products: selectedProductsOrder
                    })
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to generate chart');
                }

                const blob = await response.blob();
                
                if (blob.size === 0) {
                    throw new Error('Generated file is empty');
                }

                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `${siteName.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_wall_chart.docx`;
                document.body.appendChild(a);
                a.click();
                
                setTimeout(() => {
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);
                }, 100);

            } catch (error) {
                console.error('Download error:', error);
                alert(error.message || 'Failed to generate chart. Please try again.');
            } finally {
                button.disabled = false;
                hideLoading();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', fetchProducts);
    </script>
</body>
</html>