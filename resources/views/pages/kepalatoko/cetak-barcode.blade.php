<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        body {
            text-align: center;
            font-family: sans-serif;
            font-size: 14px;
        }

        .product-name {
            font-weight: bold;
            margin-bottom: 8px;
        }

        img {
            max-width: 100%;
        }
    </style>
</head>

<body>
    @if ($product->categories_id == 1)
        <div class="product-name">
            {{ $item->product_name }} {{ $item->kondisi }} {{ $item->warna }} {{ $item->ram }} / @if ($item->capacity != null)
        </div>
    @else
        <div class="product-name">{{ $product->product_name }} {{ $product->kondisi }}</div>
    @endif
    <img src="data:image/png;base64,{{ $barcodeData }}" alt="barcode">
    <div>{{ $product->product_code }}</div>
</body>

</html>
