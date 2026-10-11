// ============ STATE ============
// Label jenis kaos & tingkat sablon sekarang dibaca otomatis dari atribut
// data-label di index.php (yang bersumber dari database), jadi tidak perlu
// diubah manual lagi saat admin menambah produk baru.
const state = {
    jenis: null,
    jenisLabel: '',
    hargaJenis: 0,
    tingkat: null,
    tingkatLabel: '',
    hargaTingkat: 0,
    jumlah: 12,
    metode: 'kirim'
};

// ============ NAVBAR ============
const navbar = document.getElementById('navbar');
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');

window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 50);
});

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        hamburger.classList.remove('active');
    });
});

// Active nav link on scroll
const sections = document.querySelectorAll('section[id]');
window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 150;
        if (window.scrollY >= sectionTop) {
            current = section.getAttribute('id');
        }
    });
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});

// ============ PORTFOLIO FILTER ============
document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        
        const filter = btn.dataset.filter;
        document.querySelectorAll('.portfolio-item').forEach(item => {
            if (filter === 'all' || item.dataset.category === filter) {
                item.classList.remove('hide');
            } else {
                item.classList.add('hide');
            }
        });
    });
});

// ============ ORDER FORM ============
const orderForm  = document.getElementById('orderForm');
const btnOrder   = document.getElementById('btnOrder');
const metodeInput = document.getElementById('metode');
const alamatBox  = document.getElementById('alamatBox');
const tokoBox    = document.getElementById('tokoBox');

// Pilih kartu (jenis kaos, tingkat sablon, metode pengambilan)
document.querySelectorAll('.option-card').forEach(card => {
    card.addEventListener('click', () => {
        const type  = card.dataset.type;
        const value = card.dataset.value;

        document.querySelectorAll(`.option-card[data-type="${type}"]`).forEach(c => {
            c.classList.remove('selected');
        });
        card.classList.add('selected');

        if (type === 'kaos') {
            state.jenis = value;
            state.jenisLabel = card.dataset.label;
            state.hargaJenis = parseInt(card.dataset.price) || 0;
        } else if (type === 'tingkat') {
            state.tingkat = value;
            state.tingkatLabel = card.dataset.label;
            state.hargaTingkat = parseInt(card.dataset.price) || 0;
        } else if (type === 'metode') {
            state.metode = value;
            metodeInput.value = value;
            const kirim = value === 'kirim';
            alamatBox.style.display = kirim ? '' : 'none';
            tokoBox.style.display   = kirim ? 'none' : 'flex';
            if (!kirim) {
                // bersihkan error alamat yang mungkin masih tampil
                alamatBox.querySelectorAll('.field').forEach(f => setFieldError(f.dataset.field, ''));
            }
        }

        updateSummary();
    });
});

// Quantity controls
const qtyInput = document.getElementById('quantity');
const qtyMinus = document.getElementById('qtyMinus');
const qtyPlus  = document.getElementById('qtyPlus');
const MIN_ORDER = parseInt(qtyInput.min) || 12; // diambil dari pengaturan admin
state.jumlah = parseInt(qtyInput.value) || MIN_ORDER;

qtyMinus.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || MIN_ORDER;
    if (val > MIN_ORDER) {
        qtyInput.value = val - 1;
        state.jumlah = val - 1;
        updateSummary();
    }
});

qtyPlus.addEventListener('click', () => {
    let val = parseInt(qtyInput.value) || MIN_ORDER;
    if (val < 9999) {
        qtyInput.value = val + 1;
        state.jumlah = val + 1;
        updateSummary();
    }
});

qtyInput.addEventListener('input', () => {
    let val = parseInt(qtyInput.value) || MIN_ORDER;
    if (val < MIN_ORDER) val = MIN_ORDER;
    if (val > 9999) val = 9999;
    state.jumlah = val;
    updateSummary();
});

qtyInput.addEventListener('blur', () => {
    let val = parseInt(qtyInput.value) || MIN_ORDER;
    if (val < MIN_ORDER) {
        qtyInput.value = MIN_ORDER;
        state.jumlah = MIN_ORDER;
        updateSummary();
    }
});

// ============ CALCULATE & UPDATE SUMMARY ============
function formatRupiah(angka) {
    return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
}

// Aturan diskon & pembulatan HARUS sama dengan hitungRincianHarga() di functions.php
function calculate() {
    const hargaSatuan = state.hargaJenis + state.hargaTingkat;
    const subtotal = hargaSatuan * state.jumlah;

    let diskonPersen = 0;
    if (state.jumlah >= 100) diskonPersen = 15;
    else if (state.jumlah >= 50) diskonPersen = 10;
    else if (state.jumlah >= 24) diskonPersen = 5;

    const diskonNominal = Math.round(subtotal * diskonPersen / 100);
    const total = subtotal - diskonNominal;

    return { hargaSatuan, subtotal, diskonPersen, diskonNominal, total };
}

function updateSummary() {
    document.getElementById('sumJenis').textContent = state.jenis ? state.jenisLabel : '-';
    document.getElementById('sumTingkat').textContent = state.tingkat ? state.tingkatLabel : '-';
    document.getElementById('sumJumlah').textContent = state.jumlah + ' pcs';
    document.getElementById('sumMetode').textContent = state.metode === 'kirim' ? 'Kirim ke Alamat' : 'Ambil di Toko';

    const hint = document.getElementById('orderHint');

    if (state.jenis && state.tingkat) {
        const calc = calculate();
        document.getElementById('sumHargaSatuan').textContent = formatRupiah(calc.hargaSatuan);
        document.getElementById('sumSubtotal').textContent = formatRupiah(calc.subtotal);

        if (calc.diskonPersen > 0) {
            document.getElementById('discountRow').style.display = 'flex';
            document.getElementById('sumDiskon').textContent =
                `-${formatRupiah(calc.diskonNominal)} (${calc.diskonPersen}%)`;
        } else {
            document.getElementById('discountRow').style.display = 'none';
        }

        document.getElementById('sumTotal').textContent = formatRupiah(calc.total);
        btnOrder.disabled = false;
        hint.textContent = 'Lengkapi data di langkah 4-6, lalu klik "Buat Pesanan".';
    } else {
        document.getElementById('sumHargaSatuan').textContent = '-';
        document.getElementById('sumSubtotal').textContent = '-';
        document.getElementById('discountRow').style.display = 'none';
        document.getElementById('sumTotal').textContent = 'Rp 0';
        btnOrder.disabled = true;
        hint.textContent = 'Pilih jenis kaos & tingkat sablon dulu, lalu lengkapi data diri.';
    }
}

// ============ VALIDASI FORM DATA DIRI ============
function getField(name) {
    return orderForm.querySelector(`.field[data-field="${name}"]`);
}

function setFieldError(name, message) {
    const field = getField(name);
    if (!field) return;
    const err = field.querySelector('.field-error');
    field.classList.toggle('has-error', !!message);
    if (err) err.textContent = message || '';
}

function clearAllErrors() {
    orderForm.querySelectorAll('.field').forEach(f => setFieldError(f.dataset.field, ''));
}

// Hapus pesan error begitu pengunjung mulai memperbaiki isiannya
orderForm.querySelectorAll('input, textarea').forEach(el => {
    const evt = (el.type === 'checkbox' || el.type === 'file') ? 'change' : 'input';
    el.addEventListener(evt, () => {
        const f = el.closest('.field');
        if (f) setFieldError(f.dataset.field, '');
    });
});

// Nomor WA: hanya izinkan angka, spasi, +, dan -
document.getElementById('no_wa').addEventListener('input', e => {
    e.target.value = e.target.value.replace(/[^\d+\s-]/g, '');
});
document.getElementById('kode_pos').addEventListener('input', e => {
    e.target.value = e.target.value.replace(/\D/g, '');
});

function validateClient() {
    const errors = {};
    const val = id => document.getElementById(id).value.trim();

    if (val('nama').length < 3) errors.nama = 'Nama lengkap minimal 3 karakter.';

    const wa = val('no_wa').replace(/\D/g, '');
    if (!/^(62|0)?8\d{8,12}$/.test(wa)) errors.no_wa = 'Nomor WhatsApp tidak valid. Contoh: 081234567890';

    const email = val('email');
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) errors.email = 'Format email tidak valid.';

    if (!val('warna_kaos')) errors.warna_kaos = 'Isi warna kaos yang diinginkan.';

    if (state.metode === 'kirim') {
        if (val('alamat').length < 10) errors.alamat = 'Alamat lengkap minimal 10 karakter (jalan, RT/RW, nomor rumah).';
        if (!val('kecamatan')) errors.kecamatan = 'Kecamatan wajib diisi.';
        if (!val('kota'))      errors.kota = 'Kota/Kabupaten wajib diisi.';
        if (!val('provinsi'))  errors.provinsi = 'Provinsi wajib diisi.';
        const pos = val('kode_pos');
        if (pos && !/^\d{5}$/.test(pos)) errors.kode_pos = 'Kode pos harus 5 angka.';
    }

    const file = document.getElementById('file_desain').files[0];
    if (file) {
        const okType = /\.(jpe?g|png|webp|pdf)$/i.test(file.name);
        if (!okType) errors.file_desain = 'Format file harus JPG, PNG, WEBP, atau PDF.';
        else if (file.size > 5 * 1024 * 1024) errors.file_desain = 'Ukuran file terlalu besar (maks 5 MB).';
    }

    if (!document.getElementById('setuju').checked) errors.setuju = 'Centang persetujuan terlebih dahulu.';

    return errors;
}

function showErrors(errors) {
    clearAllErrors();
    let first = null;
    // urutkan sesuai posisi di halaman supaya scroll ke error paling atas
    const fields = [...orderForm.querySelectorAll('.field')].map(f => f.dataset.field);
    fields.forEach(name => {
        if (errors[name]) {
            setFieldError(name, errors[name]);
            if (!first) first = getField(name);
        }
    });
    if (first) {
        first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const input = first.querySelector('input, textarea');
        if (input && input.type !== 'checkbox') setTimeout(() => input.focus({ preventScroll: true }), 400);
    }
}

// ============ KIRIM PESANAN ============
const BTN_LABEL = 'Buat Pesanan';

function setLoading(on) {
    btnOrder.disabled = on || !(state.jenis && state.tingkat);
    document.getElementById('btnOrderText').textContent = on ? 'Mengirim pesanan...' : BTN_LABEL;
}

function resetOrderAfterSuccess() {
    orderForm.reset();
    clearAllErrors();
    document.querySelectorAll('.option-card[data-type="kaos"], .option-card[data-type="tingkat"]')
        .forEach(c => c.classList.remove('selected'));
    state.jenis = state.tingkat = null;
    state.jenisLabel = state.tingkatLabel = '';
    state.hargaJenis = state.hargaTingkat = 0;
    state.jumlah = MIN_ORDER;
    qtyInput.value = MIN_ORDER;
    // kembalikan metode ke default "kirim"
    document.querySelector('.option-card[data-type="metode"][data-value="kirim"]').click();
    updateSummary();
}

orderForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!state.jenis || !state.tingkat) {
        document.getElementById('order').scrollIntoView({ behavior: 'smooth' });
        return;
    }

    const errors = validateClient();
    if (Object.keys(errors).length) {
        showErrors(errors);
        return;
    }
    clearAllErrors();

    const fd = new FormData(orderForm);
    fd.set('jenis', state.jenis);
    fd.set('tingkat', state.tingkat);
    fd.set('jumlah', state.jumlah);

    setLoading(true);
    try {
        const res = await fetch('order-submit.php', { method: 'POST', body: fd });
        let data;
        try { data = await res.json(); }
        catch { throw new Error('Respon server tidak valid. Coba lagi beberapa saat.'); }

        if (!data.success) {
            if (data.errors) {
                showErrors(data.errors);
                // error yang bukan milik kolom form (mis. pilihan kaos tidak valid)
                if (data.errors.jenis || data.errors.jumlah) alert(data.errors.jenis || data.errors.jumlah);
            } else {
                alert(data.message || 'Gagal membuat pesanan.');
            }
            return;
        }

        const namaPemesan = document.getElementById('nama').value.trim();
        showSuccess(data, namaPemesan);
        resetOrderAfterSuccess();
    } catch (err) {
        alert(err.message || 'Tidak dapat terhubung ke server. Periksa koneksi internet kamu.');
    } finally {
        setLoading(false);
    }
});

// ============ MODAL SUKSES ============
const successModal = document.getElementById('successModal');

function showSuccess(data, nama) {
    document.getElementById('successNama').textContent = nama;
    document.getElementById('successKode').textContent = data.kode;
    document.getElementById('successTotal').textContent = data.total;

    // Nomor WA tujuan diambil dari tombol WhatsApp melayang (sama seperti sebelumnya)
    const waNumber = document.querySelector('.floating-wa').getAttribute('href').split('/').pop();
    document.getElementById('successWA').href =
        `https://wa.me/${waNumber}?text=${encodeURIComponent(data.wa_text)}`;

    successModal.classList.add('open');
    successModal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

function closeSuccess() {
    successModal.classList.remove('open');
    successModal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

document.getElementById('successClose').addEventListener('click', closeSuccess);
successModal.addEventListener('click', e => { if (e.target === successModal) closeSuccess(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSuccess(); });

// ============ SMOOTH SCROLL ============
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth' });
        }
    });
});

// ============ SCROLL ANIMATION ============
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.feature-card, .portfolio-item, .form-step, .summary-card').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(el);
});

// Initialize
updateSummary();