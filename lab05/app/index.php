<?php
declare(strict_types=1);

require __DIR__ . '/db.php';

$errors = [];
$notice = '';
$config = appConfig();

try {
    $writeDb = masterPdo();
} catch (Throwable $e) {
    $errors[] = 'Cannot connect to master: ' . $e->getMessage();
}

try {
    $readDb = replicaPdo();
} catch (Throwable $e) {
    $errors[] = 'Cannot connect to read replica: ' . $e->getMessage();
    // Fallback to master for reads so the UI still works during replica issues.
    $readDb = $writeDb ?? null;
}

function clean(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$statusOptions = ['pending', 'in progress', 'done'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($writeDb)) {
    $action = $_POST['action'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $status = trim($_POST['status'] ?? '');
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    if ($action === 'create') {
        if ($title === '') {
            $errors[] = 'Title is required to create a task.';
        } elseif (!$categoryId) {
            $errors[] = 'Select a category.';
        } elseif (!in_array($status, $statusOptions, true)) {
            $errors[] = 'Invalid status value.';
        } else {
            createTodo($writeDb, $title, $categoryId, $status);
            $notice = 'Todo created via master.';
        }
    } elseif ($action === 'update') {
        if (!$id) {
            $errors[] = 'Missing todo id for update.';
        } elseif ($title === '') {
            $errors[] = 'Title is required to update a task.';
        } elseif (!$categoryId) {
            $errors[] = 'Select a category.';
        } elseif (!in_array($status, $statusOptions, true)) {
            $errors[] = 'Invalid status value.';
        } else {
            updateTodo($writeDb, $id, $title, $categoryId, $status);
            $notice = 'Todo updated via master.';
        }
    } elseif ($action === 'delete') {
        if (!$id) {
            $errors[] = 'Missing todo id for delete.';
        } else {
            deleteTodo($writeDb, $id);
            $notice = 'Todo deleted via master.';
        }
    }
}

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$editingTodo = null;

if ($editId && $readDb) {
    $editingTodo = fetchTodo($readDb, $editId);
    if (!$editingTodo) {
        $errors[] = 'Todo not found in read replica (eventual consistency?).';
    }
}

$categories = [];
if ($readDb) {
    try {
        $categories = fetchCategories($readDb);
    } catch (Throwable $e) {
        $errors[] = 'Could not load categories from read replica: ' . $e->getMessage();
    }
}

$todos = [];
if ($readDb) {
    try {
        $todos = fetchTodos($readDb);
    } catch (Throwable $e) {
        $errors[] = 'Could not load todos from read replica: ' . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDS Todo Demo (Master + Read Replica)</title>
    <style>
        :root {
            color-scheme: light;
            font-family: "Segoe UI", sans-serif;
            --accent: #0d6efd;
            --bg: #f3f6fb;
            --card: #ffffff;
            --border: #d9e2ec;
        }
        body {
            margin: 0;
            background: radial-gradient(circle at 20% 20%, #f8fbff 0, #eef3fb 25%, #e5edf7 50%, #f3f6fb 100%);
            color: #111827;
        }
        .shell {
            max-width: 1080px;
            margin: 0 auto;
            padding: 32px 20px 48px;
        }
        h1 {
            margin: 0 0 12px;
            letter-spacing: -0.3px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.06);
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
        }
        input[type="text"], select {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #f9fbff;
            font-size: 14px;
        }
        button, .link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: transform 120ms ease, box-shadow 120ms ease, background 120ms ease;
        }
        button:hover, .link:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(13, 110, 253, 0.25);
        }
        button.secondary, .link.secondary {
            background: #e5e7eb;
            color: #111827;
        }
        .muted { color: #6b7280; font-size: 14px; }
        .chips {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 10px 0 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .table th, .table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: left;
        }
        .table th {
            color: #6b7280;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            background: #fef3c7;
            border: 1px solid #facc15;
            color: #92400e;
            margin: 10px 0 0;
        }
        .alert.error {
            background: #fee2e2;
            border-color: #f87171;
            color: #991b1b;
        }
    </style>
</head>
<body>
<div class="shell">
    <h1>RDS Todo Demo (Master + Read Replica)</h1>
    <p class="muted">Writes go to master (<?php echo clean($config['master_host']); ?>), reads come from replica (<?php echo clean($config['replica_host']); ?>). Using database "<?php echo clean($config['db_name']); ?>".</p>

    <?php if ($notice): ?>
        <div class="alert"><?php echo clean($notice); ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="alert error">
            <?php foreach ($errors as $err): ?>
                <div><?php echo clean($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="cards">
        <div class="card">
            <h3><?php echo $editingTodo ? 'Edit Todo' : 'Create Todo'; ?></h3>
            <form method="post">
                <input type="hidden" name="action" value="<?php echo $editingTodo ? 'update' : 'create'; ?>">
                <?php if ($editingTodo): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$editingTodo['id']; ?>">
                <?php endif; ?>
                <label for="title">Title</label>
                <input id="title" name="title" type="text" maxlength="200" required
                       value="<?php echo $editingTodo ? clean($editingTodo['title']) : ''; ?>">

                <label for="category_id" style="margin-top:12px;">Category</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Select category</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo (int)$category['id']; ?>"
                            <?php echo $editingTodo && (int)$editingTodo['category_id'] === (int)$category['id'] ? 'selected' : ''; ?>>
                            <?php echo clean($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="status" style="margin-top:12px;">Status</label>
                <select id="status" name="status" required>
                    <option value="">Select status</option>
                    <?php foreach ($statusOptions as $opt): ?>
                        <option value="<?php echo clean($opt); ?>" <?php echo $editingTodo && $editingTodo['status'] === $opt ? 'selected' : ''; ?>>
                            <?php echo ucfirst(clean($opt)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div class="chips">
                    <button type="submit"><?php echo $editingTodo ? 'Update via Master' : 'Create via Master'; ?></button>
                    <?php if ($editingTodo): ?>
                        <a class="link secondary" href="<?php echo clean($_SERVER['PHP_SELF']); ?>">Cancel</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="card">
            <h3>Existing Todos (Read Replica)</h3>
            <?php if (!$todos): ?>
                <p class="muted">No todos yet. Add one to see replication in action.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($todos as $todo): ?>
                        <tr>
                            <td><?php echo (int)$todo['id']; ?></td>
                            <td><?php echo clean($todo['title']); ?></td>
                            <td><?php echo clean($todo['category_name']); ?></td>
                            <td><?php echo clean($todo['status']); ?></td>
                            <td style="white-space: nowrap; display:flex; gap:6px;">
                                <a class="link secondary" href="<?php echo clean($_SERVER['PHP_SELF']); ?>?edit=<?php echo (int)$todo['id']; ?>">Edit</a>
                                <form method="post" onsubmit="return confirm('Delete todo #<?php echo (int)$todo['id']; ?>?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$todo['id']; ?>">
                                    <button type="submit" class="secondary" style="background:#fee2e2;color:#991b1b;">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
