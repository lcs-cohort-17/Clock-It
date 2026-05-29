<?php

declare(strict_types=1);

function render_qr_dropdown_button(): string
{
    return <<<HTML
    <div x-data="{ open: false }" class="dropdown">
      <button class="btn btn-primary dropdown-toggle" type="button" @click="open = !open" @keydown.escape.window="open = false" :aria-expanded="open.toString()" aria-haspopup="true">
        <span class="me-2"></span>
        Generate QR Code
      </button>
      <div class="dropdown-menu" :class="{ 'show': open }" role="menu" x-show="open" @click.outside="open = false" x-transition style="display: none;">
        <a class="dropdown-item" role="menuitem" href="?qrType=clock-in">↪ Clock In QR</a>
        <a class="dropdown-item" role="menuitem" href="?qrType=clock-out">↩ Clock Out QR</a>
      </div>
    </div>
    HTML;
}
