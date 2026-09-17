<?php
/**
 * Wrapper section SKBS: hidden input + container.
 * Dipakai halaman pemeriksaan (menu Dokter) & modal kolom SKBS (menu Anamnesa).
 * Data: $row, $skbs, $dokter_list
 */
?>
<input type="hidden" id="visit_id_skbs" value="<?= $row->id_visit ?>">

<div id="skbs-section-wrap" data-visit-id="<?= $row->id_visit ?>">
    <?php $this->load->view('dokter/_skbs_section', array(
        'row' => $row,
        'skbs' => $skbs,
        'dokter_list' => $dokter_list,
    )); ?>
</div>
