@extends('front-site.layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-4">
  <div class="flex justify-center mb-6">
    <a href="{{ route('home') }}" class="inline-flex items-center px-5 py-3 bg-black rounded-xl shadow-lg">
      <span class="text-white text-2xl font-medium">MJS</span>
      <span class="text-green-400 text-2xl font-black ml-2">Organic</span>
    </a>
  </div>

  <h2 class="text-2xl font-semibold mb-3">Products</h2>
  <div id="product-list" class="grid md:grid-cols-3 gap-6">
    @foreach ($products as $product)
      <div class="bg-white rounded-xl shadow hover:scale-[1.02] transition">
        <img src="{{ $product['img'] }}" class="h-56 w-full object-cover rounded-t-xl" alt="{{ $product['name'] }}">

        <div class="p-4">
          <h3 class="font-bold text-lg">{{ $product['name'] }}</h3>
          <p class="text-green-700 font-semibold">Tk <span>{{ $product['price'] }}</span></p>
          <p class="text-sm text-gray-500 mb-3">{{ $product['desc'] }}</p>

          <button
            data-product-id="{{ $product['id'] }}"
            class="add-to-cart-btn w-full bg-green-700 text-white py-2 rounded-lg hover:bg-green-800">
            Order Now
          </button>
        </div>
      </div>
    @endforeach
  </div>

  <h2 class="text-2xl font-semibold mt-10 mb-3">Your Cart</h2>
  <div id="cart-empty" class="text-gray-500">Cart is empty</div>
  <div id="cart-items" class="space-y-3"></div>

  <div class="mt-4 p-4 bg-white rounded-xl shadow space-y-2">
    <div class="text-lg font-semibold">
      Product Total: Tk <span id="product-total">0</span>
    </div>
    <div id="delivery-charge-container" class="text-md text-red-600 font-medium hidden">
      Delivery Charge: Tk <span id="delivery-charge">0</span>
    </div>
    <div id="free-delivery-container" class="text-md text-green-600 font-medium hidden">
      Free Delivery Available
    </div>
    <div class="text-xl font-bold mt-2">
      Grand Total: Tk <span id="grand-total">0</span>
    </div>
  </div>

  <h2 class="text-2xl font-semibold mt-10 mb-3">Checkout</h2>
  <form action="{{ route('order.complete') }}" method="POST" class="bg-white p-4 rounded-xl shadow space-y-3 mb-10">
    @csrf
    <input id="customer-name" name="name" placeholder="Name" class="w-full border p-3 rounded-lg">
    <input id="customer-phone" name="phone" placeholder="Phone" class="w-full border p-3 rounded-lg">
    <textarea id="address" name="address" placeholder="Address" class="w-full border p-3 rounded-lg"></textarea>
    <input type="hidden" id="selected-delivery-charge" name="selected_delivery_charge" value="0">

    <div id="delivery-options" class="space-y-2">
      <p class="font-medium text-gray-700">Delivery Area</p>
      <label id="inside-dhaka-option" class="flex items-center gap-2 cursor-pointer">
        <input type="radio" name="delivery_area" value="inside" class="delivery-option">
        <span>Inside Dhaka Delivery Charge: Tk {{ (float) optional($deliverySetting)->inside_dhaka_delivery_charge }}</span>
      </label>
      <label id="outside-dhaka-option" class="flex items-center gap-2 cursor-pointer">
        <input type="radio" name="delivery_area" value="outside" class="delivery-option">
        <span>Out of Dhaka Delivery Charge: Tk {{ (float) optional($deliverySetting)->outside_dhaka_delivery_charge }}</span>
      </label>
      <label id="custom-delivery-option" class="flex items-center gap-2 cursor-pointer hidden">
        <input type="radio" name="delivery_area" value="custom" class="delivery-option">
        <span>Custom Delivery Charge: Tk {{ (float) optional($deliverySetting)->custom_delivery_charge }}</span>
      </label>
    </div>

    <button id="place-order-btn" type="submit" class="w-full bg-green-700 text-white py-3 rounded-lg hover:bg-green-800">
      Confirm Order
    </button>
  </form>
</div>

<div id="orderModal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 px-4">
  <div class="bg-white p-6 rounded-xl w-full max-w-md relative shadow-2xl">
    <button id="modal-close" type="button" class="absolute top-2 right-3 text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
    <h5 class="font-bold text-lg mb-2">Visitor Information</h5>
    <p class="text-sm text-gray-500 mb-4">
      Apner sathe jogajoger subidarthe apnar name ebong phone number din
    </p>

    <form id="visitor-info-form" class="space-y-3">
      <input id="modal-customer-name" name="name" type="text" placeholder="Name" class="w-full border p-3 rounded" required>
      <input id="modal-customer-phone" name="phone" type="text" placeholder="Phone" class="w-full border p-3 rounded" required>
      <input id="modal-product-id" name="product_id" type="hidden">
      <p id="visitor-status" class="text-xs text-gray-500"></p>
      <button id="modal-add-to-cart" type="submit" class="w-full bg-green-700 text-white py-2 rounded hover:bg-green-800">
        Save Information
      </button>
    </form>
  </div>
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const productsData = @json($products);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const deliverySettings = {
        inside: Number(@json((float) optional($deliverySetting)->inside_dhaka_delivery_charge)),
        outside: Number(@json((float) optional($deliverySetting)->outside_dhaka_delivery_charge)),
        custom: Number(@json((float) optional($deliverySetting)->custom_delivery_charge)),
        freeMin: Number(@json((float) optional($deliverySetting)->free_delivery_min_order_amount))
    };

    let cart = { items: [], product_total: 0 };
    let pendingProductId = null;
    let visitorIp = null;
    let visitorLocation = {
        permission: 'unknown',
        latitude: null,
        longitude: null
    };

    let sessionCustomer = {
        name: @json(session('customer_name', null)),
        phone: @json(session('customer_phone', null)),
        saved_address: @json(optional(\App\Models\User::find(session('user_id')))->saved_address)
    };

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
    const modalCloseBtn = document.getElementById('modal-close');
    const visitorInfoForm = document.getElementById('visitor-info-form');
    const visitorStatusEl = document.getElementById('visitor-status');

    const checkoutNameEl = document.getElementById('customer-name');
    const checkoutPhoneEl = document.getElementById('customer-phone');
    const addressEl = document.getElementById('address');
    const selectedDeliveryChargeEl = document.getElementById('selected-delivery-charge');
    const deliveryOptionEls = document.querySelectorAll('.delivery-option');
    const insideDhakaOptionEl = document.getElementById('inside-dhaka-option');
    const outsideDhakaOptionEl = document.getElementById('outside-dhaka-option');
    const customDeliveryOptionEl = document.getElementById('custom-delivery-option');

    if (sessionCustomer.name) {
        checkoutNameEl.value = sessionCustomer.name;
        modalCustomerNameEl.value = sessionCustomer.name;
    }

    if (sessionCustomer.phone) {
        checkoutPhoneEl.value = sessionCustomer.phone;
        modalCustomerPhoneEl.value = sessionCustomer.phone;
    }

    if (sessionCustomer.saved_address) {
        addressEl.value = sessionCustomer.saved_address;
    }

    function showVisitorModal() {
        modal.classList.remove('hidden');
    }

    function hideVisitorModal() {
        modal.classList.add('hidden');
    }

    function updateVisitorStatus(message, isError = false) {
        visitorStatusEl.textContent = message;
        visitorStatusEl.className = isError ? 'text-xs text-red-600' : 'text-xs text-gray-500';
    }

    function findProduct(productId) {
        const cartItem = cart.items.find((p) => p.id === productId);
        if (cartItem) {
            return cartItem;
        }

        return productsData.find((p) => p.id === productId);
    }

    function renderCart() {
        cartItemsContainer.innerHTML = '';

        if (cart.items.length === 0) {
            cartEmptyMessage.style.display = 'block';
        } else {
            cartEmptyMessage.style.display = 'none';

            cart.items.forEach((item) => {
                const product = findProduct(item.id);
                const cartItemEl = document.createElement('div');
                cartItemEl.className = 'bg-white rounded-xl shadow p-3 flex justify-between items-center';
                cartItemEl.innerHTML = `
                    <div class="flex items-center gap-3">
                        <img src="${product.img}" class="w-14 h-14 rounded-lg object-cover" alt="${product.name}">
                        <div>
                            <p class="font-semibold">${product.name}</p>
                            <p class="text-green-700">Tk <span>${product.price}</span></p>
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

    function getSelectedDeliveryCharge(total) {
        const selectedOption = document.querySelector('.delivery-option:checked');

        if (!selectedOption || total <= 0) {
            return 0;
        }

        if (selectedOption.value === 'inside') {
            return deliverySettings.inside;
        }

        if (selectedOption.value === 'outside') {
            return deliverySettings.outside;
        }

        if (selectedOption.value === 'custom') {
            return deliverySettings.custom;
        }

        return 0;
    }

    function updateDeliveryOptionVisibility(total) {
        const isCustomOnly = deliverySettings.freeMin > 0 && total >= deliverySettings.freeMin;

        if (isCustomOnly) {
            insideDhakaOptionEl.classList.add('hidden');
            outsideDhakaOptionEl.classList.add('hidden');
            customDeliveryOptionEl.classList.remove('hidden');

            const customRadio = customDeliveryOptionEl.querySelector('input');
            if (customRadio) {
                customRadio.checked = true;
            }
            return;
        }

        insideDhakaOptionEl.classList.remove('hidden');
        outsideDhakaOptionEl.classList.remove('hidden');
        customDeliveryOptionEl.classList.add('hidden');

        const checkedOption = document.querySelector('.delivery-option:checked');
        if (!checkedOption || checkedOption.value === 'custom') {
            const insideRadio = insideDhakaOptionEl.querySelector('input');
            if (insideRadio) {
                insideRadio.checked = true;
            }
        }
    }

    function calculateTotals() {
        const total = Number(cart.product_total || 0);
        updateDeliveryOptionVisibility(total);
        const delivery = getSelectedDeliveryCharge(total);
        const grandTotal = total + delivery;

        productTotalEl.textContent = total;
        grandTotalEl.textContent = grandTotal;
        selectedDeliveryChargeEl.value = delivery;

        if (delivery > 0) {
            deliveryChargeEl.textContent = delivery;
            deliveryChargeContainer.classList.remove('hidden');
            freeDeliveryContainer.classList.add('hidden');
        } else {
            deliveryChargeContainer.classList.add('hidden');
            freeDeliveryContainer.classList.toggle('hidden', cart.items.length === 0);
        }
    }

    deliveryOptionEls.forEach((optionEl) => {
        optionEl.addEventListener('change', function () {
            calculateTotals();
        });
    });

    function applyLocationPlaceholder() {
        if (visitorLocation.permission === 'granted') {
            addressEl.placeholder = 'Location captured. Add full address if needed';
            return;
        }

        if (visitorLocation.permission === 'denied') {
            addressEl.placeholder = 'Location blocked. Please allow browser location or write your address manually';
            return;
        }

        addressEl.placeholder = 'Please write your full address here';
    }

    async function pingVisitor() {
        try {
            const response = await fetch('{{ route("visitor.ping") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            });

            const result = await response.json();
            if (result.success) {
                visitorIp = result.ip_address;
            }
        } catch (error) {
            console.error('Could not capture visitor IP.', error);
        }
    }

    async function requestVisitorLocation() {
        if (!navigator.geolocation) {
            visitorLocation.permission = 'unknown';
            applyLocationPlaceholder();
            return;
        }

        return new Promise((resolve) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    visitorLocation.permission = 'granted';
                    visitorLocation.latitude = position.coords.latitude;
                    visitorLocation.longitude = position.coords.longitude;
                    applyLocationPlaceholder();
                    resolve(visitorLocation);
                },
                () => {
                    visitorLocation.permission = 'denied';
                    applyLocationPlaceholder();
                    resolve(visitorLocation);
                }
            );
        });
    }

    async function registerVisitor() {
        const name = modalCustomerNameEl.value.trim();
        const phone = modalCustomerPhoneEl.value.trim();

        if (!name || !phone) {
            updateVisitorStatus('Name and phone number are required.', true);
            return false;
        }

        updateVisitorStatus('Saving visitor information...');

        try {
            const response = await fetch('{{ route("visitor.register") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    phone: phone,
                    location_permission: visitorLocation.permission,
                    gps_lat: visitorLocation.latitude,
                    gps_lng: visitorLocation.longitude,
                    gps_address: addressEl.value.trim() || null
                })
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                updateVisitorStatus('Could not save visitor info. Please try again.', true);
                return false;
            }

            sessionCustomer.name = result.user.name;
            sessionCustomer.phone = result.user.phone;
            sessionCustomer.saved_address = result.user.saved_address;
            checkoutNameEl.value = result.user.name;
            checkoutPhoneEl.value = result.user.phone;
            if (result.user.saved_address) {
                addressEl.value = result.user.saved_address;
            }
            updateVisitorStatus(`Saved. IP: ${result.user.ip_address || visitorIp || 'N/A'}`);
            hideVisitorModal();
            return true;
        } catch (error) {
            console.error('Visitor registration failed.', error);
            updateVisitorStatus('Server error. Please try again.', true);
            return false;
        }
    }

    async function addToCart(productId, name = null, phone = null) {
        const payload = {
            product_id: productId,
            _token: csrfToken
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
                    'X-CSRF-TOKEN': csrfToken,
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

                if (name) {
                    sessionCustomer.name = name;
                    checkoutNameEl.value = name;
                }

                if (phone) {
                    sessionCustomer.phone = phone;
                    checkoutPhoneEl.value = phone;
                }
            } else {
                alert('Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
            alert('Could not connect to the server. Please check your connection.');
        }
    }

    async function updateCartItemQuantity(productId, action) {
        try {
            const response = await fetch('{{ route("cart.updateQuantity") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    product_id: productId,
                    action: action,
                    _token: csrfToken
                })
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
                alert('Something went wrong. Please try again.');
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
            alert('Could not connect to the server. Please check your connection.');
        }
    }


    productList.addEventListener('click', function (e) {
        if (!e.target.classList.contains('add-to-cart-btn')) {
            return;
        }

        const productId = Number(e.target.dataset.productId);

        if (!sessionCustomer.name || !sessionCustomer.phone) {
            pendingProductId = productId;
            modalProductIdEl.value = productId;
            showVisitorModal();
            updateVisitorStatus('Please save your name and phone first.');
            return;
        }

        addToCart(productId);
    });

    cartItemsContainer.addEventListener('click', function (e) {
        if (!e.target.classList.contains('quantity-btn')) {
            return;
        }

        const productId = Number(e.target.dataset.productId);
        updateCartItemQuantity(productId, e.target.dataset.action);
    });

    modalCloseBtn.addEventListener('click', function () {
        if (!sessionCustomer.name || !sessionCustomer.phone) {
            updateVisitorStatus('Save visitor information first to continue.', true);
            return;
        }

        hideVisitorModal();
    });

    visitorInfoForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const saved = await registerVisitor();

        if (saved && pendingProductId) {
            await addToCart(pendingProductId);
            pendingProductId = null;
        }
    });

    async function initialLoad() {
        try {
            await pingVisitor();
            await requestVisitorLocation();

            const response = await fetch('{{ route("cart.get") }}');
            const data = await response.json();

            cart = {
                items: data.items || [],
                product_total: data.product_total || 0
            };

            renderCart();

            if (!sessionCustomer.name || !sessionCustomer.phone) {
                updateVisitorStatus('Enter visitor name and phone to save visit log.');
                showVisitorModal();
            }
        } catch (error) {
            console.error('Could not load cart.', error);
        }
    }

    initialLoad();
});
</script>
@endpush
