<!-- ======== sidebar =========== -->
<aside class="sidebar-nav-wrapper">
  <div class="navbar-logo">
    <a href="index.php">
      <img src="images/x.png" alt="logo" width="40%" height="70%" />
    </a>
  </div>
  <nav class="sidebar-nav">
    <ul>
      <li class="nav-item nav-item-has-children">
        <a href="#0" data-bs-toggle="collapse" data-bs-target="#ddmenu_1">
          <span class="icon"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M8.74999 18.3333C12.2376 18.3333 15.1364 15.8128 15.7244 12.4941C15.8448 11.8143 15.2737 11.25 14.5833 11.25H9.99999C9.30966 11.25 8.74999 10.6903 8.74999 10V5.41666C8.74999 4.7263 8.18563 4.15512 7.50586 4.27556C4.18711 4.86357 1.66666 7.76243 1.66666 11.25C1.66666 15.162 4.83797 18.3333 8.74999 18.3333Z" fill="red"/><path d="M17.0833 10C17.7737 10 18.3432 9.43708 18.2408 8.75433C17.7005 5.14918 14.8508 2.29947 11.2457 1.75912C10.5629 1.6568 10 2.2263 10 2.91665V9.16666C10 9.62691 10.3731 10 10.8333 10H17.0833Z" fill="red"/></svg></span>
          <span class="text">Dashboard</span>
        </a>
        <ul id="ddmenu_1" class="collapse dropdown-nav">
          <li><a href="index.php">Accueil</a></li>
        </ul>
      </li>

      <li class="nav-item nav-item-has-children">
        <a href="#0" data-bs-toggle="collapse" data-bs-target="#ddmenu_5" aria-expanded="true">
          <span class="icon"><svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M4.16666 3.33335C4.16666 2.41288 4.91285 1.66669 5.83332 1.66669H14.1667C15.0872 1.66669 15.8333 2.41288 15.8333 3.33335V16.6667C15.8333 17.5872 15.0872 18.3334 14.1667 18.3334H5.83332C4.91285 18.3334 4.16666 17.5872 4.16666 16.6667V3.33335Z" fill="red"/></svg></span>
          <span class="text">Événements</span>
        </a>
        <ul id="ddmenu_5" class="collapse show dropdown-nav">
          <li><a href="evenementList.php">Liste des Événements</a></li>
          <li><a href="ajouterevenement.php">Ajouter Événement</a></li>
        </ul>
      </li>

      <li class="nav-item nav-item-has-children">
        <a href="#0" data-bs-toggle="collapse" data-bs-target="#ddmenu_6" aria-expanded="false">
          <span class="icon"><svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2C5.58 2 2 5.58 2 10s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8zm-1 12H7V9h2v5zm4 0h-2V7h2v7z" fill="red"/></svg></span>
          <span class="text">Réservations</span>
        </a>
        <ul id="ddmenu_6" class="collapse dropdown-nav">
          <li><a href="reservations/list.php">Liste des Réservations</a></li>
          <li><a href="reservations/add.php">Ajouter Réservation</a></li>
        </ul>
      </li>

      <li class="nav-item">
        <a href="../front/index.php">
          <span class="icon"><svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2L2 7v11h6v-6h4v6h6V7l-8-5z" fill="red"/></svg></span>
          <span class="text">Voir FrontOffice</span>
        </a>
      </li>
    </ul>
  </nav>
</aside>
