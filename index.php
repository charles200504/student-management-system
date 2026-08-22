<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'StudentSys | Next-Gen Academic Management';
$basePath = '';
$activePage = 'home';

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-badge">SRI LANKA'S #1 ACADEMIC INTELLIGENCE PLATFORM</div>
    <h1 class="hero-title">
        Next-Gen Student Management <br>
        <span>AI-Powered Academic Intelligence</span>
    </h1>
    <p class="hero-author-tag">DEVELOPED FOR PLYMOUTH UNIVERSITY / NSBM COURSEWORK</p>

    <!-- 3 Golden Highlight Cards -->
    <div class="feature-cards-grid">
        <div class="feature-card">
            <div class="check-icon">✓</div>
            <h4>Zero Data Loss</h4>
            <p>100% Relational MySQL with strict foreign key constraints and PDO safety.</p>
        </div>
        <div class="feature-card">
            <div class="check-icon">✓</div>
            <h4>Automated GPA Engine</h4>
            <p>Instant weighted GPA recalculation with dynamic academic status tracking.</p>
        </div>
        <div class="feature-card">
            <div class="check-icon">✓</div>
            <h4>Digital QR Identity</h4>
            <p>One-click student verification card generation with printable badges.</p>
        </div>
    </div>

    <div class="hero-cta" style="margin-top: 36px;">
        <?php if (is_logged_in()): ?>
            <a class="button gold-btn" href="dashboard.php">📊 Access Analytics Dashboard →</a>
            <a class="button dark-btn" href="students/index.php">📂 View Directory</a>
        <?php else: ?>
            <a class="button gold-btn" href="auth/login.php">🚀 Administrator Login</a>
            <a class="button dark-btn" href="auth/register.php">Create Account</a>
        <?php endif; ?>
    </div>
</section>

<!-- Value Proposition Banner -->
<section class="landing-highlight-banner">
    <h3>All degree records are 100% synchronized with institutional curriculum.</h3>
    <p>
        Comprehensive academic tracking built for Software Engineering, Computer Science, Data Science, and Cyber Security specializations. Manage enrollments, degree programs, live modular grading, and administrative data exports effortlessly.
    </p>
</section>

<!-- Pillars Grid -->
<section class="landing-grid-3">
    <div class="panel">
        <div class="stat-icon-wrap" style="color: #fbbf24; background: rgba(245, 158, 11, 0.1); margin-bottom: 14px;">🎓</div>
        <h4 style="font-size: 17px; margin-bottom: 8px;">Modular Curriculum Auto-Sync</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            Enrolling a student automatically assigns their required faculty modules, credit weightings, and real-time GPA computations.
        </p>
    </div>

    <div class="panel">
        <div class="stat-icon-wrap" style="color: #10b981; background: rgba(16, 185, 129, 0.1); margin-bottom: 14px;">⚡</div>
        <h4 style="font-size: 17px; margin-bottom: 8px;">Real-Time Administrative Control</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            Perform complete CRUD operations on students and courses, search directories dynamically, and export full reports to CSV.
        </p>
    </div>

    <div class="panel">
        <div class="stat-icon-wrap" style="color: #8b5cf6; background: rgba(139, 92, 246, 0.1); margin-bottom: 14px;">🔒</div>
        <h4 style="font-size: 17px; margin-bottom: 8px;">Secure Role Authentication</h4>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
            Bcrypt cryptographic password hashing, session guards on all internal routes, and self-service profile avatar customization.
        </p>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>