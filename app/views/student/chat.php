<?php
$pageTitle   = 'Chat — ' . htmlspecialchars($conv['listing_title'] ?? '');
require BASE_PATH . '/app/views/layouts/header.php';
$sessionUser = getSessionUser();
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="chat-page">
<div class="container">
    <div class="chat-wrapper">

        <div class="chat-header">
            <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-ghost btn-sm">&#8592;</a>
            <div class="chat-header-info">
                <strong><?= htmlspecialchars($conv['owner_name']) ?></strong>
                <span class="text-muted">Re: <?= htmlspecialchars($conv['listing_title']) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/student/listing/<?= $conv['listing_id'] ?>"
               class="btn btn-ghost btn-sm">Lihat Iklan</a>
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
</div>
</main>

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
    role: "pelajar"
};
</script>
<script type="module" src="<?= BASE_URL ?>/public/js/chat.js?v=2"></script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
