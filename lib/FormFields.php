<?php

/**
 * Renders one field's <input>/<textarea>/<select>/<file> markup. Shared by
 * sablon.php's contract wizard and cv-olustur.php's CV wizard so the field
 * type switch isn't duplicated between them.
 *
 * $nameOverride/$dataAttr let repeatable-group cards reuse this same
 * function with a nested POST name (experience[0][company]) and a distinct
 * data attribute (data-group-field) so they're excluded from the generic
 * required-field wizard-step check, which only looks at [data-field].
 */
function renderFieldInput(array $field, string $val, ?string $nameOverride = null, string $dataAttr = 'data-field'): void
{
    $name = $nameOverride ?? $field['name'];
    $id = 'f-' . preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
    $type = $field['type'] ?? 'text';
    $required = !empty($field['required']);
    $reqAttr = $required ? 'required' : '';
    $dataVal = htmlspecialchars($field['name']);
    ?>
    <div class="field">
      <label for="<?= htmlspecialchars($id) ?>"><?= htmlspecialchars($field['label']) ?></label>

      <?php if ($type === 'textarea'): ?>
        <textarea id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>" rows="2"
          <?= $dataAttr ?>="<?= $dataVal ?>"
          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>"
          <?= $reqAttr ?>><?= htmlspecialchars($val) ?></textarea>

      <?php elseif ($type === 'select'): ?>
        <select id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" <?= $reqAttr ?>>
          <option value="">Seçiniz</option>
          <?php foreach ($field['options'] ?? [] as $opt): ?>
            <option value="<?= htmlspecialchars($opt['value']) ?>" <?= $val === $opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label']) ?></option>
          <?php endforeach; ?>
        </select>

      <?php elseif ($type === 'date'): ?>
        <input type="date" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" value="<?= htmlspecialchars($val) ?>" <?= $reqAttr ?>>

      <?php elseif ($type === 'file'): ?>
        <div class="photo-upload" data-photo-upload>
          <input type="file" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
            class="photo-upload-input" accept="image/jpeg,image/png" <?= $dataAttr ?>="<?= $dataVal ?>" data-photo-input>
          <label for="<?= htmlspecialchars($id) ?>" class="photo-upload-dropzone">
            <img class="photo-upload-thumb" data-photo-thumb hidden alt="">
            <span class="photo-upload-icon" data-photo-icon>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/>
                <circle cx="12" cy="14" r="3.5"/>
              </svg>
            </span>
            <span class="photo-upload-text">
              <strong>Fotoğraf yükle</strong>
              <small data-photo-filename>JPG veya PNG, maks. 3MB</small>
            </span>
          </label>
        </div>

      <?php else: ?>
        <input type="text" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" value="<?= htmlspecialchars($val) ?>"
          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $reqAttr ?>>
      <?php endif; ?>
    </div>
    <?php
}
