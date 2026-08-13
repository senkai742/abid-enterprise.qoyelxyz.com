<!DOCTYPE html>
<html>
<head>
    <title>Invoice - Order #{{ $order->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { width: 100%; max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; box-sizing: border-box; }
        table { width: 100%; line-height: inherit; text-align: left; border-collapse: collapse; }
        table td { padding: 8px 5px; vertical-align: top; }
        table th { padding: 10px 5px; border-bottom: 2px solid #eee; text-align: left; }
        .text-right { text-align: right !important; }
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
        <p><strong>Supplier:</strong> {{ $order->supplier ? $order->supplier->first_name . ' ' . $order->supplier->last_name : 'N/A' }}</p>
        <p><strong>Address:</strong> {{ $order->supplier->address ?? 'N/A' }}</p>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('Y-m-d') }}</p>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th class="text-right">Unit Purchase Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    @php
                        $itemTotal = $item->quantity * $item->purchase_price;
                    @endphp
                    <tr>
                        <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->purchase_price, 2) }}</td>
                        <td class="text-right">{{ number_format($itemTotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 280px; margin-left: auto; margin-top: 20px; border-top: 1px solid #eee;">
            <tr>
                <td style="padding: 4px 0;"><strong>Sub Total:</strong></td>
                <td class="text-right" style="padding: 4px 0;">{{ number_format($order->sub_total, 2) }}</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td style="padding: 4px 0;"><strong>Discount:</strong></td>
                <td class="text-right" style="padding: 4px 0;">{{ number_format($order->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 4px 0;"><strong>Grand Total:</strong></td>
                <td class="text-right" style="padding: 4px 0;"><strong>{{ number_format($order->gr_total, 2) }}</strong></td>
            </tr>
            <tr>
                <td style="padding: 4px 0;"><strong>Paid Amount:</strong></td>
                <td class="text-right" style="padding: 4px 0;">{{ number_format($order->paid_amount, 2) }}</td>
            </tr>
            @php
                $due = max(0, $order->gr_total - $order->paid_amount);
            @endphp
            @if($due > 0)
            <tr style="color: red;">
                <td style="padding: 4px 0;"><strong>Due Amount:</strong></td>
                <td class="text-right" style="padding: 4px 0;"><strong>{{ number_format($due, 2) }}</strong></td>
            </tr>
            @endif
        </table>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>