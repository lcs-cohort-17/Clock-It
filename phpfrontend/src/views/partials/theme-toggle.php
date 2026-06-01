<?php
/**
 * Theme Toggle Component
 * Dark/Light mode toggle switch
 */
?>
<div class="theme-toggle-container" x-data="{ isDark: window.themeManager?.isDark() ?? false }" x-init="isDark = window.themeManager.isDark()">
  <button 
    class="theme-toggle-btn" 
    @click="isDark = window.themeManager.toggleTheme()"
    :style="{ color: isDark ? '#f8fafc' : '#0f172a' }"
    aria-label="Toggle theme"
    type="button">
  </button>
  
  <div class="theme-toggle-icon" :style="{ color: isDark ? '#f8fafc' : '#0f172a' }">
    <i class="bi bi-moon-stars-fill" x-show="isDark" aria-hidden="true"></i>
    <i class="bi bi-sun-fill" x-show="!isDark" aria-hidden="true"></i>
  </div>
</div>
