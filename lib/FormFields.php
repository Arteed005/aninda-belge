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
        <input type="file" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          accept="image/jpeg,image/png" <?= $dataAttr ?>="<?= $dataVal ?>" data-photo-input>

      <?php else: ?>
        <input type="text" id="<?= htmlspecialchars($id) ?>" name="<?= htmlspecialchars($name) ?>"
          <?= $dataAttr ?>="<?= $dataVal ?>" value="<?= htmlspecialchars($val) ?>"
          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '') ?>" <?= $reqAttr ?>>
      <?php endif; ?>
    </div>
    <?php
}
