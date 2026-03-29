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
