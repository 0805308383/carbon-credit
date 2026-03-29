<?php
session_start();
require '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'admin') {
    exit('เฉพาะผู้ใช้งานเท่านั้น');
}

// Fetch provinces for Step 1
$provinces = ["กระบี่", "กรุงเทพมหานคร", "กาญจนบุรี", "กาฬสินธุ์", "กำแพงเพชร", "ขอนแก่น", "จันทบุรี", "ฉะเชิงเทรา", "ชลบุรี", "ชัยนาท", "ชัยภูมิ", "ชุมพร", "เชียงราย", "เชียงใหม่", "ตรัง", "ตราด", "ตาก", "นครนายก", "นครปฐม", "นครพนม", "นครราชสีมา", "นครศรีธรรมราช", "นครสวรรค์", "นนทบุรี", "นราธิวาส", "น่าน", "บึงกาฬ", "บุรีรัมย์", "ปทุมธานี", "ประจวบคีรีขันธ์", "ปราจีนบุรี", "ปัตตานี", "พระนครศรีอยุธยา", "พะเยา", "พังงา", "พัทลุง", "พิจิตร", "พิษณุโลก", "เพชรบุรี", "เพชรบูรณ์", "แพร่", "ภูเก็ต", "มหาสารคาม", "มุกดาหาร", "แม่ฮ่องสอน", "ยโสธร", "ยะลา", "ร้อยเอ็ด", "ระนอง", "ระยอง", "ราชบุรี", "ลพบุรี", "ลำปาง", "ลำพูน", "เลย", "ศรีสะเกษ", "สกลนคร", "สงขลา", "สตูล", "สมุทรปราการ", "สมุทรสงคราม", "สมุทรสาคร", "สระแก้ว", "สระบุรี", "สิงห์บุรี", "สุโขทัย", "สุพรรณบุรี", "สุราษฎร์ธานี", "สุรินทร์", "หนองคาย", "หนองบัวลำภู", "อ่างทอง", "อำนาจเจริญ", "อุดรธานี", "อุตรดิตถ์", "อุทัยธานี", "อุบลราชธานี"];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลงขายคาร์บอนเครดิต | Carbon Market</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="../assets/css/modern.css">
    
    <style>
        :root {
            --primary-color: #10b981;
            --primary-dark: #059669;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 0;
            min-height: 100vh;
        }
        .sidebar {
            background: white;
            border-right: 1px solid var(--border);
            padding: 2rem;
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
        }
        .sidebar-brand { font-size: 1.5rem; font-weight: 700; color: var(--primary); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem; }
        .sidebar-menu a { padding: 0.75rem 1rem; border-radius: 8px; color: var(--text); display: block; margin-bottom: 0.5rem; text-decoration: none; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: #f3f4f6; color: var(--primary); }
        .main-content { padding: 3rem; background: #f8fafc; overflow-y: auto; }

        .stepper-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .stepper-header::before {
            content: "";
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e2e8f0;
            z-index: 1;
        }
        .step-item {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 100%;
        }
        .step-circle {
            width: 40px;
            height: 40px;
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        .step-item.active .step-circle {
            border-color: var(--primary-color);
            background: var(--primary-color);
            color: white;
        }
        .step-item.completed .step-circle {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        .step-label { font-size: 0.85rem; color: #64748b; font-weight: 500; }
        .step-item.active .step-label { color: var(--primary-color); font-weight: 600; }

        .form-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            width: 100%;
            margin: 0 auto;
        }
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.4s ease-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .type-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        .type-card:hover { border-color: var(--primary-color); background: #f0fdf4; }
        .type-card.selected { border-color: var(--primary-color); background: #f0fdf4; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1); }
        .type-card i { font-size: 2.5rem; color: var(--primary-color); margin-bottom: 1rem; }

        .image-preview {
            width: 100%;
            height: 200px;
            border: 2px dashed #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: #f8fafc;
            margin-top: 0.5rem;
        }
        .image-preview img { width: 100%; height: 100%; object-fit: cover; }
        
        .btn-primary { background-color: var(--primary-color); border-color: var(--primary-color); }
        .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
        
        .select2-container--default .select2-selection--single {
            height: 45px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 5px;
        }
    </style>
</head>
<body>

<div class="db-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <main class="db-main">
        <header style="margin-bottom: 2rem;">
            <h1 style="margin:0;"><i class="fas fa-plus-circle text-success me-2"></i>สร้างรายการลงขาย</h1>
            <p style="color:var(--text-light);">ทำตามขั้นตอนด้านล่างเพื่อส่งโครงการให้ผู้ดูแลระบบตรวจสอบ</p>
        </header>

        <div class="form-card">
            <!-- Progress Bar -->
            <div class="stepper-header">
                <div class="step-item active" id="step-head-1">
                    <div class="step-circle">1</div>
                    <div class="step-label">ประเภทและที่ตั้ง</div>
                </div>
                <div class="step-item" id="step-head-2">
                    <div class="step-circle">2</div>
                    <div class="step-label">รายละเอียดโครงการ</div>
                </div>
                <div class="step-item" id="step-head-3">
                    <div class="step-circle">3</div>
                    <div class="step-label">หลักฐานและราคา</div>
                </div>
            </div>

            <form id="listingForm" action="../process/create_listing_process.php" method="POST" enctype="multipart/form-data">
                
                <!-- STEP 1: Type and Location -->
                <div class="step-content active" id="step-1">
                    <h4 class="mb-4 fw-bold">เลือกประเภทและที่ตั้งโครงการ</h4>
                    
                    <label class="form-label fw-bold mb-3">ประเภทคาร์บอนเครดิต</label>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="type-card" onclick="selectType('tree')" id="card-tree">
                                <i class="fas fa-tree"></i>
                                <h5 class="mb-1">ต้นไม้ (Tree)</h5>
                                <p class="text-muted small mb-0">การกักเก็บคาร์บอนจากป่าไม้</p>
                                <input type="radio" name="carbon_type" id="type-tree" value="tree" class="d-none" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="type-card" onclick="selectType('rice')" id="card-rice">
                                <i class="fas fa-seedling"></i>
                                <h5 class="mb-1">นาข้าว (Rice Field)</h5>
                                <p class="text-muted small mb-0">การลดก๊าซเรือนกระจกในนาข้าว</p>
                                <input type="radio" name="carbon_type" id="type-rice" value="rice" class="d-none">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="province" class="form-label fw-bold">พื้นที่ตั้งโครงการ (จังหวัด)</label>
                        <select class="form-select select2-province" name="province" id="province" required>
                            <option value="">-- ค้นหาจังหวัด --</option>
                            <?php foreach ($provinces as $p): ?>
                                <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-5">
                        <button type="button" class="btn btn-primary px-5 py-2 fw-bold" onclick="nextStep(2)">ถัดไป <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- STEP 2: Project Details -->
                <div class="step-content" id="step-2">
                    <h4 class="mb-4 fw-bold">กรอกรายละเอียดโครงการ</h4>
                    
                    <!-- Tree Fields -->
                    <div id="fields-tree" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">จำนวนต้น</label>
                                <input type="number" class="form-control" name="tree_count" id="tree_count" min="1" oninput="updateCarbon()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">ความสูงเฉลี่ย (เมตร)</label>
                                <input type="number" step="0.01" class="form-control" name="avg_height" id="avg_height" min="0.1" oninput="updateCarbon()">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">อายุโดยประมาณ (ปี)</label>
                                <input type="number" class="form-control" name="tree_age" id="tree_age" min="1" oninput="updateCarbon()">
                            </div>
                        </div>
                    </div>

                    <!-- Rice Fields -->
                    <div id="fields-rice" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ขนาดพื้นที่ (ไร่)</label>
                                <input type="number" step="0.01" class="form-control" name="land_area" id="land_area" min="0.1" oninput="updateCarbon()">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รอบการเก็บเกี่ยวคาร์บอน (ปี)</label>
                                <input type="number" class="form-control" name="rice_age" id="rice_age" min="1" oninput="updateCarbon()">
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-light rounded-3 border border-success-subtle">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1 text-success fw-bold">ปริมาณคาร์บอนที่ได้จากการคำนวณ</h6>
                                <p class="text-muted small mb-0">* คำนวณอัตโนมัติตามมาตรฐานโครงการ</p>
                            </div>
                            <div class="text-end">
                                <input type="hidden" name="carbon_amount" id="carbon_amount_val" value="0">
                                <span class="h2 mb-0 fw-bold text-success" id="carbon_amount_display">0.00</span>
                                <span class="ms-1 text-muted">Ton CO2e</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(1)"><i class="fas fa-arrow-left me-2"></i>ย้อนกลับ</button>
                        <button type="button" class="btn btn-primary px-5 py-2 fw-bold" onclick="nextStep(3)">ถัดไป <i class="fas fa-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- STEP 3: Evidence and Price -->
                <div class="step-content" id="step-3">
                    <h4 class="mb-4 fw-bold">อัปโหลดเอกสารหลักฐาน</h4>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">1. รูปต้นไม้เต็มต้น / พื้นที่จริง (บังคับ)</label>
                            <input type="file" class="form-control" name="full_tree_image" id="img_main" accept="image/png, image/jpeg" required onchange="previewImg(this, 'preview_main')">
                            <div class="image-preview" id="preview_main">
                                <span class="text-muted small">รูปนี้แสดงในหน้า Marketplace</span>
                            </div>
                        </div>
                        
                        <div class="col-md-6" id="box-ref-img">
                            <label class="form-label fw-bold">2. รูปถ่ายพร้อมวัตถุอ้างอิง</label>
                            <input type="file" class="form-control" name="reference_image" id="img_ref" accept="image/png, image/jpeg" onchange="previewImg(this, 'preview_ref')">
                            <div class="image-preview" id="preview_ref">
                                <span class="text-muted small">สำหรับแอดมินตรวจสอบเท่านั้น</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">3. รูปบัตรประชาชน (บังคับ)</label>
                            <input type="file" class="form-control" name="id_card_image" id="img_id" accept="image/png, image/jpeg" required onchange="previewImg(this, 'preview_id')">
                            <div class="image-preview" id="preview_id">
                                <span class="text-muted small">สำหรับแอดมินตรวจสอบเท่านั้น</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">4. เอกสารสิทธิ์ที่ดิน (บังคับ)</label>
                            <input type="file" class="form-control" name="land_document" id="doc_land" accept="application/pdf, image/png, image/jpeg" required>
                            <div class="mt-2">
                                <span class="text-muted small">เฉพาะแอดมินเท่านั้นที่สามารถดูเอกสารนี้ได้</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">5. ราคาคาร์บอน (CC / เครดิต)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" name="price" id="price" required placeholder="0.00">
                                <span class="input-group-text">CC/ตัน</span>
                            </div>
                            <p class="text-muted small mt-2">1 CC = 1 Token (โดยประมาณ)</p>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-4">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="check_truth" required>
                            <label class="form-check-label" for="check_truth">ข้าพเจ้ายืนยันว่าข้อมูลทั้งหมดเป็นความจริง</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="check_consent" required>
                            <label class="form-check-label" for="check_consent">ยินยอมให้ Admin ตรวจสอบสภาพพื้นที่จริงหากจำเป็น</label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="prevStep(2)"><i class="fas fa-arrow-left me-2"></i>ย้อนกลับ</button>
                        <button type="button" class="btn btn-success px-5 py-2 fw-bold" onclick="showConfirmModal()">ยืนยันข้อมูล <i class="fas fa-check-circle ms-2"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">ตรวจสอบข้อมูลการลงขาย</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row g-3" id="confirmData">
                    <!-- Loaded via JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">แก้ไขข้อมูล</button>
                <button type="button" id="finalSubmit" class="btn btn-success px-4" onclick="document.getElementById('listingForm').submit()">ส่งให้ Admin ตรวจสอบ</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    $('.select2-province').select2({
        theme: "default",
        width: '100%'
    });
});

let currentStep = 1;

function selectType(type) {
    $('.type-card').removeClass('selected');
    $('#card-' + type).addClass('selected');
    $('#type-' + type).prop('checked', true);
    
    // Toggle Fields
    if (type === 'tree') {
        $('#fields-tree').show();
        $('#fields-rice').hide();
        $('#box-ref-img').show();
    } else {
        $('#fields-tree').hide();
        $('#fields-rice').show();
        $('#box-ref-img').hide();
    }
    updateCarbon();
}

function nextStep(step) {
    // Validation
    if (currentStep === 1) {
        if (!$('input[name="carbon_type"]:checked').val()) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือกประเภทคาร์บอน', 'warning');
            return;
        }
        if (!$('#province').val()) {
            Swal.fire('แจ้งเตือน', 'กรุณาเลือกจังหวัด', 'warning');
            return;
        }
    }
    
    if (currentStep === 2) {
        let type = $('input[name="carbon_type"]:checked').val();
        if (type === 'tree') {
            if (!$('#tree_count').val() || !$('#avg_height').val() || !$('#tree_age').val()) {
                Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลต้นไม้ให้ครบ', 'warning');
                return;
            }
        } else {
            if (!$('#land_area').val() || !$('#rice_age').val()) {
                Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลนาข้าวให้ครบ', 'warning');
                return;
            }
        }
    }

    $(`#step-${currentStep}`).removeClass('active');
    $(`#step-head-${currentStep}`).removeClass('active').addClass('completed');
    
    currentStep = step;
    
    $(`#step-${currentStep}`).addClass('active');
    $(`#step-head-${currentStep}`).addClass('active');
}

function prevStep(step) {
    $(`#step-${currentStep}`).removeClass('active');
    $(`#step-head-${currentStep}`).removeClass('active');
    
    currentStep = step;
    
    $(`#step-${currentStep}`).addClass('active');
    $(`#step-head-${currentStep}`).addClass('active').removeClass('completed');
}

function updateCarbon() {
    let type = $('input[name="carbon_type"]:checked').val();
    if (!type) return;

    let params = { type: type };
    if (type === 'tree') {
        params.tree_count = $('#tree_count').val() || 0;
        params.tree_age = $('#tree_age').val() || 0;
        params.tree_height = $('#avg_height').val() || 0;
    } else {
        params.rice_area = $('#land_area').val() || 0;
    }

    $.get('../process/calculate_carbon_ajax.php', params, function(res) {
        if (res.success) {
            $('#carbon_amount_display').text(res.carbon.toFixed(2));
            $('#carbon_amount_val').val(res.carbon);
        }
    });
}

function previewImg(input, previewId) {
    if (input.files && input.files[0]) {
        let reader = new FileReader();
        reader.onload = function(e) {
            $(`#${previewId}`).html(`<img src="${e.target.result}">`);
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function showConfirmModal() {
    // Checkboxes check
    if (!$('#check_truth').is(':checked') || !$('#check_consent').is(':checked')) {
        Swal.fire('แจ้งเตือน', 'กรุณาติ๊กยินยอมและยืนยันข้อมูลให้ครบ', 'warning');
        return;
    }

    // Required Step 3 fields
    if (!$('#img_main').val() || !$('#img_id').val() || !$('#doc_land').val() || !$('#price').val()) {
        Swal.fire('แจ้งเตือน', 'กรุณาอัปโหลดรูปภาพ เอกสารสิทธิ์ที่ดิน และระบุราคาให้ครบ', 'warning');
        return;
    }

    let type = $('input[name="carbon_type"]:checked').val();
    let typeName = type === 'tree' ? 'ต้นไม้' : 'นาข้าว';
    
    let html = `
        <div class="col-6"><small class="text-muted d-block">ประเภท</small><strong>${typeName}</strong></div>
        <div class="col-6"><small class="text-muted d-block">จังหวัด</small><strong>${$('#province').val()}</strong></div>
        <div class="col-6"><small class="text-muted d-block">ปริมาณคาร์บอน</small><strong class="text-success">${$('#carbon_amount_display').text()} Ton</strong></div>
        <div class="col-6"><small class="text-muted d-block">ราคาที่ตั้ง</small><strong class="text-primary">${$('#price').val()} CC/ตัน</strong></div>
        <div class="col-12 mt-3"><div class="alert alert-info py-2 small">ข้อมูลส่วนตัวและรูปถ่ายจะถูกนำไปตรวจสอบความถูกต้องโดยเจ้าหน้าที่เท่านั้น</div></div>
    `;

    $('#confirmData').html(html);
    let myModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    myModal.show();
}
</script>

<?php include '../includes/alerts.php'; ?>
</body>
</html>
