<?php 
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/includes/header.php';

$portfolio = getPortfolioList();
$produk_kaos = getProdukAktif();
$tingkat_kesulitan = getTingkatAktif();
?>
<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="container hero-content">
        <div class="hero-text">
            <span class="badge">🏆 Konveksi Sablon Terpercaya</span>
            <h1>Wujudkan <span class="highlight">Kaos Custom</span> Impianmu Bersama Kami</h1>
            <p>Narcism Sablon melayani pembuatan kaos sablon custom dengan kualitas premium, harga terjangkau, dan pengerjaan cepat. Cocok untuk komunitas, event, perusahaan, dan merchandise.</p>
            <div class="hero-buttons">
                <a href="#order" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Pesan Sekarang
                </a>
                <a href="#portfolio" class="btn btn-outline">
                    <i class="fas fa-images"></i> Lihat Portfolio
                </a>
            </div>
            <div class="hero-stats">
                <div class="stat">
                    <h3>500+</h3>
                    <p>Pelanggan Puas</p>
                </div>
                <div class="stat">
                    <h3>10K+</h3>
                    <p>Kaos Diproduksi</p>
                </div>
                <div class="stat">
                    <h3>8+</h3>
                    <p>Tahun Pengalaman</p>
                </div>
            </div>
        </div>
        <div class="hero-image">
            <div class="hero-img-wrapper">
                <img src="https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=600" alt="Kaos Sablon Custom">
                <div class="floating-card card-1">
                    <i class="fas fa-check-circle"></i>
                    <span>Kualitas Premium</span>
                </div>
                <div class="floating-card card-2">
                    <i class="fas fa-bolt"></i>
                    <span>Pengerjaan Cepat</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-palette"></i></div>
                <h3>Sablon Berkualitas</h3>
                <p>Menggunakan tinta dan teknik sablon terbaik untuk hasil maksimal</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-tags"></i></div>
                <h3>Harga Terjangkau</h3>
                <p>Harga kompetitif dengan diskon untuk pemesanan dalam jumlah banyak</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                <h3>Pengiriman Cepat</h3>
                <p>Estimasi pengerjaan 3-7 hari kerja tergantung tingkat kerumitan</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-headset"></i></div>
                <h3>Konsultasi Gratis</h3>
                <p>Tim kami siap membantu konsultasi desain dan bahan terbaik</p>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Section -->
<section class="portfolio" id="portfolio">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Portfolio Kami</span>
            <h2>Hasil Karya <span class="highlight">Terbaik</span> Kami</h2>
            <p>Beberapa contoh hasil sablon yang telah kami kerjakan untuk berbagai kebutuhan pelanggan</p>
        </div>
        
        <div class="portfolio-filter">
            <button class="filter-btn active" data-filter="all">Semua</button>
            <button class="filter-btn" data-filter="Komunitas">Komunitas</button>
            <button class="filter-btn" data-filter="Seragam">Seragam</button>
            <button class="filter-btn" data-filter="Merchandise">Merchandise</button>
            <button class="filter-btn" data-filter="Event">Event</button>
            <button class="filter-btn" data-filter="Corporate">Corporate</button>
        </div>
        
        <div class="portfolio-grid">
            <?php foreach ($portfolio as $item): ?>
<div class="portfolio-item" data-category="<?= htmlspecialchars($item['kategori']) ?>">
    <div class="portfolio-img">
        <img src="<?= htmlspecialchars($item['gambar']) ?>" alt="<?= htmlspecialchars($item['judul']) ?>" loading="lazy">
        <div class="portfolio-overlay">
            <span class="portfolio-cat"><?= htmlspecialchars($item['kategori']) ?></span>
            <h3><?= htmlspecialchars($item['judul']) ?></h3>
            <p><?= htmlspecialchars($item['deskripsi']) ?></p>
        </div>
    </div>
</div>
<?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Order Section -->
<section class="order" id="order">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Pesan Sekarang</span>
            <h2>Kalkulator <span class="highlight">Harga Sablon</span></h2>
            <p>Pilih jenis kaos dan tingkat kerumitan sablon untuk melihat estimasi harga</p>
        </div>

        <div class="order-wrapper">
            <form class="order-form" id="orderForm" method="POST" action="order-submit.php" enctype="multipart/form-data" novalidate>
                <!-- Honeypot anti-spam (disembunyikan lewat CSS, jangan dihapus) -->
                <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">

                <!-- Step 1: Pilih Jenis Kaos -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">1</span> Pilih Jenis Kaos</h3>
                    <div class="options-grid">
                        <?php foreach ($produk_kaos as $kaos): ?>
<div class="option-card" data-type="kaos" data-value="<?= htmlspecialchars($kaos['kode']) ?>" data-label="<?= htmlspecialchars($kaos['nama']) ?>" data-price="<?= $kaos['harga_dasar'] ?>">
    <div class="option-icon"><?= $kaos['ikon'] ?></div>
    <div class="option-info">
        <h4><?= htmlspecialchars($kaos['nama']) ?></h4>
        <p><?= htmlspecialchars($kaos['deskripsi']) ?></p>
        <span class="option-price"><?= formatRupiah($kaos['harga_dasar']) ?></span>
    </div>
    <div class="option-check"><i class="fas fa-check"></i></div>
</div>
<?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2: Pilih Tingkat Kerumitan -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">2</span> Pilih Tingkat Kerumitan Sablon</h3>
                    <div class="options-grid options-grid-2">
                        <?php foreach ($tingkat_kesulitan as $level): ?>
                        <div class="option-card" data-type="tingkat" data-value="<?= htmlspecialchars($level['kode']) ?>" data-label="<?= htmlspecialchars($level['nama']) ?>" data-price="<?= $level['tambahan_harga'] ?>">
                            <div class="option-icon"><?= $level['ikon'] ?></div>
                            <div class="option-info">
                                <h4><?= htmlspecialchars($level['nama']) ?></h4>
                                <p><?= htmlspecialchars($level['deskripsi']) ?></p>
                                <span class="option-price">+ <?= formatRupiah($level['tambahan_harga']) ?></span>
                            </div>
                            <div class="option-check"><i class="fas fa-check"></i></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 3: Jumlah Pesanan -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">3</span> Jumlah Pesanan</h3>
                    <div class="quantity-wrapper">
                        <button type="button" class="qty-btn" id="qtyMinus"><i class="fas fa-minus"></i></button>
                        <input type="number" id="quantity" value="<?= MIN_ORDER ?>" min="<?= MIN_ORDER ?>" max="9999">
                        <button type="button" class="qty-btn" id="qtyPlus"><i class="fas fa-plus"></i></button>
                    </div>
                    <p class="qty-note"><i class="fas fa-info-circle"></i> Minimal pemesanan <?= MIN_ORDER ?> pcs</p>
                    
                    <div class="discount-info">
                        <p><i class="fas fa-gift"></i> Diskon otomatis:</p>
                        <ul>
                            <li>24+ pcs: <strong>5%</strong></li>
                            <li>50+ pcs: <strong>10%</strong></li>
                            <li>100+ pcs: <strong>15%</strong></li>
                        </ul>
                    </div>
                </div>

                <!-- Step 4: Detail Kaos & Desain -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">4</span> Detail Kaos &amp; Desain</h3>
                    <div class="form-grid">
                        <div class="field" data-field="warna_kaos">
                            <label for="warna_kaos">Warna Kaos <span class="req">*</span></label>
                            <input type="text" id="warna_kaos" name="warna_kaos" maxlength="100" placeholder="Contoh: Hitam, atau 6 Hitam + 6 Putih">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="tanggal_dibutuhkan">
                            <label for="tanggal_dibutuhkan">Tanggal Dibutuhkan <span class="opt">(opsional)</span></label>
                            <input type="date" id="tanggal_dibutuhkan" name="tanggal_dibutuhkan" min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                            <small class="field-hint">Estimasi pengerjaan 3-7 hari kerja</small>
                            <small class="field-error"></small>
                        </div>
                        <div class="field field-full" data-field="rincian_ukuran">
                            <label for="rincian_ukuran">Rincian Ukuran <span class="opt">(opsional)</span></label>
                            <input type="text" id="rincian_ukuran" name="rincian_ukuran" maxlength="500" placeholder="Contoh: S=2, M=5, L=3, XL=2">
                            <small class="field-hint">Total ukuran sebaiknya sama dengan jumlah pesanan. Bisa juga dibahas lewat WhatsApp.</small>
                            <small class="field-error"></small>
                        </div>
                        <div class="field field-full" data-field="file_desain">
                            <label for="file_desain">Upload Desain <span class="opt">(opsional)</span></label>
                            <input type="file" id="file_desain" name="file_desain" accept=".jpg,.jpeg,.png,.webp,.pdf">
                            <small class="field-hint">Format JPG, PNG, WEBP, atau PDF. Maksimal 5 MB.</small>
                            <small class="field-error"></small>
                        </div>
                        <div class="field field-full" data-field="catatan">
                            <label for="catatan">Catatan Tambahan <span class="opt">(opsional)</span></label>
                            <textarea id="catatan" name="catatan" rows="3" maxlength="1000" placeholder="Posisi sablon (dada/punggung/lengan), ukuran gambar, referensi, dll."></textarea>
                            <small class="field-error"></small>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Data Pemesan -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">5</span> Data Pemesan</h3>
                    <div class="form-grid">
                        <div class="field field-full" data-field="nama">
                            <label for="nama">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" id="nama" name="nama" maxlength="100" placeholder="Nama sesuai identitas" autocomplete="name">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="no_wa">
                            <label for="no_wa">Nomor WhatsApp <span class="req">*</span></label>
                            <input type="tel" id="no_wa" name="no_wa" maxlength="20" placeholder="08123456789" inputmode="numeric" autocomplete="tel">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="email">
                            <label for="email">Email <span class="opt">(opsional)</span></label>
                            <input type="email" id="email" name="email" maxlength="100" placeholder="nama@email.com" autocomplete="email">
                            <small class="field-error"></small>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Pengambilan / Pengiriman -->
                <div class="form-step">
                    <h3 class="step-title"><span class="step-number">6</span> Pengambilan Pesanan</h3>
                    <input type="hidden" name="metode" id="metode" value="kirim">
                    <div class="options-grid options-grid-2">
                        <div class="option-card selected" data-type="metode" data-value="kirim">
                            <div class="option-icon">🚚</div>
                            <div class="option-info">
                                <h4>Kirim ke Alamat</h4>
                                <p>Pesanan diantar ke alamatmu. Ongkir dikonfirmasi oleh tim kami.</p>
                            </div>
                            <div class="option-check"><i class="fas fa-check"></i></div>
                        </div>
                        <div class="option-card" data-type="metode" data-value="ambil">
                            <div class="option-icon">🏪</div>
                            <div class="option-info">
                                <h4>Ambil di Toko</h4>
                                <p>Ambil sendiri di workshop kami tanpa ongkir.</p>
                            </div>
                            <div class="option-check"><i class="fas fa-check"></i></div>
                        </div>
                    </div>

                    <!-- Alamat (tampil kalau pilih "Kirim ke Alamat") -->
                    <div id="alamatBox" class="form-grid address-box">
                        <div class="field field-full" data-field="alamat">
                            <label for="alamat">Alamat Lengkap <span class="req">*</span></label>
                            <textarea id="alamat" name="alamat" rows="3" maxlength="500" placeholder="Nama jalan, nomor rumah, RT/RW, patokan" autocomplete="street-address"></textarea>
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="kecamatan">
                            <label for="kecamatan">Kecamatan <span class="req">*</span></label>
                            <input type="text" id="kecamatan" name="kecamatan" maxlength="100">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="kota">
                            <label for="kota">Kota / Kabupaten <span class="req">*</span></label>
                            <input type="text" id="kota" name="kota" maxlength="100" autocomplete="address-level2">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="provinsi">
                            <label for="provinsi">Provinsi <span class="req">*</span></label>
                            <input type="text" id="provinsi" name="provinsi" maxlength="100" autocomplete="address-level1">
                            <small class="field-error"></small>
                        </div>
                        <div class="field" data-field="kode_pos">
                            <label for="kode_pos">Kode Pos <span class="opt">(opsional)</span></label>
                            <input type="text" id="kode_pos" name="kode_pos" maxlength="5" inputmode="numeric" autocomplete="postal-code">
                            <small class="field-error"></small>
                        </div>
                    </div>

                    <!-- Info toko (tampil kalau pilih "Ambil di Toko") -->
                    <div id="tokoBox" class="pickup-info" style="display:none">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Lokasi pengambilan</strong>
                            <p><?= htmlspecialchars(getSetting('alamat', 'Alamat toko akan diinformasikan oleh admin')) ?></p>
                        </div>
                    </div>

                    <div class="field consent" data-field="setuju">
                        <label class="check-label">
                            <input type="checkbox" id="setuju" name="setuju" value="1">
                            <span>Data yang saya isi sudah benar, dan saya paham harga final akan dikonfirmasi oleh tim Narcism Sablon.</span>
                        </label>
                        <small class="field-error"></small>
                    </div>
                </div>
            </form>

            <!-- Summary Card -->
            <div class="order-summary">
                <div class="summary-card">
                    <h3><i class="fas fa-receipt"></i> Ringkasan Pesanan</h3>
                    
                    <div class="summary-item">
                        <span>Jenis Kaos</span>
                        <strong id="sumJenis">-</strong>
                    </div>
                    <div class="summary-item">
                        <span>Tingkat Sablon</span>
                        <strong id="sumTingkat">-</strong>
                    </div>
                    <div class="summary-item">
                        <span>Jumlah</span>
                        <strong id="sumJumlah">-</strong>
                    </div>
                    <div class="summary-item">
                        <span>Pengambilan</span>
                        <strong id="sumMetode">Kirim ke Alamat</strong>
                    </div>
                    <div class="summary-item">
                        <span>Harga Satuan</span>
                        <strong id="sumHargaSatuan">-</strong>
                    </div>
                    <div class="summary-item">
                        <span>Subtotal</span>
                        <strong id="sumSubtotal">-</strong>
                    </div>
                    <div class="summary-item discount" id="discountRow" style="display:none">
                        <span>Diskon</span>
                        <strong id="sumDiskon">-</strong>
                    </div>
                    
                    <div class="summary-divider"></div>
                    
                    <div class="summary-total">
                        <span>Total Estimasi</span>
                        <h2 id="sumTotal">Rp 0</h2>
                    </div>

                    <button type="submit" form="orderForm" class="btn btn-primary btn-block" id="btnOrder" disabled>
                        <i class="fas fa-paper-plane"></i> <span id="btnOrderText">Buat Pesanan</span>
                    </button>
                    <p class="summary-note" id="orderHint">Pilih jenis kaos &amp; tingkat sablon dulu, lalu lengkapi data diri.</p>
                    <p class="summary-note">*Harga final akan dikonfirmasi oleh tim kami</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Pesanan Berhasil -->
<div class="modal-overlay" id="successModal" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="successTitle">
        <div class="modal-icon"><i class="fas fa-check"></i></div>
        <h3 id="successTitle">Pesanan Berhasil Dibuat!</h3>
        <p>Terima kasih, <strong id="successNama"></strong>. Pesananmu sudah kami terima.</p>
        <div class="order-code">
            <small>Kode Pesanan</small>
            <strong id="successKode">-</strong>
        </div>
        <p class="modal-total">Total estimasi: <strong id="successTotal">-</strong></p>
        <p class="modal-note">Langkah terakhir: kirim detail pesanan ke WhatsApp kami agar segera dikonfirmasi. Simpan kode pesananmu.</p>
        <a href="#" target="_blank" rel="noopener" class="btn btn-wa" id="successWA">
            <i class="fab fa-whatsapp"></i> Konfirmasi via WhatsApp
        </a>
        <button type="button" class="modal-close" id="successClose">Tutup</button>
    </div>
</div>

<!-- CTA Section -->
<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Siap Membuat Kaos Custom Impianmu?</h2>
            <p>Konsultasikan kebutuhan sablonmu sekarang juga, gratis!</p>
            <a href="https://wa.me/<?= +6285136479110 ?>?text=Halo%20Narcism%20Sablon%2C%20saya%20ingin%20konsultasi%20mengenai%20sablon%20kaos" 
               class="btn btn-light" target="_blank">
                <i class="fab fa-whatsapp"></i> Konsultasi Gratis
            </a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>