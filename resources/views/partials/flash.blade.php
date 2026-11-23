{{--
    Mensagens globais de sessão e erros de validação.
    Centralizado aqui para que todas as telas tenham o mesmo comportamento.
--}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible alert-auto-dismiss" role="status">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('warning') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if (session('status'))
    <div class="alert alert-info alert-dismissible" role="status">
        {{ session('status') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fechar">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="fas fa-exclamation-circle" style="margin-top:2px"></i>
            <div>
                <strong style="font-weight:600">
                    {{ $errors->count() === 1 ? 'Verifique o campo abaixo:' : 'Verifique os campos abaixo:' }}
                </strong>
                <ul class="mt-1 mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
