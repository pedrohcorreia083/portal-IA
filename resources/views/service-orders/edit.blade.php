@extends('layouts.app')

@section('title', 'Editar Ordem de Serviço')

@section('content-header')
Editar Ordem de Serviço
@endsection

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('service-orders.index') }}">Ordens de Serviço</a></li>
<li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <form action="{{ route('service-orders.update', $serviceOrder->id) }}" method="POST" enctype="multipart/form-data" id="serviceOrderForm">
            @csrf
            @method('PUT')

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
                                <input type="text" class="form-control" id="client_name" name="client_name" value="{{ old('client_name', $serviceOrder->client_name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_document">CPF/CNPJ</label>
                                <input type="text" class="form-control" id="client_document" name="client_document" value="{{ old('client_document', $serviceOrder->client_document) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_phone">Telefone</label>
                                <input type="text" class="form-control" id="client_phone" name="client_phone" value="{{ old('client_phone', $serviceOrder->client_phone) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="consultant_name">Consultora *</label>
                                <input type="text" class="form-control" id="consultant_name" name="consultant_name" value="{{ old('consultant_name', $serviceOrder->consultant_name) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_code">Cód. Cliente</label>
                                <input type="text" class="form-control" id="client_code" name="client_code" value="{{ old('client_code', $serviceOrder->client_code) }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="order_date">Data *</label>
                                <input type="date" class="form-control" id="order_date" name="order_date" value="{{ old('order_date', $serviceOrder->order_date->format('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="origin">Origem *</label>
                                <select class="form-control" id="origin" name="origin" required>
                                    <option value="">Selecione...</option>
                                    <option value="SHOWROOM SP" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM SP' ? 'selected' : '' }}>SHOWROOM SP</option>
                                    <option value="SHOWROOM LIMEIRA" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM LIMEIRA' ? 'selected' : '' }}>SHOWROOM LIMEIRA</option>
                                    <option value="SHOWROOM ON-LINE" {{ old('origin', $serviceOrder->origin) == 'SHOWROOM ON-LINE' ? 'selected' : '' }}>SHOWROOM ON-LINE</option>
                                    <option value="E-COMMERCE" {{ old('origin', $serviceOrder->origin) == 'E-COMMERCE' ? 'selected' : '' }}>E-COMMERCE</option>
                                    <option value="EVENTOS" {{ old('origin', $serviceOrder->origin) == 'EVENTOS' ? 'selected' : '' }}>EVENTOS</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observations">Observações</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3">{{ old('observations', $serviceOrder->observations) }}</textarea>
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
                    <button type="button" class="btn btn-info btn-sm float-right mr-2" data-toggle="modal" data-target="#locateProductModal">
                        <i class="fas fa-search"></i> Localizar Peça por IA
                    </button>
                </div>
                <div class="card-body">
                    <div id="products-container">
                        <!-- Itens serão adicionados aqui dinamicamente -->
                        @foreach($serviceOrder->items as $index => $item)
                        <div class="product-item" id="product-{{ $index }}">
                            <div class="row">
                                <div class="col-md-11">
                                    <h5>Produto #{{ $index + 1 }}</h5>
                                </div>
                                <div class="col-md-1 text-right">
                                    <button type="button" class="btn btn-danger btn-sm remove-product" onclick="PRODUCTS.remove({{ $index }})">
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
                                            name="items[{{ $index }}][identified_product_name]"
                                            id="product-name-{{ $index }}"
                                            value="{{ $item->product ? $item->product->code . ' - ' . $item->product->name : $item->identified_product_name }}"
                                            required
                                            autocomplete="off">
                                        <input type="hidden"
                                            name="items[{{ $index }}][product_id]"
                                            id="product-id-{{ $index }}"
                                            value="{{ $item->product_id ?: $item->identified_product_name }}">
                                        <input type="hidden"
                                            name="items[{{ $index }}][id]"
                                            value="{{ $item->id }}">
                                        <small class="form-text text-muted">
                                            Digite manualmente ou use "Localizar Peça por IA"
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Banho</label>
                                        <select class="form-control" name="items[{{ $index }}][coating]" id="coating-select-{{ $index }}">
                                            <option value="">Selecione...</option>
                                            <option value="DO" {{ $item->coating == 'DO' ? 'selected' : '' }}>Dourado</option>
                                            <option value="RH" {{ $item->coating == 'RH' ? 'selected' : '' }}>Ródio branco</option>
                                            <option value="RO" {{ $item->coating == 'RO' ? 'selected' : '' }}>Rose</option>
                                            <option value="NE" {{ $item->coating == 'NE' ? 'selected' : '' }}>Ródio negro</option>
                                            <option value="MIX" {{ $item->coating == 'MIX' ? 'selected' : '' }}>Mix de banhos</option>
                                            <option value="CL" {{ $item->coating == 'CL' ? 'selected' : '' }}>Colorido</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Foto do Produto (Opcional)</label>
                                        <input type="file" class="form-control-file" name="items[{{ $index }}][product_image]"
                                            accept="image/*">
                                        @if($item->product_image)
                                        <small class="form-text text-muted">
                                            <i class="fas fa-image"></i> Imagem atual:
                                            <a href="{{ Storage::url($item->product_image) }}" target="_blank">Ver imagem</a>
                                        </small>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Serviços *</label>
                                        <div>
                                            @php
                                            $services = is_array($item->services) ? $item->services : json_decode($item->services, true) ?? [];
                                            @endphp
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="B" id="service-b-{{ $index }}" {{ in_array('B', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-b-{{ $index }}">B - Banhar</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="RP" id="service-rp-{{ $index }}" {{ in_array('RP', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-rp-{{ $index }}">RP - Repor pedra</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="F" id="service-f-{{ $index }}" {{ in_array('F', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-f-{{ $index }}">F - Trocar fecho</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="L" id="service-l-{{ $index }}" {{ in_array('L', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-l-{{ $index }}">L - Limpar</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="S" id="service-s-{{ $index }}" {{ in_array('S', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-s-{{ $index }}">S - Soldar</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="items[{{ $index }}][services][]" value="RM" id="service-rm-{{ $index }}" {{ in_array('RM', $services) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="service-rm-{{ $index }}">RM - Remontar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Observações do Item</label>
                                <textarea class="form-control" name="items[{{ $index }}][observations]" rows="2">{{ $item->observations }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="purchase-date-{{ $index }}">Data da compra</label>
                                        <input type="date" class="form-control" name="items[{{ $index }}][purchase_date]"
                                            id="purchase-date-{{ $index }}"
                                            value="{{ old("items.$index.purchase_date", $item->purchase_date?->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="piece-value-{{ $index }}">Valor da peça</label>
                                        <input type="number" class="form-control" name="items[{{ $index }}][piece_value]"
                                            id="piece-value-{{ $index }}" min="0" max="99999999.99" step="0.01"
                                            value="{{ old("items.$index.piece_value", $item->piece_value) }}" placeholder="R$ 0,00">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="items[{{ $index }}][ai_identified_code]" id="ai-code-{{ $index }}" value="{{ $item->ai_identified_code }}">
                            <hr class="my-4">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Atualizar Ordem de Serviço
                    </button>
                    <a href="{{ route('service-orders.index') }}" class="btn btn-default">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Localizar Peça por IA (SIMPLIFICADO) -->
<div class="modal fade" id="locateProductModal" tabindex="-1" role="dialog" aria-labelledby="locateProductModalLabel" aria-hidden="true">
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
                            <small class="form-text text-muted">Tire uma foto nítida da peça que deseja localizar</small>

                            <!-- Preview da Imagem -->
                            <div class="mt-3 text-center">
                                <img id="searchImagePreview" class="img-fluid rounded" style="max-height: 200px; display: none;" alt="Preview da imagem">
                            </div>
                        </div>

                        <!-- Categoria -->
                        <div class="form-group">
                            <label for="searchCategory">Categoria *</label>
                            <select class="form-control" id="searchCategory" required>
                                <option value="">Selecione a categoria...</option>
                                @foreach($categoriasPrincipais as $categoria)
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

                            <button type="button" class="btn btn-success btn-block" id="addToOrderButton" data-dismiss="modal" disabled>
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
    // Configurações globais
    const CONFIG = {
        products: @json($products),
        apiRoutes: {
            identify: '{{ route("service-orders.identify") }}'
        }
    };

    // Estado da aplicação
    const STATE = {
        productIndex: {{ $serviceOrder->items->count() }},
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
                                name="items[${index}][identified_product_name]" 
                                id="product-name-${index}" 
                                required
                                autocomplete="off"
                                placeholder="Digite ou localize o produto...">
                            <input type="hidden" 
                                name="items[${index}][product_id]" 
                                id="product-id-${index}" 
                                value="">
                            <small class="form-text text-muted">
                                Digite manualmente ou use <strong>"Localizar Peça por IA"</strong>
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

                <div class="form-group">
                    <label>Observações do Item</label>
                    <textarea class="form-control" name="items[${index}][observations]" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="purchase-date-${index}">Data da compra</label>
                            <input type="date" class="form-control" name="items[${index}][purchase_date]" id="purchase-date-${index}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="piece-value-${index}">Valor da peça</label>
                            <input type="number" class="form-control" name="items[${index}][piece_value]" id="piece-value-${index}" min="0" max="99999999.99" step="0.01" placeholder="R$ 0,00">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="items[${index}][ai_identified_code]" id="ai-code-${index}">
                <hr class="my-4">
            </div>
        `
    };

    // Módulo de Produtos
    const PRODUCTS = {
        init() {
            document.getElementById('addProduct').addEventListener('click', () => this.add());
        },

        add() {
            const container = document.getElementById('products-container');
            container.innerHTML += TEMPLATES.product(STATE.productIndex);

            setTimeout(() => {
                const nameInput = document.getElementById(`product-name-${STATE.productIndex}`);
                const idInput = document.getElementById(`product-id-${STATE.productIndex}`);
                if (nameInput && idInput) {
                    nameInput.addEventListener('input', () => {
                        if (!idInput.value) {
                            idInput.value = nameInput.value.trim();
                        }
                    });
                }
            }, 50);

            STATE.productIndex++;
        },

        remove(index) {
            const element = document.getElementById(`product-${index}`);
            if (element) element.remove();
        },

        addFromAI(product, identifiedCode) {
            if (document.querySelectorAll('.product-item').length === 0) {
                this.add();
            }

            const lastIndex = STATE.productIndex - 1;

            const productNameInput = document.getElementById(`product-name-${lastIndex}`);
            const productIdInput = document.getElementById(`product-id-${lastIndex}`);
            const aiCodeInput = document.getElementById(`ai-code-${lastIndex}`);

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
                    this.autoFillCoating(lastIndex, identifiedCode);
                }

                UTILS.showMessage(
                    product ? `Peça "${product.code}" adicionada com sucesso!` :
                    identifiedCode ? `Peça "${identifiedCode}" adicionada com sucesso!` :
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
            const searchImage = document.getElementById('searchImage');
            if (searchImage) {
                searchImage.addEventListener('change', (e) => this.handleImageSelect(e));
            }

            const searchCategory = document.getElementById('searchCategory');
            if (searchCategory) {
                searchCategory.addEventListener('change', () => this.updateSearchButton());
            }

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

            if (data.ai_results && data.ai_results.mais_semelhantes && data.ai_results.mais_semelhantes.length > 0) {
                const matches = data.ai_results.mais_semelhantes;

                const resultsCount = document.createElement('div');
                resultsCount.className = 'alert alert-info mb-3';
                resultsCount.innerHTML = `<i class="fas fa-info-circle"></i> Encontrados ${matches.length} resultados:`;
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

            } else {
                resultsList.innerHTML = '<div class="alert alert-warning">Nenhuma correspondência encontrada pela IA.</div>';
            }

            searchResults.style.display = 'block';
            this.updateAddButton();
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

    // Inicialização SIMPLIFICADA - CORRIGIDA
    document.addEventListener('DOMContentLoaded', function() {
        console.log('=== INICIANDO CARREGAMENTO ===');

        PRODUCTS.init();
        IA_SEARCH.init();
        UTILS.init();

        const form = document.getElementById('serviceOrderForm');
        if (form) {
            console.log('Formulário encontrado, adicionando evento submit...');

            form.addEventListener('submit', function(e) {
                console.log('=== EVENTO SUBMIT DISPARADO ===');

                // 1. Verificar se há produtos
                const productItems = document.querySelectorAll('.product-item');
                console.log('Número de produtos:', productItems.length);

                if (productItems.length === 0) {
                    e.preventDefault();
                    alert('Adicione pelo menos um produto!');
                    return;
                }

                // 2. Verificar campos obrigatórios
                let hasErrors = false;
                let errorMessages = [];

                // Campos principais
                const clientName = document.getElementById('client_name');
                const consultantName = document.getElementById('consultant_name');
                const orderDate = document.getElementById('order_date');
                const origin = document.getElementById('origin');

                if (!clientName?.value.trim()) {
                    hasErrors = true;
                    errorMessages.push('Nome do cliente é obrigatório');
                }

                if (!consultantName?.value.trim()) {
                    hasErrors = true;
                    errorMessages.push('Nome da consultora é obrigatório');
                }

                if (!orderDate?.value) {
                    hasErrors = true;
                    errorMessages.push('Data é obrigatória');
                }

                if (!origin?.value) {
                    hasErrors = true;
                    errorMessages.push('Origem é obrigatória');
                }

                // Verificar produtos
                productItems.forEach((item, index) => {
                    const productName = item.querySelector('[id^="product-name-"]');
                    const services = item.querySelectorAll('input[type="checkbox"]:checked');

                    console.log(`Produto ${index + 1}:`, {
                        nome: productName?.value,
                        serviços: services.length
                    });

                    if (!productName?.value.trim()) {
                        hasErrors = true;
                        errorMessages.push(`Produto #${index + 1} não tem nome`);
                    }

                    if (services.length === 0) {
                        hasErrors = true;
                        errorMessages.push(`Produto #${index + 1} não tem serviços selecionados`);
                    }
                });

                if (hasErrors) {
                    e.preventDefault();
                    alert('Erros encontrados:\n\n' + errorMessages.join('\n'));
                    return;
                }

                // 3. Garantir product_id
                console.log('Garantindo product_id...');
                document.querySelectorAll('.product-item').forEach(item => {
                    const nameInput = item.querySelector('[id^="product-name-"]');
                    const idInput = item.querySelector('[id^="product-id-"]');
                    if (nameInput && idInput) {
                        const nameVal = nameInput.value.trim();
                        if (nameVal && !idInput.value) {
                            idInput.value = nameVal;
                            console.log(`Definido product_id para: ${nameVal}`);
                        }
                    }
                });

                console.log('=== FORMULÁRIO PRONTO PARA ENVIO ===');

                // Mostrar loading
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Atualizando...';
                }

                // O formulário será submetido normalmente
            });
        } else {
            console.error('Formulário não encontrado!');
        }

        console.log('=== CARREGAMENTO COMPLETO ===');
    });
</script>
@endpush
