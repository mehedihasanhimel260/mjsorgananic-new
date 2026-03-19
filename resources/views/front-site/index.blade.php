@extends('front-site.layouts.app')
@section('content')
<div class="max-w-6xl mx-auto p-4">

  <!-- TITLE -->
  <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">
    MJS-Organic
  </h1>

  <!-- PRODUCTS -->
  <h2 class="text-2xl font-semibold mb-3">Products</h2>

  <div id="product-list" class="grid md:grid-cols-3 gap-6">
    @foreach ($products as $product)
        <div class="bg-white rounded-xl shadow hover:scale-[1.02] transition">
            <img src="{{ $product['img'] }}"
                 class="h-56 w-full object-cover rounded-t-xl">

            <div class="p-4">
                <h3 class="font-bold text-lg">{{ $product['name'] }}</h3>
                <p class="text-green-700 font-semibold">৳ <span>{{ $product['price'] }}</span></p>
                <p class="text-sm text-gray-500 mb-3">{{ $product['desc'] }}</p>

                <button
                    data-product-id="{{ $product['id'] }}"
                    class="add-to-cart-btn w-full bg-green-700 text-white py-2 rounded-lg hover:bg-green-800">
                    এখনই অর্ডার করো
                </button>
            </div>
        </div>
    @endforeach
  </div>

  <!-- CART -->
  <h2 class="text-2xl font-semibold mt-10 mb-3">Your Cart</h2>
  <div id="cart-empty" class="text-gray-500">কার্ট খালি</div>
  <div id="cart-items" class="space-y-3"></div>

  <!-- TOTAL + DELIVERY -->
  <div class="mt-4 p-4 bg-white rounded-xl shadow space-y-2">
    <div class="text-lg font-semibold">
      Product Total: ৳ <span id="product-total">0</span>
    </div>
    <div id="delivery-charge-container" class="text-md text-red-600 font-medium hidden">
        Delivery Charge: ৳ <span id="delivery-charge">0</span>
    </div>
    <div id="free-delivery-container" class="text-md text-green-600 font-medium hidden">
        🎉 Free Delivery!
    </div>
    <div class="text-xl font-bold mt-2">
      Grand Total: ৳ <span id="grand-total">0</span>
    </div>
  </div>

  <!-- CHECKOUT -->
  <h2 class="text-2xl font-semibold mt-10 mb-3">Checkout</h2>
  <div class="bg-white p-4 rounded-xl shadow space-y-3 mb-10">
    <input id="customer-name"
           placeholder="Name"
           class="w-full border p-3 rounded-lg">

    <input id="customer-phone"
           placeholder="Phone"
           class="w-full border p-3 rounded-lg">

    <textarea id="address"
              placeholder="Address"
              class="w-full border p-3 rounded-lg"></textarea>

    <button
      id="place-order-btn"
      class="w-full bg-green-700 text-white py-3 rounded-lg hover:bg-green-800">
      অর্ডার নিশ্চিত করুন
    </button>
  </div>
</div>

<!-- TAILWIND MODAL -->
<div id="orderModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
  <div class="bg-white p-6 rounded-xl w-96 relative">
    <form action="{{route('cart.add')}}" method="POST">
        @csrf
    <h5 class="font-bold text-lg mb-4">আপনার তথ্য দিন</h5>
    <input id="modal-customer-name" name="name" type="text" placeholder="Name" class="w-full border p-3 rounded mb-3">
    <input id="modal-customer-phone" name="phone" type="text" placeholder="Phone" class="w-full border p-3 rounded mb-3">
    <input id="modal-product-id" name="product_id" type="hidden">
    <button id="modal-add-to-cart" type="submit" class="w-full bg-green-700 text-white py-2 rounded hover:bg-green-800">Cart এ যোগ করুন</button>
    <button id="modal-close" type="button" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
    </form>
  </div>
</div>

@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsData = @json($products);
    let cart = { items: [], product_total: 0 };

    const productList = document.getElementById('product-list');
    const cartItemsContainer = document.getElementById('cart-items');
    const cartEmptyMessage = document.getElementById('cart-empty');
    const productTotalEl = document.getElementById('product-total');
    const deliveryChargeContainer = document.getElementById('delivery-charge-container');
    const deliveryChargeEl = document.getElementById('delivery-charge');
    const freeDeliveryContainer = document.getElementById('free-delivery-container');
    const grandTotalEl = document.getElementById('grand-total');

    const modal = document.getElementById('orderModal');
    const modalCustomerNameEl = document.getElementById('modal-customer-name');
    const modalCustomerPhoneEl = document.getElementById('modal-customer-phone');
    const modalProductIdEl = document.getElementById('modal-product-id');
    const modalAddToCartBtn = document.getElementById('modal-add-to-cart');
    const modalCloseBtn = document.getElementById('modal-close');

    const checkoutNameEl = document.getElementById('customer-name');
    const checkoutPhoneEl = document.getElementById('customer-phone');

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // session থেকে নাম ও ফোন
    let sessionCustomer = {
        name: @json(session('customer_name', null)),
        phone: @json(session('customer_phone', null))
    };

    // Checkout form prefill
    if(sessionCustomer.name) checkoutNameEl.value = sessionCustomer.name;
    if(sessionCustomer.phone) checkoutPhoneEl.value = sessionCustomer.phone;

    function findProduct(productId) {
        // First, try to find in the cart data from backend
        const cartItem = cart.items.find(p => p.id === productId);
        if (cartItem) return cartItem;
        // Fallback to initial products data
        return productsData.find(p => p.id === productId);
    }

    function renderCart() {
        cartItemsContainer.innerHTML = '';
        if(cart.items.length === 0) {
            cartEmptyMessage.style.display = 'block';
        } else {
            cartEmptyMessage.style.display = 'none';
            cart.items.forEach(item => {
                const product = findProduct(item.id);
                const cartItemEl = document.createElement('div');
                cartItemEl.className = 'bg-white rounded-xl shadow p-3 flex justify-between items-center';
                cartItemEl.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${product.img}" class="w-14 h-14 rounded-lg object-cover">
                        <div>
                            <p class="font-semibold">${product.name}</p>
                            <p class="text-green-700">৳ <span>${product.price}</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="quantity-btn bg-gray-200 text-gray-700 px-2 py-1 rounded-lg hover:bg-gray-300" data-product-id="${item.id}" data-action="decrement">-</button>
                        <span>${item.qty}</span>
                        <button class="quantity-btn bg-gray-200 text-gray-700 px-2 py-1 rounded-lg hover:bg-gray-300" data-product-id="${item.id}" data-action="increment">+</button>
                    </div>
                `;
                cartItemsContainer.appendChild(cartItemEl);
            });
        }
        calculateTotals();
    }

    function calculateTotals() {
        const total = cart.product_total;
        const delivery = total > 0 && total < 600 ? 60 : 0;
        const grandTotal = total + delivery;

        productTotalEl.textContent = total;

        if(delivery>0){
            deliveryChargeEl.textContent = delivery;
            deliveryChargeContainer.classList.remove('hidden');
            freeDeliveryContainer.classList.add('hidden');
        } else {
            deliveryChargeContainer.classList.add('hidden');
            freeDeliveryContainer.classList.toggle('hidden', cart.items.length === 0);
        }

        grandTotalEl.textContent = grandTotal;
    }

    async function addToCart(productId, name=null, phone=null){
        const payload = {
            product_id: productId,
            _token: '{{ csrf_token() }}'
        };

        if (name && phone) {
            payload.name = name;
            payload.phone = phone;
        }

        try {
            const response = await fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                 const errorData = await response.json();
                 console.error('Error response:', errorData);
                 throw new Error('Network response was not ok');
            }

            const result = await response.json();

            if (result.success) {
                cart = result.cart;
                renderCart();
                // Update session customer data if it was passed
                if (name) {
                    sessionCustomer.name = name;
                    checkoutNameEl.value = name;
                }
                if (phone) {
                    sessionCustomer.phone = phone;
                    checkoutPhoneEl.value = phone;
                }
            } else {
                console.error('Failed to add to cart:', result.message);
                alert('Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
                    alert('Could not connect to the server. Please check your connection.');
                }
            }
            
                async function updateCartItemQuantity(productId, action){
                    const payload = {
                        product_id: productId,
                        action: action,
                        _token: '{{ csrf_token() }}'
                    };
            
                    try {
                        const response = await fetch('{{ route("cart.updateQuantity") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
            
                        if (!response.ok) {
                             const errorData = await response.json();
                             console.error('Error response:', errorData);
                             throw new Error('Network response was not ok');
                        }
            
                        const result = await response.json();
            
                        if (result.success) {
                            cart = result.cart;
                            renderCart();
                        } else {
                            console.error('Failed to update cart quantity:', result.message);
                            alert('Something went wrong. Please try again.');
                        }
                    } catch (error) {
                        console.error('There was a problem with the fetch operation:', error);
                        alert('Could not connect to the server. Please check your connection.');
                    }
                }
            
                // PRODUCT BUTTON CLICK
                productList.addEventListener('click', function(e){
                    if(e.target.classList.contains('add-to-cart-btn')){
                        const productId = parseInt(e.target.dataset.productId);
            
                        if(!sessionCustomer.name || !sessionCustomer.phone){
                            modalProductIdEl.value = productId;
                            modalCustomerNameEl.value = sessionCustomer.name ?? '';
                            modalCustomerPhoneEl.value = sessionCustomer.phone ?? '';
                            modal.classList.remove('hidden');
                        } else {
                            addToCart(productId);
                        }
                    }
                });
            
                // CART QUANTITY BUTTON CLICK
                cartItemsContainer.addEventListener('click', function(e){
                    if(e.target.classList.contains('quantity-btn')){
                        const productId = parseInt(e.target.dataset.productId);
                        const action = e.target.dataset.action;
                        updateCartItemQuantity(productId, action);
                    }
                });


    modalCloseBtn.addEventListener('click', function(){
        modal.classList.add('hidden');
    });

    // Initial cart load
    async function initialLoad() {
        try {
            const response = await fetch('{{ route("cart.get") }}');
            const data = await response.json();
            cart = {
                items: data.items || [],
                product_total: data.product_total || 0
            };
            renderCart();
        } catch(error) {
            console.error("Could not load cart.", error);
        }
    }

    initialLoad();
});
</script>
@endpush
