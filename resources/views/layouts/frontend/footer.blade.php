<footer class="ftco-footer ftco-bg-dark ftco-section">
    <div class="container">
        <div class="row mb-5">

            {{-- Brand & Sosial --}}
            <div class="col-md">
                <div class="ftco-footer-widget mb-4">
                    <h2 class="ftco-heading-2">
                        <a href="{{ route('home') }}" class="logo">Cosmo<span>Rent</span></a>
                    </h2>
                    <p>Cosmo Rent: Penyewaan mobil dan motor terpercaya untuk kebutuhan Anda.</p>
                    <ul class="ftco-footer-social list-unstyled float-md-left float-lft mt-5">
                        <li class="ftco-animate">
                            <a href="https://www.facebook.com/RentalMobilJakartaCosmorentcar"
                               target="_blank" rel="noopener noreferrer">
                                <span class="icon-facebook"></span>
                            </a>
                        </li>
                        <li class="ftco-animate">
                            <a href="https://www.instagram.com/rentalmobiljakartacosmo"
                               target="_blank" rel="noopener noreferrer">
                                <span class="icon-instagram"></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Informasi --}}
            <div class="col-md">
                <div class="ftco-footer-widget mb-4 ml-md-5">
                    <h2 class="ftco-heading-2">Informasi</h2>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('about') }}" class="py-2 d-block">Tentang Kami</a></li>
                        <li>
                            <a href="https://www.termsfeed.com/live/d9c1aaf3-f575-450b-b963-61dc9cce3c79"
                               class="py-2 d-block" target="_blank" rel="noopener noreferrer">
                                Syarat &amp; Ketentuan
                            </a>
                        </li>
                        <li>
                            <a href="https://www.termsfeed.com/live/8bf7d4f3-1fcb-40da-ad0f-face130814f2"
                               class="py-2 d-block" target="_blank" rel="noopener noreferrer">
                                Kebijakan Privasi
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Dukungan — FAQ & Tips dihapus karena belum ada halaman --}}
            <div class="col-md">
                <div class="ftco-footer-widget mb-4">
                    <h2 class="ftco-heading-2">Dukungan Pelanggan</h2>
                    <ul class="list-unstyled">
                        <li><a href="{{ route('contact') }}" class="py-2 d-block">Kontak Kami</a></li>
                        <li><a href="{{ route('vehicles.index') }}" class="py-2 d-block">Lihat Armada</a></li>
                    </ul>
                </div>
            </div>

            {{-- Kontak --}}
            <div class="col-md">
                <div class="ftco-footer-widget mb-4">
                    <h2 class="ftco-heading-2">Punya Pertanyaan?</h2>
                    <div class="block-23 mb-3">
                        <ul>
                            <li>
                                <span class="icon icon-map-marker"></span>
                                <span class="text">
                                    Jl. Nusa Indah No.3, RT.3/RW.1, Melawai,
                                    Kec. Kebayoran Baru, Jakarta Selatan 12160.
                                </span>
                            </li>
                            <li>
                                {{-- FIX: tambah tag <a> agar nomor bisa diklik di mobile --}}
                                <a href="tel:+6281294734527">
                                    <span class="icon icon-phone"></span>
                                    <span class="text">+62 812-9473-4527</span>
                                </a>
                            </li>
                            <li>
                                <a href="mailto:cosmorentcar.co.id@gmail.com">
                                    <span class="icon icon-envelope"></span>
                                    <span class="text ml-3">cosmorentcar.co.id@gmail.com</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-12 text-center">
                <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    Copyright &copy;
                    <script>document.write(new Date().getFullYear());</script>
                    CosmoRent. All rights reserved
                </p>
            </div>
        </div>
    </div>
</footer>