<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Order #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 100%; padding: 30px; border: 1px solid #eee; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 5px; vertical-align: top; }
        table th { padding: 10px; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <div class="invoice-box">
        @php
            $logoSetting = \App\Models\Setting::where('key','invoice_logo')->where('company_id', $order->company_id ?? auth()->user()?->company_id)->first();
        @endphp
        @if($logoSetting && $logoSetting->value)
            <div style="text-align:center; margin-bottom:15px;">
                <img src="{{ asset('public/' . $logoSetting->value) }}" alt="Company Logo" style="max-height:90px; max-width:250px;">
            </div>
        @endif
        <h2>Invoice</h2>
        <p><strong>Customer:</strong> {{ $order->customer->first_name }}</p>
        <p><strong>Address:</strong> {{ $order->customer->address }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Sell Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->sell_price, 2) }}</td>
                        <td>{{ number_format($item->quantity * $item->sell_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><strong>Grand Total:</strong> {{ number_format($order->items->sum(function($item) {
            return $item->sell_price * $item->quantity;
        }), 2) }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>