<?php
/**
 * apply.php — Membership / volunteer application form.
 * Applications land in Admin > Applications.
 */
require_once __DIR__ . '/config/functions.php';
$errors = [];
$old = [];
$states = ['Abia','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','FCT - Abuja','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara','Outside Nigeria'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors[] = 'Session expired. Please refresh and try again.';
    $old = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'dob' => trim($_POST['dob'] ?? ''),
        'gender' => $_POST['gender'] ?? '',
        'phone' => trim($_POST['phone'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'state' => $_POST['state'] ?? '',
        'diocese' => trim($_POST['diocese'] ?? ''),
        'parish' => trim($_POST['parish'] ?? ''),
        'education' => trim($_POST['education'] ?? ''),
        'membership_type' => $_POST['membership_type'] ?? '',
        'why_join' => trim($_POST['why_join'] ?? ''),
        'skills' => trim($_POST['skills'] ?? ''),
        'emergency_name' => trim($_POST['emergency_name'] ?? ''),
        'emergency_phone' => trim($_POST['emergency_phone'] ?? ''),
    ];
    if (strlen($old['full_name']) < 3) $errors[] = 'Please enter your full name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if (strlen(preg_replace('/\D/', '', $old['phone'])) < 7) $errors[] = 'Please enter a valid phone number.';
    if ($old['state'] === '') $errors[] = 'Please select your state.';
    if ($old['membership_type'] === '') $errors[] = 'Please choose a membership type.';
    if (strlen($old['why_join']) < 10) $errors[] = 'Please tell us briefly why you want to join (min. 10 characters).';

    $passport = null;
    if (!$errors && !empty($_FILES['passport']['name'])) {
        $passport = upload_image('passport', __DIR__ . '/uploads/passports', 'passport');
        if (!$passport) $errors[] = 'Passport upload failed. Use a JPG/PNG image under 5MB.';
    }

    if (!$errors) {
        // Prevent duplicate pending application with same email
        $dup = db()->prepare("SELECT id FROM applications WHERE email=? AND status='pending' LIMIT 1");
        $dup->execute([$old['email']]);
        if ($dup->fetch()) {
            $errors[] = 'You already have a pending application with this email. We will contact you soon.';
        } else {
            $code = gen_code('APP');
            $stmt = db()->prepare("INSERT INTO applications (app_code, full_name, dob, gender, phone, email, address, state, diocese, parish, education, membership_type, why_join, skills, emergency_name, emergency_phone, passport_image, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
            $stmt->execute([$code, $old['full_name'], $old['dob'], $old['gender'], $old['phone'], $old['email'], $old['address'], $old['state'], $old['diocese'], $old['parish'], $old['education'], $old['membership_type'], $old['why_join'], $old['skills'], $old['emergency_name'], $old['emergency_phone'], $passport]);
            header('Location: apply-success.php?code=' . urlencode($code));
            exit;
        }
    }
}

$active = 'apply';
$pageTitle = 'Apply / Become a Member';
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Apply</div>
    <h1>Apply / Become a Member 🙋</h1>
    <p>Join the Pallottine Nigerian Youth family — membership, volunteering or campus fellowship.</p>
  </div>
</section>

<section class="section">
  <div class="container" style="max-width:820px">
    <?php if ($errors): ?>
      <div class="flash flash-error" style="margin-bottom:1.2rem"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" action="apply.php" enctype="multipart/form-data" class="form-card">
      <?= csrf_field() ?>
      <h3>Personal Information</h3><br>
      <div class="form-grid">
        <div class="field"><label>Full Name <span class="req">*</span></label><input type="text" name="full_name" value="<?= e($old['full_name'] ?? '') ?>" required></div>
        <div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?= e($old['dob'] ?? '') ?>"></div>
        <div class="field"><label>Gender</label>
          <select name="gender">
            <option value="">— Select —</option>
            <?php foreach (['Male','Female'] as $g): ?><option <?= (($old['gender'] ?? '')===$g)?'selected':'' ?>><?= $g ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label>Passport Photo (optional)</label><input type="file" name="passport" accept="image/*"><span class="hint">JPG/PNG, max 5MB.</span></div>
        <div class="field"><label>Phone / WhatsApp <span class="req">*</span></label><input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required></div>
        <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required></div>
        <div class="field full"><label>Home Address</label><input type="text" name="address" value="<?= e($old['address'] ?? '') ?>"></div>
        <div class="field"><label>State <span class="req">*</span></label>
          <select name="state" required>
            <option value="">— Select state —</option>
            <?php foreach ($states as $s): ?><option <?= (($old['state'] ?? '')===$s)?'selected':'' ?>><?= e($s) ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label>Education / Occupation</label><input type="text" name="education" value="<?= e($old['education'] ?? '') ?>" placeholder="e.g. Student, UNILAG"></div>
      </div>
      <h3 style="margin-top:1.5rem">Faith &amp; Membership</h3><br>
      <div class="form-grid">
        <div class="field"><label>Diocese</label><input type="text" name="diocese" value="<?= e($old['diocese'] ?? '') ?>" placeholder="e.g. Lagos Archdiocese"></div>
        <div class="field"><label>Parish / Chaplaincy</label><input type="text" name="parish" value="<?= e($old['parish'] ?? '') ?>"></div>
        <div class="field"><label>Membership Type <span class="req">*</span></label>
          <select name="membership_type" required>
            <option value="">— Select —</option>
            <?php foreach (['Full Member','Volunteer','Campus Fellowship','Choir / Media / Ushering Unit','Friend / Supporter'] as $m): ?>
            <option <?= (($old['membership_type'] ?? '')===$m)?'selected':'' ?>><?= e($m) ?></option><?php endforeach; ?>
          </select></div>
        <div class="field"><label>Skills / Talents</label><input type="text" name="skills" value="<?= e($old['skills'] ?? '') ?>" placeholder="e.g. singing, graphics, football"></div>
        <div class="field full"><label>Why do you want to join? <span class="req">*</span></label><textarea name="why_join" rows="3" required><?= e($old['why_join'] ?? '') ?></textarea></div>
        <div class="field"><label>Emergency Contact Name</label><input type="text" name="emergency_name" value="<?= e($old['emergency_name'] ?? '') ?>"></div>
        <div class="field"><label>Emergency Contact Phone</label><input type="tel" name="emergency_phone" value="<?= e($old['emergency_phone'] ?? '') ?>"></div>
      </div>
      <button class="btn btn-gold btn-block" style="margin-top:1.4rem" type="submit">Submit Application ✓</button>
      <p class="hint" style="text-align:center;margin-top:.6rem">Our coordinators review applications and will contact you by phone/email.</p>
    </form>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
