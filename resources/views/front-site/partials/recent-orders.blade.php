@if(isset($recentOrders) && $recentOrders->isNotEmpty())
<div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
  <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
    <h2 class="text-base font-semibold text-gray-900">Your Recent Orders</h2>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
      <thead class="bg-white">
        <tr>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Order ID</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Track ID</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Products</th>
          <th class="px-4 py-3 text-left font-semibold text-gray-600">Quantity</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        @foreach ($recentOrders as $order)
        <tr class="align-top">
          <td class="px-4 py-3 font-medium text-gray-900">{{ $order->order_number }}</td>
          <td class="px-4 py-3">
            @if($order->track_id)
              <a href="https://steadfast.com.bd/t/{{ $order->track_id }}" target="_blank" class="font-medium text-blue-600 hover:underline">
                {{ $order->track_id }}
              </a>
            @else
              <span class="text-gray-400">N/A</span>
            @endif
          </td>
          <td class="px-4 py-3 text-gray-700">
            {{ $order->items->pluck('product.name')->filter()->join(', ') ?: 'N/A' }}
          </td>
          <td class="px-4 py-3 text-gray-700">
            {{ $order->items->sum('quantity') }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
