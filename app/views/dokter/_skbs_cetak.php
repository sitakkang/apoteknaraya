<!DOCTYPE html>
<html>
<head>
    <title>Cetak SKBS</title>
    <style>
        /* ═══════════════════════════════════════════════════
           SKBS — Desain RESMI (monokrom) — seragam dgn SKS
           ══════════════════════════════════════════════════ */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 10px 22px;
            color: #1a1a1a;
        }

        /* ── KOP SURAT ────────────────────────────────── */
        .kop { text-align: center; }
        .kop-instansi { font-size: 16px; font-weight: 700; letter-spacing: 1.6px; }
        .kop-dokter   { font-size: 13px; font-weight: 600; margin-top: 2px; }
        .kop-nip      { font-size: 12px; color: #444; margin-top: 1px; letter-spacing: 0.3px; }
        .kop-alamat   { font-size: 12px; color: #555; margin-top: 2px; }
        .kop hr.tebal { border: 0; border-top: 2px solid #1a1a1a; margin: 5px 0 0; }
        .kop hr.tipis { border: 0; border-top: 1px solid #1a1a1a; margin: 2px 0 0; }

        /* ── JUDUL DOKUMEN ────────────────────────────── */
        .judul { text-align: center; margin: 14px 0; }
        .judul h1 {
            margin: 0;
            font-size: 17px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 5px;
        }
        .judul .nomor { margin: 5px 0 0; font-size: 15px; }

        /* ── BAGIAN / SECTION ─────────────────────────── */
        .section { margin-bottom: 9px; }
        .section-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            border-bottom: 1px solid #1a1a1a;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }
        .section-title .angka { display: inline-block; min-width: 40px; }

        /* ── TABEL DATA (tanpa bingkai / garis) ───────── */
        table.data { width: 100%; border-collapse: collapse; font-size: 14px; }
        table.data td {
            border: 0;
            padding: 3px 8px;
            vertical-align: top;
        }
        table.data td.label { width: 170px; font-weight: 600; }
        table.data td.pemisah { width: 14px; text-align: center; padding-left: 0; padding-right: 0; }

        /* ── HASIL PEMERIKSAAN ───────────────────────── */
        .hasil-box { margin: 2px 0 0; }
        .hasil-item { display: inline-block; margin-right: 22px; font-size: 14px; }
        .hasil-item input { margin-right: 4px; accent-color: #1a1a1a; }
        .catatan { font-size: 14px; font-style: italic; margin: 6px 0 0; }

        /* ── PEMERIKSAAN FISIK (2 kolom) ─────────────── */
        .vital-grid { width: 100%; border-collapse: collapse; font-size: 14px; }
        .vital-grid td { border: 0; padding: 3px 8px; vertical-align: top; }
        .vital-grid td:first-child { width: 150px; font-weight: 600; }
        .vital-grid td:nth-child(2) { width: auto; }
        .vital-grid td:nth-child(3) { width: 150px; font-weight: 600; }
        .vital-grid td:nth-child(4) { width: auto; }

        /* ── TANDA TANGAN ─────────────────────────────── */
        .ttd-wrap { display: flex; justify-content: flex-end; margin-top: 14px; }
        .ttd-box  { text-align: center; min-width: 240px; }
        .ttd-place { margin: 0 0 2px; font-size: 14px; }
        .ttd-role  { margin: 0 0 2px; font-size: 14px; }
        .ttd-qr    { margin: 8px 0 4px; }
        .ttd-qr img { width: 80px; height: 80px; display: block; margin: 0 auto; }
        .ttd-name  { margin: 0; font-size: 14px; font-weight: 700; border-top: 1px solid #1a1a1a; padding-top: 4px; display: inline-block; min-width: 200px; }
        .ttd-nip   { margin: 3px 0 0; font-size: 12px; color: #555; }

        /* ── FOOTER ───────────────────────────────────── */
        .footer { margin-top: 14px; border-top: 1px solid #c4c4c4; padding-top: 5px; }
        .footer p { margin: 0; font-size: 10px; color: #666; text-align: center; font-style: italic; }

        /* ── TOMBOL (hanya tampil di layar) ───────────── */
        .btn-print { display: inline-block; margin-bottom: 10px; padding: 6px 16px; background: #1a1a1a; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-print:hover { background: #000; }

        /* ── CETAK: kunci ke 1 lembar A4 ───────────────── */
        @page { size: A4 portrait; margin: 10mm 12mm; }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .section, .ttd-wrap, .footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <button class="btn-print no-print" onclick="window.print()"><i class="fa fa-print"></i> Print / PDF</button>
    <button class="btn-print no-print" onclick="window.close()" style="background:#666;margin-left:6px;">Tutup</button>

    <!-- ══════════ KOP SURAT ══════════ -->
    <div class="kop">
        <div class="kop-instansi">PRAKTEK DOKTER UMUM</div>
        <div class="kop-dokter"><?= htmlspecialchars($row->skbs_doct_name ?: '_________________') ?></div>
        <?php if (!empty($row->nip)): ?>
        <div class="kop-nip">NIP. <?= htmlspecialchars($row->nip) ?></div>
        <?php endif; ?>
        <div class="kop-alamat">
            Jl. Poros Transmigrasi, Ds. Keurea, Kec. Bahodopi, Morowali &nbsp;|&nbsp; Telp. 082190597990
        </div>
        <hr class="tebal">
        <hr class="tipis">
    </div>

    <!-- ══════════ JUDUL DOKUMEN ══════════ -->
    <div class="judul">
        <h1>SURAT KETERANGAN BERBADAN SEHAT</h1>
        <p class="nomor">No. Dokumen: <?= htmlspecialchars($docnumb) ?></p>
    </div>

    <!-- ══════════ I. IDENTITAS PASIEN ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">I.</span>Identitas Pasien</div>
        <table class="data">
            <tr><td class="label">Nama</td><td class="pemisah">:</td><td><strong><?= htmlspecialchars($row->skbs_patient_name) ?></strong></td></tr>
            <tr><td class="label">Umur</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->skbs_patient_age ?: '-') ?> Tahun</td></tr>
            <tr><td class="label">NIK</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->skbs_patient_nik ?: '-') ?></td></tr>
            <tr><td class="label">Perusahaan</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->skbs_patient_company ?: '-') ?></td></tr>
        </table>
    </div>

    <!-- ══════════ II. HASIL PEMERIKSAAN ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">II.</span>Hasil Pemeriksaan</div>
        <div class="hasil-box">
            <span class="hasil-item"><input type="checkbox" <?= $row->skbs_result_name == 'FIT' ? 'checked' : '' ?> disabled> Fit</span>
            <span class="hasil-item"><input type="checkbox" <?= $row->skbs_result_name == 'FIT DENGAN CATATAN' ? 'checked' : '' ?> disabled> Fit dengan Catatan</span>
            <span class="hasil-item"><input type="checkbox" <?= $row->skbs_result_name == 'UNFIT' ? 'checked' : '' ?> disabled> Unfit</span>
        </div>
        <?php if (strtoupper($row->skbs_result_name) == 'FIT DENGAN CATATAN' && !empty($row->skbs_note)): ?>
            <p class="catatan">Catatan: <?= htmlspecialchars($row->skbs_note) ?></p>
        <?php elseif (strtoupper($row->skbs_result_name) == 'KETERANGAN' && !empty($row->skbs_desc)): ?>
            <p class="catatan">Keterangan: <?= htmlspecialchars($row->skbs_desc) ?></p>
        <?php endif; ?>
    </div>

    <!-- ══════════ III. PEMERIKSAAN FISIK ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">III.</span>Pemeriksaan Fisik</div>
        <table class="vital-grid">
            <tr>
                <td><strong>Tekanan Darah</strong></td>
                <td>: <?= htmlspecialchars($row->skbs_td ?: '-') ?> mmHg</td>
                <td><strong>Tinggi Badan</strong></td>
                <td>: <?= htmlspecialchars($row->skbs_tb ?: '-') ?> Cm</td>
            </tr>
            <tr>
                <td><strong>Berat Badan</strong></td>
                <td>: <?= htmlspecialchars($row->skbs_bb ?: '-') ?> Kg</td>
                <td><strong>Buta Warna</strong></td>
                <td>: <?= htmlspecialchars($row->skbs_bw ?: '-') ?></td>
            </tr>
        </table>
        <?php if (!empty($row->skbs_note)): ?>
            <p class="catatan"><strong>Catatan:</strong> <?= htmlspecialchars($row->skbs_note) ?></p>
        <?php endif; ?>
    </div>

    <!-- ══════════ TANDA TANGAN ══════════ -->
    <div class="ttd-wrap">
        <div class="ttd-box">
            <p class="ttd-place">Morowali, <?= !empty($row->skbs_doc_date) ? date('d/m/Y', strtotime($row->skbs_doc_date)) : date('d/m/Y') ?></p>
            <p class="ttd-role">Dokter Pemeriksa</p>
            <div class="ttd-qr"><?= isset($qrcode) ? '<img src="'.$qrcode.'" alt="QR Code">' : '' ?></div>
            <p class="ttd-name"><?= htmlspecialchars($row->skbs_doct_name ?: '_________________') ?></p>
            <?php if (!empty($row->nip)): ?>
            <p class="ttd-nip">NIP. <?= htmlspecialchars($row->nip) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini diterbitkan secara elektronik dan valid tanpa tanda tangan basah.</p>
    </div>
</body>
</html>
