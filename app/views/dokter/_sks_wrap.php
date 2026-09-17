<?php
/**
 * Wrapper section SKS: hidden input + container.
 * Dipakai halaman pemeriksaan (menu Dokter) & modal kolom SKS (menu Anamnesa).
 * Data: $row, $sks, $sks_list, $dokter_list, $sks_diagnosa_default, $sks_terapi_default
 */
?>
<?php $sks_list = isset($sks_list) ? $sks_list : ($sks ? array($sks) : array()); ?>
<input type="hidden" id="visit_id_sks" value="<?= $row->id_visit ?>">
<input type="hidden" id="sks_patient_name" value="<?= htmlspecialchars($row->patient_name) ?>">
<input type="hidden" id="sks_company_name" value="<?= htmlspecialchars($row->trans_patient_company) ?>">

<div id="sks-section-wrap" data-visit-id="<?= $row->id_visit ?>">
    <?php $this->load->view('dokter/_sks_section', array(
        'row' => $row,
        'sks' => $sks,
        'sks_list' => $sks_list,
        'dokter_list' => $dokter_list,
        'sks_diagnosa_default' => $sks_diagnosa_default,
        'sks_terapi_default' => $sks_terapi_default,
    )); ?>
</div>
