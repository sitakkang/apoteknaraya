<!DOCTYPE html>
<html>
<head>
    <title>Cetak SKS</title>
    <style>
        /* ═══════════════════════════════════════════════════
           SKS — Desain RESMI (monokrom)
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
        .section-title .angka { display: inline-block; min-width: 30px; }

        /* ── TABEL DATA (tanpa bingkai / garis) ───────── */
        table.data { width: 100%; border-collapse: collapse; font-size: 14px; }
        table.data td {
            border: 0;
            padding: 3px 8px;
            vertical-align: top;
        }
        table.data td.label { width: 170px; font-weight: 600; }
        table.data td.pemisah { width: 14px; text-align: center; padding-left: 0; padding-right: 0; }

        /* ── NARASI ───────────────────────────────────── */
        .narasi { font-size: 14px; line-height: 1.7; text-align: justify; margin: 0 0 8px; }
        .narasi:last-child { margin-bottom: 0; }

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

        /* ── CETAK: kunci ke 1 lembar A4 ──────────────── */
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
    <button class="btn-print no-print" onclick="window.close()" style="background: #666; margin-left: 6px;">Tutup</button>

    <!-- ══════════ KOP SURAT ══════════ -->
    <?php
    // Nama & NIP dokter diambil dari conf_users lewat sks.doctby
    // (lihat M_sks::get_sks_by_id → join sks.doctby = conf_users.id_user)
    // Kalau join gagal, jangan tampilkan id mentah (doctby berisi id_user)
    $doct_kop = !empty($row->fullname) ? $row->fullname
              : (!empty($row->doctby) && !ctype_digit((string) $row->doctby) ? $row->doctby : '');
    ?>
    <div class="kop">
        <div class="kop-instansi">PRAKTEK DOKTER UMUM</div>
        <div class="kop-dokter"><?= htmlspecialchars($doct_kop ?: '_________________') ?></div>
        <?php if (!empty($row->nip)): ?>
        <div class="kop-nip">NIP. <?= htmlspecialchars((string) $row->nip) ?></div>
        <?php endif; ?>
        <div class="kop-alamat">
            Jl. Poros Transmigrasi, Ds. Keurea, Kec. Bahodopi, Morowali &nbsp;|&nbsp; Telp. 082190597990
        </div>
        <hr class="tebal">
        <hr class="tipis">
    </div>

    <!-- ══════════ JUDUL DOKUMEN ══════════ -->
    <div class="judul">
        <h1>SURAT KETERANGAN SAKIT (SKS)</h1>
        <p class="nomor">No. Dokumen: <?= htmlspecialchars($row->docnumb) ?></p>
    </div>

    <!-- ══════════ I. IDENTITAS PASIEN ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">I.</span>Identitas Pasien</div>
        <table class="data">
            <tr><td class="label">Nama Pasien</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->patient_name) ?></td></tr>
            <tr><td class="label">NIK</td><td class="pemisah">:</td><td><?= htmlspecialchars((string) $row->patient_nik) ?: '-' ?></td></tr>
            <tr><td class="label">Umur</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->age) ?: '-' ?> Tahun</td></tr>
            <tr><td class="label">Jenis Kelamin</td><td class="pemisah">:</td><td><?= $row->gender === 'L' ? 'Laki-laki' : ($row->gender === 'P' ? 'Perempuan' : '-') ?></td></tr>
            <tr><td class="label">Pekerjaan</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->patient_job) ?: '-' ?></td></tr>
        </table>
    </div>

    <!-- ══════════ II. ALAMAT ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">II.</span>Alamat</div>
        <table class="data">
            <tr>
                <td class="label">Alamat</td><td class="pemisah">:</td>
                <td><?= !empty($row->alamat) ? nl2br(htmlspecialchars($row->alamat)) : '-' ?></td>
            </tr>
        </table>
    </div>

    <!-- ══════════ III. DIAGNOSA ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">III.</span>Diagnosa</div>
        <table class="data">
            <tr><td class="label">Diagnosa</td><td class="pemisah">:</td><td><?= nl2br(htmlspecialchars($row->diagnosa)) ?></td></tr>
        </table>
    </div>

    <!-- ══════════ IV. TERAPI ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">IV.</span>Terapi</div>
        <table class="data">
            <tr><td class="label">Terapi</td><td class="pemisah">:</td><td><?= nl2br(htmlspecialchars($row->terapi)) ?></td></tr>
        </table>
    </div>

    <!-- ══════════ V. KETERANGAN DOKTER ══════════ -->
    <div class="section">
        <div class="section-title"><span class="angka">V.</span>Keterangan Dokter</div>
        <?php
        function tgl_indo($date) {
            $bulan_indo = array(
                'January'   => 'Januari',
                'February'  => 'Februari',
                'March'     => 'Maret',
                'April'     => 'April',
                'May'       => 'Mei',
                'June'      => 'Juni',
                'July'      => 'Juli',
                'August'    => 'Agustus',
                'September' => 'September',
                'October'   => 'Oktober',
                'November'  => 'November',
                'December'  => 'Desember',
            );
            $t = strtotime($date);
            $d = date('d', $t);
            $m = $bulan_indo[date('F', $t)];
            $y = date('Y', $t);
            return $d . ' ' . $m . ' ' . $y;
        }

        $datefrom = !empty($row->datefrom) ? tgl_indo($row->datefrom) : '';
        $dateto   = !empty($row->dateto)   ? tgl_indo($row->dateto)   : '';

        // Hitung lama istirahat (hari)
        $lama = 0;
        if (!empty($row->datefrom) && !empty($row->dateto)) {
            $t1 = strtotime($row->datefrom);
            $t2 = strtotime($row->dateto);
            $lama = round(($t2 - $t1) / (60 * 60 * 24)) + 1; // +1 agar termasuk hari pertama
            if ($lama < 0) $lama = 0;
        }
        ?>
        <p class="narasi">
            Berdasarkan hasil pemeriksaan medis bahwa benar yang bersangkutan dalam keadaan sakit dan membutuhkan waktu istirahat selama <b><?= $lama ?> (hari)</b>,
            terhitung tanggal <b><?= $datefrom ?> s/d <?= $dateto ?></b>.
        </p>
        <p class="narasi">
            Demikian surat keterangan ini dibuat untuk dipergunakan sebagaimana perlunya.
        </p>
    </div>

    <!-- Tanda Tangan -->
    <?php
    $tgl_doc = !empty($row->docdate) ? tgl_indo($row->docdate) : date('d/m/Y');
    $fullname = !empty($row->doctby) ? htmlspecialchars($row->doctby) : ( !empty($row->insertby) ? htmlspecialchars($row->insertby) : '_________________' );
    ?>
    <!-- ══════════ TANDA TANGAN ══════════ -->
    <div class="ttd-wrap">
        <div class="ttd-box">
            <p class="ttd-place">Bahodopi, <?= $tgl_doc ?></p>
            <p class="ttd-role">Dokter Pemeriksa</p>
            <div class="ttd-qr"><img src="<?= $qrcode ?>" alt="QR Code"></div>
            <p class="ttd-name">( <?= htmlspecialchars($row->fullname) ?: $fullname ?> )</p>
            <?php if (!empty($row->nip)): ?>
            <p class="ttd-nip">NIP. <?= htmlspecialchars((string) $row->nip) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>
            Dokumen ini diterbitkan secara elektronik. Untuk memverifikasi keaslian surat ini, silakan pindai <strong>QR Code</strong> di atas menggunakan perangkat mobile Anda.
        </p>
    </div>

    <script>
        // Auto-trigger print dialog on load (uncomment if desired)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>