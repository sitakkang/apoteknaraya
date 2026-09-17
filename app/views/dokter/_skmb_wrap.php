<?php
/**
 * Wrapper section SKMB: hidden input + container.
 * Dipakai halaman pemeriksaan (menu Dokter) & modal kolom SKMB (menu Anamnesa).
 * Data: $row, $skmb, $dokter_list
 */
?>
<input type="hidden" id="visit_id_skmb" value="<?= $row->id_visit ?>">

<div id="skmb-section-wrap" data-visit-id="<?= $row->id_visit ?>">
    <?php $this->load->view('dokter/_skmb_section', array(
        'row' => $row,
        'skmb' => $skmb,
        'dokter_list' => $dokter_list,
    )); ?>
</div>
