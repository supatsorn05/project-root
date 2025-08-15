# Requirements Overview

## 1. User Roles & Permissions
- **Admin**
  - สร้าง / แก้ไข / ลบ ผู้ใช้ (User)
  - กำหนดสถานะ Active / Inactive ของผู้ใช้
  - ดูรายงานการอัปโหลด และสถิติการใช้งาน
- **Student**
  - สมัคร / ล็อกอิน (ถ้ามีชื่อในระบบ)
  - ดูและดาวน์โหลดเอกสารโปรเจกต์ย้อนหลัง
  - อัปโหลดไฟล์ CS01–CS05 และตัวอย่างรายงาน (.pdf, .docx)
- **Teacher**
  - ทำได้เหมือน Student
  - (ถ้ามีสิทธิพิเศษอื่น เติมตรงนี้)

## 2. Functional Requirements
1. **Authentication & Authorization**
   - Login ด้วยอีเมล + รหัสผ่าน
   - Session-based PHP
   - เข้ารหัสรหัสผ่านด้วย bcrypt
   - เปลี่ยนรหัสผ่าน / ลืมรหัสผ่าน (Optional)
2. **Document Management**
   - อัปโหลด / ดาวน์โหลดไฟล์ (PDF, DOCX)
   - แยกประเภทไฟล์: CS01, CS02, CS03, CS04, CS05, ตัวอย่างรายงาน
   - เก็บข้อมูล: ปีที่อัปโหลด, ผู้ส่ง, สถานะ (รอเซ็น, เซ็นแล้ว, ปฏิเสธ)
   - ขนาดไฟล์สูงสุดและประเภทไฟล์ที่ยอมรับ
3. **Project Archive**
   - จัดเก็บโปรเจกต์ย้อนหลัง 5 ปี
   - จัดหมวดหมู่ตามปีและประเภท (AI, Web, Embedded ฯลฯ)
4. **Admin Dashboard**
   - ดูรายการผู้ใช้ทั้งหมด
   - ดูสถิติการอัปโหลดรายเดือน / รายปี
   - จัดการเอกสาร (เซ็นเอกสาร / อนุมัติ / ปฏิเสธ)
5. **Notifications (Optional)**
   - แจ้งเตือนทางอีเมลเมื่อเอกสารถูกอนุมัติหรือปฏิเสธ
   - แสดงสถานะล่าสุดบนหน้า Dashboard

## 3. Non-functional Requirements
- **Performance**: ตอบสนองภายใน 2 วินาที
- **Security**:
  - ป้องกัน SQL Injection, XSS, CSRF
  - บังคับใช้ HTTPS (ใน production)
- **Scalability**: รองรับผู้ใช้ ~500 คนพร้อมกัน
- **Usability**: UI เรียบง่าย รองรับบนมือถือ

## 4. Technical Stack
- **Backend**: PHP 8.x, Apache
- **Database**: MySQL (utf8mb4)
- **Frontend**: Vue.js (Vite)
- **Dev Tools**: Git, Node.js, phpMyAdmin, Postman/Insomnia

## 5. User Stories
- “ในฐานะ Student ฉันอยากล็อกอินเพื่อดูไฟล์โปรเจกต์ของฉัน”
- “ในฐานะ Admin ฉันต้องการสร้างผู้ใช้ใหม่และมอบหมายบทบาทให้”
- “ในฐานะ Teacher ฉันอยากอัปโหลด CS03 และดูสถานะการอนุมัติ”

---

> **หมายเหตุ:**  
> - ลองอ่านให้ละเอียด แล้วเติมกรณี edge-case (เช่น ไฟล์ซ้ำ, ขนาดเกิน)  
> - เพิ่มสิทธิพิเศษของ Teacher (ถ้ามี)  
> - เติมฟังก์ชัน optional ทีหลัง เช่น “ลืมรหัสผ่าน”, “แจ้งเตือนอีเมล”

