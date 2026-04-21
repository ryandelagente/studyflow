<?php
// File: api/editor/read-workspace.php
// Purpose: Reads all text files in the workspace and returns content for AI context.
// Called before every agent request so the AI has a full picture of the codebase.

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/_workspace.php');

$workspace = get_workspace_root();

// ── Limits ────────────────────────────────────────────────────────────────────
const MAX_FILE_BYTES  = 50_000;   // 50 KB per individual file
const MAX_TOTAL_BYTES = 200_000;  // 200 KB total context
const MAX_FILES       = 150;

// Directories to skip entirely
const SKIP_DIRS = [
    'node_modules', '.git', 'vendor', '__pycache__', '.idea', '.vscode',
    'dist', 'build', '.svn', '.hg', 'coverage', '.next', '.nuxt',
    'storage', 'cache', 'logs', '.DS_Store',
];

// Only include files with these extensions
const TEXT_EXTS = [
    'php', 'js', 'jsx', 'ts', 'tsx', 'mjs', 'cjs',
    'html', 'htm', 'css', 'scss', 'sass', 'less',
    'json', 'jsonc', 'xml', 'yaml', 'yml', 'toml',
    'md', 'txt', 'sql', 'sh', 'bash', 'zsh',
    'py', 'rb', 'go', 'rs', 'java', 'c', 'cpp', 'h', 'cs',
    'env', 'ini', 'cfg', 'conf', 'htaccess', 'gitignore',
    'vue', 'svelte', 'astro',
];

// ── Recursive scanner ─────────────────────────────────────────────────────────
$files       = [];
$total_bytes = 0;
$file_count  = 0;
$skipped     = 0;
$truncated   = false;

function scan_workspace(
    string $dir,
    string $rel_base,
    string $workspace_real,
    array  &$files,
    int    &$total_bytes,
    int    &$file_count,
    int    &$skipped,
    bool   &$truncated
): void {
    if ($truncated) return;

    $entries = @scandir($dir);
    if (!$entries) return;

    foreach ($entries as $entry) {
        if ($entry === '.' || $entry === '..') continue;

        // Skip hidden files/dirs except a few important ones
        if (str_starts_with($entry, '.') &&
            !in_array($entry, ['.htaccess', '.env', '.gitignore', '.editorconfig'])) {
            continue;
        }

        // Skip noisy directories
        if (in_array($entry, SKIP_DIRS)) {
            continue;
        }

        $full_path = $dir . DIRECTORY_SEPARATOR . $entry;
        $rel_path  = $rel_base ? $rel_base . '/' . $entry : $entry;

        if (is_dir($full_path)) {
            // Verify symlinks don't escape workspace
            $real = realpath($full_path);
            if ($real === false || strpos($real, $workspace_real) !== 0) continue;

            scan_workspace($full_path, $rel_path, $workspace_real, $files,
                           $total_bytes, $file_count, $skipped, $truncated);
        } else {
            if ($file_count >= MAX_FILES) {
                $truncated = true;
                return;
            }

            // Extension filter
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if ($ext !== '' && !in_array($ext, TEXT_EXTS)) {
                $skipped++;
                continue;
            }

            $size = @filesize($full_path);
            if ($size === false) continue;

            // Per-file size cap
            if ($size > MAX_FILE_BYTES) {
                $files[] = [
                    'path'    => $rel_path,
                    'content' => null,
                    'note'    => 'File too large (' . round($size / 1024, 1) . ' KB) — skipped',
                ];
                $skipped++;
                continue;
            }

            // Total budget cap
            if ($total_bytes + $size > MAX_TOTAL_BYTES) {
                $truncated = true;
                $skipped++;
                return;
            }

            $raw = @file_get_contents($full_path);
            if ($raw === false) { $skipped++; continue; }

            // Skip binary files — check MIME type first, fall back to byte scan
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime  = finfo_buffer($finfo, substr($raw, 0, 8192));
                finfo_close($finfo);
                if ($mime && !str_starts_with($mime, 'text/') &&
                    !in_array($mime, ['application/json', 'application/javascript',
                                      'application/xml', 'application/x-sh'])) {
                    $skipped++;
                    continue;
                }
            }

            // Force UTF-8 — strip or convert problematic bytes
            $content = mb_convert_encoding($raw, 'UTF-8', 'UTF-8');
            if ($content === false || $content === '') {
                $content = iconv('UTF-8', 'UTF-8//IGNORE', $raw);
            }

            $files[]     = ['path' => $rel_path, 'content' => $content];
            $total_bytes += $size;
            $file_count++;
        }
    }
}

scan_workspace($workspace, '', realpath($workspace), $files,
               $total_bytes, $file_count, $skipped, $truncated);

// Sort: files first alphabetically, then by depth
usort($files, function ($a, $b) {
    $da = substr_count($a['path'], '/');
    $db = substr_count($b['path'], '/');
    if ($da !== $db) return $da - $db;
    return strcmp($a['path'], $b['path']);
});

echo json_encode([
    'workspace'   => str_replace('\\', '/', $workspace),
    'label'       => basename($workspace),
    'files'       => $files,
    'file_count'  => $file_count,
    'total_bytes' => $total_bytes,
    'skipped'     => $skipped,
    'truncated'   => $truncated,
]);
