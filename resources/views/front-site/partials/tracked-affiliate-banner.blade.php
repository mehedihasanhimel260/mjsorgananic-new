@if(!empty($trackedAffiliate))
  <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-700">
    You are visiting via affiliate: <strong>{{ $trackedAffiliate->name }}</strong>
  </div>
@endif
