<?php if (!empty($publicPage)): ?>
</main>
<?php else: ?>
    </main>
    <footer class="admin-foot">© <?= date('Y') ?> <?= e(setting('site_name')) ?> · Admin Panel</footer>
  </div>
</div>
<?php endif; ?>
<script src="../assets/js/admin.js"></script>
</body>
</html>
