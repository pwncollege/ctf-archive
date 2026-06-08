<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/db.php';

$client_id = $_GET['id'] ?? 0;
$client = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$client->execute([$client_id]);
$client = $client->fetch();

if (!$client) {
    header("Location: index.php");
    exit;
}

$archives = $pdo->prepare("SELECT * FROM archives WHERE client_id = ? ORDER BY creation_date DESC");
$archives->execute([$client_id]);
$archives_data = $archives->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($client['company_name']); ?> - Client Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: var(--radius);
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover { color: #000; }
        .form-group {
            margin-bottom: 1rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 1rem;
        }
        .btn-primary {
            background-color: var(--accent-color);
            color: white;
        }
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }
        .info-card {
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background: white;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container wide">
        <nav class="nav">
            <a href="index.php">Dashboard</a>
            <a href="profile.php">Settings</a>
            <a href="logout.php" style="margin-left:auto;">Logout</a>
        </nav>

        <div style="margin-bottom: 2rem;">
            <a href="index.php" style="color: var(--accent-color);">← Back to Dashboard</a>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h1><?php echo htmlspecialchars($client['company_name']); ?></h1>
            <button class="btn btn-primary" onclick="openEditClientModal()">Edit Client</button>
        </div>

        <div class="info-card">
            <p><strong>Cost per GB:</strong> $<?php echo number_format($client['cost_per_archive_gb'], 2); ?></p>
            <p><strong>Registration Date:</strong> <?php echo htmlspecialchars($client['registration_date']); ?></p>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Archives</h2>
            <button class="btn btn-primary" onclick="openAddArchiveModal()">Add Archive</button>
        </div>

        <div style="overflow-x: auto;">
            <?php if (empty($archives_data)): ?>
            <div style="background: white; border: 1px solid var(--border-color); border-radius: var(--radius); padding: 3rem; text-align: center; color: #6c757d;">
                <p style="margin: 0; font-size: 1.1rem;">No archives yet</p>
                <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">Click "Add Archive" to create an archive</p>
            </div>
            <?php else: ?>
            <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid var(--border-color); border-radius: var(--radius);">
                <thead>
                    <tr style="background: #f9f9f9; border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 1rem; text-align: left;">Archive</th>
                        <th style="padding: 1rem; text-align: left;">Size (GB)</th>
                        <th style="padding: 1rem; text-align: left;">Created</th>
                        <th style="padding: 1rem; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($archives_data as $archive): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($archive['id']); ?></td>
                        <td style="padding: 1rem;"><?php echo number_format($archive['size'], 2); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($archive['creation_date']); ?></td>
                        <td style="padding: 1rem;">
                            <button class="btn btn-primary action-btn" onclick='openEditArchiveModal(<?php echo json_encode($archive); ?>)'>Edit</button>
                            <button class="btn btn-danger action-btn" onclick="deleteArchive(<?php echo $archive['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Edit Client Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeClientModal()">&times;</span>
            <h2>Edit Client</h2>
            <form id="clientForm">
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($client['company_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Cost per GB ($)</label>
                    <input type="number" step="0.01" name="cost_per_archive_gb" value="<?php echo $client['cost_per_archive_gb']; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Add/Edit Archive Modal -->
    <div id="archiveModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeArchiveModal()">&times;</span>
            <h2 id="archiveModalTitle">Add Archive</h2>
            <form id="archiveForm">
                <input type="hidden" id="archive_id" name="archive_id">
                <input type="hidden" name="client_id" value="<?php echo $client_id; ?>">
                <div class="form-group">
                    <label>Size (GB)</label>
                    <input type="number" step="0.01" id="archive_size" name="size" required>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeArchiveModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openEditClientModal() {
            document.getElementById('clientModal').style.display = 'block';
        }

        function closeClientModal() {
            document.getElementById('clientModal').style.display = 'none';
        }

        function openAddArchiveModal() {
            document.getElementById('archiveModalTitle').textContent = 'Add Archive';
            document.getElementById('archiveForm').reset();
            document.getElementById('archive_id').value = '';
            document.getElementById('archiveModal').style.display = 'block';
        }

        function openEditArchiveModal(archive) {
            document.getElementById('archiveModalTitle').textContent = 'Edit Archive';
            document.getElementById('archive_id').value = archive.id;
            document.getElementById('archive_size').value = archive.size;
            document.getElementById('archiveModal').style.display = 'block';
        }

        function closeArchiveModal() {
            document.getElementById('archiveModal').style.display = 'none';
        }

        function deleteArchive(archiveId) {
            if (confirm('Are you sure you want to delete this archive?')) {
                fetch('api/delete_archive.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ archive_id: archiveId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        document.getElementById('clientForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('api/save_client.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        document.getElementById('archiveForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('api/save_archive.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        });

        window.onclick = function(event) {
            const clientModal = document.getElementById('clientModal');
            const archiveModal = document.getElementById('archiveModal');
            if (event.target == clientModal) {
                closeClientModal();
            }
            if (event.target == archiveModal) {
                closeArchiveModal();
            }
        }
    </script>
</body>
</html>
