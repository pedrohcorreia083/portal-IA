<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OS {{ $serviceOrder->order_number }} - LBF</title>
    <style>
        /* ===== VARIÁVEIS DA MARCA LBF ===== */
        :root {
            --lbf-dark: #3D3935;
            --lbf-medium: #857874;
            --lbf-accent: #C1B2A2;
            --lbf-light: #E7E3DF;
            --lbf-white: #FFFFFF;
        }

        /* ===== ESTILOS GERAIS ===== */
        body {
            font-family: 'DejaVu Sans', 'Montserrat', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: var(--lbf-dark);
            margin: 0;
            padding: 20px;
            background-color: var(--lbf-white);
        }

        /* ===== CABEÇALHO ===== */
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--lbf-accent);
            padding-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: var(--lbf-dark);
            font-size: 24px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .header .subtitle {
            margin: 5px 0;
            color: var(--lbf-medium);
            font-weight: 500;
        }

        .header .os-number {
            font-size: 18px;
            font-weight: 700;
            color: var(--lbf-dark);
            margin-top: 10px;
        }

        /* ===== SEÇÕES ===== */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: var(--lbf-light);
            padding: 8px 12px;
            border-left: 4px solid var(--lbf-accent);
            margin-bottom: 15px;
            font-weight: 700;
            color: var(--lbf-dark);
            text-transform: uppercase;
            font-size: 13px;
        }

        /* ===== LAYOUT DE INFORMAÇÕES ===== */
        .info-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-block {
            flex: 1;
            min-width: 200px;
        }

        .info-label {
            font-weight: 600;
            color: var(--lbf-medium);
            display: block;
            margin-bottom: 2px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .info-value {
            color: var(--lbf-dark);
            font-weight: 500;
        }

        /* ===== STATUS ===== */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-in_progress {
            background-color: #cce7ff;
            color: #004085;
            border: 1px solid #b3d7ff;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* ===== TABELAS ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .table th {
            background-color: var(--lbf-light);
            border: 1px solid var(--lbf-medium);
            padding: 8px;
            text-align: left;
            font-weight: 700;
            color: var(--lbf-dark);
            text-transform: uppercase;
        }

        .table td {
            border: 1px solid var(--lbf-medium);
            padding: 8px;
            vertical-align: top;
        }

        .table tr:nth-child(even) {
            background-color: rgba(231, 227, 223, 0.3);
        }

        /* ===== BADGES ===== */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            margin: 1px;
            font-size: 9px;
            border-radius: 3px;
            background-color: var(--lbf-medium);
            color: white;
            font-weight: 500;
        }

        .badge-primary {
            background-color: var(--lbf-dark);
        }

        .badge-secondary {
            background-color: var(--lbf-medium);
        }

        .badge-accent {
            background-color: var(--lbf-accent);
            color: var(--lbf-dark);
        }

        /* ===== GARANTIA ===== */
        .guarantee-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: 600;
            color: #856404;
            text-align: center;
        }

        /* ===== RODAPÉ E ASSINATURAS ===== */
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid var(--lbf-light);
            text-align: center;
            color: var(--lbf-medium);
            font-size: 10px;
        }

        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .signature-line {
            border-top: 1px solid var(--lbf-dark);
            width: 300px;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
            color: var(--lbf-medium);
        }

        /* ===== QUEBRA DE PÁGINA ===== */
        .page-break {
            page-break-before: always;
        }

        /* ===== UTILITÁRIOS ===== */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mt-3 {
            margin-top: 15px;
        }

        .p-2 {
            padding: 10px;
        }

        .bg-light {
            background-color: var(--lbf-light);
        }

        .rounded {
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>LBF - ORDEM DE SERVIÇO</h1>
        <div class="subtitle">{{ $serviceOrder->origin }}</div>
        <div class="os-number">Nº: <strong>{{ $serviceOrder->order_number }}</strong></div>
    </div>

    <!-- Informações do Cliente -->
    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <div class="info-grid">
            <div class="info-block">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $serviceOrder->client_name }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">CPF/CNPJ:</span>
                <span class="info-value">{{ $serviceOrder->client_document ?? 'Não informado' }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">Telefone:</span>
                <span class="info-value">{{ $serviceOrder->client_phone ?? 'Não informado' }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">Cód. Cliente:</span>
                <span class="info-value">{{ $serviceOrder->client_code }}</span>
            </div>
        </div>
    </div>

    <!-- Informações da OS -->
    <div class="section">
        <div class="section-title">Informações da Ordem de Serviço</div>
        <div class="info-grid">
            <div class="info-block">
                <span class="info-label">Data:</span>
                <span class="info-value">{{ $serviceOrder->order_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">Consultora:</span>
                <span class="info-value">{{ $serviceOrder->consultant_name }}</span>
            </div>
            <div class="info-block">
                <span class="info-label">Status:</span>
                <span class="status-badge status-{{ $serviceOrder->status }}">
                    {{ ucfirst(str_replace('_', ' ', $serviceOrder->status)) }}
                </span>
            </div>
            <div class="info-block">
                <span class="info-label">Tipo:</span>
                <span class="info-value">
                    @if($serviceOrder->is_guarantee)
                    <span class="badge badge-accent">Garantia</span>
                    @else
                    <span class="badge badge-primary">Assistência Técnica</span>
                    @endif
                </span>
            </div>
        </div>

        @if($serviceOrder->is_guarantee)
        <div class="guarantee-warning">
            ⚠️ ORDEM DE GARANTIA - PROCEDIMENTOS ESPECIAIS APLICÁVEIS
        </div>
        @endif

        @if($serviceOrder->observations)
        <div class="info-block mt-3">
            <span class="info-label">Observações:</span>
            <div class="info-value p-2 bg-light rounded">
                {{ $serviceOrder->observations }}
            </div>
        </div>
        @endif
    </div>

    <!-- Produtos e Serviços -->
    <div class="section">
        <div class="section-title">Produtos e Serviços ({{ $serviceOrder->items->count() }})</div>

        <table class="table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="25%">Produto</th>
                    <th width="10%">Banho</th>
                    <th width="25%">Serviços</th>
                    <th width="10%">Status</th>
                    <th width="25%">Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product->code }} - {{ $item->product->name }}</strong>
                        @if($item->identified_product_name)
                        <br>
                        <small><strong>Identificado:</strong> {{ $item->identified_product_name }}</small>
                        @endif
                    </td>
                    <td>
                        @if($item->coating)
                        <span class="badge badge-secondary">{{ $item->coating_name }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @foreach($item->services_names as $serviceName)
                        <span class="badge badge-primary">{{ $serviceName }}</span>
                        @endforeach
                    </td>
                    <td>
                        @if($item->status)
                        <span class="badge badge-info">{{ $item->status_name }}</span>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($item->observations)
                        <small>{{ $item->observations }}</small>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Legenda -->
    <div class="section">
        <div class="section-title">Legenda</div>
        <div class="info-grid">
            <div class="info-block">
                <span class="info-label">Serviços:</span>
                <small>B - Banhar, RP - Repor pedra, F - Trocar fecho,<br>
                    L - Limpar, S - Soldar, RM - Remontar</small>
            </div>
            <div class="info-block">
                <span class="info-label">Status:</span>
                <small>RP - Reparo realizado, SC - Sem comentário,<br>
                    GTC - Garantia troca física,<br>
                    GCG - Garantia crédito gerado, N - Não é LBF</small>
            </div>
            <div class="info-block">
                <span class="info-label">Banhos:</span>
                <small>DO - Dourado, RH - Ródio branco, RO - Redo,<br>
                    NE - Ródio negro, MIX - Mix, CL - Colorido</small>
            </div>
        </div>
    </div>

    <!-- Assinaturas -->
    <div class="signature-area">
        <div class="signature-line">
            Assinatura do Cliente
        </div>
        <div class="signature-line">
            Assinatura do Responsável LBF
        </div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <strong>LBF Portal</strong> - OS gerada em {{ now()->format('d/m/Y H:i') }}<br>
        Sistema interno - Uso exclusivo LBF
    </div>
</body>

</html>