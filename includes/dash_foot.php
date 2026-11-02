    </main>
  </div>
</div>

<?php
$assistantSuggestions = match($user['role']) {
  'student' => [
    ['How do I request a supervisor?','Request a supervisor'],
    ['How do I edit my project title?','Edit project title'],
    ['When can I see my evaluation result?','Evaluation results'],
  ],
  'admin' => [
    ['How do I assign or edit lecturer roles?','Lecturer roles'],
    ['How do I issue a coordinator appointment letter?','Coordinator letters'],
    ['How do I change the global dashboard theme?','Global settings'],
  ],
  default => [
    ['How do I view my role rubric criteria?','My rubric criteria'],
    ['How do I reopen an expired category or progress form?','Reopen deadlines'],
    ['How does the supervision request workflow work?','Supervision requests'],
  ],
};
$assistantAudienceLabel = $user['role']==='lecturer' ? 'lecturer and academic-role' : $user['role'];
?>
<?php if($chatbotEnabled): ?>
<!-- FAQ Assistant Widget -->
<div id="chatbotWidget" class="chatbot-widget">
  <button id="chatbotToggle" class="chatbot-fab" style="background:var(--theme)">
    <i class="fa-solid fa-comment-dots"></i>
  </button>
  <div id="chatbotPanel" class="chatbot-panel">
    <div class="chatbot-panel-header" style="background:var(--theme)">
      <div>
        <div class="fw-bold" style="font-size:14px;"><?= e($systemName) ?> Assistant</div>
        <div style="font-size:11px;opacity:.85;">Online · Ask me anything about <?= e($systemName) ?></div>
      </div>
      <button id="chatbotClose" class="btn-close btn-close-white"></button>
    </div>
    <div id="chatbotMessages" class="chatbot-messages">
      <div class="chat-msg bot">Hi <?= e(explode(' ', $user['name'])[0]) ?>! I provide answers relevant to your <?= e($assistantAudienceLabel) ?> account.</div>
      <div class="chat-suggestions"><?php foreach($assistantSuggestions as [$q,$label]): ?><button class="chip" data-q="<?= e($q) ?>"><?= e($label) ?></button><?php endforeach; ?></div>
    </div>
    <div class="chatbot-input-row">
      <input type="text" id="chatbotInput" placeholder="Type your question...">
      <button id="chatbotSend" style="background:var(--theme)"><i class="fa-solid fa-paper-plane"></i></button>
    </div>
  </div>
</div>

<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const BASE_URL = "<?= BASE_URL ?>";
const CHATBOT_CSRF = "<?= e(generateCSRFToken()) ?>";

function toggleDashboardSidebar(forceOpen = null) {
  const sidebar = document.getElementById('dashSidebar');
  const backdrop = document.getElementById('sidebarBackdrop');
  if (!sidebar || !backdrop) return;
  const open = forceOpen === null ? !sidebar.classList.contains('open') : Boolean(forceOpen);
  sidebar.classList.toggle('open', open);
  backdrop.classList.toggle('show', open);
  document.body.style.overflow = open && window.innerWidth < 992 ? 'hidden' : '';
}

window.addEventListener('resize', () => {
  if (window.innerWidth >= 992) toggleDashboardSidebar(false);
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') toggleDashboardSidebar(false);
});

// Keep every dashboard table usable on phones, including older views that did not add a wrapper.
document.querySelectorAll('table.table-fy').forEach(table => {
  if (!table.parentElement.classList.contains('table-responsive')) {
    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive';
    table.parentNode.insertBefore(wrapper, table);
    wrapper.appendChild(table);
  }
});
if (document.getElementById('chatbotToggle')) {
document.getElementById('chatbotToggle').addEventListener('click', () => {
  document.getElementById('chatbotPanel').classList.toggle('open');
});
document.getElementById('chatbotClose').addEventListener('click', () => {
  document.getElementById('chatbotPanel').classList.remove('open');
});

function appendChatMsg(text, who) {
  const box = document.getElementById('chatbotMessages');
  const div = document.createElement('div');
  div.className = 'chat-msg ' + who;
  div.textContent = text;
  box.appendChild(div);
  box.scrollTop = box.scrollHeight;
}

function sendChatQuestion(q) {
  if (!q.trim()) return;
  appendChatMsg(q, 'user');
  document.getElementById('chatbotInput').value = '';
  const typing = document.createElement('div');
  typing.className = 'chat-msg bot typing';
  typing.id = 'typingIndicator';
  typing.textContent = 'Typing...';
  document.getElementById('chatbotMessages').appendChild(typing);
  document.getElementById('chatbotMessages').scrollTop = 999999;

  fetch(BASE_URL + '/api/chatbot.php', {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: 'q=' + encodeURIComponent(q) + '&csrf_token=' + encodeURIComponent(CHATBOT_CSRF)
  }).then(r => r.json()).then(data => {
    document.getElementById('typingIndicator')?.remove();
    appendChatMsg(data.answer, 'bot');
  }).catch(() => {
    document.getElementById('typingIndicator')?.remove();
    appendChatMsg("Sorry, I couldn't process that right now. Please try again.", 'bot');
  });
}

document.getElementById('chatbotSend').addEventListener('click', () => {
  sendChatQuestion(document.getElementById('chatbotInput').value);
});
document.getElementById('chatbotInput').addEventListener('keydown', e => {
  if (e.key === 'Enter') sendChatQuestion(e.target.value);
});
document.querySelectorAll('.chip').forEach(chip => {
  chip.addEventListener('click', () => sendChatQuestion(chip.dataset.q));
});
}
</script>
</body>
</html>
