<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden {{ $order->folio }}</title>
    <style>
        body { font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; font-size: 13px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 4px 6px; }
        th { background:#f3f4f6; text-align:left; }
        .totales { margin-top: 16px; }
    </style>
</head>
<body>
    <h1>Orden de evento {{ $order->folio }}</h1>
    <p><strong>Cliente:</strong> {{ $order->cliente_nombre }}</p>
    <p><strong>Fecha evento:</strong> {{ optional($order->fecha_inicio)->format('d/m/Y') }}</p>

    <h3>Ítems</h3>
    <table>
        <thead>
        <tr>
            <th>Descripción</th>
            <th>Cant.</th>
            <th>Precio</th>
            <th>Importe</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->lineas as $line)
            @php $importe = $line->cantidad * $line->precio_unitario; @endphp
            <tr>
                <td>{{ $line->descripcion }}</td>
                <td>{{ $line->cantidad }}</td>
                <td>${{ number_format($line->precio_unitario, 2) }}</td>
                <td>${{ number_format($importe, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totales">
        <p><strong>Total:</strong> ${{ number_format($order->total, 2) }}</p>
        <p><strong>Pagado:</strong> ${{ number_format($order->pagado_total, 2) }}</p>
        <p><strong>Saldo:</strong> ${{ number_format($order->saldo_pendiente, 2) }}</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
