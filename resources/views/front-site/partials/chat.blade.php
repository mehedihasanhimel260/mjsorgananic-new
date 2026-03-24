<div
  x-data="chatWidget({
    historyUrl: '{{ route('chat.history') }}',
    messageUrl: '{{ route('chat.message') }}',
    csrfToken: '{{ csrf_token() }}'
  })"
  x-init="init()">
  <button
    type="button"
    class="fixed bottom-5 right-5 z-50 rounded-full bg-indigo-600 px-5 py-4 text-sm font-semibold text-white shadow-lg transition hover:scale-110"
    @click="toggleChat()">
    Live Chat
  </button>

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
