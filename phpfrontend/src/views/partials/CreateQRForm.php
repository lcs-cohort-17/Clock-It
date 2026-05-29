<?php

declare(strict_types=1);

function render_create_qr_form(bool $isOpen): string
{
    if (!$isOpen) {
        return '';
    }

    return <<<HTML
    <div class="panel create-form">
      <div class="panel-header">
        <h3>Create new QR code</h3>
        <a class="icon-link" href="./index.php" aria-label="Close create form">X</a>
      </div>
      <form method="post" class="form-grid">
        <input type="hidden" name="action" value="create">
        <label>
          <span>Label *</span>
          <input name="label" required placeholder="e.g., Main Entrance">
        </label>
        <label>
          <span>Type *</span>
          <select name="type">
            <option value="clock-in">Clock In</option>
            <option value="clock-out">Clock Out</option>
          </select>
        </label>
        <label>
          <span>Location (optional)</span>
          <input name="location" placeholder="e.g., HQ">
        </label>
        <label>
          <span>Expires after (hours)</span>
          <input name="expiresAfter" type="number" min="1" max="168" value="24">
        </label>
        <div class="form-actions">
          <a class="btn btn-secondary" href="./index.php">Cancel</a>
          <button class="btn btn-primary" type="submit">Create QR</button>
        </div>
      </form>
    </div>
    HTML;
}
