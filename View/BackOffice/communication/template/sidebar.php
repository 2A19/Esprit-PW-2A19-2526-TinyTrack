<!-- Main Sidebar -->
<aside class="main-sidebar elevation-4">
  <a href="/TinyTrack/View/BackOffice/communication/communication_Backend.php" class="brand-link">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" class="brand-image">
    <span class="brand-text">TinyTrack</span>
  </a>
  <?php
require __DIR__ . "/../../../../config/dev_user.php";
?>
  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image pt-1">
        <i class="fas fa-user-shield fa-lg" style="color:rgba(255,255,255,0.7);"></i>
      </div>
      <div class="info">
        <a href="#" class="d-block" style="color:#fff;">
            <?= htmlspecialchars($devUser['name']) ?>
        </a>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <li class="nav-header">Communication Backend</li>
        <li class="nav-item">
          <a href="/TinyTrack/View/BackOffice/communication/communication_Backend.php" class="nav-link">
            <i class="nav-icon fas fa-comments" style="color:#5B9BD5;"></i>
            <p style="color:#fff;">Communication Backend</p>
          </a>
        </li>

      </ul>
    </nav>

  </div>
</aside>

