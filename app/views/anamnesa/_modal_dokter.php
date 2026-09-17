<?php
/**
 * Isi modal "Pilih Dokter Pemeriksa" — dipakai kolom Dokter pada daftar Anamnesa.
 * Data yang dibutuhkan: $row (visit + medical record), $dokter_list
 */
?>
<input type="hidden" id="visit_id_sks" value="<?= $row->id_visit ?>">
<input type="hidden" id="mrd_doct_by" value="<?= intval($row->mrd_doct_by) ?>">

<div class="list-group" style="border-radius:0;margin:0;">
    <?php foreach ($dokter_list as $d): ?>
        <?php $aktif = (intval($d->id_user) === intval($row->mrd_doct_by)); ?>
        <a href="javascript:;" class="list-group-item list-group-item-action pilih-dokter-modal"
           data-id="<?= $d->id_user ?>"
           data-name="<?= htmlspecialchars($d->fullname) ?>"
           style="border-left:0;border-right:0;padding:12px 16px;display:flex;align-items:center;gap:10px;<?= $aktif ? 'background:#f0f7ff;' : '' ?>">
            <div style="width:34px;height:34px;border-radius:50%;background:var(--ds-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;">
                <?= strtoupper(substr($d->fullname, 0, 1)) ?>
            </div>
            <div>
                <div style="font-weight:600;color:#333;font-size:13px;"><?= htmlspecialchars($d->fullname) ?></div>
                <div style="font-size:11px;color:#999;"><?= $aktif ? 'Dokter pemeriksa saat ini' : 'Dokter Pemeriksa' ?></div>
            </div>
            <div class="ml-auto">
                <i class="fa <?= $aktif ? 'fa-check-circle' : 'fa-chevron-right' ?>" style="color:<?= $aktif ? '#2e7d32' : '#ccc' ?>;"></i>
            </div>
        </a>
    <?php endforeach; ?>
    <?php if (empty($dokter_list)): ?>
        <div class="text-muted text-center" style="padding:20px;">Tidak ada dokter tersedia</div>
    <?php endif; ?>
</div>
