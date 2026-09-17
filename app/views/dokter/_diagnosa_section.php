<?php
/**
 * Section Diagnosa Pasien.
 * Dipakai di: halaman pemeriksaan (menu Dokter) & modal kolom Diagnosa (menu Anamnesa).
 * Data yang dibutuhkan: $row, $diagnosa_list, $diagnosa_terpilih
 */
?>
<input type="hidden" id="mrd_id_diagnosa" value="<?= $row->id_medical_record ?>">
<input type="hidden" id="visit_id_sks" value="<?= $row->id_visit ?>">

<div class="row" style="margin: 0 -4px;">
    <div class="col-md-6" style="padding: 0 4px;">
        <select id="select_diagnosa" class="form-control autocomplete" data-placeholder="Cari & pilih diagnosa...">
            <option value=""></option>
            <?php foreach ($diagnosa_list as $d): ?>
                <option value="<?= $d->id_diagnosa ?>" data-cat="<?= htmlspecialchars($d->dgn_cat) ?>">
                    <?= htmlspecialchars($d->dgn_cat ? '['.$d->dgn_cat.'] ' : '') . htmlspecialchars($d->dgn_name) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4" style="padding: 0 4px;">
        <input type="text" id="dgn_note" class="form-control" placeholder="Catatan dokter..." maxlength="225">
    </div>
    <div class="col-md-2" style="padding: 0 4px;">
        <button class="ds-btn-action ds-btn-green" id="btn_add_diagnosa" style="padding:7px 14px;width:100%;">
            <i class="fa fa-plus"></i> Tambah
        </button>
    </div>
</div>

<hr style="border-color:var(--ds-border);margin:14px 0;">

<div class="ds-wide-table-wrap" style="margin:0;" id="diagnosa-table-wrap">
    <?php $this->load->view('dokter/_diagnosa_table', array('diagnosa_terpilih' => $diagnosa_terpilih)); ?>
</div>
