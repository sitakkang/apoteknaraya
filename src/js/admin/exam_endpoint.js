// ================================================================
// Penanda endpoint modal pemeriksaan.
//
// pemeriksaandokter.js dipakai dua halaman:
//   - menu Dokter   → endpoint di controller Dokter   (default 'dokter/')
//   - menu Anamnesa → endpoint di controller Anamnesa ('anamnesa/')
//
// File ini hanya dimuat pada halaman Anamnesa, jadi seluruh aksi modal
// (Dokter / Diagnosa / Obat & Racikan / SKS / SKBS / SKMB) memakai aturan
// akses menu Anamnesa, bukan menu Dokter.
// ================================================================
window.examEndpointBase = 'anamnesa/';
