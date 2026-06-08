<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/db.php';

$clients_count = $pdo->query("SELECT COUNT(*) FROM clients")->fetchColumn();
$archives_count = $pdo->query("SELECT COUNT(*) FROM archives")->fetchColumn();
$total_storage = $pdo->query("SELECT SUM(size) FROM archives")->fetchColumn();

$recent_clients = $pdo->query("SELECT * FROM clients ORDER BY registration_date DESC LIMIT 5");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .icon-btn {
            padding: 0.5rem;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: var(--text-secondary);
            transition: color 0.2s;
            margin: 0 0.25rem;
        }
        .icon-btn:hover {
            color: var(--accent-color);
        }
        .icon-btn.danger:hover {
            color: #dc3545;
        }
        .btn-group {
            display: flex;
            gap: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container wide">
        <nav class="nav">
            <a href="index.php" style="color:var(--accent-color); font-weight:600;">Dashboard</a>
            <a href="profile.php">Settings</a>
            <a href="logout.php" style="margin-left:auto;">Logout</a>
        </nav>

        <h1>Overview</h1>
        <p style="color:var(--text-secondary); margin-bottom: 2rem;">
            Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>.
        </p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: var(--radius); background: white;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.9rem; font-weight: normal;">Total Clients</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: 600; color: var(--accent-color);"><?php echo $clients_count; ?></p>
            </div>
            <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: var(--radius); background: white;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.9rem; font-weight: normal;">Total Archives</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: 600; color: var(--accent-color);"><?php echo $archives_count; ?></p>
            </div>
            <div style="padding: 1.5rem; border: 1px solid var(--border-color); border-radius: var(--radius); background: white;">
                <h3 style="margin: 0 0 0.5rem 0; color: var(--text-secondary); font-size: 0.9rem; font-weight: normal;">Total Storage</h3>
                <p style="margin: 0; font-size: 2rem; font-weight: 600; color: var(--accent-color);"><?php echo number_format($total_storage ?? 0, 1); ?> GB</p>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2 style="margin: 0;">Clients</h2>
            <div class="btn-group">
                <button class="btn btn-secondary" onclick="generateReport()">
                    <i class="fa-solid fa-file-pdf"></i> Generate Report
                </button>
                <button class="btn btn-primary" onclick="openAddClientModal()">Add Client</button>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid var(--border-color); border-radius: var(--radius);">
                <thead>
                    <tr style="background: #f9f9f9; border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 1rem; text-align: left;">Company Name</th>
                        <th style="padding: 1rem; text-align: left;">Cost per GB</th>
                        <th style="padding: 1rem; text-align: left;">Registration Date</th>
                        <th style="padding: 1rem; text-align: left;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recent_clients->fetch()): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($row['company_name']); ?></td>
                        <td style="padding: 1rem;">$<?php echo number_format($row['cost_per_archive_gb'], 2); ?></td>
                        <td style="padding: 1rem;"><?php echo htmlspecialchars($row['registration_date']); ?></td>
                        <td style="padding: 1rem;">
                            <button class="icon-btn" onclick="viewClient(<?php echo $row['id']; ?>)" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <button class="icon-btn danger" onclick="deleteClient(<?php echo $row['id']; ?>)" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Client Modal -->
    <div id="clientModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeClientModal()">&times;</span>
            <h2 id="clientModalTitle">Add Client</h2>
            <form id="clientForm">
                <input type="hidden" id="client_id" name="client_id">
                <div class="form-group">
                    <label>Company Name</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>
                <div class="form-group">
                    <label>Cost per GB ($)</label>
                    <input type="number" step="0.01" id="cost_per_archive_gb" name="cost_per_archive_gb" required>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-secondary" onclick="closeClientModal()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openAddClientModal() {
            document.getElementById('clientModalTitle').textContent = 'Add Client';
            document.getElementById('clientForm').reset();
            document.getElementById('client_id').value = '';
            document.getElementById('clientModal').style.display = 'block';
        }

        function closeClientModal() {
            document.getElementById('clientModal').style.display = 'none';
        }

        function viewClient(clientId) {
            window.location.href = 'client_view.php?id=' + clientId;
        }

        function deleteClient(clientId) {
            if (confirm('Are you sure you want to delete this client? This will also delete all associated archives.')) {
                fetch('api/delete_client.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ client_id: clientId })
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

        function generateReport() {
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;

            fetch('api/generate_report.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => {
                if (response.ok && response.headers.get('content-type').includes('application/pdf')) {
                    return response.blob();
                }
                return response.json().then(data => {
                    throw new Error(data.message || 'Failed to generate report');
                });
            })
            .then(blob => {
                // Create download link for PDF
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'revenue_report_' + new Date().toISOString().split('T')[0] + '.pdf';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            })
            .catch(error => {
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Error: ' + error.message);
            });
        }

        window.onclick = function(event) {
            const modal = document.getElementById('clientModal');
            if (event.target == modal) {
                closeClientModal();
            }
        }
    </script>
</body>
</html>