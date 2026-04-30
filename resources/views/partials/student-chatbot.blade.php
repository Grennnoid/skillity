<style>
    .skillify-chatbot {
        position: fixed;
        right: 16px;
        bottom: 16px;
        z-index: 1200;
        width: 58px;
        height: 58px;
        font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    }

    .skillify-chatbot * {
        box-sizing: border-box;
    }

    .skillify-chatbot__launcher {
        width: 58px;
        height: 58px;
        border: 1px solid rgba(125, 225, 255, 0.28);
        border-radius: 50%;
        background:
            radial-gradient(circle at 28% 28%, rgba(116, 239, 255, 0.42), transparent 42%),
            linear-gradient(180deg, rgba(11, 20, 37, 0.96), rgba(7, 12, 24, 0.98));
        color: #f4fbff;
        cursor: pointer;
        box-shadow: 0 18px 34px rgba(3, 9, 21, 0.45);
        display: grid;
        place-items: center;
        padding: 0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .skillify-chatbot__launcher:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 40px rgba(3, 9, 21, 0.52);
    }

    .skillify-chatbot__launcher-core {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #7cf3ff, #4fb8ff);
        color: #07111d;
        font-size: 12px;
        font-weight: 900;
        display: grid;
        place-items: center;
        letter-spacing: 0.04em;
    }

    .skillify-chatbot__panel {
        position: fixed;
        right: 16px;
        bottom: 86px;
        width: min(348px, calc(100vw - 20px));
        height: min(620px, calc(100vh - 112px));
        border: 1px solid rgba(129, 164, 222, 0.18);
        border-radius: 24px;
        overflow: hidden;
        background:
            radial-gradient(500px 240px at 0% 0%, rgba(63, 129, 239, 0.18), transparent 62%),
            linear-gradient(180deg, rgba(10, 16, 29, 0.985), rgba(6, 10, 20, 0.995));
        box-shadow: 0 34px 70px rgba(0, 0, 0, 0.55);
        display: none;
        grid-template-rows: auto 1fr auto;
        backdrop-filter: blur(18px);
    }

    .skillify-chatbot.open .skillify-chatbot__panel {
        display: grid;
    }

    .skillify-chatbot__header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 14px 14px 12px;
        border-bottom: 1px solid rgba(129, 164, 222, 0.12);
    }

    .skillify-chatbot__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #9fcdf9;
        font-size: 11px;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .skillify-chatbot__eyebrow::before {
        content: "";
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #7cf3ff;
        box-shadow: 0 0 10px rgba(124, 243, 255, 0.58);
    }

    .skillify-chatbot__title {
        margin: 8px 0 0;
        color: #f7fbff;
        font-size: 16px;
        line-height: 1.25;
        font-weight: 800;
    }

    .skillify-chatbot__subtitle {
        display: none;
    }

    .skillify-chatbot__actions {
        display: flex;
        gap: 8px;
    }

    .skillify-chatbot__ghost-btn {
        border: 1px solid rgba(129, 164, 222, 0.2);
        border-radius: 999px;
        background: rgba(14, 22, 39, 0.92);
        color: #eef6ff;
        font-size: 11px;
        font-weight: 700;
        padding: 9px 12px;
        cursor: pointer;
        white-space: nowrap;
    }

    .skillify-chatbot__ghost-btn:hover {
        border-color: rgba(124, 243, 255, 0.28);
    }

    .skillify-chatbot__messages {
        min-height: 0;
        padding: 10px 10px 12px;
        overflow-y: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .skillify-chatbot__messages::-webkit-scrollbar {
        width: 0;
        height: 0;
        display: none;
    }

    .skillify-chatbot__suggestions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .skillify-chatbot__suggestion {
        border: 1px solid rgba(129, 164, 222, 0.16);
        border-radius: 999px;
        background: rgba(12, 19, 34, 0.86);
        color: #e9f3ff;
        font-size: 11px;
        padding: 8px 10px;
        cursor: pointer;
    }

    .skillify-chatbot__suggestion:hover {
        border-color: rgba(93, 202, 255, 0.3);
    }

    .skillify-chatbot__empty {
        border: 1px dashed rgba(129, 164, 222, 0.14);
        border-radius: 18px;
        padding: 16px 14px;
        background: rgba(12, 19, 34, 0.72);
        color: #c4d5f2;
        font-size: 13px;
        line-height: 1.7;
    }

    .skillify-chatbot__message {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .skillify-chatbot__message--assistant {
        align-items: flex-start;
    }

    .skillify-chatbot__message--user {
        align-items: flex-end;
    }

    .skillify-chatbot__meta {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #89a3cc;
        font-size: 10px;
        letter-spacing: 0.03em;
        padding: 0 4px;
    }

    .skillify-chatbot__copy {
        border: 0;
        background: transparent;
        color: #8fd4ff;
        font-size: 10px;
        cursor: pointer;
        padding: 0;
    }

    .skillify-chatbot__bubble {
        max-width: 88%;
        border-radius: 18px;
        padding: 11px 13px;
        font-size: 12.5px;
        line-height: 1.6;
        word-break: break-word;
        white-space: pre-wrap;
    }

    .skillify-chatbot__bubble--assistant {
        border: 1px solid rgba(129, 164, 222, 0.12);
        background: rgba(17, 25, 42, 0.94);
        color: #f0f6ff;
        box-shadow: 0 10px 22px rgba(4, 10, 24, 0.2);
    }

    .skillify-chatbot__bubble--user {
        border: 1px solid rgba(109, 212, 255, 0.12);
        background: linear-gradient(135deg, #7f6dff, #40b8ff);
        color: #fdfefe;
        box-shadow: 0 14px 28px rgba(41, 110, 188, 0.28);
    }

    .skillify-chatbot__typing {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .skillify-chatbot__typing span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #9fd6ff;
        opacity: 0.4;
        animation: skillify-chatbot-pulse 1s infinite ease-in-out;
    }

    .skillify-chatbot__typing span:nth-child(2) {
        animation-delay: 0.18s;
    }

    .skillify-chatbot__typing span:nth-child(3) {
        animation-delay: 0.36s;
    }

    @keyframes skillify-chatbot-pulse {
        0%, 80%, 100% { transform: scale(0.9); opacity: 0.35; }
        40% { transform: scale(1); opacity: 1; }
    }

    .skillify-chatbot__composer {
        min-height: 0;
        padding: 10px;
        border-top: 1px solid rgba(129, 164, 222, 0.1);
        background: rgba(8, 13, 24, 0.98);
    }

    .skillify-chatbot__form {
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 8px;
        align-items: center;
    }

    .skillify-chatbot__input-wrap {
        display: flex;
        align-items: center;
        gap: 0;
        border: 1px solid rgba(129, 164, 222, 0.16);
        border-radius: 999px;
        background: rgba(14, 21, 36, 0.95);
        padding: 10px 12px;
        min-height: 48px;
    }

    .skillify-chatbot__textarea {
        width: 100%;
        min-height: 22px;
        max-height: 84px;
        resize: none;
        border: 0;
        background: transparent;
        color: #eef6ff;
        font-size: 13px;
        line-height: 1.5;
        outline: none;
        padding: 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .skillify-chatbot__textarea::-webkit-scrollbar {
        display: none;
    }

    .skillify-chatbot__send {
        min-width: 76px;
        height: 48px;
        border: 0;
        border-radius: 999px;
        background: linear-gradient(135deg, #76e9ff, #4cafff);
        color: #07111c;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 16px 28px rgba(58, 167, 255, 0.24);
    }

    .skillify-chatbot__send:disabled,
    .skillify-chatbot__textarea:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .skillify-chatbot__footer-row {
        margin-top: 7px;
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
    }

    .skillify-chatbot__hint {
        color: #8098c2;
        font-size: 10px;
        line-height: 1.5;
    }

    .skillify-chatbot__counter {
        color: #748cb6;
        font-size: 10px;
        white-space: nowrap;
    }

    @media (max-width: 720px) {
        .skillify-chatbot {
            right: 12px;
            bottom: 12px;
        }

        .skillify-chatbot__panel {
            width: min(356px, calc(100vw - 16px));
            height: min(76vh, calc(100vh - 40px));
            right: 12px;
            bottom: 80px;
            border-radius: 22px;
        }
    }

    @media (max-width: 520px) {
        .skillify-chatbot {
            right: 10px;
            bottom: 10px;
        }

        .skillify-chatbot__panel {
            width: calc(100vw - 20px);
            height: min(74vh, calc(100vh - 28px));
            right: 0;
            bottom: 72px;
            border-radius: 18px;
        }

        .skillify-chatbot__header,
        .skillify-chatbot__composer {
            padding-left: 12px;
            padding-right: 12px;
        }

        .skillify-chatbot__messages {
            padding-left: 12px;
            padding-right: 12px;
        }

        .skillify-chatbot__actions {
            gap: 6px;
        }

        .skillify-chatbot__ghost-btn {
            padding: 8px 10px;
            font-size: 12px;
        }

        .skillify-chatbot__form {
            gap: 8px;
        }

        .skillify-chatbot__send {
            min-width: 68px;
            height: 44px;
        }

        .skillify-chatbot__footer-row {
            flex-wrap: wrap;
        }

        .skillify-chatbot__hint,
        .skillify-chatbot__counter {
            width: 100%;
        }
    }
</style>

<div class="skillify-chatbot" id="skillifyChatbot">
    <button class="skillify-chatbot__launcher" id="skillifyChatbotLauncher" type="button" aria-label="{{ __('ui.chatbot.open_label') }}">
        <span class="skillify-chatbot__launcher-core">AI</span>
    </button>

    <section class="skillify-chatbot__panel" aria-live="polite" aria-label="{{ __('ui.chatbot.panel_label') }}">
        <header class="skillify-chatbot__header">
            <div>
                <span class="skillify-chatbot__eyebrow">{{ __('ui.chatbot.eyebrow') }}</span>
                <h3 class="skillify-chatbot__title">{{ $chatbotSettings['name'] ?? __('ui.chatbot.default_name') }}</h3>
            </div>

            <div class="skillify-chatbot__actions">
                <button class="skillify-chatbot__ghost-btn" id="skillifyChatbotClear" type="button">{{ __('ui.chatbot.new_chat') }}</button>
                <button class="skillify-chatbot__ghost-btn" id="skillifyChatbotClose" type="button">{{ __('ui.chatbot.close') }}</button>
            </div>
        </header>

        <div class="skillify-chatbot__messages" id="skillifyChatbotMessages">
            <div class="skillify-chatbot__suggestions" id="skillifyChatbotSuggestions"></div>
        </div>

        <div class="skillify-chatbot__composer">
            <form class="skillify-chatbot__form" id="skillifyChatbotForm">
                <div class="skillify-chatbot__input-wrap">
                    <textarea
                        class="skillify-chatbot__textarea"
                        id="skillifyChatbotInput"
                        rows="1"
                        placeholder="{{ $chatbotSettings['placeholder'] ?? __('ui.chatbot.default_placeholder') }}"
                        {{ empty($chatbotSettings['configured']) ? 'disabled' : '' }}
                    ></textarea>
                </div>
                <button class="skillify-chatbot__send" id="skillifyChatbotSend" type="submit" {{ empty($chatbotSettings['configured']) ? 'disabled' : '' }}>
                    {{ __('ui.chatbot.send') }}
                </button>
            </form>

            <div class="skillify-chatbot__footer-row">
                <div class="skillify-chatbot__hint">
                    {{ !empty($chatbotSettings['configured']) ? '' : __('ui.chatbot.activation_hint') }}
                </div>
                <div class="skillify-chatbot__counter" id="skillifyChatbotCounter">0 / 3000</div>
            </div>
        </div>
    </section>
</div>

@php
    $chatbotConfig = [
        'name' => $chatbotSettings['name'] ?? __('ui.chatbot.default_name'),
        'welcome' => $chatbotSettings['welcome'] ?? __('ui.chatbot.default_welcome'),
        'placeholder' => $chatbotSettings['placeholder'] ?? __('ui.chatbot.default_placeholder'),
        'configured' => !empty($chatbotSettings['configured']),
        'historyUrl' => route('student.chatbot.history'),
        'sendUrl' => route('student.chatbot.send'),
        'clearUrl' => route('student.chatbot.clear'),
        'csrf' => csrf_token(),
        'labels' => [
            'suggestions' => [
                __('ui.chatbot.suggestion_summary'),
                __('ui.chatbot.suggestion_explain'),
                __('ui.chatbot.suggestion_next'),
                __('ui.chatbot.suggestion_progress'),
            ],
            'you' => __('ui.chatbot.you'),
            'copy' => __('ui.chatbot.copy'),
            'copied' => __('ui.chatbot.copied'),
            'copyFailed' => __('ui.chatbot.copy_failed'),
            'historyError' => __('ui.chatbot.history_error'),
            'newChatError' => __('ui.chatbot.new_chat_error'),
            'messageError' => __('ui.chatbot.message_error'),
            'connectionError' => __('ui.chatbot.connection_error'),
        ],
    ];
@endphp

<script>
    (() => {
        const root = document.getElementById('skillifyChatbot');
        if (!root) {
            return;
        }

        const config = @json($chatbotConfig);
        const launcher = document.getElementById('skillifyChatbotLauncher');
        const closeBtn = document.getElementById('skillifyChatbotClose');
        const clearBtn = document.getElementById('skillifyChatbotClear');
        const form = document.getElementById('skillifyChatbotForm');
        const input = document.getElementById('skillifyChatbotInput');
        const sendBtn = document.getElementById('skillifyChatbotSend');
        const messages = document.getElementById('skillifyChatbotMessages');
        const suggestions = document.getElementById('skillifyChatbotSuggestions');
        const counter = document.getElementById('skillifyChatbotCounter');
        const storageKey = 'skillify-chatbot-open';
        let loaded = false;
        let busy = false;
        let typingNode = null;

        const defaultSuggestions = config.labels.suggestions;

        function setOpen(open) {
            root.classList.toggle('open', open);
            window.localStorage.setItem(storageKey, open ? '1' : '0');
            if (open && !loaded) {
                loadHistory();
            }
        }

        function scrollToBottom() {
            messages.scrollTop = messages.scrollHeight;
        }

        function formatTime(value) {
            if (!value) {
                return '';
            }

            const date = new Date(value);
            if (Number.isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function createBubble(role, content, createdAt = null) {
            const wrapper = document.createElement('div');
            wrapper.className = `skillify-chatbot__message skillify-chatbot__message--${role}`;

            const meta = document.createElement('div');
            meta.className = 'skillify-chatbot__meta';
            meta.textContent = role === 'user' ? config.labels.you : config.name;

            const timeLabel = formatTime(createdAt);
            if (timeLabel) {
                const time = document.createElement('span');
                time.textContent = timeLabel;
                meta.appendChild(time);
            }

            if (role === 'assistant') {
                const copyBtn = document.createElement('button');
                copyBtn.className = 'skillify-chatbot__copy';
                copyBtn.type = 'button';
                copyBtn.textContent = config.labels.copy;
                copyBtn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(content);
                        copyBtn.textContent = config.labels.copied;
                        setTimeout(() => { copyBtn.textContent = config.labels.copy; }, 1300);
                    } catch (error) {
                        copyBtn.textContent = config.labels.copyFailed;
                        setTimeout(() => { copyBtn.textContent = config.labels.copy; }, 1300);
                    }
                });
                meta.appendChild(copyBtn);
            }

            const bubble = document.createElement('div');
            bubble.className = `skillify-chatbot__bubble skillify-chatbot__bubble--${role}`;
            bubble.textContent = content;

            wrapper.appendChild(meta);
            wrapper.appendChild(bubble);

            return wrapper;
        }

        function renderSuggestions() {
            suggestions.innerHTML = '';
            defaultSuggestions.forEach((text) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'skillify-chatbot__suggestion';
                button.textContent = text;
                button.addEventListener('click', () => {
                    input.value = text;
                    autosize();
                    input.focus();
                });
                suggestions.appendChild(button);
            });
        }

        function renderEmptyState() {
            messages.innerHTML = '';
            renderSuggestions();

            const empty = document.createElement('div');
            empty.className = 'skillify-chatbot__empty';
            empty.textContent = config.welcome;
            messages.appendChild(empty);
            scrollToBottom();
        }

        function renderMessages(items) {
            messages.innerHTML = '';
            renderSuggestions();

            if (!items.length) {
                renderEmptyState();
                return;
            }

            items.forEach((item) => {
                messages.appendChild(createBubble(item.role === 'user' ? 'user' : 'assistant', item.content, item.created_at || null));
            });

            scrollToBottom();
        }

        function showTyping() {
            hideTyping();
            typingNode = document.createElement('div');
            typingNode.className = 'skillify-chatbot__message skillify-chatbot__message--assistant';

            const meta = document.createElement('div');
            meta.className = 'skillify-chatbot__meta';
            meta.textContent = config.name;

            const bubble = document.createElement('div');
            bubble.className = 'skillify-chatbot__bubble skillify-chatbot__bubble--assistant';
            bubble.innerHTML = '<div class="skillify-chatbot__typing"><span></span><span></span><span></span></div>';

            typingNode.appendChild(meta);
            typingNode.appendChild(bubble);
            messages.appendChild(typingNode);
            scrollToBottom();
        }

        function hideTyping() {
            if (typingNode) {
                typingNode.remove();
                typingNode = null;
            }
        }

        async function loadHistory() {
            loaded = true;
            try {
                const response = await fetch(config.historyUrl, {
                    headers: { 'Accept': 'application/json' },
                });
                const data = await response.json();
                renderMessages(data.messages || []);
            } catch (error) {
                renderEmptyState();
                messages.appendChild(createBubble('assistant', config.labels.historyError));
            }
        }

        async function clearHistory() {
            if (busy) {
                return;
            }

            try {
                const response = await fetch(config.clearUrl, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                });

                if (!response.ok) {
                    throw new Error('Clear failed');
                }

                renderEmptyState();
            } catch (error) {
                messages.appendChild(createBubble('assistant', config.labels.newChatError));
                scrollToBottom();
            }
        }

        function autosize() {
            input.style.height = 'auto';
            input.style.height = `${Math.min(input.scrollHeight, 98)}px`;
            counter.textContent = `${input.value.length} / 3000`;
        }

        function collectLocalProgress() {
            const chunks = [];
            const progressText = document.getElementById('progressText')?.textContent?.trim();
            const completionBadge = document.getElementById('completionBadge')?.textContent?.trim();
            const attendanceLabel = document.getElementById('attendanceProgressLabel')?.textContent?.trim();
            const chapterStates = Array.from(document.querySelectorAll('.chapter-item')).slice(0, 14).map((item) => {
                const title = item.querySelector('span')?.textContent?.trim() || '';
                const state = item.querySelector('.chapter-complete-text')?.textContent?.trim() || '';
                const chapterNo = item.dataset.chapterNumber || '';
                return title ? `Chapter ${chapterNo}: ${title} (${state || 'status unknown'})` : null;
            }).filter(Boolean);

            if (progressText) chunks.push(`Local progress bar: ${progressText}`);
            if (completionBadge) chunks.push(`Current chapter completion badge: ${completionBadge}`);
            if (attendanceLabel) chunks.push(`Attendance/progress label: ${attendanceLabel}`);
            if (chapterStates.length) chunks.push(`Visible chapter completion states: ${chapterStates.join(' | ')}`);

            return chunks.join('\n');
        }

        function collectLocalNotes() {
            return document.getElementById('notesField')?.value?.trim() || '';
        }

        function collectPageSnapshot() {
            const selectors = ['main', '#panel-course-content', '#panel-overview', '#panel-qa .qa-list', '.hero', '.section'];
            const areas = [];

            selectors.forEach((selector) => {
                document.querySelectorAll(selector).forEach((node, index) => {
                    if (selector === '.section' && index > 3) {
                        return;
                    }

                    const text = (node.innerText || '').replace(/\s+\n/g, '\n').trim();
                    if (text) {
                        areas.push(text);
                    }
                });
            });

            return areas.join('\n\n---\n\n').trim().slice(0, 7000);
        }

        async function submitMessage(event) {
            event.preventDefault();

            if (busy || !config.configured) {
                return;
            }

            const value = input.value.trim();
            if (!value) {
                return;
            }

            busy = true;
            sendBtn.disabled = true;
            input.disabled = true;
            const nowIso = new Date().toISOString();

            messages.appendChild(createBubble('user', value, nowIso));
            scrollToBottom();
            showTyping();

            try {
                const response = await fetch(config.sendUrl, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': config.csrf,
                    },
                    body: JSON.stringify({
                        message: value,
                        page_title: document.title,
                        page_path: window.location.pathname,
                        page_snapshot: collectPageSnapshot(),
                        local_progress: collectLocalProgress(),
                        local_notes: collectLocalNotes(),
                    }),
                });

                const data = await response.json();
                hideTyping();

                if (!response.ok) {
                    messages.appendChild(createBubble('assistant', data.message || config.labels.messageError));
                    scrollToBottom();
                    return;
                }

                messages.appendChild(createBubble('assistant', data.message.content, data.message.created_at || new Date().toISOString()));
                scrollToBottom();
                input.value = '';
                autosize();
            } catch (error) {
                hideTyping();
                messages.appendChild(createBubble('assistant', config.labels.connectionError));
                scrollToBottom();
            } finally {
                busy = false;
                sendBtn.disabled = !config.configured;
                input.disabled = !config.configured;
                input.focus();
            }
        }

        launcher.addEventListener('click', () => setOpen(!root.classList.contains('open')));
        closeBtn.addEventListener('click', () => setOpen(false));
        clearBtn.addEventListener('click', clearHistory);
        form.addEventListener('submit', submitMessage);
        input.addEventListener('input', autosize);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                form.requestSubmit();
            }
        });

        autosize();

        if (window.localStorage.getItem(storageKey) === '1') {
            setOpen(true);
        }
    })();
</script>

