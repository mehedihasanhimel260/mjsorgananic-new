@php
  $whatsappDigits = preg_replace('/\D+/', '', (string) ($siteSetting?->whatsapp_number ?? ''));
  if (str_starts_with($whatsappDigits, '0')) {
      $whatsappDigits = '88'.$whatsappDigits;
  }
  $whatsappUrl = $whatsappDigits ? 'https://wa.me/'.$whatsappDigits : null;
  $facebookUrl = $siteSetting?->facebook_url ?: 'https://m.me/mjsorganic';
@endphp

<div
  id="chat-widget"
  x-data="chatWidget({
    historyUrl: '{{ route('chat.history') }}',
    messageUrl: '{{ route('chat.message') }}',
    csrfToken: '{{ csrf_token() }}'
  })"
  x-init="init()">
  <button
    type="button"
    class="fixed bottom-5 right-5 z-50 rounded-full bg-indigo-600 px-5 py-4 text-sm font-semibold text-white shadow-lg transition hover:scale-110"
    @click="menuOpen = !menuOpen">
    Live Chat
  </button>

  <div
    x-show="menuOpen"
    x-transition
    class="fixed bottom-20 right-5 z-50 flex flex-col gap-3"
    style="display: none;">
    @if($whatsappUrl)
      <a
        href="{{ $whatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="WhatsApp"
        title="WhatsApp"
        class="flex h-12 w-12 items-center justify-center rounded-full bg-green-500 text-white shadow-lg transition hover:scale-110 hover:bg-green-600">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.33 4.95L2 22l5.28-1.39a9.88 9.88 0 0 0 4.76 1.21h.01c5.46 0 9.91-4.45 9.91-9.91C21.96 6.45 17.51 2 12.04 2Zm0 18.14h-.01a8.18 8.18 0 0 1-4.17-1.14l-.3-.18-3.13.82.84-3.05-.2-.31a8.19 8.19 0 1 1 6.97 3.86Zm4.49-6.14c-.25-.12-1.46-.72-1.69-.8-.23-.08-.4-.12-.56.12-.16.25-.65.8-.79.96-.15.16-.29.18-.54.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.22-1.45-1.36-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.16.04-.31-.02-.43-.06-.12-.56-1.35-.77-1.85-.2-.49-.41-.42-.56-.43h-.48c-.16 0-.43.06-.65.31-.23.25-.86.84-.86 2.05s.88 2.38 1 2.54c.12.16 1.73 2.64 4.2 3.7.59.25 1.05.4 1.4.51.59.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.17.21-.58.21-1.07.15-1.17-.06-.1-.22-.16-.47-.28Z"/>
        </svg>
      </a>
    @endif

    <a
      href="{{ $facebookUrl }}"
      target="_blank"
      rel="noopener noreferrer"
      aria-label="Facebook Messenger"
      title="Facebook Messenger"
      class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg transition hover:scale-110 hover:bg-blue-700">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.13 2 11.23c0 2.91 1.46 5.51 3.74 7.2V22l3.42-1.88c.9.25 1.86.38 2.84.38 5.52 0 10-4.13 10-9.23S17.52 2 12 2Zm1 12.4-2.55-2.72-4.98 2.72 5.48-5.82 2.61 2.72 4.92-2.72L13 14.4Z"/>
      </svg>
    </a>

    <button
      type="button"
      aria-label="Live chat"
      title="Live chat"
      class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition hover:scale-110 hover:bg-indigo-700"
      @click="openLiveChat()">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a8 8 0 0 1-8 8H7l-4 3 1.4-5.2A8 8 0 1 1 21 12Z"/>
      </svg>
    </button>
  </div>

  <div
    x-show="open"
    x-transition
    class="fixed bottom-20 right-5 z-50 flex h-[75vh] w-96 max-w-[95vw] flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
    style="display: none;">
    <div class="flex items-center justify-between bg-indigo-600 px-4 py-3 text-white">
      <div>
        <p class="text-sm font-semibold">Customer Support</p>
        <p class="text-xs text-indigo-100" x-text="chatTicket ? chatTicket : 'Start a chat'"></p>
      </div>
      <button type="button" class="text-sm" @click="closeChat()">Close</button>
    </div>

    <div class="border-b bg-indigo-50 px-4 py-3 text-xs text-indigo-700" x-show="flashMessage">
      <span x-text="flashMessage"></span>
    </div>

    <template x-if="!registered">
      <div class="space-y-3 border-b bg-gray-50 p-4">
        <input
          type="text"
          x-model="name"
          placeholder="Your name"
          class="w-full rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <input
          type="text"
          x-model="phone"
          placeholder="Phone number"
          class="w-full rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <p class="text-xs text-gray-500">Message pathate hole name ar phone submit korte hobe.</p>
      </div>
    </template>

    <div class="flex-1 space-y-3 overflow-y-auto bg-gray-100 p-4" x-ref="chatBody">
      <template x-if="messages.length === 0">
        <div class="rounded-2xl bg-white px-4 py-3 text-sm text-gray-500 shadow-sm">
          Kono message nei. Nicher box theke chat start korun.
        </div>
      </template>

      <template x-for="msg in messages" :key="msg.id">
        <div class="flex" :class="msg.type === 'user' ? 'justify-end' : 'justify-start'">
          <div
            :class="msg.type === 'user' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800'"
            class="max-w-[80%] rounded-2xl px-4 py-3 shadow-sm">
            <p class="text-sm" x-text="msg.text"></p>
            <p class="mt-1 text-[11px] opacity-70" x-text="msg.created_at || ''"></p>
          </div>
        </div>
      </template>
      <div x-ref="scrollAnchor"></div>
    </div>

    <div class="border-t bg-white p-3">
      <div class="flex gap-2">
        <textarea
          x-model="newMsg"
          placeholder="Type a message..."
          rows="2"
          class="flex-1 resize-none rounded-xl border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        <button
          type="button"
          @click="sendUser()"
          :disabled="sending"
          class="rounded-xl bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-indigo-300">
          <span x-text="sending ? 'Sending...' : 'Send'"></span>
        </button>
      </div>
    </div>
  </div>
</div>
