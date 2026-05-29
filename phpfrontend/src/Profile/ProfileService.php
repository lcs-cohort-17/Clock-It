<?php

class ProfileService
{
    public function updatePassword($current, $new, $confirm): array
    {
        if (trim((string) $current) === '') {
            return ['success' => false, 'message' => 'Current password is required.'];
        }

        if ((string) $new !== (string) $confirm) {
            return ['success' => false, 'message' => 'New passwords do not match.'];
        }

        if (strlen((string) $new) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters.'];
        }

        return ['success' => true, 'message' => 'Password updated successfully.'];
    }

    public function handleFlash(&$session)
    {
        $flash = $session['flash'] ?? null;
        unset($session['flash']);

        return $flash;
    }
}
