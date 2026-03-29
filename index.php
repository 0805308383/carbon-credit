<?php
include 'config/db.php';

if ($conn) {
  echo "DB Connected ✅";
} else {
  echo "DB Failed ❌";
}
?>
<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carbon Market - ซื้อขายคาร์บอนเครดิตแบบมืออาชีพ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --secondary: #0f172a;
            --bg-light: #f8fafc;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        body {
            font-family: 'Kanit', 'Inter', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            color: var(--text-dark);
        }
        /* Navbar Glassmorphism */
        .navbar-pro {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 5%;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .navbar-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            margin-right: 1.5rem;
            transition: color 0.2s;
        }
        .navbar-links a:hover {
            color: var(--primary);
        }
        .btn-pro {
            background: var(--primary);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-pro:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -2px rgba(16, 185, 129, 0.4);
        }
        .btn-outline-pro {
            background: transparent;
            color: var(--text-dark) !important;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            border: 2px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-outline-pro:hover {
            border-color: var(--primary);
            color: var(--primary) !important;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding: 0 5%;
            margin-top: 70px; /* Offset for navbar */
            background: radial-gradient(circle at top right, rgba(16,185,129,0.1), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(16,185,129,0.05), transparent 40%);
        }
        .hero-content {
            flex: 1;
            max-width: 600px;
            animation: slideUp 0.8s ease forwards;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--secondary);
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }
        .hero h1 span {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero p {
            font-size: 1.2rem;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            line-height: 1.6;
        }
        .hero-graphics {
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            position: relative;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            position: relative;
            z-index: 2;
            width: 80%;
            max-width: 450px;
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        .circle-blur {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            z-index: 1;
        }
        .circle-1 {
            width: 300px;
            height: 300px;
            background: rgba(16, 185, 129, 0.3);
            top: 10%;
            right: 20%;
        }
        .circle-2 {
            width: 250px;
            height: 250px;
            background: rgba(52, 211, 153, 0.2);
            bottom: 10%;
            right: 0%;
        }

        /* Features Section */
        .features-section {
            padding: 5rem 5%;
            background: white;
            position: relative;
        }
        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem auto;
        }
        .section-header h2 {
            font-size: 2.5rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        .section-header p {
            font-size: 1.1rem;
            color: var(--text-muted);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        .feature-card {
            background: var(--bg-light);
            border-radius: 16px;
            padding: 2.5rem 2rem;
            text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(0,0,0,0.03);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px -5px rgba(0,0,0,0.1);
            background: white;
            border-color: rgba(16, 185, 129, 0.2);
        }
        .feature-icon-wrapper {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.05));
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem auto;
        }
        .feature-card h3 {
            font-size: 1.3rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        .feature-card p {
            color: var(--text-muted);
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: var(--secondary);
            color: rgba(255,255,255,0.7);
            padding: 4rem 5% 2rem 5%;
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            max-width: 1200px;
            margin: 0 auto;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 2rem;
            margin-bottom: 2rem;
        }
        .footer-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .footer-copyright {
            text-align: center;
            font-size: 0.9rem;
        }

        /* Media Queries */
        @media(max-width: 900px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding-top: 4rem;
                padding-bottom: 4rem;
            }
            .hero-graphics {
                margin-top: 3rem;
                justify-content: center;
            }
            .hero h1 { font-size: 2.5rem; }
            .glass-card { width: 100%; max-width: 100%; }
        }
    </style>
</head>
<body>

<nav class="navbar-pro">
    <a href="index.php" class="navbar-brand">
        <i class="fa-solid fa-leaf"></i> CarbonMarket
    </a>
    <div class="navbar-links">
        <a href="auth/login.php" class="btn-outline-pro"><i class="fa-solid fa-arrow-right-to-bracket"></i> เข้าสู่ระบบ</a>
        <a href="auth/register.php" class="btn-pro"><i class="fa-solid fa-user-plus"></i> สมัครสมาชิก</a>
    </div>
</nav>

<section class="hero">
    <div class="hero-content">
        <h1>เปลี่ยนพื้นที่สีเขียวของคุณให้เป็น <span>รายได้ที่ยั่งยืน</span></h1>
        <p>แพลตฟอร์มกลางระดับพรีเมียมสำหรับซื้อขายคาร์บอนเครดิต เชื่อมต่อผู้ผลิตและผู้ซื้อโดยตรง ด้วยระบบที่โปร่งใส ปลอดภัย และตรวจสอบได้ 100%</p>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="auth/register.php" class="btn-pro" style="padding: 0.8rem 2rem; font-size: 1.1rem;">
                เริ่มต้นใช้งานฟรี <i class="fa-solid fa-arrow-right"></i>
            </a>
            <a href="#features" class="btn-outline-pro" style="padding: 0.8rem 2rem; font-size: 1.1rem;">
                เรียนรู้เพิ่มเติม
            </a>
        </div>
        
        <div style="margin-top: 3rem; display: flex; gap: 2rem; align-items: center;">
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 2rem; font-weight: 800; color: var(--secondary);">1k+</span>
                <span style="color: var(--text-muted); font-size: 0.9rem;">ผู้ใช้งานในระบบ</span>
            </div>
            <div style="width: 1px; height: 40px; background: #e2e8f0;"></div>
            <div style="display: flex; flex-direction: column;">
                <span style="font-size: 2rem; font-weight: 800; color: var(--primary);">2.5M</span>
                <span style="color: var(--text-muted); font-size: 0.9rem;">ตันคาร์บอนที่ลดได้</span>
            </div>
        </div>
    </div>
    
    <div class="hero-graphics">
        <div class="circle-blur circle-1"></div>
        <div class="circle-blur circle-2"></div>
        <div class="glass-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">
                        <i class="fa-solid fa-tree"></i>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--secondary); font-size:1.1rem;">ยอดจำหน่ายล่าสุด</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">สวนป่าพังงา (🌳 ต้นไม้)</div>
                    </div>
                </div>
                <div style="font-weight: 800; color: var(--primary); font-size:1.25rem;">+20.70 CC</div>
            </div>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="background: rgba(255,255,255,0.7); padding: 1.25rem; border-radius: 12px; display: flex; justify-content: space-between; border: 1px solid rgba(0,0,0,0.03);">
                    <span style="color: var(--text-muted); font-weight:500;">ราคาประเมิน</span>
                    <strong style="color: var(--secondary); font-size:1.1rem;">76,890.00 THB</strong>
                </div>
                <div style="background: linear-gradient(135deg, rgba(16,185,129,0.1), rgba(16,185,129,0.05)); padding: 1.25rem; border-radius: 12px; display: flex; justify-content: space-between; border: 1px solid rgba(16,185,129,0.2);">
                    <span style="color: var(--primary-dark); font-weight: 600;">สถานะการซื้อขาย</span>
                    <strong style="color: var(--primary); font-size:1.05rem; display:flex; align-items:center; gap:0.5rem;">พร้อมจำหน่าย <i class="fa-solid fa-circle-check"></i></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="features" class="features-section">
    <div class="section-header">
        <h2>ทำไมถึงต้องเลือก <span style="color:var(--primary);">CarbonMarket</span> ?</h2>
        <p>เราออกแบบระบบที่ตอบโจทย์ทั้งผู้ประเมิน ผู้ซื้อ และผู้ขาย ด้วยเทคโนโลยีที่ทันสมัย โปร่งใส และใช้งานง่าย</p>
    </div>
    
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:linear-gradient(135deg, #ecfdf5, #d1fae5);">
                <i class="fa-solid fa-seedling" style="color:#10b981;"></i>
            </div>
            <h3>สำหรับผู้ขาย (Sellers)</h3>
            <p>ลงทะเบียนพื้นที่ปลูกป่าหรือนาข้าวของคุณเข้าสู่ระบบ คำนวณคาร์บอนเครดิตแม่นยำ และเปลี่ยนพื้นที่สีเขียวเป็นรายได้แบบยั่งยืน</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:linear-gradient(135deg, #eff6ff, #dbeafe);">
                <i class="fa-solid fa-industry" style="color:#3b82f6;"></i>
            </div>
            <h3>สำหรับผู้ซื้อ (Buyers)</h3>
            <p>ชดเชยการปล่อยก๊าซเรือนกระจกขององค์กรหรือบุคคลได้อย่างง่ายดาย สนับสนุนโครงการและเกษตรกรที่ดูแลสิ่งแวดล้อม</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon-wrapper" style="background:linear-gradient(135deg, #fef3c7, #fde68a);">
                <i class="fa-solid fa-shield-halved" style="color:#f59e0b;"></i>
            </div>
            <h3>ความปลอดภัยสูงสุด</h3>
            <p>ทุกธุรกรรมผ่านการยืนยันตัวตน มีระบบ OTP และตรวจสอบสถานะคำสั่งซื้อได้ 100% โปร่งใส ไร้กังวล ป้องกันการฉ้อโกง</p>
        </div>
    </div>
</section>

<footer>
    <div class="footer-content">
        <div style="flex:1; min-width:300px;">
            <div class="footer-brand">
                <i class="fa-solid fa-leaf"></i> CarbonMarket
            </div>
            <p style="max-width: 350px; margin-top: 1.5rem; line-height: 1.8; color:rgba(255,255,255,0.6);">
                ร่วมกันสร้างโลกที่น่าอยู่ผ่านวิถีคาร์บอนต่ำ สนับสนุนเกษตรกรและชุมชนในการดูแลรักษาพื้นที่สีเขียว เพื่ออนาคตที่ยั่งยืน
            </p>
        </div>
        <div style="display: flex; gap: 4rem; flex-wrap:wrap;">
            <div>
                <h4 style="color: white; margin-bottom: 1.5rem; font-size:1.1rem;">เมนู</h4>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="auth/login.php" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">เข้าสู่ระบบ</a>
                    <a href="auth/register.php" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">สมัครสมาชิกผู้ขาย</a>
                    <a href="auth/register.php" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">สมัครสมาชิกผู้ซื้อ</a>
                </div>
            </div>
            <div>
                <h4 style="color: white; margin-bottom: 1.5rem; font-size:1.1rem;">ความช่วยเหลือ</h4>
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <a href="#" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">คำถามที่พบบ่อย (FAQ)</a>
                    <a href="#" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">ติดต่อทีมสนับสนุน</a>
                    <a href="#" style="color: rgba(255,255,255,0.6); text-decoration: none; transition:color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">นโยบายความเป็นส่วนตัว</a>
                </div>
            </div>
        </div>
    </div>
    <div class="footer-copyright">
        &copy; <?= date("Y"); ?> Carbon Credit Simulator. All rights reserved.
    </div>
</footer>

<?php include 'includes/alerts.php'; ?>
</body>
</html>
