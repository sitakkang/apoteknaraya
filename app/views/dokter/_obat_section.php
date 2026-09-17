<?php
/**
 * Section Obat & Racikan.
 * Dipakai di: halaman pemeriksaan (menu Dokter) & modal kolom Obat (menu Anamnesa).
 * Data yang dibutuhkan: $row, $obat_terpilih, $pulv_list, $pulv_items
 */
?>
<input type="hidden" id="mrd_id_obat" value="<?= $row->id_medical_record ?>">
<input type="hidden" id="visit_id_sks" value="<?= $row->id_visit ?>">

<div class="row" style="margin: 0 -4px;">
    <div class="col-md-6" style="padding: 0 4px;">
        <input type="text" id="obat_name" class="form-control" placeholder="Ketik nama obat / terapi..." maxlength="100" autocomplete="off">
    </div>
    <div class="col-md-2" style="padding: 0 4px;">
        <input type="number" id="obat_qty" class="form-control" value="1" min="1" placeholder="Qty">
    </div>
    <div class="col-md-2" style="padding: 0 4px;">
        <input type="text" id="obat_dosis" class="form-control" placeholder="Dosis" maxlength="100">
    </div>
    <div class="col-md-2" style="padding: 0 4px;">
        <button class="ds-btn-action ds-btn-green" id="btn_add_obat" style="padding:7px 14px;width:100%;">
            <i class="fa fa-plus"></i> Tambah
        </button>
    </div>
</div>

<hr style="border-color:var(--ds-border);margin:14px 0;">

<div class="ds-wide-table-wrap" style="margin:0;" id="obat-table-wrap">
    <?php $this->load->view('dokter/_obat_table', array('obat_terpilih' => $obat_terpilih)); ?>
</div>

<hr style="border-color:var(--ds-border);margin:14px 0;">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
    <h6 style="font-weight:700;margin:0;">
        <i class="fa fa-mortar-pestle"></i> Racikan
    </h6>
    <button class="ds-btn-action ds-btn-green" id="btn-add-racikan" style="padding:6px 14px;font-size:12px;">
        <i class="fa fa-plus"></i> Tambah Racikan
    </button>
</div>
<div id="pulv-list-wrap">
    <?php $this->load->view('dokter/racikan/v_list', array(
        'pulv_list' => $pulv_list,
        'pulv_items' => $pulv_items,
    )); ?>
</div>
