<?php
// auth/register.php
session_start();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก | Carbon Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f3f4f6;
            padding: 2rem 0;
        }
        .auth-box {
            max-width: 700px;
            margin: 0;
            width: 100%;
        }
        .bank-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .bank-option {
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .bank-option:hover {
            border-color: var(--primary);
            background: #ecfdf5;
        }
        .bank-option input {
            display: none;
        }
        .bank-option.selected {
            border-color: var(--primary);
            background: #d1fae5;
            box-shadow: 0 0 0 2px var(--primary);
        }
        .bank-option img {
            width: 40px; 
            height: 40px; 
            object-fit: contain;
            margin-bottom: 5px;
        }
        /* Group selection styles */
        .group-select {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .group-option {
            flex: 1;
            padding: 15px;
            border: 2px solid var(--border);
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.2s;
        }
        .group-option.active {
            border-color: var(--primary);
            background: #f0fdf4;
            color: var(--primary);
        }
        .group-option input {
            display: none;
        }
        .dynamic-section {
            display: none;
            background: #fdfdfd;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }
        .dynamic-section.active {
            display: block;
        }
    </style>
</head>
<body>

<div class="auth-box">
    <div style="text-align:center; margin-bottom:2rem;">
        <h1 style="color:var(--primary);">สมัครสมาชิก</h1>
        <p style="color:var(--text-light);">เริ่มต้นใช้งาน Carbon Credit Simulator</p>
    </div>

<?php include '../includes/alerts.php'; ?>

    <form action="../process/send_otp.php" method="POST" enctype="multipart/form-data">
        
        <label style="display:block; margin-bottom:10px; font-weight:bold;">ประเภทผู้ใช้งาน</label>
        <div class="group-select">
            <label class="group-option active" onclick="selectGroup('individual')">
                <input type="radio" name="user_type" value="individual" checked>
                👤 บุคคลธรรมดา
            </label>
            <label class="group-option" onclick="selectGroup('corporate')">
                <input type="radio" name="user_type" value="corporate">
                🏢 นิติบุคคล (เอกชน)
            </label>
            <label class="group-option" onclick="selectGroup('government')">
                <input type="radio" name="user_type" value="government">
                🏛️ หน่วยงานภาครัฐ
            </label>
        </div>

        <!-- Section: Individual -->
        <div id="section-individual" class="dynamic-section active">
            <h4 style="margin-top:0; margin-bottom:15px; color:#4b5563;">ข้อมูลบุคคลธรรมดา</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>ชื่อจริง</label>
                    <input type="text" name="first_name" id="req_first_name" required placeholder="ชื่อจริง">
                </div>
                <div>
                    <label>นามสกุล</label>
                    <input type="text" name="last_name" id="req_last_name" required placeholder="นามสกุล">
                </div>
            </div>
            <label>รหัสบัตรประชาชน</label>
            <input type="text" name="national_id" id="req_national_id" required maxlength="17" placeholder="13 หลัก">
        </div>

        <!-- Section: Corporate -->
        <div id="section-corporate" class="dynamic-section">
            <h4 style="margin-top:0; margin-bottom:15px; color:#4b5563;">ข้อมูลนิติบุคคล (เอกชน)</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>หนังสือรับรองบริษัท (PDF/Image)</label>
                    <input type="file" name="corp_cert" id="req_corp_cert" accept="application/pdf,image/*">
                </div>
                <div>
                    <label>ใบภ.พ.20 (PDF/Image)</label>
                    <input type="file" name="vat_id" id="req_vat_id" accept="application/pdf,image/*">
                </div>
            </div>
            <label>เลขประจำตัวผู้เสียภาษี</label>
            <input type="text" name="tax_id" id="req_tax_id" placeholder="13 หลัก">
        </div>

        <!-- Section: Government -->
        <div id="section-government" class="dynamic-section">
            <h4 style="margin-top:0; margin-bottom:15px; color:#4b5563;">ข้อมูลหน่วยงานภาครัฐ</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>ชื่อหน่วยงานภาครัฐ</label>
                    <input type="text" name="agency_name" id="req_agency_name" placeholder="ระบุชื่อหน่วยงาน">
                </div>
                <div>
                    <label>เลขประจำตัวผู้เสียภาษี</label>
                    <input type="text" name="tax_id_gov" id="req_tax_id_gov" placeholder="13 หลัก">
                </div>
            </div>
            
            <label>ที่อยู่หน่วยงาน</label>
            <textarea name="agency_address" id="req_agency_address" rows="3" placeholder="ระบุที่อยู่หน่วยงาน ครบถ้วน" style="width:100%; padding:0.75rem; border:1px solid #cbcbcb; border-radius:6px; margin-bottom:15px; font-family:inherit;"></textarea>
            
            <label>หนังสือยืนยันตัวตนภาครัฐ (PDF)</label>
            <input type="file" name="gov_doc" id="req_gov_doc" accept="application/pdf" style="margin-bottom:15px;">

            <hr style="border:0; border-top:1px dashed #e5e7eb; margin:20px 0;">

            <h4 style="margin-top:0; margin-bottom:15px; color:#2563eb;">ผู้มีอำนาจลงนาม</h4>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>ชื่อจริง</label>
                    <input type="text" name="auth_first_name" id="req_auth_first_name" placeholder="ชื่อจริงผู้มีอำนาจ">
                </div>
                <div>
                    <label>นามสกุล</label>
                    <input type="text" name="auth_last_name" id="req_auth_last_name" placeholder="นามสกุลผู้มีอำนาจ">
                </div>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                <div>
                    <label>ตำแหน่ง</label>
                    <input type="text" name="auth_position" id="req_auth_position" placeholder="ระบุตำแหน่ง">
                </div>
                <div>
                    <label>หนังสือแต่งตั้ง (PDF/Image)</label>
                    <input type="file" name="auth_doc" id="req_auth_doc" accept="application/pdf,image/*">
                </div>
            </div>

            <label style="display:flex; align-items:center; cursor:pointer; background:#f0fdf4; padding:15px; border-radius:8px; border:1px solid #bbf7d0; margin-bottom:15px; margin-top:15px;">
                <input type="checkbox" name="has_poa" id="has_poa_checkbox" value="1" style="margin-right:1rem; transform:scale(1.3); display:block;"> 
                <span style="font-weight:600; color:#166534;">มีการมอบอำนาจ (ถ้ามี)</span>
            </label>

            <div id="poa_section" style="display:none; background:#fafafa; padding:15px; border:1px dashed #d1d5db; border-radius:8px;">
                <h4 style="margin-top:0; margin-bottom:15px; color:#4b5563;">ข้อมูลผู้รับมอบอำนาจ</h4>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:15px;">
                    <div>
                        <label>ชื่อจริง (ผู้รับมอบ)</label>
                        <input type="text" name="poa_first_name" id="req_poa_first_name" placeholder="ชื่อจริงผู้รับมอบ">
                    </div>
                    <div>
                        <label>นามสกุล (ผู้รับมอบ)</label>
                        <input type="text" name="poa_last_name" id="req_poa_last_name" placeholder="นามสกุลผู้รับมอบ">
                    </div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div>
                        <label>ตำแหน่ง</label>
                        <input type="text" name="poa_position" id="req_poa_position" placeholder="ระบุตำแหน่ง">
                    </div>
                    <div>
                        <label>หนังสือมอบอำนาจ + บัตรปชช. (PDF/Image)</label>
                        <input type="file" name="poa_doc" id="req_poa_doc" accept="application/pdf,image/*">
                    </div>
                </div>
            </div>
        </div>

        <hr style="border:0; border-top:1px solid #e5e7eb; margin:20px 0;">

        <!-- Common Fields -->
        <h4 style="margin-top:0; margin-bottom:15px; color:#4b5563;">ข้อมูลบัญชีผู้ใช้</h4>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div>
                <label>ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" name="username" required placeholder="สำหรับเข้าสู่ระบบ">
            </div>
            <div>
                <label>รหัสผ่าน</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div>
                <label>เบอร์โทรศัพท์ (สำหรับรับ OTP)</label>
                <input type="text" name="phone" required placeholder="08XXXXXXXX">
            </div>
            <div>
                <label>อีเมล (Email)</label>
                <input type="email" name="email" required placeholder="example@email.com">
            </div>
        </div>

        <label style="margin-top:10px; display:block;">ข้อมูลธนาคาร (สำหรับรับและถอนเงิน)</label>
        <div class="bank-grid">
            <label class="bank-option" onclick="selectBank(this)">
                <img src="../assets/banks/kbank.png" onerror="this.src='https://placehold.co/40x40?text=KB'"><br>
                กสิกรไทย
                <input type="radio" name="bank_name" value="กสิกรไทย" required>
            </label>
            <label class="bank-option" onclick="selectBank(this)">
                <img src="../assets/banks/scb.png" onerror="this.src='https://placehold.co/40x40?text=SCB'"><br>
                ไทยพาณิชย์
                <input type="radio" name="bank_name" value="ไทยพาณิชย์">
            </label>
            <label class="bank-option" onclick="selectBank(this)">
                <img src="../assets/banks/bbl.png" onerror="this.src='https://placehold.co/40x40?text=BBL'"><br>
                กรุงเทพ
                <input type="radio" name="bank_name" value="กรุงเทพ">
            </label>
            <label class="bank-option" onclick="selectBank(this)">
                <img src="../assets/banks/ktb.png" onerror="this.src='https://placehold.co/40x40?text=KTB'"><br>
                กรุงไทย
                <input type="radio" name="bank_name" value="กรุงไทย">
            </label>
            <label class="bank-option" onclick="selectBank(this)">
                <img src="../assets/banks/gsb.png" onerror="this.src='https://placehold.co/40x40?text=GSB'"><br>
                ออมสิน
                <input type="radio" name="bank_name" value="ออมสิน">
            </label>
            <label class="bank-option" onclick="selectBank(this)">
                <span style="font-size:24px">🏦</span><br>
                อื่น ๆ
                <input type="radio" name="bank_name" value="อื่นๆ" onchange="toggleOther(true)">
            </label>
        </div>

        <input type="text" name="bank_other" id="other-bank" placeholder="ระบุชื่อธนาคาร" style="display:none;">

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
            <div>
                <label>เลขบัญชี</label>
                <input type="text" name="account_number" id="account_number" required oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            <div>
                <label>ชื่อบัญชี</label>
                <input type="text" name="account_name" required>
            </div>
        </div>

        <button type="submit" style="width:100%; margin-top:1rem;">สมัครสมาชิก</button>
    </form>

    <div style="text-align:center; margin-top:1.5rem;">
        <span style="color:var(--text-light);">มีบัญชีอยู่แล้ว?</span>
        <a href="login.php" style="font-weight:600;">เข้าสู่ระบบ</a>
    </div>
</div>

<script>
const idFields = ['national_id', 'tax_id', 'tax_id_gov'];

idFields.forEach(name => {
    let el = document.querySelector('input[name="' + name + '"]');
    if (el) {
        el.setAttribute('maxlength', '17'); // 13 digits + 4 dashes
        el.setAttribute('minlength', '17');
        el.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 13) value = value.slice(0, 13);
            
            let formatted = '';
            if (value.length > 0) formatted += value.substring(0, 1);
            if (value.length > 1) formatted += '-' + value.substring(1, 5);
            if (value.length > 5) formatted += '-' + value.substring(5, 10);
            if (value.length > 10) formatted += '-' + value.substring(10, 12);
            if (value.length > 12) formatted += '-' + value.substring(12, 13);
            
            e.target.value = formatted;
        });
    }
});

function selectBank(element) {
    document.querySelectorAll('.bank-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    const radio = element.querySelector('input[type="radio"]');
    radio.checked = true;
    
    if (radio.value !== 'อื่นๆ') {
        document.getElementById('other-bank').style.display = 'none';
        document.getElementById('other-bank').removeAttribute('required');
    } else {
        document.getElementById('other-bank').style.display = 'block';
        document.getElementById('other-bank').setAttribute('required', 'true');
    }
}

function selectGroup(type) {
    // UI Update
    document.querySelectorAll('.group-option').forEach(el => el.classList.remove('active'));
    event.currentTarget.classList.add('active');
    
    // Sections Toggle
    document.querySelectorAll('.dynamic-section').forEach(el => el.classList.remove('active'));
    document.getElementById('section-' + type).classList.add('active');
    
    // Manage 'required' attributes dynamically
    const fields = {
        'individual': ['req_first_name', 'req_last_name', 'req_national_id'],
        'corporate': ['req_corp_cert', 'req_vat_id', 'req_tax_id'],
        'government': ['req_agency_name', 'req_tax_id_gov', 'req_agency_address', 'req_auth_first_name', 'req_auth_last_name', 'req_auth_position', 'req_auth_doc']
    };
    
    // Remove all required first
    Object.values(fields).flat().forEach(id => {
        document.getElementById(id).removeAttribute('required');
    });
    
    // Add required to active
    fields[type].forEach(id => {
        document.getElementById(id).setAttribute('required', 'true');
    });
}

document.getElementById('has_poa_checkbox').addEventListener('change', function(e) {
    const poaSection = document.getElementById('poa_section');
    const poaFields = ['req_poa_first_name', 'req_poa_last_name', 'req_poa_position', 'req_poa_doc'];
    
    if (e.target.checked) {
        poaSection.style.display = 'block';
        poaFields.forEach(id => document.getElementById(id).setAttribute('required', 'true'));
    } else {
        poaSection.style.display = 'none';
        poaFields.forEach(id => document.getElementById(id).removeAttribute('required'));
    }
});
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
