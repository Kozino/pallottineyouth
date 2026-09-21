<?php
$active = 'events';
$pageTitle = 'Events';
require_once __DIR__ . '/includes/header.php';
$events = db()->query("SELECT * FROM events WHERE status='upcoming' ORDER BY event_date ASC")->fetchAll();
$past = db()->query("SELECT * FROM events WHERE status!='upcoming' ORDER BY event_date DESC LIMIT 6")->fetchAll();
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Events</div>
    <h1>Events &amp; Programs</h1>
    <p>Festivals, retreats, outreaches and gatherings — come and see.</p>
  </div>
</section>
<section class="section">
  <div class="container">
    <h2 style="margin-bottom:1.2rem">Upcoming Events</h2>
    <?php if ($events): ?>
    <div class="grid-2">
      <?php foreach ($events as $ev): ?>
      <div class="event-card">
        <div class="event-date"><strong><?= e(format_date($ev['event_date'],'d')) ?></strong><span><?= e(format_date($ev['event_date'],'M Y')) ?></span></div>
        <div>
          <h3><?= e($ev['title']) ?></h3>
          <p class="hint">📍 <?= e($ev['venue'] ?: 'TBA') ?> · 🕘 <?= e($ev['event_time'] ?: '') ?></p>
          <p><?= nl2br(e($ev['description'] ?? '')) ?></p>
        </div>
        <div><a href="apply.php" class="btn btn-navy btn-sm">Register Interest</a></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="form-card"><p>No upcoming events published yet. Follow our news page for announcements.</p></div>
    <?php endif; ?>

    <?php if ($past): ?>
    <h2 style="margin:2.5rem 0 1.2rem">Past Events</h2>
    <div class="grid-3">
      <?php foreach ($past as $ev): ?>
      <div class="card"><div class="card-body">
        <span class="pill"><?= e(format_date($ev['event_date'])) ?></span>
        <h3><?= e($ev['title']) ?></h3>
        <p class="hint"><?= e(excerpt($ev['description'] ?? '', 100)) ?></p>
      </div></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
