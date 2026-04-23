<div id="cart-section">
  <h2 class="text-2xl font-semibold mt-10 mb-3">আপনার কার্ট</h2>
  <div id="cart-empty" class="text-gray-500">কার্ট খালি আছে</div>
  <div id="cart-items" class="space-y-3"></div>

  <div class="mt-4 p-4 bg-white rounded-xl shadow space-y-2">
    <div class="text-lg font-semibold">
      পণ্যের মোট মূল্য: Tk <span id="product-total">0</span>
    </div>
    <div id="delivery-charge-container" class="text-md text-red-600 font-medium hidden">
      ডেলিভারি চার্জ: Tk <span id="delivery-charge">0</span>
    </div>
    <div id="free-delivery-container" class="text-md text-green-600 font-medium hidden">
      ফ্রি ডেলিভারি প্রযোজ্য
    </div>
    <div class="text-xl font-bold mt-2">
      সর্বমোট: Tk <span id="grand-total">0</span>
    </div>
  </div>

  <h2 class="text-2xl font-semibold mt-10 mb-3">চেকআউট</h2>
  <form action="{{ route('order.complete') }}" method="POST" class="bg-white p-4 rounded-xl shadow space-y-3 mb-10">
    @csrf
    <input id="customer-name" name="name" placeholder="নাম" class="w-full border p-3 rounded-lg">
    <input id="customer-phone" name="phone" placeholder="ফোন নম্বর" class="w-full border p-3 rounded-lg">
    <textarea id="address" name="address" placeholder="ঠিকানা" class="w-full border p-3 rounded-lg"></textarea>
    <input type="hidden" id="selected-delivery-charge" name="selected_delivery_charge" value="0">

    <div id="delivery-options" class="space-y-2">
      <p class="font-medium text-gray-700">ডেলিভারি এরিয়া</p>
      <label id="inside-dhaka-option" class="flex items-center gap-2 cursor-pointer">
        <input type="radio" name="delivery_area" value="inside" class="delivery-option">
        <span>ঢাকার ভিতরে ডেলিভারি চার্জ: Tk {{ (float) optional($deliverySetting)->inside_dhaka_delivery_charge }}</span>
      </label>
      <label id="outside-dhaka-option" class="flex items-center gap-2 cursor-pointer">
        <input type="radio" name="delivery_area" value="outside" class="delivery-option">
        <span>ঢাকার বাইরে ডেলিভারি চার্জ: Tk {{ (float) optional($deliverySetting)->outside_dhaka_delivery_charge }}</span>
      </label>
      <label id="custom-delivery-option" class="flex items-center gap-2 cursor-pointer hidden">
        <input type="radio" name="delivery_area" value="custom" class="delivery-option">
        <span>কাস্টম ডেলিভারি চার্জ: Tk {{ (float) optional($deliverySetting)->custom_delivery_charge }}</span>
      </label>
    </div>

    <button id="place-order-btn" type="submit" class="w-full bg-green-700 text-white py-3 rounded-lg hover:bg-green-800">
      অর্ডার কনফার্ম করুন
    </button>
  </form>
</div>
