@extends('layouts.admin_main')
@section('content')
<section class="section main-section">
  <div class="card mb-6">
    <div class="card-content">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-sm text-gray-500">Operational overview for the selected date range.</p>
        </div>
        <div class="buttons">
          <a href="{{ route('admin.affiliates.index') }}" class="button">Affiliates</a>
          <a href="{{ route('admin.dashboard', ['range' => 'today']) }}" class="button {{ $range === 'today' ? 'blue' : '' }}">Today</a>
          <a href="{{ route('admin.dashboard', ['range' => '7d']) }}" class="button {{ $range === '7d' ? 'blue' : '' }}">7 Days</a>
          <a href="{{ route('admin.dashboard', ['range' => '30d']) }}" class="button {{ $range === '30d' ? 'blue' : '' }}">30 Days</a>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-4 mb-6">
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Total Orders</h3>
            <h1>{{ $summary['total_orders'] }}</h1>
          </div>
          <span class="icon widget-icon text-blue-500"><i class="mdi mdi-cart-outline mdi-48px"></i></span>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Total Sales</h3>
            <h1>{{ number_format($summary['total_sales'], 2) }}</h1>
          </div>
          <span class="icon widget-icon text-green-500"><i class="mdi mdi-currency-bdt mdi-48px"></i></span>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>New Users</h3>
            <h1>{{ $summary['total_users'] }}</h1>
          </div>
          <span class="icon widget-icon text-purple-500"><i class="mdi mdi-account-multiple mdi-48px"></i></span>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Pending Chats</h3>
            <h1>{{ $summary['pending_chats'] }}</h1>
          </div>
          <span class="icon widget-icon text-yellow-500"><i class="mdi mdi-chat-processing-outline mdi-48px"></i></span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 md:grid-cols-2 xl:grid-cols-3 mb-6">
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Low Stock Products</h3>
            <h1>{{ $summary['low_stock_products'] }}</h1>
          </div>
          <span class="icon widget-icon text-red-500"><i class="mdi mdi-alert-outline mdi-48px"></i></span>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Affiliate Orders</h3>
            <h1>{{ $summary['affiliate_orders'] }}</h1>
          </div>
          <span class="icon widget-icon text-indigo-500"><i class="mdi mdi-account-star-outline mdi-48px"></i></span>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-content">
        <div class="flex items-center justify-between">
          <div class="widget-label">
            <h3>Affiliate Commission</h3>
            <h1>{{ number_format($summary['affiliate_commission'], 2) }}</h1>
          </div>
          <span class="icon widget-icon text-emerald-500"><i class="mdi mdi-cash-multiple mdi-48px"></i></span>
        </div>
      </div>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 xl:grid-cols-2 mb-6">
    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-cart-outline"></i></span>Recent Orders</p>
      </header>
      <div class="card-content">
        <table>
          <thead>
            <tr>
              <th>Order No</th>
              <th>Customer</th>
              <th>Amount</th>
              <th>Delivery</th>
              <th>Status</th>
              <th>Track ID</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentOrders as $order)
            <tr>
              <td><a href="{{ route('admin.orders.show', $order->id) }}" class="text-blue-600 hover:underline">{{ $order->order_number }}</a></td>
              <td>{{ $order->user?->name ?? 'N/A' }}</td>
              <td>{{ number_format((float) $order->total_amount, 2) }}</td>
              <td>{{ number_format((float) ($order->delivery_charge ?? 0), 2) }}</td>
              <td>{{ $order->order_status ?? 'pending' }}</td>
              <td>
                @if($order->track_id)
                  <a href="https://steadfast.com.bd/t/{{ $order->track_id }}" target="_blank" class="text-blue-600 hover:underline">
                    {{ $order->track_id }}
                  </a>
                @else
                  N/A
                @endif
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="has-text-centered">No recent orders found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-chat-processing-outline"></i></span>Recent Chats</p>
      </header>
      <div class="card-content">
        <table>
          <thead>
            <tr>
              <th>Ticket</th>
              <th>User</th>
              <th>Latest Message</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentChats as $chat)
            <tr>
              <td>{{ $chat->ticket_number }}</td>
              <td>{{ $chat->user?->name ?? 'Guest' }}</td>
              <td>{{ \Illuminate\Support\Str::limit($chat->latestConversion?->convertion_message ?? 'No message', 45) }}</td>
              <td>{{ $chat->status }}</td>
              <td><a href="{{ route('admin.chats.show', $chat->id) }}" class="button small blue">View</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="has-text-centered">No recent chat found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 xl:grid-cols-2 mb-6">
    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-alert-outline"></i></span>Low Stock Alerts</p>
      </header>
      <div class="card-content">
        <table>
          <thead>
            <tr>
              <th>Product</th>
              <th>SKU</th>
              <th>Stock</th>
              <th>Alert</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($stockAlerts as $product)
            <tr>
              <td><a href="{{ route('admin.products.edit', $product->id) }}" class="text-blue-600 hover:underline">{{ $product->name }}</a></td>
              <td>{{ $product->sku }}</td>
              <td>{{ (int) $product->stock_qty }}</td>
              <td>{{ (int) $product->stock_qty === 0 ? 'Out of stock' : 'Low stock' }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="has-text-centered">No stock alert found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-cash-multiple"></i></span>Affiliate Activity</p>
      </header>
      <div class="card-content">
        <table>
          <thead>
            <tr>
              <th>Affiliate</th>
              <th>Order</th>
              <th>Product</th>
              <th>Commission</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($affiliateActivities as $activity)
            <tr>
              <td>{{ $activity->affiliate?->name ?? 'N/A' }}</td>
              <td>
                @if($activity->order)
                  <a href="{{ route('admin.orders.show', $activity->order->id) }}" class="text-blue-600 hover:underline">{{ $activity->order->order_number }}</a>
                @else
                  N/A
                @endif
              </td>
              <td>{{ $activity->product?->name ?? 'N/A' }}</td>
              <td>{{ number_format((float) $activity->commission_amount, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="has-text-centered">No affiliate activity found.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="grid gap-6 grid-cols-1 xl:grid-cols-3 mb-6">
    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-truck-fast-outline"></i></span>Courier Snapshot</p>
      </header>
      <div class="card-content">
        <table>
          <tbody>
            <tr><td>Booked Orders</td><td>{{ $courierSnapshot['booked'] }}</td></tr>
            <tr><td>Unbooked Orders</td><td>{{ $courierSnapshot['unbooked'] }}</td></tr>
            <tr><td>In Review</td><td>{{ $courierSnapshot['in_review'] }}</td></tr>
            <tr><td>Delivered</td><td>{{ $courierSnapshot['delivered'] }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-chart-pie"></i></span>Order Source</p>
      </header>
      <div class="card-content">
        <table>
          <tbody>
            <tr><td>Direct Orders</td><td>{{ $sourceOverview['direct_orders'] }}</td></tr>
            <tr><td>Affiliate Orders</td><td>{{ $sourceOverview['affiliate_orders'] }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card has-table">
      <header class="card-header">
        <p class="card-header-title"><span class="icon"><i class="mdi mdi-map-marker-outline"></i></span>Delivery Overview</p>
      </header>
      <div class="card-content">
        <table>
          <tbody>
            <tr><td>Inside Dhaka ({{ number_format($deliveryOverview['inside_charge'], 2) }})</td><td>{{ $deliveryOverview['inside_count'] }}</td></tr>
            <tr><td>Outside Dhaka ({{ number_format($deliveryOverview['outside_charge'], 2) }})</td><td>{{ $deliveryOverview['outside_count'] }}</td></tr>
            <tr><td>Custom Charge ({{ number_format($deliveryOverview['custom_charge'], 2) }})</td><td>{{ $deliveryOverview['custom_count'] }}</td></tr>
            <tr><td>Free Delivery</td><td>{{ $deliveryOverview['free_count'] }}</td></tr>
            <tr><td>Total Delivery Amount</td><td>{{ number_format($deliveryOverview['delivery_amount'], 2) }}</td></tr>
            <tr><td>Wallet Credit</td><td>{{ number_format($deliveryOverview['wallet_credit'], 2) }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card has-table">
    <header class="card-header">
      <p class="card-header-title"><span class="icon"><i class="mdi mdi-star-outline"></i></span>Top Selling Products</p>
    </header>
    <div class="card-content">
      <table>
        <thead>
          <tr>
            <th>Product</th>
            <th>Quantity Sold</th>
            <th>Revenue</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($topProducts as $product)
          <tr>
            <td>{{ $product->name }}</td>
            <td>{{ (int) $product->total_qty }}</td>
            <td>{{ number_format((float) $product->total_revenue, 2) }}</td>
          </tr>
          @empty
          <tr><td colspan="3" class="has-text-centered">No product sales data found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</section>
@endsection


