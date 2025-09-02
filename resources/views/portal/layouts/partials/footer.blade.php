<footer id="footer" class="footer position-relative dark-background">

    <div class="footer-top">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 col-md-6 footer-about">
            <a href="index.html" class="logo d-flex align-items-center">
              <span class="sitename">{{ $toko_setting->nama_toko }}</span>
            </a>
            <div class="footer-contact pt-3">
              <p>{{ $toko_setting->alamat_toko }}</p>
              <p><strong>Telepon:</strong> <span>{{ $toko_setting->nomor_hp_toko }}</span></p>
            </div>
          </div>

          <div class="col-lg-6 col-md-6 footer-links">
            <h4>Useful Links</h4>
            <ul>
              <li><a href="#">Home</a></li>
              <li><a href="#">Gallery</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div>

    <div class="copyright text-center">
      <div class="container d-flex flex-column flex-lg-row justify-content-center justify-content-lg-between align-items-center">

        <div class="d-flex flex-column align-items-center align-items-lg-start">
          <div>
            © Copyright <strong><span>Sarababisa</span></strong>. All Rights Reserved
          </div>
          <div class="credits">
            Designed by <a href="https://saraba-bisa.com">Sarababisa.com</a>
          </div>
        </div>

      </div>
    </div>

  </footer>