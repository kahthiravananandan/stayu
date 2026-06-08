<?php
$pageTitle   = isset($conv) ? 'Chat — ' . htmlspecialchars($conv['listing_title']) : 'Peti Masuk';
require BASE_PATH . '/app/views/layouts/header.php';
$sessionUser = getSessionUser();
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="<?= isset($conv) ? 'chat-page' : 'dashboard-page' ?>">
<div class="container">

<?php if (isset($conv)): ?>
    <!-- Single conversation view -->
    <div class="chat-wrapper">

        <div class="chat-header">
            <a href="<?= BASE_URL ?>/owner/chat_inbox" class="btn btn-ghost btn-sm">&#8592;</a>
            <div class="chat-header-info">
                <strong><?= htmlspecialchars($conv['student_name']) ?></strong>
                <small class="text-muted"><?= htmlspecialchars($conv['matric_number'] ?? '') ?></small>
                <span class="text-muted">Re: <?= htmlspecialchars($conv['listing_title']) ?></span>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <div class="chat-loading">Memuatkan mesej...</div>
        </div>

        <form class="chat-input-form" id="chatForm">
            <input type="text" id="msgInput" placeholder="Taip mesej..." autocomplete="off"
                   required class="chat-input" maxlength="1000">
            <button type="submit" class="btn btn-primary">Hantar</button>
        </form>

    </div>

    <script>
    window.FIREBASE_CONFIG = {
        apiKey:            "<?= htmlspecialchars(FIREBASE_API_KEY) ?>",
        authDomain:        "<?= htmlspecialchars(FIREBASE_AUTH_DOMAIN) ?>",
        databaseURL:       "<?= htmlspecialchars(FIREBASE_DATABASE_URL) ?>",
        projectId:         "<?= htmlspecialchars(FIREBASE_PROJECT_ID) ?>",
        storageBucket:     "<?= htmlspecialchars(FIREBASE_STORAGE_BUCKET) ?>",
        messagingSenderId: "<?= htmlspecialchars(FIREBASE_MESSAGING_SENDER_ID) ?>",
        appId:             "<?= htmlspecialchars(FIREBASE_APP_ID) ?>"
    };
    window.CHAT_SESSION = "<?= htmlspecialchars($conv['firebase_session_id']) ?>";
    window.CURRENT_USER = {
        id:   <?= (int)$sessionUser['user_id'] ?>,
        name: <?= json_encode($sessionUser['full_name']) ?>,
        role: "pemilik"
    };
    </script>
    <script type="module" src="<?= BASE_URL ?>/public/js/chat.js?v=2"></script>

<?php else: ?>
    <!-- Inbox list -->
    <div class="page-header"><h1>Peti Masuk</h1></div>

    <?php if (empty($conversations)): ?>
    <div class="empty-results"><p>Tiada perbualan lagi.</p></div>
    <?php else: ?>
    <ul class="conv-list conv-list--full">
    <?php foreach ($conversations as $c): ?>
    <li class="conv-item">
        <div class="conv-avatar"><?= strtoupper(substr($c['student_name'], 0, 1)) ?></div>
        <div class="conv-info">
            <strong><?= htmlspecialchars($c['student_name']) ?></strong>
            <small><?= htmlspecialchars($c['listing_title']) ?></small>
            <small class="text-muted"><?= htmlspecialchars($c['matric_number'] ?? '') ?></small>
        </div>
        <a href="<?= BASE_URL ?>/owner/chat_inbox/<?= $c['conversation_id'] ?>"
           class="btn btn-outline">Buka Chat</a>
    </li>
    <?php endforeach; ?>
    </ul>
    <?php endif; ?>

<?php endif; ?>

</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
