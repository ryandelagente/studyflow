<?php
/**
 * StudyFlow — Sample Data Seeder
 * Run once via browser: http://localhost/studyflow/sql/seed.php
 * Creates 2 tenants with 2 users each plus sample content.
 * DELETE this file after running it.
 */

require_once __DIR__ . '/../config.php';

if (!$link) { die('DB connection failed.'); }

mysqli_begin_transaction($link);

try {

    // ---------------------------------------------------------------
    // CLEAN UP any previous seed data
    // ---------------------------------------------------------------
    $tables = ['chat_messages','ai_chats','study_sessions','flashcards',
               'flashcard_collections','todos','study_goals','notes',
               'assignments','events','contacts','spreadsheets','users','tenants'];
    foreach ($tables as $t) {
        mysqli_query($link, "DELETE FROM `$t` WHERE 1");
        mysqli_query($link, "ALTER TABLE `$t` AUTO_INCREMENT = 1");
    }

    // ---------------------------------------------------------------
    // TENANT 1 — Alice's workspace (school student)
    // ---------------------------------------------------------------
    $r = mysqli_query($link, "INSERT INTO tenants (name, owner_id) VALUES ('Alice\\'s Workspace', 0)");
    $t1 = mysqli_insert_id($link);

    // Admin: Alice
    $pw_alice = password_hash('alice123', PASSWORD_DEFAULT);
    mysqli_query($link, "INSERT INTO users (username, email, password, role, tenant_id)
        VALUES ('Alice Reyes', 'alice@demo.com', '$pw_alice', 'admin', $t1)");
    $u_alice = mysqli_insert_id($link);

    // Member: Bob (belongs to same tenant/school)
    $pw_bob = password_hash('bob123', PASSWORD_DEFAULT);
    mysqli_query($link, "INSERT INTO users (username, email, password, role, tenant_id)
        VALUES ('Bob Cruz', 'bob@demo.com', '$pw_bob', 'member', $t1)");
    $u_bob = mysqli_insert_id($link);

    // Fix owner_id now that we have Alice's ID
    mysqli_query($link, "UPDATE tenants SET owner_id = $u_alice WHERE id = $t1");

    // ---------------------------------------------------------------
    // TENANT 2 — Carlos's workspace (different school)
    // ---------------------------------------------------------------
    $r = mysqli_query($link, "INSERT INTO tenants (name, owner_id) VALUES ('Carlos\\' Workspace', 0)");
    $t2 = mysqli_insert_id($link);

    $pw_carlos = password_hash('carlos123', PASSWORD_DEFAULT);
    mysqli_query($link, "INSERT INTO users (username, email, password, role, tenant_id)
        VALUES ('Carlos Tan', 'carlos@demo.com', '$pw_carlos', 'admin', $t2)");
    $u_carlos = mysqli_insert_id($link);

    $pw_diana = password_hash('diana123', PASSWORD_DEFAULT);
    mysqli_query($link, "INSERT INTO users (username, email, password, role, tenant_id)
        VALUES ('Diana Santos', 'diana@demo.com', '$pw_diana', 'member', $t2)");
    $u_diana = mysqli_insert_id($link);

    mysqli_query($link, "UPDATE tenants SET owner_id = $u_carlos WHERE id = $t2");

    // ---------------------------------------------------------------
    // SAMPLE DATA — Alice (tenant 1)
    // ---------------------------------------------------------------

    // Notes
    mysqli_query($link, "INSERT INTO notes (user_id, tenant_id, title, content) VALUES
        ($u_alice, $t1, 'Biology Chapter 3', '<p>Mitosis stages: Prophase, Metaphase, Anaphase, Telophase.</p>'),
        ($u_alice, $t1, 'Math Formulas', '<p>Quadratic: x = (-b ± √(b²-4ac)) / 2a</p>'),
        ($u_bob,   $t1, 'History Notes',  '<p>World War II ended in 1945 with Allied victory.</p>')");

    // Todos
    mysqli_query($link, "INSERT INTO todos (user_id, tenant_id, task, is_completed) VALUES
        ($u_alice, $t1, 'Read Biology textbook Chapter 4', 0),
        ($u_alice, $t1, 'Submit Math homework', 0),
        ($u_alice, $t1, 'Review flashcards before exam', 1),
        ($u_bob,   $t1, 'Write History essay draft', 0)");

    // Study Goals
    mysqli_query($link, "INSERT INTO study_goals (user_id, tenant_id, title, description, target_date, is_completed) VALUES
        ($u_alice, $t1, 'Pass Biology Midterm', 'Score at least 85%', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 0),
        ($u_alice, $t1, 'Finish Math Problem Sets', 'Complete all 5 sets', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 0),
        ($u_bob,   $t1, 'Complete History Research', 'Finish 10-page paper', DATE_ADD(CURDATE(), INTERVAL 21 DAY), 0)");

    // Flashcard collections
    mysqli_query($link, "INSERT INTO flashcard_collections (user_id, tenant_id, title, description) VALUES
        ($u_alice, $t1, 'Biology Terms', 'Key vocabulary for Bio exam'),
        ($u_alice, $t1, 'Math Formulas', 'Essential formulas to memorize')");
    $fc1 = mysqli_insert_id($link) - 1; // collection 1 id
    $fc2 = mysqli_insert_id($link);     // collection 2 id

    // Re-fetch actual IDs
    $res = mysqli_query($link, "SELECT id FROM flashcard_collections WHERE user_id = $u_alice ORDER BY id ASC");
    $fc_ids = [];
    while ($row = mysqli_fetch_assoc($res)) { $fc_ids[] = $row['id']; }
    $fc1 = $fc_ids[0]; $fc2 = $fc_ids[1];

    mysqli_query($link, "INSERT INTO flashcards (collection_id, question, answer) VALUES
        ($fc1, 'What is mitosis?', 'Cell division producing two genetically identical daughter cells.'),
        ($fc1, 'What is meiosis?', 'Cell division producing four genetically unique gametes.'),
        ($fc2, 'Area of a circle?', 'π × r²'),
        ($fc2, 'Pythagorean theorem?', 'a² + b² = c²')");

    // Calendar events
    mysqli_query($link, "INSERT INTO events (user_id, tenant_id, title, description, start_time, end_time) VALUES
        ($u_alice, $t1, 'Biology Exam', 'Chapter 1-3 midterm', DATE_ADD(NOW(), INTERVAL 3 DAY), DATE_ADD(NOW(), INTERVAL 3 DAY)),
        ($u_alice, $t1, 'Study Group', 'Review session with classmates', DATE_ADD(NOW(), INTERVAL 1 DAY), DATE_ADD(NOW(), INTERVAL 1 DAY))");

    // ---------------------------------------------------------------
    // SAMPLE DATA — Carlos (tenant 2)
    // ---------------------------------------------------------------

    // Notes — completely separate, never visible to Alice
    mysqli_query($link, "INSERT INTO notes (user_id, tenant_id, title, content) VALUES
        ($u_carlos, $t2, 'Physics Notes', '<p>Newton\\'s laws of motion — inertia, F=ma, action-reaction.</p>'),
        ($u_diana,  $t2, 'Chemistry Lab', '<p>Titration experiment results for Experiment 5.</p>')");

    // Todos
    mysqli_query($link, "INSERT INTO todos (user_id, tenant_id, task, is_completed) VALUES
        ($u_carlos, $t2, 'Finish Physics problem set', 0),
        ($u_carlos, $t2, 'Submit lab report', 0),
        ($u_diana,  $t2, 'Prepare chemistry presentation', 0)");

    // Study Goals
    mysqli_query($link, "INSERT INTO study_goals (user_id, tenant_id, title, description, target_date, is_completed) VALUES
        ($u_carlos, $t2, 'Master Newtonian Mechanics', 'Score 90%+ on Physics final', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 0),
        ($u_diana,  $t2, 'Complete Organic Chemistry', 'Finish all lab practicals', DATE_ADD(CURDATE(), INTERVAL 20 DAY), 0)");

    mysqli_commit($link);

    // Summary
    echo "<pre style='font-family:monospace; font-size:14px; padding:20px'>";
    echo "=== StudyFlow Sample Data Seeded ===\n\n";

    echo "TENANT 1 — Alice's Workspace (tenant_id: $t1)\n";
    echo "  Admin : alice@demo.com  / alice123  (user_id: $u_alice)\n";
    echo "  Member: bob@demo.com    / bob123    (user_id: $u_bob)\n";
    echo "  Data  : 3 notes, 4 todos, 3 goals, 2 flashcard sets, 2 events\n\n";

    echo "TENANT 2 — Carlos' Workspace (tenant_id: $t2)\n";
    echo "  Admin : carlos@demo.com / carlos123 (user_id: $u_carlos)\n";
    echo "  Member: diana@demo.com  / diana123  (user_id: $u_diana)\n";
    echo "  Data  : 2 notes, 3 todos, 2 goals\n\n";

    echo "Isolation test:\n";
    echo "  Log in as alice@demo.com → sees only Tenant 1 data\n";
    echo "  Log in as carlos@demo.com → sees only Tenant 2 data\n";
    echo "  They cannot see each other's notes, todos, goals, etc.\n\n";

    echo "Login URL: http://localhost/studyflow/login.php\n\n";
    echo "⚠  DELETE this file after use: sql/seed.php\n";
    echo "</pre>";

} catch (Exception $e) {
    mysqli_rollback($link);
    echo "<pre>Error: " . $e->getMessage() . "</pre>";
}
?>
