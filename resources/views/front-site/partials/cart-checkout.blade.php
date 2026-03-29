<div id="cart-section">
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
