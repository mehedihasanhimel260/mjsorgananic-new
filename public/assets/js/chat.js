function chatWidget(config) {
  return {
    open: false,
    sending: false,
    registered: false,
    messages: [],
    newMsg: '',
    name: '',
    phone: '',
    flashMessage: '',
    chatTicket: '',
    historyUrl: config.historyUrl,
    messageUrl: config.messageUrl,
    csrfToken: config.csrfToken,
    pollIntervalMs: 5000,
    pollTimer: null,

    init() {
      this.loadHistory();
    },

    toggleChat() {
      this.open = !this.open;

      if (this.open) {
        this.loadHistory();
        this.startPolling();
        this.$nextTick(() => this.scrollToBottom());
        return;
      }

      this.stopPolling();
    },

    closeChat() {
      this.open = false;
      this.stopPolling();
    },

    startPolling() {
      this.stopPolling();

      this.pollTimer = setInterval(() => {
        if (!this.open || this.sending) {
          return;
        }

        this.loadHistory(true);
      }, this.pollIntervalMs);
    },

    stopPolling() {
      if (this.pollTimer) {
        clearInterval(this.pollTimer);
        this.pollTimer = null;
      }
    },

    async parseJsonResponse(response) {
      const text = await response.text();

      if (!text) {
        return {};
      }

      try {
        return JSON.parse(text);
      } catch (error) {
        console.error('Invalid JSON response.', text);
        return {
          message: 'Server returned an invalid response.',
        };
      }
    },

    async loadHistory(isPolling = false) {
      try {
        const response = await fetch(this.historyUrl, {
          headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
        });

        if (!response.ok) {
          return;
        }

        const data = await this.parseJsonResponse(response);
        const incomingMessages = data.messages || [];
        const hasNewMessages = JSON.stringify(incomingMessages) !== JSON.stringify(this.messages);

        this.registered = !!data.registered;
        this.messages = incomingMessages;
        this.chatTicket = data.chat ? data.chat.ticket_number : '';
        if (!this.name.trim()) {
          this.name = data.user ? data.user.name || '' : '';
        }
        if (!this.phone.trim()) {
          this.phone = data.user ? data.user.phone || '' : '';
        }

        if (!isPolling || hasNewMessages) {
          this.$nextTick(() => this.scrollToBottom());
        }
      } catch (error) {
        console.error('Chat history load failed.', error);
      }
    },

    scrollToBottom() {
      if (this.$refs.scrollAnchor) {
        this.$refs.scrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'end' });
      }
    },

    showMessage(text) {
      this.flashMessage = text;

      setTimeout(() => {
        if (this.flashMessage === text) {
          this.flashMessage = '';
        }
      }, 3000);
    },

    async sendUser() {
      if (this.sending) {
        return;
      }

      if (!this.name.trim() || !this.phone.trim()) {
        this.showMessage('Name and phone number are required.');
        return;
      }

      if (!this.newMsg.trim()) {
        this.showMessage('Please write a message.');
        return;
      }

      this.sending = true;

      try {
        const formData = new URLSearchParams();
        formData.append('_token', this.csrfToken);
        formData.append('name', this.name);
        formData.append('phone', this.phone);
        formData.append('message', this.newMsg);

        const response = await fetch(this.messageUrl, {
          method: 'POST',
          headers: {
            Accept: 'application/json',
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-TOKEN': this.csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin',
          body: formData.toString(),
        });

        const data = await this.parseJsonResponse(response);

        if (!response.ok) {
          const errorMessage = data.message || Object.values(data.errors || {}).flat()[0] || 'Message send failed.';
          this.showMessage(errorMessage);
          return;
        }

        this.registered = true;
        this.messages = data.messages || [];
        this.chatTicket = data.chat ? data.chat.ticket_number : '';
        this.newMsg = '';
        this.startPolling();
        this.showMessage(data.message || 'Message sent successfully.');
        this.$nextTick(() => this.scrollToBottom());
      } catch (error) {
        console.error('Chat send failed.', error);
        this.showMessage('Could not send message right now.');
      } finally {
        this.sending = false;
      }
    }
  };
}
