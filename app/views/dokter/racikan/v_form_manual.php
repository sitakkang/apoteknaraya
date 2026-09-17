<?php
/**
 * Form tambah Racikan — input manual (tanpa memilih obat dari tabel).
 * Dipakai di: modal Obat & Racikan (menu Anamnesa) & halaman pemeriksaan (menu Dokter).
 * Isi racikan diketik satu baris per obat, contoh:
 *   Gg 3 tab
 *   Ctm 3 tab
 *   Dexa 3 tab
 *   S. M.f pulv 3dd1 no.x
 *
 * Data yang dibutuhkan: $medical_record_id
 * Isi racikan disimpan pada kolom pulv_notes (trans_obat_racikan).
 */
?>
<input type="hidden" id="pulv_medical_record_id" value="<?= intval($medical_record_id) ?>">

<div class="ds-form-group">
    <label>Nama Racikan</label>
    <input type="text" id="pulv_name" class="form-control" value="PUYER"
           placeholder="Misal: PUYER, Racikan Batuk" maxlength="255">
</div>

<div class="ds-form-group">
    <label>Isi Racikan <span class="text-danger">*</span></label>
    <textarea id="pulv_komposisi" class="form-control" rows="7"
              placeholder="Gg 3 tab&#10;Ctm 3 tab&#10;Dexa 3 tab&#10;S. M.f pulv 3dd1 no.x"></textarea>
    <small class="text-muted">Satu baris untuk satu obat / aturan pakai.</small>
</div>

<div class="row" style="margin:0 -6px;">
    <div class="col-md-6" style="padding:0 6px;">
        <div class="ds-form-group">
            <label>Jumlah Bungkus</label>
            <input type="number" id="pulv_qty" class="form-control" value="1" min="1">
        </div>
    </div>
    <div class="col-md-6" style="padding:0 6px;">
        <div class="ds-form-group">
            <label>Dosis / Aturan Pakai</label>
            <input type="text" id="pulv_dosis" class="form-control"
                   placeholder="Misal: 3dd1" maxlength="50">
        </div>
    </div>
</div>
