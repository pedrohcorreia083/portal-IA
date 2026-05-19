@extends('layouts.app')

@section('title', 'Nova Ordem de Serviço')

@section('content-header')
    Nova Ordem de Serviço
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
    <li class="breadcrumb-item active">Nova</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('service-orders.store') }}" method="POST" enctype="multipart/form-data"
                id="serviceOrderForm">
                @csrf

                <!-- Dados do Cliente -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Dados do Cliente</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="client_name">Cliente *</label>
                                    <input type="text" class="form-control" id="client_name" name="client_name" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="client_document">CPF/CNPJ</label>
                                    <input type="text" class="form-control" id="client_document" name="client_document">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="client_phone">Telefone</label>
                                    <input type="text" class="form-control" id="client_phone" name="client_phone">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="consultant_name">Consultora *</label>
                                    <input type="text" class="form-control" id="consultant_name" name="consultant_name"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="client_code">Cód. Cliente</label>
                                    <input type="text" class="form-control" id="client_code" name="client_code">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="order_date">Data *</label>
                                    <input type="date" class="form-control" id="order_date" name="order_date"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="origin">Origem *</label>
                                    <select class="form-control" id="origin" name="origin" required>
                                        <option value="">Selecione...</option>
                                        <option value="SHOWROOM SP">SHOWROOM SP</option>
                                        <option value="SHOWROOM LIMEIRA">SHOWROOM LIMEIRA</option>
                                        <option value="SHOWROOM ON-LINE">SHOWROOM ON-LINE</option>
                                        <option value="E-COMMERCE">E-COMMERCE</option>
                                        <option value="EVENTOS">EVENTOS</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observations">Observações</label>
                            <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Produtos e Serviços -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Produtos e Serviços</h3>
                        <button type="button" class="btn btn-primary btn-sm float-right" id="addProduct">
                            <i class="fas fa-plus"></i> Adicionar Produto
                        </button>
                        <button type="button" class="btn btn-info btn-sm float-right mr-2" data-toggle="modal"
                            data-target="#locateProductModal">
                            <i class="fas fa-search"></i> Localizar Peça por IA
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="products-container">
                            <!-- Itens serão adicionados aqui dinamicamente -->
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Criar Ordem de Serviço
                        </button>
                        <a href="{{ route('service-orders.index') }}" class="btn btn-default">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Localizar Peça por IA (SIMPLIFICADO) -->
    <div class="modal fade" id="locateProductModal" tabindex="-1" role="dialog"
        aria-labelledby="locateProductModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="locateProductModalLabel">Localizar Peça</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Upload da Imagem -->
                            <div class="form-group">
                                <label for="searchImage">Foto da Peça *</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="searchImage" accept="image/*">
                                    <label class="custom-file-label" for="searchImage">Selecione a imagem...</label>
                                </div>
                                <small class="form-text text-muted">Tire uma foto nítida da peça que deseja
                                    localizar</small>

                                <!-- Preview da Imagem -->
                                <div class="mt-3 text-center">
                                    <img id="searchImagePreview" class="img-fluid rounded"
                                        style="max-height: 200px; display: none;" alt="Preview da imagem">
                                </div>
                            </div>

                            <!-- Categoria -->
                            <div class="form-group">
                                <label for="searchCategory">Categoria *</label>
                                <select class="form-control" id="searchCategory" required>
                                    <option value="">Selecione a categoria...</option>
                                    @foreach ($categoriasPrincipais as $categoria)
                                        <option value="{{ $categoria }}">{{ $categoria }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Botão de Busca -->
                            <button type="button" class="btn btn-primary btn-block" id="searchButton" disabled>
                                <i class="fas fa-search"></i> Localizar Peça
                            </button>
                        </div>

                        <div class="col-md-8">
                            <!-- Resultados da Busca -->
                            <div id="searchResults" style="display: none;">
                                <h6>Resultados da Busca:</h6>

                                <div id="resultsList" class="mb-3">
                                    <!-- Resultados serão inseridos aqui -->
                                </div>

                                <div class="form-group">
                                    <label>Selecione a peça identificada:</label>
                                    <select class="form-control" id="productSelection" size="4">
                                        <option value="">Selecione uma peça...</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        Você também pode clicar diretamente nos resultados acima ou selecionar aqui
                                    </small>
                                </div>

                                <button type="button" class="btn btn-success btn-block" id="addToOrderButton"
                                    data-dismiss="modal" disabled>
                                    <i class="fas fa-plus"></i> Adicionar à Ordem de Serviço
                                </button>
                            </div>

                            <!-- Loading -->
                            <div id="searchLoading" class="text-center" style="display: none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Processando...</span>
                                </div>
                                <p class="mt-2">Analisando imagem com IA...</p>
                                <small class="text-muted">Isso pode levar alguns segundos</small>
                            </div>

                            <!-- Mensagem de Erro -->
                            <div id="searchError" class="alert alert-danger" style="display: none;">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span id="errorMessage"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        .result-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }

        .result-item:hover {
            background-color: #f8f9fa;
            border-color: #007bff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .result-item.selected {
            background-color: #e3f2fd !important;
            border-color: #007bff;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .similarity-badge {
            font-size: 0.8em;
            font-weight: bold;
            padding: 6px 10px;
        }

        .product-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }

        .result-item:hover .product-thumbnail {
            border-color: #007bff;
            transform: scale(1.05);
        }

        .result-item.selected .product-thumbnail {
            border-color: #007bff;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.4);
        }

        .border-left-success {
            border-left: 4px solid #28a745 !important;
        }

        .border-left-warning {
            border-left: 4px solid #ffc107 !important;
        }

        .border-left-danger {
            border-left: 4px solid #dc3545 !important;
        }

        #resultsList {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
        }

        .text-info {
            font-size: 0.85em;
        }

        .text-info:hover {
            text-decoration: underline;
        }

        .price-field {
            display: none;
        }
    </style>
@endpush

@push('js')
    <script>
        // Configurações globais (SIMPLIFICADO)
        const CONFIG = {
            products: @json($products),
            apiRoutes: {
                identify: '{{ route('service-orders.identify') }}'
            }
        };

        // Estado da aplicação
        const STATE = {
            productIndex: 0,
            currentSearchResults: null
        };

        // Templates
        const TEMPLATES = {
            product: (index) => `
            <div class="product-item" id="product-${index}">
                <div class="row">
                    <div class="col-md-11">
                        <h5>Produto #${index + 1}</h5>
                    </div>
                    <div class="col-md-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-product" onclick="PRODUCTS.remove(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Produto *</label>
                            <input type="text" 
                                class="form-control" 
                                name="items[${index}][product_name]" 
                                id="product-name-${index}" 
                                required
                                autocomplete="off">
                            <input type="hidden" 
                                name="items[${index}][product_id]" 
                                id="product-id-${index}" 
                                value="">
                            <small class="form-text text-muted">
                                Digite manualmente ou use "Localizar Peça por IA"
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Banho</label>
                            <select class="form-control" name="items[${index}][coating]" id="coating-select-${index}">
                                <option value="">Selecione...</option>
                                <option value="DO">Dourado</option>
                                <option value="RH">Ródio branco</option>
                                <option value="RO">Rose</option>
                                <option value="NE">Ródio negro</option> 
                                <option value="MIX">Mix de banhos</option>
                                <option value="CL">Colorido</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Foto do Produto (Opcional)</label>
                            <input type="file" class="form-control-file" name="items[${index}][product_image]" 
                                   accept="image/*">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Serviços *</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="B" id="service-b-${index}">
                                    <label class="form-check-label" for="service-b-${index}">B - Banhar</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="RP" id="service-rp-${index}">
                                    <label class="form-check-label" for="service-rp-${index}">RP - Repor pedra</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="F" id="service-f-${index}">
                                    <label class="form-check-label" for="service-f-${index}">F - Trocar fecho</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="L" id="service-l-${index}">
                                    <label class="form-check-label" for="service-l-${index}">L - Limpar</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="S" id="service-s-${index}">
                                    <label class="form-check-label" for="service-s-${index}">S - Soldar</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="items[${index}][services][]" value="RM" id="service-rm-${index}">
                                    <label class="form-check-label" for="service-rm-${index}">RM - Remontar</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="purchase_date">Data da Compra</label>
                            <input 
                                type="date" 
                                name="purchase_date" 
                                id="purchase_date" 
                                class="form-control"
                                value="{{ old('purchase_date') }}"
                            >
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="purchase_value">Valor da Compra</label>
                            <input 
                                type="number" 
                                name="purchase_value" 
                                id="purchase_value" 
                                class="form-control"
                                step="0.01"
                                placeholder="R$ 0,00"
                                value="{{ old('purchase_value') }}"
                            >
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Observações do Item</label>
                    <textarea class="form-control" name="items[${index}][observations]" rows="2"></textarea>
                </div>

                <input type="hidden" name="items[${index}][ai_identified_code]" id="ai-code-${index}">
                <hr class="my-4">
            </div>
        `
        };

        // Módulo de Produtos
        const PRODUCTS = {
            init() {
                // inicializa apenas se não existir ainda
                if (typeof STATE.productIndex === 'undefined') {
                    STATE.productIndex = document.querySelectorAll('.product-item').length || 0;
                }

                document.getElementById('addProduct').addEventListener('click', () => this.add());
            },

            add() {
                const container = document.getElementById('products-container');
                const index = STATE.productIndex;

                // adiciona o novo produto
                container.insertAdjacentHTML('beforeend', TEMPLATES.product(index));

                // listener para sincronizar product_name → product_id
                setTimeout(() => {
                    const nameInput = document.getElementById(`product-name-${index}`);
                    const idInput = document.getElementById(`product-id-${index}`);
                    if (nameInput && idInput) {
                        nameInput.addEventListener('input', () => {
                            if (!idInput.value) {
                                idInput.value = nameInput.value.trim();
                            }
                        });
                    }
                }, 50);

                STATE.productIndex++;
            }, // 👈 vírgula ESSENCIAL AQUI!

            remove(index) {
                const element = document.getElementById(`product-${index}`);
                if (element) element.remove();

            },

            addFromAI(product, identifiedCode) {
                // Se não há produtos, adiciona um novo
                if (document.querySelectorAll('.product-item').length === 0) {
                    this.add();
                }

                // procura o primeiro campo de produto vazio
                let targetIndex = null;
                document.querySelectorAll('.product-item').forEach((item, i) => {
                    const nameInput = item.querySelector(`[id^="product-name-"]`);
                    if (nameInput && !nameInput.value.trim() && targetIndex === null) {
                        targetIndex = i;
                    }
                });

                // se todos estão preenchidos, adiciona um novo produto
                if (targetIndex === null) {
                    this.add();
                    targetIndex = STATE.productIndex - 1;
                }

                const productNameInput = document.getElementById(`product-name-${targetIndex}`);
                const productIdInput = document.getElementById(`product-id-${targetIndex}`);
                const aiCodeInput = document.getElementById(`ai-code-${targetIndex}`);

                if (productNameInput && productIdInput && aiCodeInput) {
                    if (product) {
                        productNameInput.value = `${product.code} - ${product.name}`;
                        productIdInput.value = product.id;
                    } else if (identifiedCode) {
                        productNameInput.value = identifiedCode;
                        productIdInput.value = identifiedCode;
                    }

                    aiCodeInput.value = identifiedCode || '';

                    if (identifiedCode) {
                        this.autoFillCoating(targetIndex, identifiedCode);
                    }

                    UTILS.showMessage(
                        product ? `Peça "${product.code}" adicionada no produto #${targetIndex + 1}!` :
                        identifiedCode ? `Peça "${identifiedCode}" adicionada no produto #${targetIndex + 1}!` :
                        'Peça adicionada com sucesso!',
                        'success'
                    );
                } else {
                    UTILS.showMessage('Erro ao adicionar peça: campos não encontrados.', 'info');
                }
            },

            autoFillCoating(index, productCode) {
                const coatingSelect = document.getElementById(`coating-select-${index}`);
                if (!coatingSelect) return;

                // Extrair código do banho do final do código do produto (últimos 2 caracteres)
                const coatingCode = productCode.slice(-2).toUpperCase();
                const coatingMap = {
                    'DO': 'DO',
                    'RH': 'RH',
                    'RO': 'RO',
                    'NE': 'NE',
                    'MI': 'MIX',
                    'MX': 'MIX',
                    'CL': 'CL'
                };

                if (coatingMap[coatingCode]) {
                    coatingSelect.value = coatingMap[coatingCode];
                }
            }
        };


        // Módulo de IA
        const IA_SEARCH = {
            init() {
                this.setupEventListeners();
            },

            setupEventListeners() {
                // Imagem
                const searchImage = document.getElementById('searchImage');
                if (searchImage) {
                    searchImage.addEventListener('change', (e) => this.handleImageSelect(e));
                }

                // Categoria
                const searchCategory = document.getElementById('searchCategory');
                if (searchCategory) {
                    searchCategory.addEventListener('change', () => this.updateSearchButton());
                }

                // Botões
                const searchButton = document.getElementById('searchButton');
                const addToOrderButton = document.getElementById('addToOrderButton');
                const productSelection = document.getElementById('productSelection');

                if (searchButton) {
                    searchButton.addEventListener('click', () => this.search());
                }

                if (addToOrderButton) {
                    addToOrderButton.addEventListener('click', () => this.addToOrder());
                }

                if (productSelection) {
                    productSelection.addEventListener('change', () => this.updateAddButton());
                }
            },

            handleImageSelect(e) {
                const file = e.target.files[0];
                const preview = document.getElementById('searchImagePreview');
                const label = document.querySelector('.custom-file-label');

                if (file) {
                    if (label) label.textContent = file.name;
                    if (preview) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.src = e.target.result;
                            preview.style.display = 'block';
                        }
                        reader.readAsDataURL(file);
                    }
                } else {
                    if (preview) preview.style.display = 'none';
                    if (label) label.textContent = 'Selecione a imagem...';
                }

                this.updateSearchButton();
            },

            updateSearchButton() {
                const searchButton = document.getElementById('searchButton');
                if (!searchButton) return;

                const hasImage = document.getElementById('searchImage')?.files?.length > 0;
                const hasCategory = document.getElementById('searchCategory')?.value;

                console.log('updateSearchButton:', {
                    hasImage,
                    hasCategory
                }); // Debug

                searchButton.disabled = !(hasImage && hasCategory);
            },

            updateAddButton() {
                const addToOrderButton = document.getElementById('addToOrderButton');
                if (!addToOrderButton) return;

                const hasSelection = document.getElementById('productSelection')?.value !== '';
                addToOrderButton.disabled = !hasSelection;
            },

            async search() {
                const fileInput = document.getElementById('searchImage');
                const category = document.getElementById('searchCategory');

                if (!fileInput || !category) {
                    alert('Elementos do formulário não encontrados.');
                    return;
                }

                const file = fileInput.files[0];

                if (!file || !category.value) {
                    alert('Por favor, preencha todos os campos obrigatórios.');
                    return;
                }

                this.showLoading();

                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('product_type', category.value);

                    const response = await fetch(CONFIG.apiRoutes.identify, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: formData
                    });

                    if (!response.ok) {
                        throw new Error(`Erro HTTP: ${response.status}`);
                    }

                    const data = await response.json();
                    this.hideLoading();

                    if (data.success) {
                        this.displayResults(data);
                    } else {
                        this.showError(data.error || 'Erro desconhecido na busca.');
                    }

                } catch (error) {
                    this.hideLoading();
                    this.showError('Erro de comunicação: ' + error.message);
                    console.error('Erro na busca por IA:', error);
                }
            },

            showLoading() {
                const searchLoading = document.getElementById('searchLoading');
                const searchResults = document.getElementById('searchResults');
                const searchError = document.getElementById('searchError');
                const searchButton = document.getElementById('searchButton');

                if (searchLoading) searchLoading.style.display = 'block';
                if (searchResults) searchResults.style.display = 'none';
                if (searchError) searchError.style.display = 'none';
                if (searchButton) searchButton.disabled = true;
            },

            hideLoading() {
                const searchLoading = document.getElementById('searchLoading');
                const searchButton = document.getElementById('searchButton');

                if (searchLoading) searchLoading.style.display = 'none';
                if (searchButton) searchButton.disabled = false;
            },

            displayResults(data) {
                const resultsList = document.getElementById('resultsList');
                const productSelection = document.getElementById('productSelection');
                const searchResults = document.getElementById('searchResults');

                if (!resultsList || !productSelection || !searchResults) return;

                resultsList.innerHTML = '';
                productSelection.innerHTML = '<option value="">Selecione uma peça...</option>';

                if (data.ai_results && data.ai_results.mais_semelhantes && data.ai_results.mais_semelhantes.length >
                    0) {
                    const matches = data.ai_results.mais_semelhantes;

                    const resultsCount = document.createElement('div');
                    resultsCount.className = 'alert alert-info mb-3';
                    resultsCount.innerHTML =
                        `<i class="fas fa-info-circle"></i> Encontrados ${matches.length} resultados:`;
                    resultsList.appendChild(resultsCount);

                    matches.forEach((match, index) => {
                        const similarityPercent = (match.similaridade * 100).toFixed(1);
                        const extractedCode = UTILS.extractCodeFromFileName(match.arquivo);

                        const resultItem = document.createElement('div');
                        resultItem.className = 'result-item';

                        if (similarityPercent > 80) {
                            resultItem.style.borderLeft = '4px solid #28a745';
                        } else if (similarityPercent > 60) {
                            resultItem.style.borderLeft = '4px solid #ffc107';
                        } else {
                            resultItem.style.borderLeft = '4px solid #dc3545';
                        }

                        resultItem.innerHTML = `
                    <div class="d-flex align-items-start">
                        <div class="mr-3" style="flex-shrink: 0;">
                            <img src="${match.url}" 
                                 alt="${match.arquivo}" 
                                 class="product-thumbnail"
                                 onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYwIiBoZWlnaHQ9IjYwIiBmaWxsPSIjRjBGMEYwIi8+CjxwYXRoIGQ9Ik0zMCAzN0MzMi44NjYgMzcgMzcgMzMuODY2IDM3IDMwQzM3IDI2LjEzNCAzMy44NjYgMjMgMzAgMjNDMjYuMTM0IDIzIDIzIDI2LjEzNCAyMyAzMEMyMyAzMy44NjYgMjYuMTM0IDM3IDMwIDM3WiIgZmlsbD0iI0RERERERCIvPgo8cGF0aCBkPSJNMTggMTdINDJWNDNIMThWMTdaIiBzdHJva2U9IiNEREREREQiIHN0cm9rZS13aWR0aD0iMiIvPgo8L3N2Zz4K'">
                        </div>
                        
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div>
                                    <strong class="text-primary">${match.arquivo}</strong>
                                    ${extractedCode ? `
                                                <div>
                                                    <small class="text-muted">
                                                        <strong>Código:</strong> 
                                                        <span class="badge badge-light">${extractedCode}</span>
                                                    </small>
                                                </div>
                                            ` : ''}
                                </div>
                                
                            </div>
                            
                            <div class="mt-1">
                                <small class="text-muted">
                                    <a href="${match.url}" target="_blank" class="text-info" title="Abrir imagem em nova aba">
                                        <i class="fas fa-external-link-alt"></i> Ver imagem original
                                    </a>
                                </small>
                            </div>
                        </div>
                    </div>
                `;

                        resultItem.addEventListener('click', () => {
                            document.querySelectorAll('.result-item').forEach(item => {
                                item.classList.remove('selected');
                                item.style.backgroundColor = '';
                            });
                            resultItem.classList.add('selected');
                            resultItem.style.backgroundColor = '#e3f2fd';
                            productSelection.value = index;
                            this.updateAddButton();
                        });

                        resultsList.appendChild(resultItem);

                        const option = document.createElement('option');
                        option.value = index;
                        option.textContent = `#${index + 1} - ${match.arquivo} (${similarityPercent}% similar)`;
                        if (extractedCode) {
                            option.textContent += ` - ${extractedCode}`;
                        }
                        productSelection.appendChild(option);
                    });

                    STATE.currentSearchResults = data;
                    const stats = this.calculateStats(matches);
                    const statsDiv = document.createElement('div');
                    // statsDiv.className = 'alert alert-secondary mt-3';
                    // statsDiv.innerHTML = `<small><strong>Estatísticas:</strong> ${stats.highSimilarity} alta similaridade (>80%) | ${stats.mediumSimilarity} média similaridade (60-80%) | ${stats.lowSimilarity} baixa similaridade (<60%)</small>`;
                    resultsList.appendChild(statsDiv);

                } else {
                    resultsList.innerHTML =
                        '<div class="alert alert-warning">Nenhuma correspondência encontrada pela IA.</div>';
                }

                searchResults.style.display = 'block';
                this.updateAddButton();
            },

            calculateStats(matches) {
                const stats = {
                    highSimilarity: 0,
                    mediumSimilarity: 0,
                    lowSimilarity: 0
                };

                matches.forEach(match => {
                    const similarityPercent = match.similaridade * 100;
                    if (similarityPercent > 80) {
                        stats.highSimilarity++;
                    } else if (similarityPercent > 60) {
                        stats.mediumSimilarity++;
                    } else {
                        stats.lowSimilarity++;
                    }
                });

                return stats;
            },

            showError(message) {
                const searchError = document.getElementById('searchError');
                const errorMessage = document.getElementById('errorMessage');

                if (searchError && errorMessage) {
                    errorMessage.textContent = message;
                    searchError.style.display = 'block';
                } else {
                    alert('Erro: ' + message);
                }
            },

            addToOrder() {
                const selectedIndex = document.getElementById('productSelection')?.value;
                if (!selectedIndex || selectedIndex === '') return;

                const searchData = STATE.currentSearchResults;
                if (!searchData || !searchData.ai_results || !searchData.ai_results.mais_semelhantes) {
                    alert('Nenhum resultado disponível para adicionar.');
                    return;
                }

                const selectedMatch = searchData.ai_results.mais_semelhantes[selectedIndex];
                if (!selectedMatch) {
                    alert('Resultado selecionado não encontrado.');
                    return;
                }

                const extractedCode = UTILS.extractCodeFromFileName(selectedMatch.arquivo);

                let matchedProduct = null;
                if (extractedCode) {
                    matchedProduct = CONFIG.products.find(p => p.code.includes(extractedCode));
                }

                if (!matchedProduct && searchData.matched_product) {
                    matchedProduct = CONFIG.products.find(p => p.id == searchData.matched_product.id);
                }

                PRODUCTS.addFromAI(matchedProduct, extractedCode);
                this.reset();
                $('#locateProductModal').modal('hide');
            },

            reset() {
                const searchImage = document.getElementById('searchImage');
                const searchImagePreview = document.getElementById('searchImagePreview');
                const customFileLabel = document.querySelector('.custom-file-label');
                const searchCategory = document.getElementById('searchCategory');
                const searchResults = document.getElementById('searchResults');
                const searchError = document.getElementById('searchError');
                const searchButton = document.getElementById('searchButton');

                if (searchImage) searchImage.value = '';
                if (searchImagePreview) searchImagePreview.style.display = 'none';
                if (customFileLabel) customFileLabel.textContent = 'Selecione a imagem...';
                if (searchCategory) searchCategory.value = '';

                if (searchResults) searchResults.style.display = 'none';
                if (searchError) searchError.style.display = 'none';
                if (searchButton) searchButton.disabled = true;

                STATE.currentSearchResults = null;
            }
        };

        // Utilitários
        const UTILS = {
            init() {
                this.setupMasks();
            },

            setupMasks() {
                const clientDocument = document.getElementById('client_document');
                if (clientDocument) {
                    clientDocument.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        if (value.length <= 11) {
                            value = value.replace(/(\d{3})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d{1,2})/, '$1-$2');
                        } else {
                            value = value.replace(/(\d{2})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d)/, '$1.$2')
                                .replace(/(\d{3})(\d)/, '$1/$2')
                                .replace(/(\d{4})(\d{1,2})/, '$1-$2');
                        }
                        e.target.value = value;
                    });
                }

                const clientPhone = document.getElementById('client_phone');
                if (clientPhone) {
                    clientPhone.addEventListener('input', function(e) {
                        let value = e.target.value.replace(/\D/g, '');
                        value = value.replace(/(\d{2})(\d)/, '($1) $2')
                            .replace(/(\d{5})(\d)/, '$1-$2')
                            .replace(/(-\d{4})\d+?$/, '$1');
                        e.target.value = value;
                    });
                }
            },

            extractCodeFromFileName(fileName) {
                if (!fileName) return null;

                const fileNameWithoutExt = fileName.replace(/\.[^/.]+$/, "");
                const normalized = fileNameWithoutExt
                    .normalize("NFD")
                    .replace(/[^A-Z0-9]/g, '')
                    .toUpperCase();

                const patterns = [
                    /[A-Z]{2}\d{8}[A-Z]{2}/,
                    /[A-Z]{2}\d{6}[A-Z]{2}/,
                    /[A-Z]{2}\d+[A-Z]*/
                ];

                for (let pattern of patterns) {
                    const match = normalized.match(pattern);
                    if (match) return match[0];
                }

                return null;
            },

            showMessage(message, type = 'info') {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${alertClass} alert-dismissible fade show`;
                alertDiv.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;

                const content = document.querySelector('.content');
                if (content) {
                    content.insertBefore(alertDiv, content.firstChild);
                }

                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 3000);
            }
        };

        // Antes do submit: garantir que product_id não fique null
        document.addEventListener('DOMContentLoaded', function() {
            PRODUCTS.init();
            IA_SEARCH.init();
            UTILS.init();

            const form = document.getElementById('serviceOrderForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    // para cada product-item, garanta que o hidden product-id não esteja vazio
                    document.querySelectorAll('.product-item').forEach(item => {
                        const nameInput = item.querySelector('[id^="product-name-"]');
                        const idInput = item.querySelector('[id^="product-id-"]');
                        if (nameInput && idInput) {
                            const nameVal = (nameInput.value || '').trim();
                            const idVal = (idInput.value || '').toString().trim();
                            // Se id estiver vazio ou for string vazia, preenche com o nome (evita NULL no banco)
                            if (!idVal && nameVal) {
                                idInput.value = nameVal;
                            }
                        }
                    });
                });
            }
        });
    </script>
@endpush
