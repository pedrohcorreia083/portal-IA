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
        <form action="{{ route('service-orders.store') }}" method="POST" enctype="multipart/form-data" id="serviceOrderForm">
            @csrf

            <!-- Cabeçalho da OS -->
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
                                <input type="text" class="form-control" id="consultant_name" name="consultant_name" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="client_code">Cód. Cliente *</label>
                                <input type="text" class="form-control" id="client_code" name="client_code" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="order_date">Data *</label>
                                <input type="date" class="form-control" id="order_date" name="order_date" value="{{ date('Y-m-d') }}" required>
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
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox mt-4">
                                    <input type="checkbox" class="custom-control-input" id="is_guarantee" name="is_guarantee" value="1">
                                    <label class="custom-control-label" for="is_guarantee">É Garantia?</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="observations">Observações</label>
                        <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <!-- Itens da OS -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Produtos e Serviços</h3>
                    <button type="button" class="btn btn-primary btn-sm float-right" id="addProduct">
                        <i class="fas fa-plus"></i> Adicionar Produto
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
@endsection

@push('css')
<style>
    .product-item {
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 1rem;
        margin-bottom: 1rem;
        background: #f8f9fa;
    }

    .remove-product {
        color: #dc3545;
        cursor: pointer;
    }
</style>
@endpush

@push('js')
<script>
    let productIndex = 0;
    const products = @json($products);

    // Template para um item de produto
    const productTemplate = (index) => `
<div class="product-item" id="product-${index}">
    <div class="row">
        <div class="col-md-11">
            <h5>Produto #${index + 1}</h5>
        </div>
        <div class="col-md-1 text-right">
            <button type="button" class="btn btn-danger btn-sm remove-product" onclick="removeProduct(${index})">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <label>Foto do Produto</label>
                <input type="file" class="form-control-file" name="items[${index}][product_image]" accept="image/*">
                <small class="form-text text-muted">Tire uma foto ou anexe imagem do produto</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Tipo de Produto *</label>
                <select class="form-control" name="items[${index}][product_id]" required onchange="updateProductInfo(${index}, this.value)">
                    <option value="">Selecione...</option>
                    ${products.map(product => `
                        <option value="${product.id}" data-category="${product.category}">
                            ${product.code} - ${product.name}
                        </option>
                    `).join('')}
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label>Banho</label>
                <select class="form-control" name="items[${index}][coating]">
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
        <div class="col-md-6">
            <div class="form-group">
                <label>Status</label>
                <select class="form-control" name="items[${index}][status]">
                    <option value="">Selecione...</option>
                    <option value="RP">RP - Reparo realizado</option>
                    <option value="SC">SC - Sem comentário</option>
                    <option value="GTC">GTC - Garantia troca física</option>
                    <option value="GCG">GCG - Garantia crédito gerado</option>
                    <option value="N">N - Não é LBF</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label>Observações do Item</label>
        <textarea class="form-control" name="items[${index}][observations]" rows="2"></textarea>
    </div>
</div>
`;

    // Adicionar produto
    document.getElementById('addProduct').addEventListener('click', function() {
        const container = document.getElementById('products-container');
        container.innerHTML += productTemplate(productIndex);
        productIndex++;
    });

    // Remover produto
    function removeProduct(index) {
        const element = document.getElementById(`product-${index}`);
        if (element) {
            element.remove();
        }
    }

    // Atualizar informações do produto
    function updateProductInfo(index, productId) {
        // Aqui você pode adicionar lógica para atualizar informações baseadas no produto selecionado
        console.log(`Produto ${productId} selecionado no item ${index}`);
    }

    // Adicionar primeiro produto ao carregar a página
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('addProduct').click();
    });

    // Máscaras para CPF/CNPJ e telefone
    document.getElementById('client_document').addEventListener('input', function(e) {
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

    document.getElementById('client_phone').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        value = value.replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{5})(\d)/, '$1-$2')
            .replace(/(-\d{4})\d+?$/, '$1');
        e.target.value = value;
    });
</script>
@endpush