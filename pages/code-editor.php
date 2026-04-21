<?php
// File: pages/code-editor.php
// Purpose: Full IDE — Monaco editor + local folder picker + AI assistant (bottom panel)

session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: ../login.php'); exit;
}
require_once(__DIR__ . '/../config.php');

// Pass server-side info to JS
$doc_root  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
$htdocs    = $doc_root;   // on XAMPP, document root IS htdocs
require_once(BASE_PATH . '/partials/header.php');
?>

<style>
/* ═══════════════════════════════════════════════════════
   Layout skeleton
═══════════════════════════════════════════════════════ */
#editor-root       { display:flex; flex-direction:column; flex:1; overflow:hidden;
                     background:#1e1e2e; }
#editor-toolbar    { display:flex; align-items:center; gap:.4rem; padding:.45rem .7rem;
                     background:#181825; border-bottom:1px solid #313244; flex-shrink:0;
                     flex-wrap:wrap; min-height:46px; }
#editor-panels     { display:flex; flex:1; overflow:hidden; position:relative; }

/* ── File tree ─────────────────────────────── */
#file-tree-panel   { width:220px; min-width:160px; max-width:340px; background:#1e1e2e;
                     color:#cdd6f4; display:flex; flex-direction:column;
                     border-right:1px solid #313244; overflow:hidden; flex-shrink:0; }
#tree-top          { display:flex; align-items:center; justify-content:space-between;
                     padding:.4rem .6rem; border-bottom:1px solid #313244; flex-shrink:0;
                     gap:.25rem; }
#tree-workspace-name { font-size:.7rem; font-weight:700; letter-spacing:.05em;
                       color:#6c7086; text-transform:uppercase; flex:1;
                       overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
#file-tree         { flex:1; overflow-y:auto; padding:.2rem 0; }
.tree-item         { display:flex; align-items:center; gap:.35rem; padding:.26rem .6rem;
                     cursor:pointer; font-size:.79rem; color:#cdd6f4; user-select:none;
                     white-space:nowrap; overflow:hidden; position:relative; }
.tree-item:hover   { background:#313244; }
.tree-item.active  { background:#313244; color:#cba6f7; }
.tree-chevron      { width:.7rem; flex-shrink:0; font-size:.6rem; transition:transform .13s;
                     color:#6c7086; }
.tree-chevron.open { transform:rotate(90deg); }

/* ── Main editor column ─────────────────────── */
/* NOTE: no overflow:hidden here — that would clip the AI panel and block pointer events */
#editor-main       { display:flex; flex-direction:column; flex:1; min-width:0; }

/* ── Tab bar ────────────────────────────────── */
#tab-bar           { display:flex; background:#181825; border-bottom:1px solid #313244;
                     overflow-x:auto; flex-shrink:0; min-height:34px; }
#tab-bar::-webkit-scrollbar { height:3px; }
#tab-bar::-webkit-scrollbar-thumb { background:#45475a; }
.editor-tab        { display:flex; align-items:center; gap:.35rem; padding:.35rem .85rem;
                     font-size:.77rem; color:#6c7086; cursor:pointer;
                     border-right:1px solid #313244; white-space:nowrap; flex-shrink:0;
                     position:relative; }
.editor-tab:hover  { background:#1e1e2e; color:#cdd6f4; }
.editor-tab.active { background:#1e1e2e; color:#cdd6f4;
                     border-top:2px solid #cba6f7; padding-top:.2rem; }
.tab-close         { opacity:0; font-size:.68rem; padding:.1rem .22rem;
                     border-radius:3px; line-height:1; }
.editor-tab:hover .tab-close,
.editor-tab.active .tab-close { opacity:.7; }
.editor-tab:hover .tab-close:hover { opacity:1; background:#45475a; }
.tab-dirty::after  { content:'●'; color:#f38ba8; margin-left:.2rem;
                     font-size:.55rem; vertical-align:super; }

/* ── Monaco wrapper ─────────────────────────── */
/* min-height:0 is critical — without it flex won't shrink this below its content size */
#editor-container  { flex:1; overflow:hidden; min-height:0; position:relative; }

/* ── Resize handle (vertical divider, drag left/right) ── */
#ai-resize-handle  { width:5px; background:#313244; cursor:ew-resize; flex-shrink:0;
                     transition:background .15s; align-self:stretch; }
#ai-resize-handle:hover,
#ai-resize-handle.dragging { background:#cba6f7; }

/* ── AI right sidebar ───────────────────────── */
#ai-bottom         { width:320px; min-width:220px; display:flex; flex-direction:column;
                     border-left:1px solid #313244; background:#181825; flex-shrink:0;
                     overflow:hidden; transition:width .18s; }
#ai-bottom.closed  { width:0 !important; min-width:0; }
#ai-bottom-header  { display:flex; align-items:center; gap:.5rem; padding:.5rem .75rem;
                     border-bottom:1px solid #313244; flex-shrink:0; }
#ai-bottom-header span { font-size:.78rem; font-weight:700; color:#cba6f7; flex:1;
                          white-space:nowrap; }
#ai-messages       { flex:1; overflow-y:auto; padding:.5rem .75rem;
                     display:flex; flex-direction:column; gap:.45rem; }
#ai-messages::-webkit-scrollbar { width:4px; }
#ai-messages::-webkit-scrollbar-thumb { background:#45475a; }
.ai-msg            { padding:.45rem .6rem; border-radius:.4rem; font-size:.78rem;
                     line-height:1.55; word-break:break-word; }
.ai-msg.user       { background:#313244; color:#cdd6f4; align-self:flex-end;
                     max-width:92%; white-space:pre-wrap; }
.ai-msg.assistant  { background:#1e1e2e; border:1px solid #313244; color:#cdd6f4;
                     align-self:flex-start; max-width:100%; }
.ai-msg.assistant pre  { background:#11111b; padding:.45rem .6rem; border-radius:.3rem;
                          overflow-x:auto; margin:.3rem 0; font-size:.72rem; }
.ai-msg code       { background:#11111b; padding:.05rem .28rem; border-radius:.2rem;
                     font-size:.72rem; }
.ai-msg .insert-btn { display:block; margin:.4rem 0 .1rem; padding:.2rem .55rem;
                       font-size:.68rem; border-radius:.25rem; border:1px solid #45475a;
                       background:#313244; color:#a6e3a1; cursor:pointer; width:100%; text-align:center; }
.ai-msg .insert-btn:hover { background:#45475a; }
#ai-input-row      { display:flex; flex-direction:column; gap:.4rem; padding:.5rem .75rem;
                     border-top:1px solid #313244; flex-shrink:0;
                     background:#181825; position:sticky; bottom:0; }
#ai-input          { width:100%; background:#11111b; border:1px solid #45475a; border-radius:.4rem;
                     color:#cdd6f4; padding:.45rem .6rem; font-size:.78rem; resize:none;
                     outline:none; font-family:inherit; line-height:1.4; box-sizing:border-box;
                     position:relative; z-index:20; cursor:text; }
#ai-input:focus    { border-color:#cba6f7; }
#ai-send-row       { display:flex; justify-content:flex-end; }
#ai-send           { padding:.35rem .85rem; border-radius:.4rem; font-size:.75rem; font-weight:700;
                     border:none; background:#cba6f7; color:#1e1e2e; cursor:pointer; }
#ai-send:hover     { background:#b4befe; }
#ai-send:disabled  { opacity:.5; cursor:not-allowed; }

/* ── Agent mode ─────────────────────────────── */
#ai-mode-toggle    { display:flex; border:1px solid #45475a; border-radius:.3rem;
                     overflow:hidden; flex-shrink:0; }
.ai-mode-btn       { padding:.18rem .55rem; font-size:.68rem; font-weight:700;
                     border:none; background:transparent; color:#6c7086; cursor:pointer; }
.ai-mode-btn:hover { color:#cdd6f4; }
.ai-mode-btn.active{ background:#cba6f7; color:#1e1e2e; }
#agent-ctx-bar     { display:none; align-items:center; gap:.5rem; padding:.35rem .75rem;
                     background:#11111b; border-bottom:1px solid #313244; flex-shrink:0; }
#agent-ctx-label   { font-size:.71rem; color:#6c7086; flex:1; }
/* Proposed changes block */
.changes-wrap      { border:1px solid #45475a; border-radius:.4rem; overflow:hidden;
                     margin:.3rem 0; background:#11111b; flex-shrink:0; }
.changes-header    { display:flex; align-items:center; gap:.5rem; padding:.4rem .65rem;
                     background:#181825; border-bottom:1px solid #313244; }
.changes-title     { font-size:.72rem; font-weight:700; color:#cba6f7; flex:1; }
.btn-apply-all     { padding:.2rem .65rem; font-size:.68rem; font-weight:700;
                     border:none; border-radius:.3rem; background:#a6e3a1;
                     color:#1e1e2e; cursor:pointer; white-space:nowrap; }
.btn-apply-all:hover  { background:#94e2d5; }
.btn-apply-all:disabled { opacity:.5; cursor:not-allowed; }
.change-row        { display:flex; align-items:center; gap:.4rem; padding:.32rem .65rem;
                     border-top:1px solid #1e1e2e; font-size:.73rem; }
.change-action-icon{ font-size:.8rem; flex-shrink:0; width:1rem; text-align:center; }
.change-path       { flex:1; color:#cdd6f4; overflow:hidden; text-overflow:ellipsis;
                     white-space:nowrap; font-family:monospace; font-size:.71rem; }
.change-lines      { color:#6c7086; font-size:.67rem; flex-shrink:0; }
.btn-preview-chg, .btn-apply-chg {
                     padding:.15rem .38rem; font-size:.65rem; font-weight:600;
                     border:1px solid #45475a; border-radius:.25rem;
                     background:transparent; color:#cdd6f4; cursor:pointer; flex-shrink:0; }
.btn-preview-chg:hover { background:#313244; }
.btn-apply-chg     { background:#313244; }
.btn-apply-chg:hover { background:#45475a; }
.btn-apply-chg.done{ background:#1e3a2e; color:#a6e3a1; border-color:#a6e3a1; cursor:default; }
.btn-apply-chg.err { background:#3a1e1e; color:#f38ba8; border-color:#f38ba8; cursor:default; }
/* Preview modal */
#preview-modal     { display:none; position:fixed; inset:0; background:rgba(0,0,0,.7);
                     z-index:400; align-items:center; justify-content:center; }
#preview-modal .modal-box { width:min(900px,96vw); max-height:88vh;
                             display:flex; flex-direction:column; }
#preview-header    { display:flex; align-items:center; gap:.5rem; padding:.6rem 1rem;
                     border-bottom:1px solid #313244; flex-shrink:0; }
#preview-filepath  { flex:1; font-size:.78rem; font-weight:700; color:#cba6f7;
                     font-family:monospace; }
#preview-code      { flex:1; overflow:auto; margin:0; padding:.75rem 1rem;
                     background:#11111b; color:#cdd6f4; font-family:'Fira Code',monospace;
                     font-size:.75rem; line-height:1.55; white-space:pre;
                     tab-size:4; }
#preview-code::-webkit-scrollbar { width:6px; height:6px; }
#preview-code::-webkit-scrollbar-thumb { background:#45475a; }

/* ── Toolbar buttons ────────────────────────── */
.tb-btn            { display:inline-flex; align-items:center; gap:.3rem; padding:.3rem .6rem;
                     border-radius:.3rem; font-size:.73rem; font-weight:600; cursor:pointer;
                     border:none; background:#313244; color:#cdd6f4; white-space:nowrap; }
.tb-btn:hover      { background:#45475a; }
.tb-btn:disabled   { opacity:.4; cursor:not-allowed; }
.tb-btn.green      { background:#a6e3a1; color:#1e1e2e; }
.tb-btn.green:hover{ background:#94e2d5; }
.tb-btn.purple     { background:#cba6f7; color:#1e1e2e; }
.tb-btn.purple:hover{ background:#b4befe; }
.tb-btn.active     { background:#cba6f7 !important; color:#1e1e2e !important; }
#tb-file-info      { display:flex; align-items:center; gap:.4rem; flex:1; min-width:0;
                     overflow:hidden; }
#tb-filename       { font-size:.79rem; font-weight:600; color:#cdd6f4;
                     overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
#tb-lang-badge     { background:#313244; color:#89b4fa; font-size:.67rem; padding:.12rem .42rem;
                     border-radius:.25rem; font-weight:700; flex-shrink:0; }
#tb-save-indicator { font-size:.68rem; color:#6c7086; flex-shrink:0; }

/* ── Drop overlay ───────────────────────────── */
#drop-overlay      { position:absolute; inset:0; background:rgba(203,166,247,.1);
                     border:2px dashed #cba6f7; z-index:50; display:none;
                     align-items:center; justify-content:center; pointer-events:none; }
#drop-overlay span { color:#cba6f7; font-size:1rem; font-weight:700; }

/* ── Empty state ────────────────────────────── */
#editor-empty      { flex:1; display:flex; flex-direction:column; align-items:center;
                     justify-content:center; color:#6c7086; background:#1e1e2e;
                     text-align:center; padding:2rem; }
#editor-empty h3   { font-size:.95rem; font-weight:700; margin:.75rem 0 .35rem; color:#45475a; }
#editor-empty p    { font-size:.78rem; line-height:1.6; max-width:320px; }

/* ── Context menu ───────────────────────────── */
#ctx-menu          { position:fixed; background:#1e1e2e; border:1px solid #45475a;
                     border-radius:.4rem; box-shadow:0 6px 24px rgba(0,0,0,.7);
                     z-index:999; padding:.2rem 0; display:none; min-width:150px; }
.ctx-item          { padding:.32rem .85rem; font-size:.77rem; cursor:pointer; color:#cdd6f4; }
.ctx-item:hover    { background:#313244; }
.ctx-item.red      { color:#f38ba8; }
.ctx-sep           { height:1px; background:#313244; margin:.2rem 0; }

/* ── Folder picker modal ────────────────────── */
#folder-modal, #upload-modal
                   { display:none; position:fixed; inset:0; background:rgba(0,0,0,.65);
                     z-index:300; align-items:center; justify-content:center; }
.modal-box         { background:#1e1e2e; border:1px solid #45475a; border-radius:.6rem;
                     color:#cdd6f4; box-shadow:0 12px 40px rgba(0,0,0,.8); }
#folder-modal .modal-box { width:520px; max-width:95vw; }
.modal-title       { padding:.75rem 1rem; border-bottom:1px solid #313244;
                     font-size:.9rem; font-weight:700; display:flex;
                     justify-content:space-between; align-items:center; }
.modal-body        { padding:1rem; }
.modal-footer      { padding:.65rem 1rem; border-top:1px solid #313244;
                     display:flex; gap:.5rem; justify-content:flex-end; }
#fp-path-bar       { display:flex; gap:.4rem; margin-bottom:.6rem; }
#fp-path-input     { flex:1; background:#11111b; border:1px solid #45475a; border-radius:.35rem;
                     color:#cdd6f4; padding:.35rem .6rem; font-size:.78rem; outline:none; }
#fp-path-input:focus { border-color:#cba6f7; }
#fp-breadcrumb     { display:flex; align-items:center; gap:.2rem; flex-wrap:wrap;
                     font-size:.72rem; margin-bottom:.5rem; color:#6c7086; min-height:1.4rem; }
.fp-crumb          { cursor:pointer; color:#89b4fa; }
.fp-crumb:hover    { text-decoration:underline; }
.fp-crumb-sep      { color:#45475a; }
#fp-list           { max-height:240px; overflow-y:auto; border:1px solid #313244;
                     border-radius:.35rem; background:#11111b; }
.fp-dir-item       { display:flex; align-items:center; gap:.5rem; padding:.35rem .65rem;
                     cursor:pointer; font-size:.79rem; color:#cdd6f4; border-bottom:1px solid #1e1e2e; }
.fp-dir-item:hover { background:#1e1e2e; }
.fp-dir-item svg   { flex-shrink:0; }
#fp-current        { font-size:.72rem; color:#a6e3a1; margin-top:.5rem; padding:.3rem .5rem;
                     background:#11111b; border-radius:.3rem; word-break:break-all; }
#fp-shortcuts      { display:flex; gap:.4rem; flex-wrap:wrap; margin-bottom:.6rem; }
.fp-shortcut       { padding:.2rem .55rem; border-radius:.3rem; font-size:.7rem; font-weight:600;
                     border:1px solid #45475a; color:#cdd6f4; background:transparent; cursor:pointer; }
.fp-shortcut:hover { background:#313244; }

/* scrollbars */
::-webkit-scrollbar       { width:5px; height:5px; }
::-webkit-scrollbar-track { background:#1e1e2e; }
::-webkit-scrollbar-thumb { background:#45475a; border-radius:3px; }

/* ── Viewport lock — code editor must fill exactly the screen height ── */
/* Override the site-wide min-h-screen so the flex chain gets definite heights */
html, body                { height:100vh !important; overflow:hidden !important; }
body > .flex              { height:100vh !important; min-height:unset !important;
                            overflow:hidden !important; }
body > .flex > .flex-1    { height:100vh !important; overflow:hidden !important;
                            display:flex !important; flex-direction:column !important; }
body > .flex > .flex-1 > header { flex-shrink:0 !important; }
</style>

<!-- ════════════════════════════════════════════
     MAIN EDITOR ROOT
════════════════════════════════════════════ -->
<div id="editor-root">

  <!-- ── Toolbar ─────────────────────────────── -->
  <div id="editor-toolbar">

    <!-- Workspace / folder controls -->
    <button class="tb-btn" id="btn-open-folder" title="Open local folder">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      Open Folder
    </button>

    <div style="width:1px;height:16px;background:#313244;"></div>

    <!-- File info -->
    <div id="tb-file-info">
      <span id="tb-filename" style="color:#6c7086;">No file open</span>
      <span id="tb-lang-badge" style="display:none;"></span>
      <span id="tb-save-indicator"></span>
    </div>

    <!-- Spacer -->
    <div style="flex:1;"></div>

    <!-- Actions -->
    <button class="tb-btn" id="btn-new-file" title="New file (Ctrl+N)" disabled>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
      New File
    </button>
    <button class="tb-btn" id="btn-new-folder" title="New folder" disabled>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/><line x1="12" y1="11" x2="12" y2="17"/><line x1="9" y1="14" x2="15" y2="14"/></svg>
      New Folder
    </button>
    <button class="tb-btn purple" id="btn-upload" title="Upload files to current workspace">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
      Upload
    </button>
    <button class="tb-btn green" id="btn-save" title="Save (Ctrl+S)" disabled>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
      Save
    </button>
    <button class="tb-btn" id="btn-ai-toggle" title="Toggle AI assistant (Ctrl+`)">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 6v6l4 2"/><circle cx="18" cy="6" r="3" fill="currentColor"/></svg>
      AI
    </button>
    <button class="tb-btn" id="btn-refresh-tree" title="Refresh explorer">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
    </button>
  </div>

  <!-- ── Panels row ───────────────────────────── -->
  <div id="editor-panels">
    <div id="drop-overlay"><span>⬆ Drop to upload</span></div>

    <!-- File tree -->
    <div id="file-tree-panel">
      <div id="tree-top">
        <span id="tree-workspace-name">No workspace</span>
        <button class="tb-btn" id="btn-close-ws" title="Close workspace (use default)" style="padding:.15rem .35rem;font-size:.65rem;display:none;">✕</button>
      </div>
      <div id="file-tree">
        <div style="padding:1.5rem .75rem;color:#6c7086;font-size:.75rem;text-align:center;line-height:1.7;">
          Click <strong style="color:#cba6f7;">Open Folder</strong> to open<br>a local project folder.<br><br>
          <span style="font-size:.68rem;">Or upload files with the Upload button.</span>
        </div>
      </div>
    </div>

    <!-- Editor main -->
    <div id="editor-main">

      <!-- Tab bar -->
      <div id="tab-bar"></div>

      <!-- Monaco / empty state -->
      <div id="editor-empty">
        <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#45475a" stroke-width="1.3"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        <h3>Open a file to start editing</h3>
        <p>Select a file from the explorer, or open a local folder with <strong style="color:#cba6f7;">Open Folder</strong> in the toolbar.</p>
        <p style="margin-top:.5rem;font-size:.72rem;">Drag &amp; drop files anywhere to upload them.</p>
      </div>
      <div id="editor-container" style="display:none;"></div>

    </div><!-- end editor-main -->

    <!-- ── Resize handle (drag to widen/narrow AI sidebar) ── -->
    <div id="ai-resize-handle" style="display:none;"></div>

    <!-- ── AI right sidebar ──────────────────────────────── -->
    <div id="ai-bottom" class="closed">
      <div id="ai-bottom-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#cba6f7" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>AI Coding Assistant</span>
        <div id="ai-mode-toggle">
          <button class="ai-mode-btn active" data-mode="chat">Chat</button>
          <button class="ai-mode-btn" data-mode="agent">Agent</button>
        </div>
        <div style="display:flex;gap:.3rem;align-items:center;">
          <button class="tb-btn" id="btn-ai-clear" style="padding:.15rem .45rem;font-size:.68rem;" title="Clear chat">Clear</button>
          <button class="tb-btn" id="btn-ai-close" style="padding:.15rem .35rem;" title="Close AI panel">✕</button>
        </div>
      </div>
      <div id="agent-ctx-bar">
        <span id="agent-ctx-label">No workspace files loaded</span>
        <button class="tb-btn" id="btn-load-ctx" style="padding:.18rem .5rem;font-size:.68rem;flex-shrink:0;">Load Files</button>
      </div>
      <div id="ai-messages">
        <div class="ai-msg assistant">Hi! I can help you write, explain, fix, or review code. Open a file and use the quick actions, or just ask me anything.</div>
      </div>
      <div id="ai-input-row">
        <textarea id="ai-input" rows="3" placeholder="Ask about the code… (Ctrl+Enter to send)"></textarea>
        <div id="ai-send-row">
          <button id="ai-send">Send ↑</button>
        </div>
      </div>
    </div><!-- end ai-bottom -->

  </div><!-- end editor-panels -->

  <!-- ── File preview modal (Agent mode) ──────── -->
  <div id="preview-modal">
    <div class="modal-box" style="display:flex;flex-direction:column;max-height:88vh;overflow:hidden;width:min(900px,96vw);">
      <div id="preview-header">
        <span id="preview-filepath"></span>
        <button id="preview-close" style="background:none;border:none;color:#6c7086;font-size:1.1rem;cursor:pointer;padding:.2rem .5rem;">✕</button>
      </div>
      <pre id="preview-code"></pre>
    </div>
  </div>

</div><!-- end editor-root -->

<!-- ── Hidden file inputs ───────────────────── -->
<input type="file" id="upload-files-input"  multiple style="display:none">
<input type="file" id="upload-folder-input" multiple style="display:none" webkitdirectory>

<!-- ── Context menu ─────────────────────────── -->
<div id="ctx-menu">
  <div class="ctx-item" id="ctx-newfile">New File Here</div>
  <div class="ctx-item" id="ctx-newfolder">New Folder Here</div>
  <div class="ctx-sep"></div>
  <div class="ctx-item" id="ctx-rename">Rename</div>
  <div class="ctx-sep"></div>
  <div class="ctx-item red" id="ctx-delete">Delete</div>
</div>

<!-- ── Upload modal ─────────────────────────── -->
<div id="upload-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:300;align-items:center;justify-content:center;">
  <div class="modal-box" style="width:340px;">
    <div class="modal-title">Upload Files <button id="upload-modal-close" style="background:none;border:none;color:#6c7086;font-size:1.1rem;cursor:pointer;">✕</button></div>
    <div class="modal-body" style="display:flex;flex-direction:column;gap:.55rem;">
      <button class="tb-btn purple" id="modal-files-btn" style="justify-content:center;padding:.6rem;">📄 Upload Files</button>
      <button class="tb-btn" id="modal-folder-btn" style="justify-content:center;padding:.6rem;background:#45475a;">📁 Upload Folder (preserves structure)</button>
      <div id="upload-status" style="font-size:.75rem;color:#a6e3a1;display:none;"></div>
    </div>
  </div>
</div>

<!-- ── Folder picker modal ───────────────────── -->
<div id="folder-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:300;align-items:center;justify-content:center;">
  <div class="modal-box" style="width:540px;max-width:96vw;">
    <div class="modal-title">
      Open Local Folder
      <button id="fp-close" style="background:none;border:none;color:#6c7086;font-size:1.1rem;cursor:pointer;">✕</button>
    </div>
    <div class="modal-body">
      <!-- Quick shortcuts -->
      <div id="fp-shortcuts">
        <span style="font-size:.7rem;color:#6c7086;align-self:center;margin-right:.2rem;">Quick:</span>
        <button class="fp-shortcut" id="fp-htdocs">htdocs</button>
        <button class="fp-shortcut" id="fp-studyflow">studyflow</button>
        <button class="fp-shortcut" id="fp-default">Default workspace</button>
      </div>
      <!-- Path input -->
      <div id="fp-path-bar">
        <input id="fp-path-input" placeholder="e.g. C:/xampp/htdocs/myproject" spellcheck="false">
        <button class="tb-btn purple" id="fp-go">Go</button>
      </div>
      <!-- Breadcrumb -->
      <div id="fp-breadcrumb"></div>
      <!-- Dir listing -->
      <div id="fp-list"></div>
      <!-- Selected -->
      <div id="fp-current" style="display:none;"></div>
    </div>
    <div class="modal-footer">
      <button class="tb-btn" id="fp-cancel">Cancel</button>
      <button class="tb-btn purple" id="fp-open" disabled>Open This Folder</button>
    </div>
  </div>
</div>

<!-- Lucide must load BEFORE Monaco loader — Monaco's AMD require hijacks UMD bundles loaded after it -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>if(window.lucide) lucide.createIcons();</script>

<!-- ══════════════════════════════════════════════
     Monaco Editor (CDN)
══════════════════════════════════════════════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs/loader.min.js"></script>
<script>
(function () {
'use strict';

/* ── Constants ───────────────────────────────── */
const BASE = window.APP_BASE_URL || '';
const CSRF = () => window.CSRF_TOKEN || '';
const JHDRS = () => ({ 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF() });
const HTDOCS = <?php echo json_encode($htdocs); ?>;
const BASE_PATH_PHP = <?php echo json_encode(str_replace('\\', '/', BASE_PATH)); ?>;

/* ── State ───────────────────────────────────── */
let editor      = null;          // monaco editor instance
let openTabs    = [];            // [{ path, name, model, dirty }]
let activeTab   = null;          // currently active file path
let ctxTarget   = null;          // { path, type } for right-click menu
let aiOpen      = false;
let currentWs   = null;          // { workspace, label } from server
let aiAction    = 'chat';
let agentMode   = false;
let wsContext   = null;    // [{path, content}] loaded from read-workspace.php
let wsContextBytes = 0;
let pendingFile = null;          // file to open after Monaco loads

/* ── Language / icon maps ────────────────────── */
const LANG_MAP = {
    js:'javascript',jsx:'javascript',mjs:'javascript',cjs:'javascript',
    ts:'typescript',tsx:'typescript',
    py:'python',php:'php',html:'html',htm:'html',
    css:'css',scss:'scss',sass:'scss',less:'less',
    json:'json',jsonc:'json',xml:'xml',svg:'xml',
    md:'markdown',markdown:'markdown',
    sql:'sql',sh:'shell',bash:'shell',zsh:'shell',
    c:'c',h:'cpp',cpp:'cpp',cc:'cpp',cxx:'cpp',
    java:'java',go:'go',rs:'rust',rb:'ruby',
    kt:'kotlin',swift:'swift',dart:'dart',
    yaml:'yaml',yml:'yaml',toml:'ini',ini:'ini',env:'ini',
    txt:'plaintext',csv:'plaintext',log:'plaintext',
};
const EXT_ICONS = {
    js:'🟨',jsx:'🟨',ts:'🔷',tsx:'🔷',py:'🐍',php:'🐘',
    html:'🌐',htm:'🌐',css:'🎨',scss:'🎨',sass:'🎨',
    json:'📋',xml:'📋',svg:'🖼',md:'📝',sql:'🗄️',
    sh:'⚙️',bash:'⚙️',c:'⚡',cpp:'⚡',h:'⚡',
    java:'☕',go:'🐹',rs:'🦀',rb:'💎',
    jpg:'🖼',jpeg:'🖼',png:'🖼',gif:'🖼',webp:'🖼',ico:'🖼',
    pdf:'📕',zip:'📦',gz:'📦',tar:'📦',
};
function fileExt(name)  { return (name.split('.').pop() || '').toLowerCase(); }
function fileIcon(name, type) { return type==='dir' ? '📁' : (EXT_ICONS[fileExt(name)] || '📄'); }
function langFor(path)  { return LANG_MAP[fileExt(path)] || 'plaintext'; }

/* ── Generic API call ────────────────────────── */
async function api(url, opts = {}) {
    try {
        const r = await fetch(BASE + url, opts);
        return await r.json();
    } catch (e) {
        return { error: e.message };
    }
}

/* ══════════════════════════════════════════════
   FILE TREE
══════════════════════════════════════════════ */
async function refreshTree() {
    const treeEl = document.getElementById('file-tree');
    treeEl.innerHTML = '<div style="padding:.75rem;color:#6c7086;font-size:.75rem;text-align:center;">⟳ Loading…</div>';
    const data = await api('/api/editor/list-files.php');
    if (data.error) {
        treeEl.innerHTML = `<div style="padding:.75rem;color:#f38ba8;font-size:.75rem;">${esc(data.error)}</div>`;
        return;
    }
    // Update workspace label
    currentWs = { workspace: data.workspace, label: data.label };
    document.getElementById('tree-workspace-name').textContent = data.label || 'Workspace';
    document.getElementById('btn-close-ws').style.display = data.label !== 'Default workspace' ? '' : 'none';
    ['btn-new-file','btn-new-folder'].forEach(id => document.getElementById(id).disabled = false);

    treeEl.innerHTML = '';
    if (!data.tree || data.tree.length === 0) {
        treeEl.innerHTML = '<div style="padding:.75rem;color:#6c7086;font-size:.75rem;text-align:center;">Empty folder.</div>';
        return;
    }
    renderTree(data.tree, treeEl, 0);
}

function renderTree(nodes, container, depth) {
    for (const node of nodes) {
        const row = document.createElement('div');
        row.className = 'tree-item' + (node.path === activeTab ? ' active' : '');
        row.style.paddingLeft = (0.6 + depth * 0.85) + 'rem';
        row.dataset.path = node.path;
        row.dataset.type = node.type;

        if (node.type === 'dir') {
            const chevron = document.createElement('span');
            chevron.className = 'tree-chevron';
            chevron.textContent = '▶';
            const icon = document.createElement('span');
            icon.textContent = '📁';
            const label = document.createElement('span');
            label.style.cssText = 'overflow:hidden;text-overflow:ellipsis;';
            label.textContent = node.name;

            row.append(chevron, icon, label);

            const childWrap = document.createElement('div');
            let open = false;
            row.addEventListener('click', e => {
                e.stopPropagation();
                open = !open;
                chevron.classList.toggle('open', open);
                childWrap.style.display = open ? 'block' : 'none';
            });
            container.appendChild(row);
            childWrap.style.display = 'none';
            renderTree(node.children || [], childWrap, depth + 1);
            container.appendChild(childWrap);
        } else {
            const spacer = document.createElement('span');
            spacer.className = 'tree-chevron';
            const icon = document.createElement('span');
            icon.textContent = fileIcon(node.name, 'file');
            const label = document.createElement('span');
            label.style.cssText = 'overflow:hidden;text-overflow:ellipsis;';
            label.textContent = node.name;

            row.append(spacer, icon, label);
            row.addEventListener('click', e => { e.stopPropagation(); openFile(node.path, node.name); });
            container.appendChild(row);
        }

        row.addEventListener('contextmenu', e => {
            e.preventDefault();
            ctxTarget = { path: node.path, type: node.type };
            showCtx(e.clientX, e.clientY);
        });
    }
}

/* ══════════════════════════════════════════════
   FILE OPEN / TABS
══════════════════════════════════════════════ */
async function openFile(path, name) {
    if (openTabs.find(t => t.path === path)) { activateTab(path); return; }

    const data = await api('/api/editor/get-file.php?path=' + encodeURIComponent(path));
    if (data.error) { toast(data.error, 'err'); return; }

    const lang  = langFor(path);
    const model = editor ? monaco.editor.createModel(data.content, lang) : null;
    openTabs.push({ path, name: name || path.split('/').pop(), model, content: data.content, dirty: false });
    renderTabs();
    activateTab(path);
}

function activateTab(path) {
    const tab = openTabs.find(t => t.path === path);
    if (!tab) return;
    activeTab = path;

    document.querySelectorAll('.editor-tab').forEach(el => el.classList.toggle('active', el.dataset.path === path));
    document.querySelectorAll('.tree-item').forEach(el => el.classList.toggle('active', el.dataset.path === path));

    document.getElementById('tb-filename').textContent = tab.name;
    document.getElementById('tb-filename').style.color = '#cdd6f4';
    const lb = document.getElementById('tb-lang-badge');
    lb.textContent = langFor(path);
    lb.style.display = '';
    document.getElementById('btn-save').disabled = false;
    updateSaveIndicator(tab);

    if (editor && tab.model) {
        editor.setModel(tab.model);
        showEditorArea();
        editor.layout();
        editor.focus();
    } else if (!editor) {
        pendingFile = tab;
    }
}

function showEditorArea() {
    document.getElementById('editor-container').style.display = '';
    document.getElementById('editor-empty').style.display = 'none';
}

function renderTabs() {
    const bar = document.getElementById('tab-bar');
    bar.innerHTML = '';
    openTabs.forEach(tab => {
        const el = document.createElement('div');
        el.className = 'editor-tab' + (tab.path === activeTab ? ' active' : '') + (tab.dirty ? ' tab-dirty' : '');
        el.dataset.path = tab.path;
        el.innerHTML = `<span>${fileIcon(tab.name,'file')}</span><span>${esc(tab.name)}</span><span class="tab-close" title="Close">×</span>`;
        el.querySelector('span:nth-child(2)').addEventListener('click', () => activateTab(tab.path));
        el.querySelector('.tab-close').addEventListener('click', e => { e.stopPropagation(); closeTab(tab.path); });
        bar.appendChild(el);
    });
}

function closeTab(path) {
    const tab = openTabs.find(t => t.path === path);
    if (!tab) return;
    if (tab.dirty && !confirm('Unsaved changes in ' + tab.name + '. Close anyway?')) return;
    if (tab.model) tab.model.dispose();
    openTabs = openTabs.filter(t => t.path !== path);
    renderTabs();
    if (activeTab === path) {
        if (openTabs.length) {
            activateTab(openTabs[openTabs.length - 1].path);
        } else {
            activeTab = null;
            document.getElementById('tb-filename').textContent = 'No file open';
            document.getElementById('tb-filename').style.color = '#6c7086';
            document.getElementById('tb-lang-badge').style.display = 'none';
            document.getElementById('tb-save-indicator').textContent = '';
            document.getElementById('btn-save').disabled = true;
            document.getElementById('editor-container').style.display = 'none';
            document.getElementById('editor-empty').style.display = 'flex';
        }
    }
}

function updateSaveIndicator(tab) {
    const el = document.getElementById('tb-save-indicator');
    el.textContent = tab.dirty ? '● unsaved' : '✓ saved';
    el.style.color = tab.dirty ? '#f38ba8' : '#6c7086';
}

/* ══════════════════════════════════════════════
   SAVE
══════════════════════════════════════════════ */
async function saveCurrentFile() {
    if (!activeTab || !editor) return;
    const tab = openTabs.find(t => t.path === activeTab);
    if (!tab) return;

    const ind = document.getElementById('tb-save-indicator');
    ind.textContent = '⟳ saving…'; ind.style.color = '#89b4fa';
    const content = editor.getValue();
    const res = await api('/api/editor/save-file.php', {
        method: 'POST', headers: JHDRS(),
        body: JSON.stringify({ path: tab.path, content }),
    });
    if (res.success) {
        tab.dirty = false; tab.content = content;
        renderTabs(); updateSaveIndicator(tab);
        toast('Saved');
    } else {
        ind.textContent = '✗ save failed'; ind.style.color = '#f38ba8';
        toast(res.error || 'Save failed', 'err');
    }
}

/* ══════════════════════════════════════════════
   NEW FILE / FOLDER
══════════════════════════════════════════════ */
async function createItem(type, basePath) {
    const label = type === 'dir' ? 'folder' : 'file';
    const prefix = basePath ? (basePath.includes('/') ? basePath.substring(0, basePath.lastIndexOf('/')+1) : '') : (activeTab ? activeTab.substring(0, activeTab.lastIndexOf('/')+1) : '');
    const name = prompt(`New ${label} name:`, prefix);
    if (!name) return;
    const rel = name.replace(/\\/g,'/').replace(/^\//,'');
    const res = await api('/api/editor/new-item.php', {
        method:'POST', headers:JHDRS(),
        body: JSON.stringify({ path: rel, type }),
    });
    if (res.success) {
        await refreshTree();
        if (type === 'file') openFile(res.path, res.path.split('/').pop());
    } else {
        toast(res.error || 'Could not create ' + label, 'err');
    }
}

/* ══════════════════════════════════════════════
   DELETE / RENAME
══════════════════════════════════════════════ */
async function deleteItem(path) {
    const name = path.split('/').pop();
    if (!confirm(`Delete "${name}"? This cannot be undone.`)) return;
    const res = await api('/api/editor/delete.php', {
        method:'POST', headers:JHDRS(), body: JSON.stringify({ path }),
    });
    if (res.success) {
        openTabs.filter(t => t.path === path || t.path.startsWith(path+'/')).forEach(t => closeTab(t.path));
        await refreshTree(); toast('Deleted');
    } else {
        toast(res.error || 'Delete failed', 'err');
    }
}

async function renameItem(oldPath) {
    const oldName = oldPath.split('/').pop();
    const newName = prompt('Rename to:', oldName);
    if (!newName || newName === oldName) return;
    const dir     = oldPath.substring(0, oldPath.lastIndexOf('/')+1);
    const newPath = dir + newName;
    const res = await api('/api/editor/rename.php', {
        method:'POST', headers:JHDRS(),
        body: JSON.stringify({ old_path: oldPath, new_path: newPath }),
    });
    if (res.success) {
        const tab = openTabs.find(t => t.path === oldPath);
        if (tab) { tab.path = res.new_path; tab.name = newName; }
        if (activeTab === oldPath) activeTab = res.new_path;
        renderTabs(); await refreshTree(); toast('Renamed');
    } else {
        toast(res.error || 'Rename failed', 'err');
    }
}

/* ══════════════════════════════════════════════
   CONTEXT MENU
══════════════════════════════════════════════ */
function showCtx(x, y) {
    const m = document.getElementById('ctx-menu');
    m.style.cssText = `display:block;left:${x}px;top:${y}px;`;
}
document.getElementById('ctx-newfile') .addEventListener('click', () => { hideCtx(); createItem('file',  ctxTarget?.path); });
document.getElementById('ctx-newfolder').addEventListener('click', () => { hideCtx(); createItem('dir',   ctxTarget?.path); });
document.getElementById('ctx-rename')  .addEventListener('click', () => { hideCtx(); if (ctxTarget) renameItem(ctxTarget.path); });
document.getElementById('ctx-delete')  .addEventListener('click', () => { hideCtx(); if (ctxTarget) deleteItem(ctxTarget.path); });
function hideCtx() { document.getElementById('ctx-menu').style.display = 'none'; }
document.addEventListener('click', hideCtx);

/* ══════════════════════════════════════════════
   UPLOAD
══════════════════════════════════════════════ */
function showUploadModal() {
    const m = document.getElementById('upload-modal');
    m.style.display = 'flex';
    document.getElementById('upload-status').style.display = 'none';
}
document.getElementById('btn-upload').addEventListener('click', showUploadModal);
document.getElementById('upload-modal-close').addEventListener('click', () => { document.getElementById('upload-modal').style.display='none'; });
document.getElementById('modal-files-btn').addEventListener('click', () => document.getElementById('upload-files-input').click());
document.getElementById('modal-folder-btn').addEventListener('click', () => document.getElementById('upload-folder-input').click());

async function doUpload(fileList, useRelPaths) {
    if (!fileList?.length) return;
    const s = document.getElementById('upload-status');
    s.style.display=''; s.textContent = `Uploading ${fileList.length} file(s)…`;
    const fd = new FormData();
    fd.append('csrf_token', CSRF());
    Array.from(fileList).forEach((f,i) => {
        fd.append('files[]', f);
        fd.append('paths[]', useRelPaths ? (f.webkitRelativePath || f.name) : f.name);
    });
    const r = await fetch(BASE + '/api/editor/upload.php', { method:'POST', body: fd });
    const d = await r.json();
    if (d.uploaded > 0) {
        s.textContent = `✓ Uploaded ${d.uploaded} file(s)`;
        await refreshTree();
        setTimeout(() => { document.getElementById('upload-modal').style.display='none'; }, 1000);
    } else {
        s.style.color = '#f38ba8';
        s.textContent = '✗ ' + (d.errors?.[0] || 'Upload failed');
    }
}

document.getElementById('upload-files-input').addEventListener('change', e => { doUpload(e.target.files, false); e.target.value=''; });
document.getElementById('upload-folder-input').addEventListener('change', e => { doUpload(e.target.files, true);  e.target.value=''; });

// Drag and drop
const panelsEl = document.getElementById('editor-panels');
const dropEl   = document.getElementById('drop-overlay');
panelsEl.addEventListener('dragover',  e => { e.preventDefault(); dropEl.style.display='flex'; });
panelsEl.addEventListener('dragleave', e => { if (!panelsEl.contains(e.relatedTarget)) dropEl.style.display='none'; });
panelsEl.addEventListener('drop', async e => {
    e.preventDefault(); dropEl.style.display='none';
    if (e.dataTransfer.files.length) { showUploadModal(); await doUpload(e.dataTransfer.files, true); }
});

/* ══════════════════════════════════════════════
   FOLDER PICKER
══════════════════════════════════════════════ */
let fpSelected = '';

function openFolderModal() {
    document.getElementById('folder-modal').style.display = 'flex';
    document.getElementById('fp-open').disabled = true;
    document.getElementById('fp-current').style.display = 'none';
    browseTo(HTDOCS);
}
function closeFolderModal() { document.getElementById('folder-modal').style.display = 'none'; }

document.getElementById('btn-open-folder').addEventListener('click', openFolderModal);
document.getElementById('fp-close').addEventListener('click', closeFolderModal);
document.getElementById('fp-cancel').addEventListener('click', closeFolderModal);
document.getElementById('fp-htdocs').addEventListener('click', () => browseTo(HTDOCS));
document.getElementById('fp-studyflow').addEventListener('click', () => browseTo(BASE_PATH_PHP));
document.getElementById('fp-default').addEventListener('click', async () => {
    closeFolderModal();
    await setWorkspace('');
});
document.getElementById('fp-go').addEventListener('click', () => {
    const v = document.getElementById('fp-path-input').value.trim();
    if (v) browseTo(v);
});
document.getElementById('fp-path-input').addEventListener('keydown', e => { if (e.key==='Enter') document.getElementById('fp-go').click(); });

document.getElementById('fp-open').addEventListener('click', async () => {
    if (!fpSelected) return;
    closeFolderModal();
    await setWorkspace(fpSelected);
});

async function browseTo(path) {
    const listEl = document.getElementById('fp-list');
    listEl.innerHTML = '<div style="padding:.5rem .75rem;font-size:.75rem;color:#6c7086;">Loading…</div>';
    document.getElementById('fp-path-input').value = path;

    const d = await api('/api/editor/browse-dirs.php?path=' + encodeURIComponent(path));
    if (d.error) {
        listEl.innerHTML = `<div style="padding:.5rem .75rem;font-size:.75rem;color:#f38ba8;">${esc(d.error)}</div>`;
        return;
    }

    // Breadcrumb
    const bc = document.getElementById('fp-breadcrumb');
    bc.innerHTML = '';
    d.crumbs.forEach((c, i) => {
        if (i > 0) { const sep=document.createElement('span'); sep.className='fp-crumb-sep'; sep.textContent=' / '; bc.appendChild(sep); }
        const cr = document.createElement('span'); cr.className='fp-crumb'; cr.textContent=c.label;
        cr.addEventListener('click', () => browseTo(c.path));
        bc.appendChild(cr);
    });

    // Select current dir + dir list
    fpSelected = d.path;
    const cur = document.getElementById('fp-current');
    cur.textContent = '📁 ' + d.path + (d.writable ? '' : ' (read-only)');
    cur.style.display = '';
    cur.style.color = d.writable ? '#a6e3a1' : '#fab387';
    document.getElementById('fp-open').disabled = false;

    listEl.innerHTML = '';
    // Up directory button (if not at root)
    if (d.parent && d.parent !== d.path) {
        const up = document.createElement('div');
        up.className='fp-dir-item';
        up.innerHTML=`<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#89b4fa" stroke-width="2"><path d="M20 12H4M4 12l6-6M4 12l6 6"/></svg><span style="color:#89b4fa;font-size:.75rem;">.. (up)</span>`;
        up.addEventListener('click', () => browseTo(d.parent));
        listEl.appendChild(up);
    }
    d.dirs.forEach(dir => {
        const item = document.createElement('div');
        item.className = 'fp-dir-item';
        item.innerHTML = `<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#cba6f7" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg><span>${esc(dir)}</span>`;
        item.addEventListener('dblclick', () => browseTo(d.path + '/' + dir));
        item.addEventListener('click', () => {
            document.querySelectorAll('.fp-dir-item').forEach(el => el.style.background='');
            item.style.background = '#1e1e2e';
            fpSelected = d.path + '/' + dir;
            cur.textContent = '📁 ' + fpSelected;
        });
        listEl.appendChild(item);
    });
    if (d.dirs.length === 0 && d.files.length === 0) {
        listEl.innerHTML = '<div style="padding:.5rem .75rem;font-size:.75rem;color:#6c7086;">Empty directory</div>';
    }
}

async function setWorkspace(path) {
    const res = await api('/api/editor/set-workspace.php', {
        method: 'POST', headers: JHDRS(), body: JSON.stringify({ path }),
    });
    if (res.error) { toast(res.error, 'err'); return; }
    // Close all tabs (they belong to old workspace)
    [...openTabs].forEach(t => { if (t.model) t.model.dispose(); });
    openTabs = []; activeTab = null; renderTabs();
    document.getElementById('editor-container').style.display = 'none';
    document.getElementById('editor-empty').style.display = 'flex';
    document.getElementById('btn-save').disabled = true;
    document.getElementById('tb-filename').textContent = 'No file open';
    document.getElementById('tb-filename').style.color = '#6c7086';
    document.getElementById('tb-lang-badge').style.display = 'none';
    await refreshTree();
    toast('Opened: ' + res.label);
}

document.getElementById('btn-close-ws').addEventListener('click', async () => {
    if (confirm('Switch back to the default workspace?')) await setWorkspace('');
});

/* ══════════════════════════════════════════════
   AI ASSISTANT
══════════════════════════════════════════════ */
const aiPanel    = document.getElementById('ai-bottom');
const aiHandle   = document.getElementById('ai-resize-handle');

function openAI() {
    aiOpen = true;
    aiPanel.classList.remove('closed');
    aiHandle.style.display = '';
    document.getElementById('btn-ai-toggle').classList.add('active');
    // Restore last width if user had resized it, or use default 320px
    if (!aiPanel.style.width || aiPanel.style.width === '0px') {
        aiPanel.style.width = '320px';
    }
    setTimeout(() => { if (editor) editor.layout(); }, 20);
}
function closeAI() {
    aiOpen = false;
    aiPanel.classList.add('closed');
    aiHandle.style.display = 'none';
    document.getElementById('btn-ai-toggle').classList.remove('active');
    setTimeout(() => { if (editor) editor.layout(); }, 20);
}
document.getElementById('btn-ai-toggle').addEventListener('click', () => aiOpen ? closeAI() : openAI());
document.getElementById('btn-ai-close').addEventListener('click', closeAI);
document.getElementById('btn-ai-clear').addEventListener('click', () => {
    document.getElementById('ai-messages').innerHTML = '<div class="ai-msg assistant">Chat cleared. Ask me anything!</div>';
});

// ── Critical: prevent Monaco from stealing focus/keyboard away from AI inputs ──
// Monaco listens for mousedown on the document and can re-grab keyboard focus.
// Stopping propagation on the AI panel ensures Monaco doesn't intercept our clicks.
document.getElementById('ai-bottom').addEventListener('mousedown', (e) => {
    e.stopPropagation();
});

// When the textarea is clicked, explicitly take focus after any Monaco handlers run.
const aiInputEl = document.getElementById('ai-input');
aiInputEl.addEventListener('mousedown', (e) => { e.stopPropagation(); });
aiInputEl.addEventListener('click', (e) => {
    e.stopPropagation();
    // Use setTimeout so we grab focus AFTER Monaco's blur/focus cycle settles
    setTimeout(() => { aiInputEl.focus(); }, 0);
});
// Stop ALL key events from bubbling out of the textarea so Monaco never sees them.
// Handle our own shortcuts explicitly within this listener.
aiInputEl.addEventListener('keydown', (e) => {
    e.stopPropagation();
    if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); sendToAI(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveCurrentFile(); }
});
aiInputEl.addEventListener('keyup',    (e) => { e.stopPropagation(); });
aiInputEl.addEventListener('keypress', (e) => { e.stopPropagation(); });

// (quick-action pills removed)

document.getElementById('ai-send').addEventListener('click', sendToAI);

async function sendToAI() {
    if (agentMode) { await sendAgentTask(); return; }
    const question = document.getElementById('ai-input').value.trim();
    const code     = editor ? editor.getValue() : '';
    const selected = editor ? (editor.getModel()?.getValueInRange(editor.getSelection()) || '') : '';
    const language = activeTab ? langFor(activeTab) : 'plaintext';
    if (!question && !code) { toast('Open a file first', 'warn'); return; }

    if (!aiOpen) openAI();

    const actionLabel = { explain:'Explain this code', fix:'Fix bugs', review:'Code review', complete:'Complete code', chat: question }[aiAction] || question;
    appendMsg(question || actionLabel, 'user');
    document.getElementById('ai-input').value = '';
    document.getElementById('ai-send').disabled = true;

    const thinking = appendMsg('⟳ Thinking…', 'assistant');
    const res = await api('/api/editor/ai-code.php', {
        method: 'POST', headers: JHDRS(),
        body: JSON.stringify({ action: aiAction, question, code, language, selected }),
    });
    thinking.remove();
    document.getElementById('ai-send').disabled = false;

    if (res.error) {
        appendMsg('⚠ ' + res.error, 'assistant');
    } else {
        const msgEl = appendMsg(res.response, 'assistant', true);
        // Add "Insert at cursor" button if code block detected
        if (res.response.includes('```') && editor) {
            const btn = document.createElement('button');
            btn.className = 'insert-btn';
            btn.textContent = '⎆ Insert at cursor';
            btn.addEventListener('click', () => {
                // Extract first code block
                const m = res.response.match(/```(?:\w+)?\n([\s\S]*?)```/);
                if (m) {
                    editor.focus();
                    editor.executeEdits('ai-insert', [{ range: editor.getSelection(), text: m[1] }]);
                    toast('Code inserted');
                }
            });
            msgEl.prepend(btn);
        }
    }

    aiAction = 'chat';
}

function appendMsg(text, role, isMarkdown = false) {
    const msgs = document.getElementById('ai-messages');
    const div  = document.createElement('div');
    div.className = 'ai-msg ' + role;
    div.innerHTML  = isMarkdown ? renderMd(esc(text)) : esc(text);
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
    return div;
}

function renderMd(html) {
    return html
        .replace(/```(\w*)\n?([\s\S]*?)```/g, (_, l, c) => `<pre><code>${c}</code></pre>`)
        .replace(/`([^`\n]+)`/g, '<code>$1</code>')
        .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
        .replace(/\n/g, '<br>');
}

/* ══════════════════════════════════════════════
   AGENT MODE
══════════════════════════════════════════════ */
// ── Mode toggle buttons ───────────────────────
document.querySelectorAll('.ai-mode-btn').forEach(btn => {
    btn.addEventListener('click', () => switchMode(btn.dataset.mode));
});

function switchMode(mode) {
    agentMode = (mode === 'agent');
    document.querySelectorAll('.ai-mode-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.mode === mode);
    });
    const ctxBar = document.getElementById('agent-ctx-bar');
    ctxBar.style.display = agentMode ? 'flex' : 'none';
    document.getElementById('ai-input').placeholder = agentMode
        ? 'Describe what to build / fix / refactor… (Ctrl+Enter)'
        : 'Ask about the code… (Ctrl+Enter to send)';
}

// ── Load workspace context ────────────────────
document.getElementById('btn-load-ctx').addEventListener('click', loadWorkspaceContext);

async function loadWorkspaceContext() {
    const label = document.getElementById('agent-ctx-label');
    const btn   = document.getElementById('btn-load-ctx');
    label.textContent = '⟳ Reading workspace…';
    btn.disabled = true;
    const d = await api('/api/editor/read-workspace.php');
    btn.disabled = false;
    if (d.error) {
        label.textContent = '✗ ' + d.error;
        return;
    }
    wsContext      = d.files;
    wsContextBytes = d.total_bytes;
    const kb = (wsContextBytes / 1024).toFixed(1);
    label.textContent = `${d.file_count} files · ${kb} KB${d.truncated ? ' (truncated)' : ''}`;
    toast(`Workspace loaded: ${d.file_count} files`);
}

// ── Send task to agent ────────────────────────
async function sendAgentTask() {
    const task = document.getElementById('ai-input').value.trim();
    if (!task) { toast('Describe the task first', 'warn'); return; }
    if (!wsContext) {
        toast('Click "Load Files" to load the workspace first', 'warn');
        return;
    }

    if (!aiOpen) openAI();
    appendMsg(task, 'user');
    document.getElementById('ai-input').value = '';
    document.getElementById('ai-send').disabled = true;

    const thinking = appendMsg('⟳ Agent is thinking… (this may take 30–60 s)', 'assistant');
    const res = await api('/api/editor/ai-agent.php', {
        method: 'POST', headers: JHDRS(),
        body: JSON.stringify({ task, context: wsContext }),
    });
    thinking.remove();
    document.getElementById('ai-send').disabled = false;

    if (res.error) {
        appendMsg('⚠ ' + res.error, 'assistant');
        return;
    }

    if (res.message) appendMsg(res.message, 'assistant', true);
    if (res.errors?.length) {
        appendMsg('⚠ Parse warnings:\n' + res.errors.join('\n'), 'assistant');
    }
    if (res.changes?.length) {
        renderProposedChanges(res.changes);
    } else {
        appendMsg('ℹ No file changes were proposed.', 'assistant');
    }
}

// ── Render proposed changes block ────────────
function renderProposedChanges(changes) {
    const msgs = document.getElementById('ai-messages');

    const wrap = document.createElement('div');
    wrap.className = 'changes-wrap';

    const hdr = document.createElement('div');
    hdr.className = 'changes-header';
    hdr.innerHTML = `<span class="changes-title">📦 ${changes.length} file change${changes.length !== 1 ? 's' : ''} proposed</span>`;

    const applyBtns = [];
    const applyAllBtn = document.createElement('button');
    applyAllBtn.className = 'btn-apply-all';
    applyAllBtn.textContent = 'Apply All';
    applyAllBtn.addEventListener('click', () => applyAllChanges(changes, applyAllBtn, applyBtns));
    hdr.appendChild(applyAllBtn);
    wrap.appendChild(hdr);

    changes.forEach(ch => {
        const row = document.createElement('div');
        row.className = 'change-row';

        const actionIcon  = { create:'✚', edit:'✎', delete:'✕' }[ch.action] || '?';
        const actionColor = { create:'#a6e3a1', edit:'#89b4fa', delete:'#f38ba8' }[ch.action] || '#cdd6f4';
        const lines = ch.content ? ch.content.split('\n').length : 0;

        row.innerHTML = `
            <span class="change-action-icon" style="color:${actionColor};">${actionIcon}</span>
            <span class="change-path" title="${esc(ch.path)}">${esc(ch.path)}</span>
            ${lines > 0 ? `<span class="change-lines">${lines}L</span>` : ''}
        `;

        if (ch.action !== 'delete' && ch.content) {
            const prevBtn = document.createElement('button');
            prevBtn.className = 'btn-preview-chg';
            prevBtn.textContent = 'Preview';
            prevBtn.addEventListener('click', () => previewChange(ch));
            row.appendChild(prevBtn);
        }

        const applyBtn = document.createElement('button');
        applyBtn.className = 'btn-apply-chg';
        applyBtn.textContent = 'Apply';
        applyBtn.addEventListener('click', () => applySingleChange(ch, applyBtn));
        row.appendChild(applyBtn);
        applyBtns.push(applyBtn);

        wrap.appendChild(row);
    });

    msgs.appendChild(wrap);
    msgs.scrollTop = msgs.scrollHeight;
}

// ── Apply a single change ─────────────────────
async function applySingleChange(ch, btn) {
    if (btn.classList.contains('done') || btn.classList.contains('err')) return;
    btn.disabled = true;
    btn.textContent = '⟳';

    let res;
    if (ch.action === 'delete') {
        res = await api('/api/editor/delete.php', {
            method: 'POST', headers: JHDRS(),
            body: JSON.stringify({ path: ch.path }),
        });
    } else {
        res = await api('/api/editor/save-file.php', {
            method: 'POST', headers: JHDRS(),
            body: JSON.stringify({ path: ch.path, content: ch.content }),
        });
    }

    if (res.success) {
        btn.classList.add('done'); btn.textContent = '✓ Done';
        // Update open editor tab if it has this file
        const tab = openTabs.find(t => t.path === ch.path);
        if (tab && tab.model && ch.content != null) {
            tab.model.setValue(ch.content);
            tab.dirty = false; tab.content = ch.content;
            renderTabs(); updateSaveIndicator(tab);
        }
        // Keep wsContext in sync
        if (wsContext && ch.content != null) {
            const fc = wsContext.find(f => f.path === ch.path);
            if (fc) fc.content = ch.content;
            else wsContext.push({ path: ch.path, content: ch.content });
        }
        if (ch.action === 'create' || ch.action === 'delete') refreshTree();
        toast((ch.action === 'delete' ? 'Deleted' : 'Saved') + ': ' + ch.path.split('/').pop());
    } else {
        btn.classList.add('err'); btn.textContent = '✗ Failed';
        btn.disabled = false;
        toast(res.error || 'Apply failed', 'err');
    }
}

async function applyAllChanges(changes, applyAllBtn, applyBtns) {
    applyAllBtn.disabled = true;
    applyAllBtn.textContent = 'Applying…';
    let ok = 0, fail = 0;
    for (let i = 0; i < changes.length; i++) {
        const btn = applyBtns[i];
        if (btn.classList.contains('done')) { ok++; continue; }
        await applySingleChange(changes[i], btn);
        if (btn.classList.contains('done')) ok++; else fail++;
    }
    applyAllBtn.textContent = fail ? `Done (${fail} failed)` : '✓ All Applied';
}

// ── Preview a proposed change ─────────────────
function previewChange(ch) {
    document.getElementById('preview-filepath').textContent = ch.path;
    document.getElementById('preview-code').textContent = ch.content || '';
    document.getElementById('preview-modal').style.display = 'flex';
}
document.getElementById('preview-close').addEventListener('click', () => {
    document.getElementById('preview-modal').style.display = 'none';
});
document.getElementById('preview-modal').addEventListener('click', e => {
    if (e.target === document.getElementById('preview-modal'))
        document.getElementById('preview-modal').style.display = 'none';
});

/* ══════════════════════════════════════════════
   RESIZE HANDLE (drag left/right to widen AI panel)
══════════════════════════════════════════════ */
{
    let dragging = false, startX = 0, startW = 0;
    aiHandle.addEventListener('mousedown', e => {
        dragging = true; startX = e.clientX; startW = aiPanel.offsetWidth;
        aiHandle.classList.add('dragging');
        document.body.style.cursor = 'ew-resize';
        document.body.style.userSelect = 'none';
    });
    document.addEventListener('mousemove', e => {
        if (!dragging) return;
        const delta = startX - e.clientX;  // drag left = wider AI panel
        const newW  = Math.max(220, Math.min(window.innerWidth * .5, startW + delta));
        aiPanel.style.width = newW + 'px';
        if (editor) editor.layout();
    });
    document.addEventListener('mouseup', () => {
        if (dragging) { dragging = false; aiHandle.classList.remove('dragging'); document.body.style.cursor=''; document.body.style.userSelect=''; }
    });
}

/* ══════════════════════════════════════════════
   TOOLBAR BUTTONS
══════════════════════════════════════════════ */
document.getElementById('btn-new-file').addEventListener('click',   () => createItem('file'));
document.getElementById('btn-new-folder').addEventListener('click', () => createItem('dir'));
document.getElementById('btn-save').addEventListener('click', saveCurrentFile);
document.getElementById('btn-refresh-tree').addEventListener('click', refreshTree);

/* ══════════════════════════════════════════════
   KEYBOARD SHORTCUTS
══════════════════════════════════════════════ */
document.addEventListener('keydown', e => {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); saveCurrentFile(); }
    if ((e.ctrlKey || e.metaKey) && e.key === '`') { e.preventDefault(); aiOpen ? closeAI() : openAI(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'w') {
        e.preventDefault();
        if (activeTab) closeTab(activeTab);
    }
});

/* ══════════════════════════════════════════════
   TOAST notifications
══════════════════════════════════════════════ */
function toast(msg, type = 'ok') {
    const el = document.createElement('div');
    const colors = { ok:'#a6e3a1', err:'#f38ba8', warn:'#fab387' };
    el.style.cssText = `position:fixed;bottom:1.2rem;right:1.2rem;background:#313244;color:${colors[type]||colors.ok};
        padding:.45rem .9rem;border-radius:.4rem;font-size:.75rem;font-weight:600;
        box-shadow:0 4px 16px rgba(0,0,0,.5);z-index:9999;transition:opacity .3s;`;
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => { el.style.opacity='0'; setTimeout(() => el.remove(), 300); }, 2500);
}

/* ══════════════════════════════════════════════
   UTILITY
══════════════════════════════════════════════ */
function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ══════════════════════════════════════════════
   MONACO INIT
══════════════════════════════════════════════ */
require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.45.0/min/vs' } });
require(['vs/editor/editor.main'], function () {

    monaco.editor.defineTheme('sf-dark', {
        base: 'vs-dark', inherit: true,
        rules: [
            { token:'comment',   foreground:'6c7086', fontStyle:'italic' },
            { token:'keyword',   foreground:'cba6f7' },
            { token:'string',    foreground:'a6e3a1' },
            { token:'number',    foreground:'fab387' },
            { token:'type',      foreground:'89dceb' },
            { token:'function',  foreground:'89b4fa' },
            { token:'variable',  foreground:'cdd6f4' },
            { token:'operator',  foreground:'89dceb' },
        ],
        colors: {
            'editor.background':             '#1e1e2e',
            'editor.foreground':             '#cdd6f4',
            'editorLineNumber.foreground':   '#45475a',
            'editorCursor.foreground':       '#f5c2e7',
            'editor.selectionBackground':    '#45475a',
            'editor.lineHighlightBackground':'#181825',
            'editorWidget.background':       '#181825',
            'editorWidget.border':           '#313244',
            'input.background':              '#11111b',
            'input.foreground':              '#cdd6f4',
            'scrollbarSlider.background':    '#45475a55',
            'scrollbarSlider.hoverBackground':'#45475a',
        },
    });

    editor = monaco.editor.create(document.getElementById('editor-container'), {
        theme:                   'sf-dark',
        automaticLayout:         true,
        fontSize:                14,
        fontFamily:              "'Fira Code','JetBrains Mono','Cascadia Code',Consolas,monospace",
        fontLigatures:           true,
        lineNumbers:             'on',
        minimap:                 { enabled: true, scale: 1 },
        scrollBeyondLastLine:    false,
        wordWrap:                'off',
        tabSize:                 4,
        detectIndentation:       true,
        renderWhitespace:        'selection',
        bracketPairColorization: { enabled: true },
        guides:                  { bracketPairs: true },
        smoothScrolling:         true,
        cursorBlinking:          'phase',
        cursorSmoothCaretAnimation: 'on',
        formatOnPaste:           true,
        suggest:                 { showIcons: true, preview: true },
        quickSuggestions:        { other: true, comments: false, strings: true },
        inlineSuggest:           { enabled: true },
    });

    editor.onDidChangeModelContent(() => {
        const tab = openTabs.find(t => t.path === activeTab);
        if (tab && !tab.dirty) {
            tab.dirty = true; renderTabs(); updateSaveIndicator(tab);
        }
    });

    // Ctrl+S inside Monaco
    editor.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyS, saveCurrentFile);

    if (pendingFile) {
        pendingFile.model = monaco.editor.createModel(pendingFile.content, langFor(pendingFile.path));
        editor.setModel(pendingFile.model);
        showEditorArea(); editor.layout(); editor.focus();
        pendingFile = null;
    }
});

/* ── Initial load ────────────────────────────── */
refreshTree();

})();
</script>

<?php
// Custom page close — bypass footer.php (editor must fill full height, no standard footer bar)
?>
</div><!-- close .flex-1.flex-col from header.php -->
</div><!-- close .flex.min-h-screen from header.php -->

<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
