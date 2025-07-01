<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบประเมิน ITA - มาตรฐานความโปร่งใส MOIT</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Sarabun', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .header h1 {
            color: #2c3e50;
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .stats-bar {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 15px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .assessment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .moit-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .moit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }

        .moit-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ecf0f1;
        }

        .moit-number {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .moit-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            line-height: 1.4;
        }

        .progress-container {
            margin: 15px 0;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: #ecf0f1;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 10px;
            transition: width 0.8s ease;
        }

        .progress-text {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            font-size: 0.9rem;
            color: #7f8c8d;
        }

        .requirements-list {
            list-style: none;
            margin-top: 15px;
        }

        .requirements-list li {
            padding: 8px 0;
            border-bottom: 1px solid #ecf0f1;
            font-size: 0.9rem;
            color: #5a6c7d;
            display: flex;
            align-items: center;
        }

        .requirements-list li:last-child {
            border-bottom: none;
        }

        .check-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #2ecc71;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-size: 10px;
            flex-shrink: 0;
        }

        .incomplete {
            background: #e74c3c;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #ecf0f1;
            color: #7f8c8d;
        }

        .btn-secondary:hover {
            background: #d5dbdb;
        }

        .summary-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }

        .summary-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .category-item {
            text-align: center;
            padding: 20px;
            background: linear-gradient(45deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 15px;
            transition: transform 0.3s ease;
        }

        .category-item:hover {
            transform: scale(1.05);
        }

        .category-score {
            font-size: 2.5rem;
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        .category-name {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .export-section {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 2px solid #ecf0f1;
        }

        @media (max-width: 768px) {
            .assessment-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                flex-direction: column;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .category-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header fade-in">
            <h1>ระบบประเมิน ITA</h1>
            <p>Information Technology Assessment - มาตรฐานความโปร่งใสและการต่อต้านการทุจริต MOIT</p>
            <p><strong>ปีงบประมาณ พ.ศ. 2568</strong> | กระทรวงสาธารณสุข</p>
            
            <div class="stats-bar">
                <div class="stat-item">
                    <span class="stat-number" id="totalItems">22</span>
                    <span class="stat-label">รายการทั้งหมด</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="completedItems">14</span>
                    <span class="stat-label">ดำเนินการแล้ว</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="overallScore">64%</span>
                    <span class="stat-label">คะแนนรวม</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="daysLeft">45</span>
                    <span class="stat-label">วันที่เหลือ</span>
                </div>
            </div>
        </div>

        <div class="assessment-grid" id="moitGrid">
            <!-- MOIT items will be dynamically generated here -->
        </div>

        <div class="summary-section fade-in">
            <h2 class="summary-title">สรุปผลการประเมินตามหมวดหมู่</h2>
            
            <div class="category-grid">
                <div class="category-item">
                    <span class="category-score">75%</span>
                    <span class="category-name">การเปิดเผยข้อมูล</span>
                </div>
                <div class="category-item">
                    <span class="category-score">68%</span>
                    <span class="category-name">การจัดซื้อจัดจ้าง</span>
                </div>
                <div class="category-item">
                    <span class="category-score">58%</span>
                    <span class="category-name">การบริหารบุคคล</span>
                </div>
                <div class="category-item">
                    <span class="category-score">72%</span>
                    <span class="category-name">การต่อต้านทุจริต</span>
                </div>
                <div class="category-item">
                    <span class="category-score">45%</span>
                    <span class="category-name">การมีส่วนร่วม</span>
                </div>
                <div class="category-item">
                    <span class="category-score">82%</span>
                    <span class="category-name">สิทธิมนุษยชน</span>
                </div>
            </div>

            <div class="export-section">
                <button class="btn btn-primary" onclick="generateReport()">📊 สร้างรายงานสรุป</button>
                <button class="btn btn-secondary" onclick="exportData()">📁 ส่งออกข้อมูล</button>
            </div>
        </div>
    </div>

    <script>
        const moitData = [
            {
                number: 1,
                title: "MOIT 1 : หน่วยงานมีการวางระบบโดยการกำหนดมาตรการการเผยแพร่ข้อมูลต่อสาธารณะผ่านเว็บไซต์ของ หน่วยงาน",
                progress: 100,
                requirements: [
                    "คำสั่ง / ประกาศ ที่ระบุกรอบแนวทาง",
                    "รายงานผลการกำกับติดตามการเผยแพร่ข้อมูลต่อสาธารณะผ่านเว็บไซต์ของหน่วยงาน ในปีที่ผ่านมา (ของปีงบประมาณ พ.ศ. 2567)"
                ],
                category: "transparency"
            },
            {
                number: 2,
                title: "MOIT 2 : หน่วยงานมีการเปิดเผยข้อมูลข่าวสารที่เป็นปัจจุบันและเป็นข้อมูลที่จำเป็นต่อการปฏิบัติงานของหน่วยงาน ตามกรอบแนวทางการเปิดเผยข้อมูลข่าวสารของหน่วยงาน",
                progress: 100,
                requirements: [
                    "1. ข้อมูลพื้นฐานที่เป็นปัจจุบัน ประกอบด้วย",
                    "1.1 ข้อมูลผู้บริหาร",
                    "1.2 นโยบายของผู้บริหาร",
                    "1.3 โครงสร้างหน่วยงาน",
                    "1.4 หน้าที่และอำนาจของหน่วยงานตามกฎหมายจัดตั้ง หรือกฎหมายอื่นที่เกี่ยวข้อง",
                    "1.5 กฎหมายที่เกี่ยวข้องกับการดำเนินงาน หรือการปฏิบัติงานของหน่วยงาน",
                    "1.6 ข่าวประชาสัมพันธ์ ที่แสดงข้อมูลข่าวสารที่เกี่ยวกับการดำเนินงานตามหน้าที่และอำนาจ และภารกิจของหน่วยงาน และเป็นข้อมูลข่าวสารที่เกิดขึ้นในปีงบประมาณ พ.ศ. 2567",
                    "1.7 ข้อมูลการติดต่อหน่วยงาน",
                    "1.8 ช่องทางการรับฟังความคิดเห็นที่บุคคลภายนอกสามารถแสดงความคิดเห็นต่อการดำเนินงาน ตามหน้าที่และอำนาจและตามภารกิจของหน่วยงาน",
                    "วิสัยทัศน์ พันธกิจ ค่านิยม MOPH",
                    "พระราชบัญญัติมาตรฐานทางจริยธรรม พ.ศ. 2562",
                    "ประมวลจริยธรรมข้าราชการพลเรือน พ.ศ. 2564",
                    "ข้อกำหนดจริยธรรมเจ้าหน้าที่ของรัฐสำนักงานปลัดกระทรวงสาธารณสุข พ.ศ. 2564",
                    "ยุทธศาสตร์และแผนระดับชาติ จำนวน 3 ระดับ ประกอบด้วย",
                    "6.1 แผนระดับที่ 1 ได้แก่ ยุทธศาสตร์ชาติ พ.ศ. 2561-2580",
                    "6.2 แผนระดับที่ 2 ได้แก่",
                    "6.2.1 แผนแม่บทภายใต้ยุทธศาสตร์ชาติ (พ.ศ. 2566-2580) (ฉบับแก้ไขเพิ่มเติม)",
                    "6.2.2 แผนพัฒนาเศรษฐกิจและสังคมแห่งชาติ ฉบับที่ 13 (พ.ศ. 2566-2570)",
                    "6.2.3 นโยบายและแผนระดับชาติว่าด้วยความมั่นคงแห่งชาติ (พ.ศ. 2566-2570)",
                    "6.3 แผนระดับที่ 3 ที่เกี่ยวข้องกับการต่อต้านการทุจริตและประพฤติมิชอบ และการส่งเสริม คุณธรรม จริยธรรม ได้แก่",
                    "6.3.1 แผนปฏิบัติการด้านการต่อต้านการทุจริตและประพฤติมิชอบ ระยะที่ 2 (พ.ศ. 2566-2570)",
                    "6.3.2 แผนปฏิบัติการด้านการส่งเสริมคุณธรรมแห่งชาติ ระยะที่ 2 (พ.ศ. 2566-2570)",
                    "6.3.3 ยุทธศาสตร์ด้านมาตรฐานทางจริยธรรมและการส่งเสริมจริยธรรมภาครัฐ (พ.ศ. 2565-2570)",
                    "แผนปฏิบัติการด้านการป้องกันปราบปรามการทุจริตและประพฤติมิชอบ และการส่งเสริม คุณธรรม จริยธรรม ของกระทรวงสาธารณสุข ประกอบด้วย",
                    "7.1 แผนปฏิบัติราชการด้านการป้องกัน ปราบปรามการทุจริตและประพฤติมิชอบ กระทรวงสาธารณสุข ระยะที่ 2 (พ.ศ. 2566-2570)",
                    "7.2 แผนปฏิบัติราชการด้านการส่งเสริมคุณธรรม จริยธรรม กระทรวงสาธารณสุข ระยะที่ 2 (พ.ศ. 2566-2570)",
                    "นโยบายและยุทธศาสตร์ของหน่วยงาน",
                    "แผนปฏิบัติการประจำปีของหน่วยงาน (แผนปฏิบัติการประจำปีของหน่วยงานทุกแผน)",
                    "รายงานผลการดำเนินงานตามแผนปฏิบัติการประจำปีของหน่วยงาน (เป็นไปตามข้อ 9.)",
                    "แผนการใช้จ่ายงบประมาณประจำปีของหน่วยงาน และผลการใช้จ่ายงบประมาณ ประจำปีของหน่วยงาน ตามแผนการใช้จ่ายงบประมาณประจำปีของหน่วยงาน",
                    "คู่มือการปฏิบัติงานการร้องเรียนการปฏิบัติงานหรือให้บริการของเจ้าหน้าที่",
                    "คู่มือการปฏิบัติงานการร้องเรียนการทุจริตและประพฤติมิชอบ",
                    "คู่มือการปฏิบัติงานตามภารกิจหลักและภารกิจสนับสนุนของหน่วยงาน",
                    "คู่มือขั้นตอนการให้บริการ (ภารกิจให้บริการประชาชนตามพระราชบัญญัติ การอำนวยความสะดวกในการพิจารณาอนุญาตของทางราชการ พ.ศ. 2558) (เฉพาะสำนักงานสาธารณสุขจังหวัด และสำนักงานสาธารณสุขอำเภอ)",
                    "รายงานผลการดำเนินการเกี่ยวกับเรื่องร้องเรียนการปฏิบัติงานหรือการให้บริการ ประจำปีงบประมาณ พ.ศ. 2567",
                    "รอบ 12 เดือน ประจำปีงบประมาณ พ.ศ. 2567",
                    "รายงานผลการดำเนินการเกี่ยวกับเรื่องร้องเรียนการทุจริตและประพฤติมิชอบ ประจำปีงบประมาณ พ.ศ. 2567",
                    "รอบ 12 เดือน ประจำปีงบประมาณ พ.ศ. 2567",
                    "ข้อมูลการจัดซื้อจัดจ้าง ประกอบด้วย",
                    "18.1 การวิเคราะห์ผลการจัดซื้อจัดจ้างและการจัดหาพัสดุของปีงบประมาณ พ.ศ. 2567",
                    "18.2 แผนการจัดซื้อจัดจ้างและการจัดหาพัสดุ ประจำปีงบประมาณ พ.ศ. 2568",
                    "18.3 ผลการดำเนินการตามแผนการจัดซื้อจัดจ้างและการจัดหาพัสดุ ประจำปี งบประมาณ พ.ศ. 2568 ตามรอบระยะเวลาที่กำหนดในกรอบแนวทางของหน่วยงาน",
                    "18.4 ประกาศสำนักงานปลัดกระทรวงสาธารณสุขว่าด้วยแนวทางปฏิบัติงาน เพื่อตรวจสอบบุคลากรในหน่วยงานด้านการจัดซื้อจัดจ้าง พ.ศ. 2560 และแบบแสดงความบริสุทธิ์ใจในการจัดซื้อจัดจ้างของหน่วยงานในการเปิดเผยข้อมูล ความขัดแย้งทางผลประโยชน์ของหัวหน้าเจ้าหน้าที่"
                ],
                category: "transparency"
            },
            {
                number: 3,
                title: "MOIT 3 : หน่วยงานมีรายงานการวิเคราะห์ผลการจัดซื้อจัดจ้างและการจัดหาพัสดุ ของปีงบประมาณ พ.ศ. 2568",
                progress: 100,
                requirements: [
                    "รายงานวิเคราะห์ปี 2567",
                    "เตรียมรายงานปี 2568"
                ],
                category: "procurement"
            },
            {
                number: 4,
                title: "MOIT 4 : หน่วยงานมีการวางระบบการจัดซื้อจัดจ้างและการจัดหาพัสดุ ประจำปีงบประมาณ พ.ศ. 2568",
                progress: 65,
                requirements: [
                    "ประกาศแผนจัดซื้อจัดจ้าง",
                    "รายงานผลรายไตรมาส",
                    "มาตรการป้องกันผลประโยชน์ทับซ้อน"
                ],
                category: "procurement"
            },
            {
                number: 5,
                title: "MOIT 5 : หน่วยงานมีการสรุปผลการจัดซื้อจัดจ้างและการจัดหาพัสดุรายเดือน ประจำปีงบประมาณ พ.ศ. 2568",
                progress: 45,
                requirements: [
                    "ไตรมาส 1 แสดงแบบ สขร. 1 เดือนตุลาคม 2567-ธันวาคม 2567",
                    "เดือน ตุลาคม 2567",
                    "เดือน พฤศจิกายน 2567",
                    "เดือน ธันวาคม 2567",
                    "ไตรมาส 2 แสดงแบบ สขร. 1 เดือนมกราคม 2568-มีนาคม 2568",
                    "เดือน มกราคม 2568",
                    "เดือน กุมภาพันธ์ 2568",
                    "เดือน มีนาคม 2568",
        "- ไตรมาส 3 แสดงแบบ สขร. 1 เดือนเมษายน 2568-มิถุนายน 2568",
     "เดือน เมษายน 2568",
     "เดือน พฤษภาคม 2568",
    "เดือน มิถุนายน 2568",
    "- ไตรมาส 4 แสดงแบบ สขร. 1 เดือนกรกฎาคม 2568-กันยายน 2568",
    "เดือน กรกฎาคม 2568",
    "เดือน สิงหาคม 2568",
    "เดือน กันยายน 2568"
                ],
                category: "procurement"
            },
            {
                number: 6,
                title: "MOIT 6 : ผู้บริหารแสดงนโยบายการบริหารและพัฒนาทรัพยากรบุคคล",
                progress: 88,
                requirements: [
                    "นโยบายบริหารทรัพยากรบุคคล",
                    "แผนการบริหารทรัพยากรบุคคล"
                ],
                category: "hr"
            },
            {
                number: 7,
                title: "MOIT 7 : หน่วยงานมีการรายงานการประเมินและเกี่ยวกับการประเมินผลการปฏิบัติราชการ ของบุคลากรในหน่วยงาน และเปิดเผยผลการปฏิบัติราชการ ระดับดีเด่น และระดับดีมาก ในที่เปิดเผยให้ทราบปีงบประมาณ พ.ศ. 2567 และปีงบประมาณ พ.ศ. 2568",
                progress: 52,
                requirements: [
                    "รายงานประเมินผลรอบ 2 ปี 2567",
                    "รายงานประเมินผลรอบ 1 ปี 2568"
                ],
                category: "hr"
            },
            {
                number: 8,
                title: "MOIT 8 : หน่วยงานมีการอบรมให้ความรู้แก่เจ้าหน้าที่ภายในหน่วยงานเกี่ยวกับการเสริมสร้าง และพัฒนาทางด้านจริยธรรม และการรักษาวินัย รวมทั้งการป้องกันมิให้กระทำผิดวินัย (0) ปีงบประมาณ พ.ศ. 2568",
                progress: 35,
                requirements: [
                    "หลักสูตรอบรมด้านจริยธรรม ปี 2568"
                ],
                category: "hr"
            },
            {
                number: 9,
                title: "MOIT 9 : หน่วยงานมีแนวปฏิบัติการจัดการเรื่องร้องเรียน และช่องทางการร้องเรียน",
                progress: 95,
                requirements: [
                    "คู่มือปฏิบัติการการดำเนินงานเรื่องร้องเรียนการปฏิบัติงานหรือการให้บริการ ของเจ้าหน้าที่ภายในหน่วยงาน ที่มีแบบฟอร์มการเผยแพร่ข้อมูลต่อสาธารณะผ่านเว็บไซต์ ของหน่วยงาน",
                    "คู่มือปฏิบัติการการดำเนินงานเรื่องร้องเรียนการทุจริตและประพฤติมิชอบ",
                    "ช่องทางการร้องเรียน ตามข้อ 1. และข้อ 2. อาทิ ผ่านระบบ หมายเลขโทรศัพท์ ผ่านระบบอินเตอร์เน็ต ผ่านระบบไปรษณีย์ ผ่าน Application หรือช่องทางอื่น ๆ ที่หน่วยงานกำหนดตามความเหมาะสม"
                ],
                category: "complaints"
            },
            {
                number: 10,
                title: "MOIT 10 : หน่วยงานมีสรุปผลการดำเนินงานเรื่องร้องเรียนการปฏิบัติงานหรือการให้บริการ ของเจ้าหน้าที่ภายในหน่วยงาน และเรื่องร้องเรียนการทุจริตและประพฤติมิชอบ",
                progress: 68,
                requirements: [
                    "ไตรมาสที่ 2 (สรุปผลการดำเนินงานฯ รอบ 6 เดือน 1 ตุลาคม 2567-31 มีนาคม 2568)",
                    "ไตรมาสที่ 4 (สรุปผลการดำเนินงานฯ รอบ 12 เดือน 1 ตุลาคม 2567-31 สิงหาคม 2568)"
                ],
                category: "complaints"
            },
            {
                number: 11,
                title: "MOIT 11 : หน่วยงานของท่านเปิดโอกาสให้ผู้มีส่วนได้ส่วนเสียมีโอกาสเข้ามามีส่วนร่วมในการดำเนินงาน ตามภารกิจของหน่วยงาน",
                progress: 45,
                requirements: [
                    "หลักฐานโครงการ / กิจกรรมที่ดำเนินการ ตั้งแต่วันที่ 1 ตุลาคม 2567 ถึงวันที่ 31 สิงหาคม 2568",
                    "หลักฐานโครงการ / กิจกรรมที่แสดงให้เห็นถึงกระบวนการมีส่วนร่วม ตั้งแต่ (1) กระบวนการ มีส่วนร่วมในการวางแผน (2) กระบวนการมีส่วนร่วมในการดำเนินการ และ (3) กระบวนการมีส่วนร่วม ในการติดตามประเมินผล",
                    "หลักฐานแสดงถึงการมีส่วนร่วมตามกระบวนการในข้อ 2. โดยจะต้องระบุรายละเอียด ของผู้มีส่วนได้ส่วนเสียที่เข้ามาร่วมในการดำเนินการในแต่ละขั้นตอนด้วย",
                    "ภาพกิจกรรม ต้องระบุวัน เวลา และสถานที่จัดกิจกรรมการประชุม / สัมมนา",
                    "ผู้บังคับบัญชา จะต้องสั่งการหรืออนุญาตให้นำรายละเอียดการดำเนินงานไปเผยแพร่ ผ่านเว็บไซต์ของหน่วยงาน และมีแบบฟอร์มการเผยแพร่ข้อมูลต่อสาธารณะผ่านเว็บไซต์ของหน่วยงาน หรือสื่อสารเผยแพร่ในช่องทางอื่น"
                ],
                category: "participation"
            },
            {
                number: 12,
                title: "MOIT 12 : หน่วยงานมีมาตรการ “การป้องกันการรับสินบน” ที่เป็นระบบ",
                progress: 82,
                requirements: [
                    "ประกาศ No Gift Policy",
                    "มาตรการป้องกันในกระบวนการต่างๆ"
                ],
                category: "anticorruption"
            },
            {
                number: 13,
                title: "ประเมินเกณฑ์จริยธรรมการจัดซื้อยาและเวชภัณฑ์",
                progress: 0,
                requirements: [
                    "รายงานการประเมินตามเกณฑ์ จ.ศ. 2564",
                    "ดำเนินการให้เสร็จในไตรมาส 4"
                ],
                category: "anticorruption"
            },
            {
                number: 14,
                title: "แนวทางการใช้ทรัพย์สินของราชการ",
                progress: 75,
                requirements: [
                    "แนวทางปฏิบัติการใช้ทรัพย์สิน",
                    "ขั้นตอนการขออนุญาตยืม"
                ],
                category: "assets"
            },
            {
                number: 15,
                title: "แผนปฏิบัติการป้องกันทุจริตและส่งเสริมคุณธรรม",
                progress: 90,
                requirements: [
                    "แผนป้องกันทุจริต ปี 2568",
                    "แผนส่งเสริมคุณธรรมชมรมจริยธรรม"
                ],
                category: "anticorruption"
            },
            {
                number: 16,
                title: "รายงานผลการดำเนินงานตามแผนป้องกันทุจริต",
                progress: 55,
                requirements: [
                    "รายงานผลรอบ 6 เดือน",
                    "รายงานผลรอบ 12 เดือน"
                ],
                category: "anticorruption"
            },
            {
                number: 17,
                title: "การประเมินความเสี่ยงการทุจริต",
                progress: 72,
                requirements: [
                    "การประชุมจัดทำแผนบริหารความเสี่ยง",
                    "รายงานแผนบริหารความเสี่ยง ปี 2568"
                ],
                category: "risk"
            },
            {
                number: 18,
                title: "การปฏิบัติตามมาตรการป้องกันการทุจริต",
                progress: 88,
                requirements: [
                    "การนำมาตรการป้องกันมาใช้"
                ],
                category: "anticorruption"
            },
            {
                number: 19,
                title: "รายงานการส่งเสริมการปฏิบัติตามประมวลจริยธรรม",
                progress: 42,
                requirements: [
                    "รายงานการเรี่ยไรรอบ 6 เดือน",
                    "รายงานการเรี่ยไรรอบ 12 เดือน"
                ],
                category: "ethics"
            },
            {
                number: 20,
                title: "การอบรมเรื่องผลประโยชน์ทับซ้อน",
                progress: 78,
                requirements: [
                    "หลักสูตรต้านทุจริตศึกษา",
                    "ประมวลผลภาพกิจกรรม"
                ],
                category: "training"
            },
            {
                number: 21,
                title: "การเผยแพร่เจตจำนงสุจริตและสิทธิมนุษยชน",
                progress: 95,
                requirements: [
                    "เจตนารมณ์ป้องกันทุจริต",
                    "เจตนารมณ์ป้องกันการล่วงละเมิดทางเพศ"
                ],
                category: "humanrights"
            },
            {
                number: 22,
                title: "MOIT 22 : หน่วยงานมีแนวปฏิบัติที่เคารพสิทธิมนุษยชนและศักดิ์ศรีของผู้ปฏิบัติงาน และรายงาน การป้องกันและแก้ไขปัญหาการล่วงละเมิดหรือคุกคามทางเพศในการทำงาน ประจำปีงบประมาณ พ.ศ. 2568",
                progress: 68,
                requirements: [
                    "คู่มือแนวทางปฏิบัติการป้องกันและแก้ไขปัญหาการล่วงละเมิดหรือคุกคามทางเพศในการทำงานของหน่วยงาน",
                    "รายงานผลการดำเนินงานตามมาตรการป้องกันและแก้ไขปัญหาการล่วงละเมิดทางเพศ ประจำปี 2568"
                ],
                category: "humanrights"
            }
        ];

        function renderMOITCards() {
            const grid = document.getElementById('moitGrid');
            grid.innerHTML = '';
            
            moitData.forEach((item, index) => {
                const card = document.createElement('div');
                card.className = 'moit-card fade-in';
                card.style.animationDelay = `${index * 0.1}s`;
                
                const requirementsList = item.requirements.map(req => 
                    `<li><span class="check-icon ${item.progress > 70 ? '' : 'incomplete'}">✓</span>${req}</li>`
                ).join('');
                
                card.innerHTML = `
                    <div class="moit-header">
                        <div class="moit-number">${item.number}</div>
                        <div class="moit-title">${item.title}</div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${item.progress}%"></div>
                        </div>
                        <div class="progress-text">
                            <span>ความคืบหน้า</span>
                            <span><strong>${item.progress}%</strong></span>
                        </div>
                    </div>
                    
                    <ul class="requirements-list">
                        ${requirementsList}
                    </ul>
                    
                    <div class="action-buttons">
                        <button class="btn btn-primary" onclick="viewDetails(${item.number})">ดูรายละเอียด</button>
                        <button class="btn btn-secondary" onclick="updateProgress(${item.number})">อัปเดต</button>
                    </div>
                `;
                
                grid.appendChild(card);
            });
        }

        function viewDetails(moitNumber) {
            alert(`กำลังเปิดรายละเอียด MOIT ${moitNumber}`);
        }

        function updateProgress(moitNumber) {
            const card = event.target.closest('.moit-card');
            card.classList.add('loading');
            
            setTimeout(() => {
                card.classList.remove('loading');
                alert(`อัปเดต MOIT ${moitNumber} เรียบร้อยแล้ว`);
            }, 1500);
        }

        function generateReport() {
            alert('กำลังสร้างรายงานสรุป...\n\nรายงานจะรวมถึง:\n- ผลการประเมินแต่ละรายการ\n- แผนภูมิเปรียบเทียบ\n- ข้อเสนอแนะการปรับปรุง');
        }

        function exportData() {
            const data = {
                timestamp: new Date().toISOString(),
                totalItems: moitData.length,
                completedItems: moitData.filter(item => item.progress >= 70).length,
                overallScore: Math.round(moitData.reduce((sum, item) => sum + item.progress, 0) / moitData.length),
                details: moitData
            };
            
            const dataStr = JSON.stringify(data, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `MOIT_Assessment_${new Date().toISOString().split('T')[0]}.json`;
            link.click();
        }

        // Initialize the dashboard
        document.addEventListener('DOMContentLoaded', function() {
            renderMOITCards();
            
            // Update stats
            const completedItems = moitData.filter(item => item.progress >= 70).length;
            const overallScore = Math.round(moitData.reduce((sum, item) => sum + item.progress, 0) / moitData.length);
            
            document.getElementById('completedItems').textContent = completedItems;
            document.getElementById('overallScore').textContent = overallScore + '%';
            
            // Animate progress bars
            setTimeout(() => {
                const progressBars = document.querySelectorAll('.progress-fill');
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 500);
                });
            }, 1000);
        });
    </script>
</body>
</html>