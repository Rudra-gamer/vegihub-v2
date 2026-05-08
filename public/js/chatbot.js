(function() {
    const chatbot = {
        isOpen: false,
        messages: [],
        suggestions: ['Track my order', 'Check fresh arrivals', 'How to sell?'],
        
        init: function() {
            this.render();
            this.bindEvents();
            this.appendMessage('bot', "Hello! I'm VegiBot 🌿, your personal shopping assistant. How can I help you today?");
            this.renderSuggestions(this.suggestions);
        },

        render: function() {
            const widget = document.createElement('div');
            widget.id = 'chatbot-widget';
            widget.innerHTML = `
                <button id="chatbot-fab" title="Chat with VegiBot">💬</button>
                <div id="chatbot-window">
                    <div class="chatbot-header">
                        <div class="chatbot-info">
                            <div class="chatbot-avatar">🌿</div>
                            <div class="chatbot-title">
                                <h4>VegiBot</h4>
                                <div class="chatbot-status">Online • Smart Assistant</div>
                            </div>
                        </div>
                        <button class="chatbot-close">×</button>
                    </div>
                    <div class="chatbot-messages" id="chat-messages"></div>
                    <div class="chatbot-input">
                        <input type="text" id="chat-input-field" placeholder="Type your message..." autocomplete="off">
                        <button id="chat-send-btn">➤</button>
                    </div>
                </div>
            `;
            document.body.appendChild(widget);
        },

        bindEvents: function() {
            const fab = document.getElementById('chatbot-fab');
            const closeBtn = document.querySelector('.chatbot-close');
            const sendBtn = document.getElementById('chat-send-btn');
            const input = document.getElementById('chat-input-field');

            fab.addEventListener('click', () => this.toggle());
            closeBtn.addEventListener('click', () => this.toggle());
            
            sendBtn.addEventListener('click', () => this.sendMessage());
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') this.sendMessage();
            });
        },

        toggle: function() {
            this.isOpen = !this.isOpen;
            document.getElementById('chatbot-window').classList.toggle('active', this.isOpen);
            if (this.isOpen) {
                document.getElementById('chat-input-field').focus();
            }
        },

        sendMessage: async function(text = null) {
            const input = document.getElementById('chat-input-field');
            const messageText = text || input.value.trim();
            if (!messageText) return;

            if (!text) input.value = '';
            
            this.appendMessage('user', messageText);
            
            // Show typing indicator
            const typingId = this.showTyping();
            
            try {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content || 
                           document.querySelector('input[name="_csrf_token"]')?.value || '';

                // Get last 10 messages for history
                const currentHistory = this.messages.slice(-10);

                const response = await fetch(baseUrl + 'api/chatbot', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `message=${encodeURIComponent(messageText)}&_csrf_token=${encodeURIComponent(csrf)}&history=${encodeURIComponent(JSON.stringify(currentHistory))}`
                });

                const data = await response.json();
                this.removeTyping(typingId);

                if (data.success) {
                    this.appendMessage('bot', data.response);
                    if (data.suggestions && data.suggestions.length > 0) {
                        this.renderSuggestions(data.suggestions);
                    }
                } else {
                    this.appendMessage('bot', "Sorry, I'm having trouble connecting right now. Please try again later.");
                }
            } catch (error) {
                console.error('Chatbot error:', error);
                this.removeTyping(typingId);
                this.appendMessage('bot', "Oops! Something went wrong. You can still reach us via the Contact page.");
            }
        },

        appendMessage: function(role, text) {
            this.messages.push({ role, text });
            const container = document.getElementById('chat-messages');
            const msgDiv = document.createElement('div');
            msgDiv.className = `message ${role}`;
            msgDiv.innerHTML = text; // Allow HTML for links
            container.appendChild(msgDiv);
            container.scrollTop = container.scrollHeight;
        },

        renderSuggestions: function(suggestions) {
            const container = document.getElementById('chat-messages');
            const suggDiv = document.createElement('div');
            suggDiv.className = 'suggestions';
            
            suggestions.forEach(s => {
                const btn = document.createElement('button');
                btn.className = 'suggestion-btn';
                btn.textContent = s;
                btn.onclick = () => this.sendMessage(s);
                suggDiv.appendChild(btn);
            });
            
            container.appendChild(suggDiv);
            container.scrollTop = container.scrollHeight;
        },

        showTyping: function() {
            const container = document.getElementById('chat-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message bot typing-indicator';
            const id = 'typing-' + Date.now();
            typingDiv.id = id;
            typingDiv.innerHTML = `<div class="typing"><span></span><span></span><span></span></div>`;
            container.appendChild(typingDiv);
            container.scrollTop = container.scrollHeight;
            return id;
        },

        removeTyping: function(id) {
            const el = document.getElementById(id);
            if (el) el.remove();
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => chatbot.init());
    } else {
        chatbot.init();
    }
})();
