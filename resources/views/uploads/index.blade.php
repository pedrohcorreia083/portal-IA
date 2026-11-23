@extends('layouts.app')

@section('title', 'Biblioteca de imagens')

@section('content-header')
    <div class="eyebrow">Acervo de produtos</div>
    <h1 class="page-title">Biblioteca de imagens</h1>
    <p class="page-subtitle">Envie e organize as imagens de referência usadas na identificação de peças.</p>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item active">
        Biblioteca de imagens
    </li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Enviar novas imagens</h3>
                </div>

                {{-- O enctype é OBRIGATÓRIO para upload de arquivos --}}
                <form id="form-upload" action="{{ route('uploads.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card-body">
                        <div class="form-group">
                            <label for="pasta_destino">Escolha a pasta de destino:</label>
                            <select name="pasta_destino" id="pasta_destino" class="form-control" required>
                                <option value="">Selecione uma pasta...</option>
                                @foreach ($pastas as $key => $nome)
                                    <option value="{{ $key }}"
                                        {{ old('pasta_destino', $pastaSelecionada) == $key ? 'selected' : '' }}>
                                        {{ $nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="imagens">Selecione as imagens (pode escolher várias):</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" name="imagens[]" class="custom-file-input" id="imagens" multiple
                                        accept="image/*" required>
                                    <label class="custom-file-label" for="imagens">Escolher arquivos...</label>
                                </div>
                            </div>
                            <small class="text-muted">Formatos aceitos: JPG, PNG, GIF, WEBP. Tamanho máx: 5MB por
                                arquivo.</small>
                            <div id="preview-imagens" class="row mt-3"></div>
                        </div>
                    </div>

                    <div id="upload-status" class="px-3 pb-3" style="display: none;">
                        <p id="upload-mensagem" class="mb-2">Enviando imagens...</p>

                        <div class="progress">
                            <div id="upload-barra" class="progress-bar progress-bar-striped progress-bar-animated"
                                role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0"
                                aria-valuemax="100">0%</div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Fazer Upload
                        </button>
                    </div>
                </form>

                @if ($pastaSelecionada)
                    <div class="card-body border-top">
                        <h5>Imagens na pasta {{ $pastaSelecionada }} ({{ count($imagensSalvas) }})</h5>

                        @if (count($imagensSalvas))
                            <div class="row">
                                @foreach ($imagensSalvas as $nome)
                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                                        <div class="card h-100 mb-0">
                                            <img src="{{ route('uploads.imagem', ['pasta' => $pastaSelecionada, 'nome' => $nome]) }}"
                                                alt="{{ $nome }}" loading="lazy" class="card-img-top"
                                                style="height: 130px; object-fit: contain; padding: 8px;">
                                            <div class="card-body p-2">
                                                <small class="d-block text-truncate" title="{{ $nome }}">
                                                    {{ $nome }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted mb-0">Esta pasta ainda não contém imagens.</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>
@stop


@section('js')

    <script>
        const inputImagens = document.getElementById('imagens');
        const preview = document.getElementById('preview-imagens');

        inputImagens.addEventListener('change', function() {
            preview.innerHTML = '';

            const arquivos = Array.from(this.files);
            const label = this.nextElementSibling;

            label.textContent = arquivos.length > 1 ?
                `${arquivos.length} arquivos selecionados` :
                (arquivos[0]?.name || 'Escolher arquivos...');

            arquivos.forEach(function(arquivo) {
                if (!arquivo.type.startsWith('image/')) return;

                const url = URL.createObjectURL(arquivo);

                const coluna = document.createElement('div');
                coluna.className = 'col-6 col-sm-4 col-md-3 col-lg-2 mb-3';

                const card = document.createElement('div');
                card.className = 'card h-100 mb-0';

                const imagem = document.createElement('img');
                imagem.src = url;
                imagem.alt = arquivo.name;
                imagem.className = 'card-img-top';
                imagem.style.cssText = 'height: 130px; object-fit: contain; padding: 8px;';

                imagem.addEventListener('load', function() {
                    URL.revokeObjectURL(url);
                }, {
                    once: true
                });

                const corpo = document.createElement('div');
                corpo.className = 'card-body p-2';

                const nome = document.createElement('small');
                nome.className = 'd-block text-truncate';
                nome.title = arquivo.name;
                nome.textContent = arquivo.name;

                corpo.appendChild(nome);
                card.appendChild(imagem);
                card.appendChild(corpo);
                coluna.appendChild(card);
                preview.appendChild(coluna);
            });
        });
    </script>
@stop
