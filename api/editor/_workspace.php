<?php
// File: api/editor/_workspace.php
// Purpose: Shared helper — returns the resolved workspace root for the current session.
//          Loaded via require_once, never called directly over HTTP.

if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Forbidden');
}

/**
 * Returns the absolute workspace root path.
 * Priority:
 *   1. $_SESSION['editor_workspace']  — a local folder the user opened
 *   2. Default uploads/code/u{id}_t{tenant_id}/ inside the app
 */
function get_workspace_root(): string {
    $custom = $_SESSION['editor_workspace'] ?? '';
    if ($custom !== '') {
        $real = realpath($custom);
        if ($real !== false && is_dir($real)) {
            return $real;
        }
        // Stored path no longer valid — clear it
        unset($_SESSION['editor_workspace']);
    }

    $user_id   = (int)$_SESSION['id'];
    $tenant_id = (int)($_SESSION['tenant_id'] ?? 0);
    $path      = BASE_PATH . '/uploads/code/u' . $user_id . '_t' . $tenant_id;

    if (!is_dir($path)) {
        mkdir($path, 0755, true);
    }

    return realpath($path);
}

/**
 * Resolves a user-supplied relative path to a safe absolute path within $workspace.
 * Returns the absolute path on success, or false if the path escapes the workspace.
 * Works for both existing and not-yet-created paths (validates parent directory).
 */
function safe_path(string $workspace, string $rel_path, bool $must_exist = false): string|false {
    // Strip traversal sequences and normalise
    $rel  = ltrim(str_replace(['\\', '../', './'], ['/', '', ''], $rel_path), '/');
    $full = $workspace . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);

    if ($must_exist) {
        $real = realpath($full);
        if ($real === false) return false;
        if (strpos($real, $workspace) !== 0) return false;
        return $real;
    }

    // For writes: resolve parent and check it's inside workspace
    $parent = dirname($full);
    if (!is_dir($parent)) {
        mkdir($parent, 0755, true);
    }
    $parent_real = realpath($parent);
    if ($parent_real === false || strpos($parent_real, $workspace) !== 0) return false;

    return $parent_real . DIRECTORY_SEPARATOR . basename($full);
}
