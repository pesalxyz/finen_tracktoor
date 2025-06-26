<?php
// index.php - Finance Tracker for Vercel with SQLite
session_start();

// SQLite database configuration
$db_path = __DIR__ . '/db/finance_tracker.sqlite';
$db_exists = file_exists($db_path);

// Create database directory if it doesn't exist
if (!file_exists(__DIR__ . '/db')) {
    mkdir(__DIR__ . '/db', 0755, true);
}

$conn = null;
$error_message = "";
$success_message = "";

// Connect to SQLite database
try {
    $conn = new PDO("sqlite:$db_path");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Initialize database tables if they don't exist
    if (!$db_exists) {
        $conn->exec("
            CREATE TABLE IF NOT EXISTS transactions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                type TEXT NOT NULL CHECK(type IN ('income', 'withdrawal', 'savings')),
                amount REAL NOT NULL,
                description TEXT,
                transaction_date TEXT NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $conn->exec("
            CREATE TABLE IF NOT EXISTS daily_targets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                target_amount REAL NOT NULL,
                target_date TEXT UNIQUE NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Insert some sample data if needed
        // $conn->exec("INSERT INTO daily_targets (target_amount, target_date) VALUES (100.00, date('now'))");
    }
} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}

?>

<?php
// index.php - Finance Tracker (Fixed Reset Target Issue)
session_start();

// Database configuration
$host = "localhost";
$db_name = "finance_tracker";
$username = "root";
$password = "";

$conn = null;
$error_message = "";
$success_message = "";

// Connect to database
try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $error_message = "Database connection failed: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $conn) {
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            
            case 'add_income':
                try {
                    $stmt = $conn->prepare("INSERT INTO transactions (type, amount, description, transaction_date) VALUES ('income', :amount, :description, :date)");
                    $stmt->bindParam(':amount', $_POST['amount']);
                    $stmt->bindParam(':description', $_POST['description']);
                    $stmt->bindParam(':date', $_POST['date']);
                    $stmt->execute();
                    $success_message = "Income berhasil ditambahkan!";
                } catch (Exception $e) {
                    $error_message = "Error adding income: " . $e->getMessage();
                }
                break;
                
            case 'add_withdrawal':
                try {
                    $stmt = $conn->prepare("INSERT INTO transactions (type, amount, description, transaction_date) VALUES ('withdrawal', :amount, :description, :date)");
                    $stmt->bindParam(':amount', $_POST['amount']);
                    $stmt->bindParam(':description', $_POST['description']);
                    $stmt->bindParam(':date', $_POST['date']);
                    $stmt->execute();
                    $success_message = "Withdrawal berhasil ditambahkan!";
                } catch (Exception $e) {
                    $error_message = "Error adding withdrawal: " . $e->getMessage();
                }
                break;
                
            case 'add_savings':
                try {
                    $stmt = $conn->prepare("INSERT INTO transactions (type, amount, description, transaction_date) VALUES ('savings', :amount, :description, :date)");
                    $stmt->bindParam(':amount', $_POST['amount']);
                    $stmt->bindParam(':description', $_POST['description']);
                    $stmt->bindParam(':date', $_POST['date']);
                    $stmt->execute();
                    $success_message = "Tabungan berhasil ditambahkan!";
                } catch (Exception $e) {
                    $error_message = "Error adding savings: " . $e->getMessage();
                }
                break;
                
            case 'set_target':
                try {
                    $today = date('Y-m-d');
                    
                    // Check if target exists for today
                    $check_stmt = $conn->prepare("SELECT id FROM daily_targets WHERE target_date = :date");
                    $check_stmt->bindParam(':date', $today);
                    $check_stmt->execute();
                    
                    if ($check_stmt->rowCount() > 0) {
                        // Update existing target
                        $stmt = $conn->prepare("UPDATE daily_targets SET target_amount = :amount WHERE target_date = :date");
                    } else {
                        // Insert new target
                        $stmt = $conn->prepare("INSERT INTO daily_targets (target_amount, target_date) VALUES (:amount, :date)");
                    }
                    
                    $stmt->bindParam(':amount', $_POST['target_amount']);
                    $stmt->bindParam(':date', $today);
                    $stmt->execute();
                    $success_message = "Target harian berhasil diset!";
                } catch (Exception $e) {
                    $error_message = "Error setting target: " . $e->getMessage();
                }
                break;
                
            case 'reset_target':
                try {
                    $today = date('Y-m-d');
                    
                    // Delete today's target or set to 0
                    $stmt = $conn->prepare("DELETE FROM daily_targets WHERE target_date = :date");
                    $stmt->bindParam(':date', $today);
                    $stmt->execute();
                    
                    $success_message = "Target hari ini berhasil direset!";
                } catch (Exception $e) {
                    $error_message = "Error resetting target: " . $e->getMessage();
                }
                break;
                
            case 'reset_all_targets':
                try {
                    // Delete all targets
                    $stmt = $conn->prepare("DELETE FROM daily_targets");
                    $stmt->execute();
                    
                    $success_message = "Semua target berhasil direset!";
                } catch (Exception $e) {
                    $error_message = "Error resetting all targets: " . $e->getMessage();
                }
                break;
                
            case 'edit_transaction':
                try {
                    $stmt = $conn->prepare("UPDATE transactions SET type = :type, amount = :amount, description = :description, transaction_date = :date WHERE id = :id");
                    $stmt->bindParam(':type', $_POST['edit_type']);
                    $stmt->bindParam(':amount', $_POST['edit_amount']);
                    $stmt->bindParam(':description', $_POST['edit_description']);
                    $stmt->bindParam(':date', $_POST['edit_date']);
                    $stmt->bindParam(':id', $_POST['transaction_id']);
                    $stmt->execute();
                    
                    $success_message = "Transaksi berhasil diupdate!";
                } catch (Exception $e) {
                    $error_message = "Error updating transaction: " . $e->getMessage();
                }
                break;
                
            case 'delete_transaction':
                try {
                    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = :id");
                    $stmt->bindParam(':id', $_POST['transaction_id']);
                    $stmt->execute();
                    
                    $success_message = "Transaksi berhasil dihapus!";
                } catch (Exception $e) {
                    $error_message = "Error deleting transaction: " . $e->getMessage();
                }
                break;
        }
    }
}

// Calculate dashboard data
$total_income = 0;
$total_withdrawals = 0;
$total_savings = 0;
$today_income = 0;
$daily_target = 0;
$is_carried_over = false;
$transactions = array();

if ($conn) {
    try {
        // Get summary data
        $summary_stmt = $conn->prepare("SELECT type, SUM(amount) as total_amount FROM transactions GROUP BY type");
        $summary_stmt->execute();
        
        while ($row = $summary_stmt->fetch(PDO::FETCH_ASSOC)) {
            switch ($row['type']) {
                case 'income':
                    $total_income = $row['total_amount'];
                    break;
                case 'withdrawal':
                    $total_withdrawals = $row['total_amount'];
                    break;
                case 'savings':
                    $total_savings = $row['total_amount'];
                    break;
            }
        }
        
        // Get today's income
        $today = date('Y-m-d');
        $today_stmt = $conn->prepare("SELECT SUM(amount) as today_income FROM transactions WHERE transaction_date = :today AND type = 'income'");
        $today_stmt->bindParam(':today', $today);
        $today_stmt->execute();
        $today_result = $today_stmt->fetch(PDO::FETCH_ASSOC);
        $today_income = $today_result['today_income'] ?: 0;
        
        // Get today's target dengan logika reset yang benar
        $target_stmt = $conn->prepare("SELECT target_amount FROM daily_targets WHERE target_date = :today");
        $target_stmt->bindParam(':today', $today);
        $target_stmt->execute();
        $target_result = $target_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($target_result) {
            // Ada target spesifik untuk hari ini
            $daily_target = $target_result['target_amount'];
            $is_carried_over = false;
        } else {
            // Tidak ada target hari ini
            // Cek apakah user baru reset target (dari session atau parameter)
            $user_just_reset = isset($_POST['action']) && ($_POST['action'] === 'reset_target' || $_POST['action'] === 'reset_all_targets');
            
            if ($user_just_reset) {
                // User baru reset, set target = 0
                $daily_target = 0;
                $is_carried_over = false;
            } else {
                // Carry over dari target terakhir (exclude hari ini)
                $last_target_stmt = $conn->prepare("SELECT target_amount FROM daily_targets WHERE target_date < :today ORDER BY target_date DESC LIMIT 1");
                $last_target_stmt->bindParam(':today', $today);
                $last_target_stmt->execute();
                $last_target_result = $last_target_stmt->fetch(PDO::FETCH_ASSOC);
                $daily_target = $last_target_result ? $last_target_result['target_amount'] : 0;
                $is_carried_over = $last_target_result ? true : false;
            }
        }
        
        // Get recent transactions
        $trans_stmt = $conn->prepare("SELECT * FROM transactions ORDER BY created_at DESC LIMIT 10");
        $trans_stmt->execute();
        $transactions = $trans_stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        $error_message = "Error loading data: " . $e->getMessage();
    }
}

// Calculate derived values - FIXED LOGIC
$total_balance = $total_income - $total_withdrawals + $total_savings; // Tabungan adalah aset
$balance_exclude_savings = $total_income - $total_withdrawals; // Balance tanpa menghitung tabungan
$target_progress = $daily_target > 0 ? min(($today_income / $daily_target) * 100, 100) : 0;

// Get today's date for form defaults
$today_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .positive { color: #28a745; }
        .negative { color: #dc3545; }
        .neutral { color: #17a2b8; }
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }
        .transaction-item:last-child {
            border-bottom: none;
        }
        .transaction-item:hover {
            background: rgba(0,123,255,0.05);
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }
        .progress {
            height: 10px;
        }
        .fade-in {
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .reset-highlight {
            border: 2px solid #ffc107 !important;
            background: linear-gradient(45deg, #fff3cd, #ffffff) !important;
            animation: pulse 1s ease-in-out 3;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
        .btn-group-sm .btn {
            padding: 0.25rem 0.4rem;
            font-size: 0.75rem;
        }
        .gap-2 {
            gap: 0.5rem;
        }
        .flex-grow-1 {
            flex-grow: 1;
        }
        @media (max-width: 768px) {
            .transaction-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            .transaction-item .d-flex {
                align-self: flex-end;
                width: 100%;
                justify-content: space-between;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="text-center text-white mb-4">
            <h1><i class="fas fa-chart-line"></i> Finance Tracker</h1>
            <p>Kelola keuangan Anda dengan mudah dan efisien</p>
            <?php if ($conn): ?>
                <small><i class="fas fa-database"></i> Database Connected</small>
            <?php else: ?>
                <small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Database Error</small>
            <?php endif; ?>
        </div>

        <!-- Alerts -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card fade-in">
                    <h6 class="text-muted">TOTAL BALANCE</h6>
                    <div class="stat-value positive">$<?php echo number_format($total_balance, 2); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card fade-in">
                    <h6 class="text-muted">TOTAL TABUNGAN</h6>
                    <div class="stat-value neutral">$<?php echo number_format($total_savings, 2); ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card fade-in">
                    <h6 class="text-muted">BALANCE (EXCLUDE TABUNGAN)</h6>
                    <div class="stat-value <?php echo $balance_exclude_savings >= 0 ? 'positive' : 'negative'; ?>">
                        $<?php echo number_format($balance_exclude_savings, 2); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card fade-in <?php echo (isset($_POST['action']) && ($_POST['action'] === 'reset_target' || $_POST['action'] === 'reset_all_targets')) ? 'reset-highlight' : ''; ?>" id="target-card">
                    <h6 class="text-muted">TARGET HARIAN</h6>
                    <div class="stat-value">$<?php echo number_format($daily_target, 2); ?></div>
                    
                    <?php if ($daily_target > 0): ?>
                        <div class="progress mt-2">
                            <div class="progress-bar <?php echo $target_progress >= 100 ? 'bg-success' : 'bg-primary'; ?>" 
                                 style="width: <?php echo min($target_progress, 100); ?>%"></div>
                        </div>
                        <small><?php echo round($target_progress); ?>% tercapai</small>
                        
                        <?php if ($today_income >= $daily_target): ?>
                            <div class="alert alert-success mt-2 mb-0 py-1">
                                <small>🎉 Target tercapai!</small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning mt-2 mb-0 py-1">
                                <small>💪 Perlu $<?php echo number_format($daily_target - $today_income, 2); ?> lagi</small>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($is_carried_over): ?>
                            <div class="alert alert-info mt-2 mb-0 py-1">
                                <small><i class="fas fa-arrow-right"></i> Carry Over</small>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="alert alert-secondary mt-2 mb-0 py-1">
                            <small><i class="fas fa-target"></i> Target direset ke $0</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Forms -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus-circle text-success"></i> Tambah Income</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_income">
                            <div class="mb-3">
                                <label class="form-label">Jumlah ($)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="date" value="<?php echo $today_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" class="form-control" name="description" placeholder="Gaji, bonus, dll.">
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="fas fa-plus"></i> Tambah Income
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-minus-circle text-danger"></i> Tambah Withdrawal</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_withdrawal">
                            <div class="mb-3">
                                <label class="form-label">Jumlah ($)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="date" value="<?php echo $today_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" class="form-control" name="description" placeholder="Makanan, transport, dll.">
                            </div>
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-minus"></i> Tambah Withdrawal
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Savings and Target -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-piggy-bank text-warning"></i> Tambah ke Tabungan</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_savings">
                            <div class="mb-3">
                                <label class="form-label">Jumlah ($)</label>
                                <input type="number" class="form-control" name="amount" step="0.01" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="date" value="<?php echo $today_date; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" class="form-control" name="description" placeholder="Tabungan rutin, emergency fund, dll.">
                            </div>
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-piggy-bank"></i> Tambah ke Tabungan
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-target text-primary"></i> Set Target Harian</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="set_target">
                            <div class="mb-3">
                                <label class="form-label">Target ($)</label>
                                <input type="number" class="form-control" name="target_amount" step="0.01" 
                                       value="<?php echo $daily_target > 0 ? $daily_target : ''; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-target"></i> Set Target
                            </button>
                        </form>
                        
                        <?php if ($daily_target > 0): ?>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="action" value="reset_target">
                                <button type="submit" class="btn btn-outline-warning btn-sm w-100" 
                                        onclick="return confirm('Reset target hari ini ke $0?')">
                                    <i class="fas fa-undo"></i> Reset Target Hari Ini
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($is_carried_over && $daily_target > 0): ?>
                            <form method="POST" class="mt-2">
                                <input type="hidden" name="action" value="reset_all_targets">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                        onclick="return confirm('Reset SEMUA target ke $0?')">
                                    <i class="fas fa-trash"></i> Reset Semua Target
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <?php if ($daily_target > 0): ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    Target hari ini: $<?php echo number_format($daily_target, 2); ?>
                                    <?php if ($is_carried_over): ?>
                                        <span class="badge bg-info">Carry Over</span>
                                    <?php endif; ?><br>
                                    Income hari ini: $<?php echo number_format($today_income, 2); ?>
                                </small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <small class="text-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Target System:</strong><br>
                                <?php if ($daily_target == 0): ?>
                                    • Target saat ini: $0 (belum ada/direset)<br>
                                    • Set target baru untuk mulai tracking<br>
                                <?php elseif ($is_carried_over): ?>
                                    • Target carry over dari hari sebelumnya<br>
                                    • Set target baru untuk override hari ini<br>
                                    • Reset untuk kembali ke $0<br>
                                <?php else: ?>
                                    • Target sudah diset untuk hari ini<br>
                                    • Update target atau reset ke $0<br>
                                <?php endif; ?>
                                • Progress dihitung dari income hari ini saja
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-list"></i> Transaksi Terbaru (<?php echo count($transactions); ?>)</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($transactions)): ?>
                            <p class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                Belum ada transaksi
                            </p>
                        <?php else: ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="transaction-item fade-in">
                                    <div class="flex-grow-1">
                                        <strong>
                                            <?php
                                            $icons = ['income' => '💰', 'withdrawal' => '💸', 'savings' => '🏦'];
                                            echo $icons[$transaction['type']] ?? '📝';
                                            ?>
                                            <?php echo htmlspecialchars($transaction['description'] ?: ucfirst($transaction['type'])); ?>
                                        </strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date('d F Y', strtotime($transaction['transaction_date'])); ?>
                                            <i class="fas fa-clock ml-2"></i>
                                            <?php echo date('H:i', strtotime($transaction['created_at'])); ?>
                                        </small>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold <?php echo $transaction['type'] === 'income' ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo $transaction['type'] === 'income' ? '+' : '-'; ?>$<?php echo number_format($transaction['amount'], 2); ?>
                                        </span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    onclick="editTransaction(<?php echo htmlspecialchars(json_encode($transaction)); ?>)"
                                                    title="Edit Transaksi">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    onclick="deleteTransaction(<?php echo $transaction['id']; ?>, '<?php echo htmlspecialchars($transaction['description'] ?: ucfirst($transaction['type'])); ?>')"
                                                    title="Hapus Transaksi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-calendar-alt"></i> Target History</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        // Get target history
                        try {
                            $history_stmt = $conn->prepare("SELECT * FROM daily_targets ORDER BY target_date DESC LIMIT 5");
                            $history_stmt->execute();
                            $target_history = $history_stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            $target_history = [];
                        }
                        ?>
                        
                        <?php if (empty($target_history)): ?>
                            <p class="text-center text-muted">
                                <i class="fas fa-target fa-2x mb-2 d-block"></i>
                                Belum ada target
                            </p>
                        <?php else: ?>
                            <?php foreach ($target_history as $target): ?>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <div>
                                        <small class="text-muted">
                                            <?php echo date('d M Y', strtotime($target['target_date'])); ?>
                                            <?php if ($target['target_date'] === date('Y-m-d')): ?>
                                                <span class="badge bg-primary">Hari Ini</span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <div>
                                        <strong>$<?php echo number_format($target['target_amount'], 2); ?></strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <div class="mt-3">
                            <small class="text-info">
                                <i class="fas fa-lightbulb"></i> 
                                <strong>Sistem Target:</strong><br>
                                • Target carry over otomatis<br>
                                • Reset manual jika diperlukan<br>
                                • Progress harian independen
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Transaction Modal -->
        <div class="modal fade" id="editTransactionModal" tabindex="-1" aria-labelledby="editTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editTransactionModalLabel">
                            <i class="fas fa-edit"></i> Edit Transaksi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" id="editTransactionForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="edit_transaction">
                            <input type="hidden" name="transaction_id" id="edit_transaction_id">
                            
                            <div class="mb-3">
                                <label class="form-label">Tipe Transaksi</label>
                                <select class="form-select" name="edit_type" id="edit_type" required>
                                    <option value="income">💰 Income</option>
                                    <option value="withdrawal">💸 Withdrawal</option>
                                    <option value="savings">🏦 Savings</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Jumlah ($)</label>
                                <input type="number" class="form-control" name="edit_amount" id="edit_amount" step="0.01" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Tanggal</label>
                                <input type="date" class="form-control" name="edit_date" id="edit_date" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <input type="text" class="form-control" name="edit_description" id="edit_description" placeholder="Deskripsi transaksi">
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <small>Mengubah transaksi akan memperbarui balance dan statistik secara otomatis.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Transaction Modal -->
        <div class="modal fade" id="deleteTransactionModal" tabindex="-1" aria-labelledby="deleteTransactionModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="deleteTransactionModalLabel">
                            <i class="fas fa-exclamation-triangle"></i> Hapus Transaksi
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" id="deleteTransactionForm">
                        <div class="modal-body">
                            <input type="hidden" name="action" value="delete_transaction">
                            <input type="hidden" name="transaction_id" id="delete_transaction_id">
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Peringatan!</strong> Transaksi yang dihapus tidak dapat dikembalikan.
                            </div>
                            
                            <p>Apakah Anda yakin ingin menghapus transaksi berikut?</p>
                            <div class="card">
                                <div class="card-body">
                                    <strong id="delete_transaction_details"></strong>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Batal
                            </button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Ya, Hapus
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-white mt-4 mb-3">
            <small>
                <i class="fas fa-code"></i> Finance Tracker v2.2 - Edit & Delete Transactions
                <?php if ($conn): ?>
                    | <i class="fas fa-database text-success"></i> Database OK
                <?php endif; ?>
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(function(alert) {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 150);
                }
            });
        }, 5000);

        // Add form validation feedback
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                        submitBtn.disabled = true;
                    }
                });
            });
        });

        // Form auto-clear after success (optional)
        <?php if ($success_message): ?>
            // Clear forms after successful submission
            setTimeout(function() {
                const forms = document.querySelectorAll('form');
                forms.forEach(function(form) {
                    const actionInput = form.querySelector('input[name="action"]');
                    if (actionInput && !['set_target', 'reset_target', 'reset_all_targets', 'edit_transaction', 'delete_transaction'].includes(actionInput.value)) {
                        form.reset();
                        // Reset date to today
                        const dateInput = form.querySelector('input[type="date"]');
                        if (dateInput) {
                            dateInput.value = '<?php echo $today_date; ?>';
                        }
                    }
                });
                
                // Special handling for target reset success
                <?php if (strpos($success_message, 'reset') !== false || strpos($success_message, 'Reset') !== false): ?>
                    // Remove reset highlight after 3 seconds
                    setTimeout(() => {
                        const targetCard = document.getElementById('target-card');
                        if (targetCard) {
                            targetCard.classList.remove('reset-highlight');
                        }
                    }, 3000);
                <?php endif; ?>
                
                // Close modals after successful edit/delete
                <?php if (strpos($success_message, 'update') !== false || strpos($success_message, 'hapus') !== false): ?>
                    const editModal = bootstrap.Modal.getInstance(document.getElementById('editTransactionModal'));
                    const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteTransactionModal'));
                    if (editModal) editModal.hide();
                    if (deleteModal) deleteModal.hide();
                <?php endif; ?>
            }, 1000);
        <?php endif; ?>

        // Transaction Edit/Delete Functions
        function editTransaction(transaction) {
            console.log('Editing transaction:', transaction);
            
            // Populate modal fields
            document.getElementById('edit_transaction_id').value = transaction.id;
            document.getElementById('edit_type').value = transaction.type;
            document.getElementById('edit_amount').value = transaction.amount;
            document.getElementById('edit_date').value = transaction.transaction_date;
            document.getElementById('edit_description').value = transaction.description || '';
            
            // Update modal title with transaction info
            const modalTitle = document.getElementById('editTransactionModalLabel');
            const icons = {'income': '💰', 'withdrawal': '💸', 'savings': '🏦'};
            modalTitle.innerHTML = `<i class="fas fa-edit"></i> Edit ${icons[transaction.type]} ${transaction.type.charAt(0).toUpperCase() + transaction.type.slice(1)}`;
            
            // Show modal
            const editModal = new bootstrap.Modal(document.getElementById('editTransactionModal'));
            editModal.show();
        }

        function deleteTransaction(id, description) {
            console.log('Deleting transaction:', id, description);
            
            // Populate hidden form
            document.getElementById('delete_transaction_id').value = id;
            document.getElementById('delete_transaction_details').textContent = description;
            
            // Show modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteTransactionModal'));
            deleteModal.show();
        }

        // Add loading state to modal forms
        document.getElementById('editTransactionForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            submitBtn.disabled = true;
        });

        document.getElementById('deleteTransactionForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menghapus...';
            submitBtn.disabled = true;
        });

        console.log('✅ Finance Tracker v2.2 loaded successfully - Edit & Delete Transactions Added!');
        console.log('📊 Stats:', {
            totalBalance: <?php echo $total_balance; ?>,
            totalSavings: <?php echo $total_savings; ?>,
            targetProgress: <?php echo round($target_progress, 2); ?>,
            dailyTarget: <?php echo $daily_target; ?>,
            isCarriedOver: <?php echo $is_carried_over ? 'true' : 'false'; ?>,
            todayIncome: <?php echo $today_income; ?>,
            justReset: <?php echo (isset($_POST['action']) && ($_POST['action'] === 'reset_target' || $_POST['action'] === 'reset_all_targets')) ? 'true' : 'false'; ?>,
            transactionCount: <?php echo count($transactions); ?>
        });
        console.log('🔧 New Features: Edit & Delete transactions with auto-sync balance!');
    </script>
</body>
</html>