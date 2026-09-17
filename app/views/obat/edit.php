<input type="hidden" id="edit_id" name="id" value="<?= $row->id_obat ?>">

<div class="ds-form-group">
    <label>Nama Obat <span class="text-danger">*</span></label>
    <input type="text" id="edit_obat_name" name="obat_name" class="form-control"
           value="<?= htmlspecialchars($row->obat_name) ?>" maxlength="100" autocomplete="off">
</div>
