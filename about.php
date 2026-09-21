<?php
$active = 'about';
$pageTitle = 'About Us';
$pageDesc = 'About the Pallottine Nigerian Youth — our charism, mission and patron St. Vincent Pallotti.';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / About</div>
    <h1>About Pallottine Nigerian Youth</h1>
    <p>Youth of the Society of the Catholic Apostolate (Pallottines) in Nigeria</p>
  </div>
</section>

<section class="section">
  <div class="container split">
    <div>
      <span class="sec-kicker">Our story</span>
      <h2>Rooted in the Charism of St. Vincent Pallotti</h2>
      <p>St. Vincent Pallotti (1795–1850), a Roman priest, believed that <strong>every Christian — lay or clergy, young or old — is called to be an apostle</strong>. He founded the Union of Catholic Apostolate to revive faith, rekindle charity, and unite all in the mission of Christ.</p>
      <p>The <strong>Pallottine Nigerian Youth</strong> is the youth expression of this family in Nigeria. We gather young people in parishes, schools, campuses and communities to pray together, grow in the faith, serve the poor, and joyfully proclaim the Gospel.</p>
      <ul class="check-list">
        <li><strong>Reviving Faith</strong> — deep personal encounter with Christ</li>
        <li><strong>Rekindling Charity</strong> — love expressed in concrete service</li>
        <li><strong>Forming Apostles</strong> — raising missionary leaders for tomorrow</li>
      </ul>
    </div>
    <div>
      <img src="assets/images/placeholder-post.svg" alt="About Pallottine Nigerian Youth">
      <div class="form-card" style="margin-top:1.2rem;background:var(--cream)">
        <h3 style="font-size:1.05rem">Our Motto</h3>
        <p style="font-family:var(--font-display);font-size:1.2rem;color:var(--navy)">“Caritas Christi Urget Nos — The Love of Christ Impels Us”</p>
        <p class="hint">2 Corinthians 5:14</p>
      </div>
    </div>
  </div>
</section>

<section class="section section-cream">
  <div class="container">
    <div class="sec-head center"><span class="sec-kicker">What drives us</span><h2>Mission, Vision &amp; Values</h2></div>
    <div class="grid-3">
      <div class="feature"><div class="f-ico">🎯</div><h3>Our Mission</h3><p>To help young Nigerians understand, live and share the Catholic faith through prayer, formation, fellowship and service.</p></div>
      <div class="feature"><div class="f-ico">👁️</div><h3>Our Vision</h3><p>A vibrant generation of young apostles transforming the Church and Nigerian society with the love of Christ.</p></div>
      <div class="feature"><div class="f-ico">💛</div><h3>Our Values</h3><p>Faith, charity, unity, humility, service, purity of intention and collaboration with all in the Church.</p></div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head center"><span class="sec-kicker">Our patron</span><h2>St. Vincent Pallotti (1795 – 1850)</h2>
    <p>Founder of the Society of the Catholic Apostolate. Canonised in 1963. Feast day: <strong>22 January</strong>.</p></div>
    <div class="form-card" style="max-width:760px;margin:0 auto">
      <p>“God is infinite love and infinite mercy. Seek God and you will find God. Seek God in all things and you will find God in all things.”</p>
      <p class="hint">— St. Vincent Pallotti</p>
    </div>
    <div style="text-align:center;margin-top:2rem">
      <a href="apply.php" class="btn btn-gold">Join the Family</a>
      <a href="programs.php" class="btn btn-outline">See Our Apostolates</a>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
