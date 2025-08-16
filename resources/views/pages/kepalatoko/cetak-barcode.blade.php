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
   
    <div class="product-name">

    @if ($product->categories_id == 1)
        {{ $product->product_name }} {{ $product->kondisi }} {{ $product->warna }} {{ $product->ram }} / @if ($product->capacity != null)
            {{ $product->capacity->name }}
        @else
            -
        @endif (IMEI {{ $product->nomor_seri }})
    @else
        {{ $product->product_name }} {{ $product->nomor_seri }}
    @endif
    </div>
    <img src="data:image/png;base64,{{ $barcodeData }}" alt="barcode">
    <div>{{ $product->product_code }}</div>
</body>

</html>
