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
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--lbf-dark);
            text-transform: uppercase;
            font-size: 13px;
        }

        /* ===== TABELAS ===== */
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }

        .table th,
        .table td {
            border: 1px solid var(--lbf-medium);
            padding: 8px;
            vertical-align: top;
        }

        .table th {
            background-color: var(--lbf-light);
            font-weight: 700;
            color: var(--lbf-dark);
            text-transform: uppercase;
            width: 25%;
        }

        .table tr:nth-child(even) {
            background-color: rgba(231, 227, 223, 0.3);
        }

        /* ===== STATUS E BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-in_progress {
            background-color: #cce7ff;
            color: #004085;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge {
            display: inline-block;
            padding: 3px 6px;
            font-size: 9px;
            border-radius: 3px;
            font-weight: 600;
            margin: 1px;
        }

        .badge-primary {
            background-color: var(--lbf-dark);
            color: var(--lbf-white);
        }

        .badge-accent {
            background-color: var(--lbf-accent);
            color: var(--lbf-dark);
        }

        .badge-secondary {
            background-color: var(--lbf-medium);
            color: var(--lbf-white);
        }

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
    </style>
</head>

<body>

    <!-- Cabeçalho -->
    <div class="header">
        <h1>LBF - ORDEM DE SERVIÇO</h1>
        <div class="subtitle">{{ $serviceOrder->origin }}</div>
        <div class="os-number">Nº: <strong>{{ $serviceOrder->order_number }}</strong></div>
    </div>

    <!-- Dados do Cliente -->
    <div class="section">
        <div class="section-title">Dados do Cliente</div>
        <table class="table">
            <tr>
                <th>Cliente</th>
                <td>{{ $serviceOrder->client_name }}</td>
                <th>CPF/CNPJ</th>
                <td>{{ $serviceOrder->client_document ?? 'Não informado' }}</td>
            </tr>
            <tr>
                <th>Telefone</th>
                <td>{{ $serviceOrder->client_phone ?? 'Não informado' }}</td>
                <th>Cód. Cliente</th>
                <td>{{ $serviceOrder->client_code }}</td>
            </tr>
        </table>
    </div>

    <!-- Informações da Ordem de Serviço -->
    <div class="section">
        <div class="section-title">Informações da Ordem de Serviço</div>
        <table class="table">
            <tr>
                <th>Data</th>
                <td>{{ $serviceOrder->order_date->format('d/m/Y') }}</td>
                <th>Consultora</th>
                <td>{{ $serviceOrder->consultant_name }}</td>
            </tr>
            <tr>
                <th>Tipo</th>
                <td>
                    @if($serviceOrder->is_guarantee)
                    <span class="badge badge-accent">Garantia</span>
                    @else
                    <span class="badge badge-primary">Assistência Técnica</span>
                    @endif
                </td>
                <th>Observações</th>
                <td> {{ $serviceOrder->observations }}</td>
            </tr>
        </table>

        @if($serviceOrder->is_guarantee)
        <div class="guarantee-warning">
            ⚠️ ORDEM DE GARANTIA - PROCEDIMENTOS ESPECIAIS APLICÁVEIS
        </div>
        @endif

        <!-- @if($serviceOrder->observations)
        <div class="mt-3">
            <strong>Observações:</strong>
            <div style="background-color: var(--lbf-light); padding: 10px; border-radius: 4px; margin-top: 5px;">
                {{ $serviceOrder->observations }}
            </div>
        </div>
        @endif -->
    </div>

    <!-- Produtos e Serviços -->
    <div class="section">
        <div class="section-title">Produtos e Serviços ({{ $serviceOrder->items->count() }})</div>
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Produto</th>
                    <th>Banho</th>
                    <th>Serviços</th>
                    <th>Observações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($serviceOrder->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        @if($item->product)
                        <strong>{{ $item->product->code }} - {{ $item->product->name }}</strong>
                        @elseif($item->identified_product_name)
                        <strong>{{ $item->identified_product_name }}</strong>
                        @else
                        <span class="text-muted">Produto não especificado</span>
                        @endif
                        @if($item->ai_identified_code)
                        <br><small class="text-muted">IA: {{ $item->ai_identified_code }}</small>
                        @endif
                    </td>
                    <td>
                        @if($item->coating_name)
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
                    <td>{{ $item->observations ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Assinaturas -->
    <div class="signature-area">
        <div class="signature-line">Assinatura do Cliente</div>
        <div class="signature-line">Assinatura do Responsável LBF</div>
    </div>

    <!-- Rodapé -->
    <div class="footer">
        <strong>LBF Portal</strong> - OS gerada em {{ now()->format('d/m/Y H:i') }}<br>
        Sistema interno - Uso exclusivo LBF
    </div>

</body>

</html>