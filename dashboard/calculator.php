<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>คำนวณเครดิต | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .calc-grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: start;
        }
        .ref-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .ref-table th, .ref-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 0.95rem;
        }
        .ref-table th {
            background: #f9fafb;
            font-weight: 600;
            color: var(--text-light);
        }
        .result-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            padding: 2rem;
            border-radius: 16px;
            margin-top: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .result-val {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        .formula-card {
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 12px;
            margin-top: 2rem;
            border: 1px solid #e2e8f0;
        }
        .formula-card ul {
            margin: 0.75rem 0 0;
            padding-left: 1.25rem;
            color: var(--text-light);
            font-size: 0.9rem;
        }
        .formula-card li { margin-bottom: 0.25rem; }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;">🧮 คำนวณคาร์บอนเครดิต</h1>
            <p style="color:var(--text-light);">ประเมินปริมาณคาร์บอนเครดิตเบื้องต้นจากพื้นที่และทรัพยากรของคุณ</p>
        </header>

        <div class="calc-grid-layout">
            <!-- Left: Reference Table -->
            <div class="card">
                <h3 style="margin-bottom:1.5rem;"><i class="fas fa-chart-line" style="color:var(--primary);"></i> ตารางอ้างอิง (Reference)</h3>
                <p style="color:var(--text-light); font-size: 0.9rem; margin-bottom: 1rem;">ค่าประเมินการกักเก็บคาร์บอนต่อปี (Ton CO2e/ปี)</p>
                
                <table class="ref-table">
                    <thead>
                        <tr>
                            <th>ประเภท</th>
                            <th>รายละเอียด</th>
                            <th style="text-align: right;">คาร์บอน (Ton)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>🌳 ยืนต้น (Tree)</td>
                            <td>ความสูง 1.5 - 3 เมตร</td>
                            <td style="text-align: right; font-weight: 600;">0.01 - 0.05</td>
                        </tr>
                        <tr>
                            <td>🌳 ยืนต้น (Tree)</td>
                            <td>ความสูง > 3 เมตร</td>
                            <td style="text-align: right; font-weight: 600;">0.05 - 0.20</td>
                        </tr>
                        <tr>
                            <td>🌾 นาข้าว</td>
                            <td>แบบเปียกสลับแห้ง</td>
                            <td style="text-align: right; font-weight: 600;">0.50 / ไร่</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="formula-card">
                    <strong style="color: var(--text); font-size: 0.95rem;"><i class="fas fa-lightbulb" style="color: #f59e0b;"></i> สูตรคำนวณเบื้องต้น</strong>
                    <ul>
                        <li><strong>ไม้ยืนต้น:</strong> จำนวน x (สูง x 0.1 + อายุ x 0.05)</li>
                        <li><strong>นาข้าว:</strong> พื้นที่ (ไร่) x 0.50</li>
                    </ul>
                </div>
            </div>

            <!-- Right: Calculator -->
            <div class="card">
                <h3 style="margin-bottom:1.5rem; color: var(--primary);"><i class="fas fa-calculator"></i> เครื่องมือคำนวณ</h3>
                
                <form id="calcForm" action="../process/calc_carbon.php" method="POST">
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:500;">เลือกประเภทกิจกรรม</label>
                        <select name="activity" id="activity" onchange="toggleCalc()" required style="margin-bottom:0;">
                            <option value="tree">🌳 การปลูกต้นไม้ (Tree)</option>
                            <option value="rice">🌾 ภาคการเกษตร / นาข้าว (Rice)</option>
                        </select>
                    </div>

                    <!-- Tree Inputs -->
                    <div id="treeInputs" style="animation: fadeIn 0.3s ease;">
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem; margin-bottom: 1.25rem;">
                            <div>
                                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">จำนวนต้น</label>
                                <input type="number" id="tree_count" name="tree_count" value="1" min="1" oninput="calculate()" style="margin-bottom:0;">
                            </div>
                            <div>
                                <label style="display:block; margin-bottom:0.5rem; font-weight:500;">อายุเฉลี่ย (ปี)</label>
                                <input type="number" id="tree_age" name="tree_age" value="3" min="1" oninput="calculate()" style="margin-bottom:0;">
                            </div>
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display:block; margin-bottom:0.5rem; font-weight:500;">ความสูงเฉลี่ย (เมตร)</label>
                            <input type="number" step="0.1" id="tree_height" name="tree_height" value="1.5" oninput="calculate()" style="margin-bottom:0;">
                        </div>
                    </div>

                    <!-- Rice Inputs -->
                    <div id="riceInputs" style="display:none; animation: fadeIn 0.3s ease; margin-bottom: 1.5rem;">
                        <label style="display:block; margin-bottom:0.5rem; font-weight:500;">ขนาดพื้นที่ (ไร่)</label>
                        <div style="position:relative;">
                            <input type="number" step="0.1" id="rice_area" name="rice_area" value="1" oninput="calculate()" style="margin-bottom:0; padding-right: 3rem;">
                            <span style="position:absolute; right:1rem; top:50%; transform:translateY(-50%); color:var(--text-light); font-weight:600;">ไร่</span>
                        </div>
                    </div>
                    
                    <!-- Hidden Result -->
                    <input type="hidden" name="calculated_carbon" id="hidden_carbon">

                    <div class="result-box">
                        <div style="font-size:0.95rem; color:var(--text-light); font-weight:500;">ปริมาณคาร์บอนที่กักเก็บได้</div>
                        <div class="result-val" id="showResult">0.00 Ton</div>
                        <div style="font-size:0.9rem; color:var(--text-light); margin-top:0.5rem;">
                            ≈ <span id="showToken" style="font-weight:700; color: #f59e0b;">0</span> Token (สะสม)
                        </div>
                    </div>

                    <button type="submit" style="width:100%; margin-top:2rem; font-weight:700; padding: 1rem;">
                        <i class="fas fa-save me-2"></i> บันทึกผลการคำนวณ
                    </button>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function toggleCalc() {
    const type = document.getElementById('activity').value;
    document.getElementById('treeInputs').style.display = type === 'tree' ? 'block' : 'none';
    document.getElementById('riceInputs').style.display = type === 'rice' ? 'block' : 'none';
    calculate();
}

function calculate() {
    const type = document.getElementById('activity').value;
    let carbon = 0;

    if (type === 'tree') {
        const count = parseFloat(document.getElementById('tree_count').value) || 0;
        const age = parseFloat(document.getElementById('tree_age').value) || 0;
        const height = parseFloat(document.getElementById('tree_height').value) || 0;
        carbon = count * ((height * 0.1) + (age * 0.05));
    } else if (type === 'rice') {
        const area = parseFloat(document.getElementById('rice_area').value) || 0;
        carbon = area * 0.5;
    }

    document.getElementById('showResult').innerText = carbon.toFixed(2) + ' Ton';
    document.getElementById('showToken').innerText = (carbon * 10).toFixed(2); 
    document.getElementById('hidden_carbon').value = carbon.toFixed(4);
}

toggleCalc();
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>

<script>
function toggleCalc() {
    const type = document.getElementById('activity').value;
    document.getElementById('treeInputs').style.display = type === 'tree' ? 'block' : 'none';
    document.getElementById('riceInputs').style.display = type === 'rice' ? 'block' : 'none';
    calculate();
}

function calculate() {
    const type = document.getElementById('activity').value;
    let carbon = 0;

    if (type === 'tree') {
        const count = parseFloat(document.getElementById('tree_count').value) || 0;
        const age = parseFloat(document.getElementById('tree_age').value) || 0;
        const height = parseFloat(document.getElementById('tree_height').value) || 0;
        
        // Formula: Count * (Height * 0.1 + Age * 0.05)
        // Example: 1 * (1.5*0.1 + 3*0.05) = 1 * (0.15 + 0.15) = 0.30
        carbon = count * ((height * 0.1) + (age * 0.05));
        
    } else if (type === 'rice') {
        const area = parseFloat(document.getElementById('rice_area').value) || 0;
        // Formula: Area * 0.5
        carbon = area * 0.5;
    }

    // Update Display
    document.getElementById('showResult').innerText = carbon.toFixed(2) + ' Ton';
    document.getElementById('showToken').innerText = (carbon * 10).toFixed(2); // Mock 1 Ton = 10 Token
    
    // Update Hidden Input
    document.getElementById('hidden_carbon').value = carbon.toFixed(4);
}

// Init
toggleCalc();
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
