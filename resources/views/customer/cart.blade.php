@extends('customer.layouts.master')

@section('content')
       <div class="container-fluid py-5">
            <div class="container py-5">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (empty($cart))
                    <h4 class="text-center">Cart is empty</h4>
                @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                          <tr>
                            <th scope="col">Gambar</th>
                            <th scope="col">Menu</th>
                            <th scope="col">Harga</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Total</th>
                            <th scope="col">Aksi</th>
                          </tr>
                        </thead>
                        <tbody>

                            @php
                               $subtotal = 0; 
                               $total = $subTotal + $tax;
                            @endphp
                            
                            @foreach ($cart as $item)
                            @php
                                $itemTotal = $item['price'] * $item['quantity'];
                            @endphp
                            @endforeach

                            <tr>
                                <th scope="row">
                                    <div class="d-flex align-items-center">
                                        <img src="https://images.unsplash.com/photo-1591325418441-ff678baf78ef" class="img-fluid me-5 rounded-circle" style="width: 80px; height: 80px;" alt="">
                                    </div>
                                </th>
                                <td>
                                    <p class="mb-0 mt-4">Ichiraku Ramen</p>
                                </td>
                                <td>
                                    <p class="mb-0 mt-4">Rp25.000,00</p>
                                </td>
                                <td>
                                    <div class="input-group quantity mt-4" style="width: 100px;">
                                        <div class="input-group-btn">
                                            <button class="btn btn-sm btn-minus rounded-circle bg-light border" onclick="updateQuantity(-1, {{ $item['id'] }})">
                                            <i class="fa fa-minus"></i>
                                            </button>
                                        </div>
                                        <input id="qty-{{ $item['id'] }}" type="text" class="form-control form-control-sm text-center border-0" value="1">
                                        <div class="input-group-btn">
                                            <button class="btn btn-sm btn-plus rounded-circle bg-light border" onclick="updateQuantity(1, {{ $item['id'] }})">
                                                <i class="fa fa-plus" ></i>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="mb-0 mt-4">Rp25.000,00</p>
                                </td>
                                <td>
                                    <button class="btn btn-md rounded-circle bg-light border mt-4" onclick="if(confirm('Apakah anda yakin ingin menghapus item ini?')) { removeItemFromCart('{{ $item['id']}}')}">
                                        <i class="fa fa-times text-danger" ></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    @php
                        $tax = $subtotal * 0.1;
                        $total = $subtotal + $tax;
                    @endphp

                    <div class="d-flex justify-content-end">
                        <a href="{{route('cart.clear')}}" class="btn border-secondary py-3 text-primary text-uppercase mb-4" type="button" onclick=" return confirm('Apakah anda yakin ingin mengosongkan keranjang?')">Kosongkan Keranjang</a>
                    </div>

                </div>
                <div class="row g-4 justify-content-end mt-1">
                    <div class="col-8"></div>
                    <div class="col-sm-8 col-md-7 col-lg-6 col-xl-4">
                        <div class="bg-light rounded">
                            <div class="p-4">
                                <h2 class="display-6 mb-4">Total <span class="fw-normal">Pesanan</span></h2>
                                <div class="d-flex justify-content-between mb-4">
                                    <h5 class="mb-0 me-4">Subtotal</h5>
                                    <p class="mb-0">Rp85.000,00</p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0 me-4">Pajak (10%)</p>
                                    <div class="">
                                        <p class="mb-0">Rp8.500,00</p>
                                    </div>
                                </div>
                            </div>
                            <div class="py-4 mb-4 border-top d-flex justify-content-between">
                                <h4 class="mb-0 ps-4 me-4">Total</h4>
                                <h5 class="mb-0 pe-4">Rp93.500,00</h5>
                            </div>
                            
                        </div>
                        <div class="d-flex justify-content-end">
                            <div class="mb-0 mb-3">
                                <a href="{{ route('checkout') }}" class="btn border-secondary py-3 text-primary text-uppercase mb-4" type="button">Lanjut ke Pembayaran</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

@endsection


@section('scripts')
<script>

    // haus item cart
    function updateQuantity(itemId, change) {
        var qtyInput = document.querySelector('qty-'+itemId);
        var currentQty = parseInt(qtyInput.value);
        var newQty = currentQty + change;


        if (newQty <= 0) {
            if (confirm('Apakah anda yakin ingin menghapus item ini?') {
                removeItemFromCart(itemId);
            })
            return;
        }

        fetch ("{{ route('cart.update')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: itemId})
        })

        .then(response => response.json())
        .then(data => {
            if (data.success) {
                qtyInput.value = data.qty;
                window.location.reload();   
            } else {
                alert(data.message);
            }

        })

    }

    function removeItemFromCart(itemId) {
        fetch("{{route('cart.remove')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ id: itemId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alet(data.message);
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus item dari keranjang.');
        })
    }


</script>
@endsection