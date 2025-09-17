{{-- <div wire:change="updateQuantity('{{ $cart_item->rowId }}', '{{ $cart_item->id }}')">
    <x-input style="min-width: 40px;max-width: 90px;" type="number" value="{{ $cart_item->qty }}"
        min="1" wire:model.defer="quantity.{{ $cart_item->id }}" />
</div> --}}
<div wire:change="updateQuantity('{{ $cart_item->rowId }}', '{{ $cart_item->id }}')">
    <x-input 
        style="min-width: 40px; max-width: 90px;" 
        type="number" 
        value="{{ $cart_item->qty }}" 
        min="1"
        oninput="if(this.value==='' || this.value<1) this.value=1;" 
        wire:model.defer="quantity.{{ $cart_item->id }}" 
    />
</div>
