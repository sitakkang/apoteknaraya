<!DOCTYPE html>
<html>
<head>
    <title>Cetak SKMB</title>
    <style>
        /* ═══════════════════════════════════════════════════
           SKMB — Desain RESMI (monokrom) — seragam dgn SKS
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
    <button class="btn-print no-print" onclick="window.close()" style="background: #666; margin-left: 6px;">Tutup</button>

    <!-- ══════════ KOP SURAT ══════════ -->
    <div class="kop">
        <div class="kop-instansi">PRAKTEK DOKTER UMUM</div>
        <div class="kop-dokter">dr. Steve Kojongian</div>
        <div class="kop-alamat">
            Jl. Trans Sulawesi, Ds. Bahodopi, Kec. Bahodopi, Morowali &nbsp;|&nbsp; Telp. 081342161194
        </div>
        <hr class="tebal">
        <hr class="tipis">
    </div>

    <!-- ══════════ JUDUL DOKUMEN ══════════ -->
    <div class="judul">
        <h1>SURAT KETERANGAN MENGANTAR BEROBAT</h1>
        <p class="nomor">No. Dokumen: <?= htmlspecialchars($row->docnumb) ?></p>
    </div>

    <div class="section">
        <p class="narasi">
            Yang bertanda tangan di bawah ini menerangkan bahwa:
        </p>
        
        <table class="data">
            <tr><td class="label">Nama Pengantar</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->pengantar) ?: '-' ?></td></tr>
            <tr><td class="label">NIK Pengantar</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->nik_pengantar) ?: '-' ?></td></tr>
            <tr><td class="label">Perusahaan</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->company_pengantar) ?: '-' ?></td></tr>
        </table>
    </div>

    <div class="section">
        <?php
        $hubungan = htmlspecialchars($row->hubungan);
        $hubungan_label = '';
        switch ($hubungan) {
            case 'SUAMI':    $hubungan_label = 'Suami';    break;
            case 'ISTRI':    $hubungan_label = 'Istri';    break;
            case 'ANAK':     $hubungan_label = 'Anak';     break;
            case 'ORANG TUA':$hubungan_label = 'Orang Tua';break;
            case 'SAUDARA':  $hubungan_label = 'Saudara';  break;
            default:         $hubungan_label = $hubungan;   break;
        }
        ?>
        <p class="narasi">
            Pada tanggal <strong><?= !empty($row->tgl_datang) ? date('d-m-Y', strtotime($row->tgl_datang)) : '-' ?></strong> benar-benar telah mengantar anggota keluarga yang sakit, yaitu :
        </p>
        
        <table class="data">
            <tr><td class="label">Nama Diantar</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->patient_name) ?></td></tr>
            <tr><td class="label">NIK Diantar</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->nik) ?: '-' ?></td></tr>
            <tr><td class="label">Perusahaan</td><td class="pemisah">:</td><td><?= htmlspecialchars($row->company_name) ?: '-' ?></td></tr>
            <tr><td class="label">Hubungan</td><td class="pemisah">:</td><td><?= $hubungan_label ?: '-' ?></td></tr>
        </table>



    </div>

    <div class="section">
        <p class="narasi">
            Demikian surat keterangan ini dibuat untuk digunakan semestinya.
        </p>
    </div>

    <!-- Tanda Tangan -->
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

    $tgl_doc = !empty($row->docdate) ? tgl_indo($row->docdate) : tgl_indo(date('Y-m-d'));
    $fullname = !empty($row->doct_by_name) ? htmlspecialchars($row->doct_by_name) : ( !empty($row->insertby) ? htmlspecialchars($row->insertby) : '_________________' );
    ?>
    <div class="ttd-wrap">
        <div class="ttd-box">
            <p class="ttd-place">Fatufia, <?= $tgl_doc ?></p>
            <p class="ttd-role">Dokter Pemeriksa</p>
            <div class="ttd-qr"><img src="<?= $qrcode ?>" alt="QR Code"></div>
            <p class="ttd-name">( <?= htmlspecialchars($row->doct_name ?? $row->fullname ?? '') ?: $fullname ?> )</p>
            <?php if (!empty($row->nip)): ?>
            <p class="ttd-nip">NIP. <?= htmlspecialchars($row->nip) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>Dokumen ini diterbitkan secara elektronik.</p>
    </div>
</body>
</html>
