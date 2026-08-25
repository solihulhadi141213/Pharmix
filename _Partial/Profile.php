<li class="nav-item dropdown pe-3">
    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
        <?php
            echo '<img src="image_proxy.php?dir=User&filename='.$SessionGambar.'" alt="Profile" class="rounded-circle">';
            echo '<span class="d-none d-md-block dropdown-toggle ps-2 text-white">'.$SessionNama.'</span>';
        ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
        <li class="dropdown-header">
            <h6><?php echo $SessionNama;?></h6>
            <span><?php echo $SessionLevelAkses;?></span>
        </li>
        <?php
            echo '
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="index.php?Page=MyProfile">
                        <i class="bi bi-person"></i>
                        <span>Profil Saya</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="index.php?Page=Bantuan">
                        <i class="bi bi-question-circle"></i>
                        <span>Bantuan</span>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#ModalLogout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </a>
                </li>
            ';

        ?>
    </ul>
</li>