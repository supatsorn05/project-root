# แนวคิดเรื่องการยืนยันตัวตนและการจัดการเซสชัน (Authentication and Session Management)

เอกสารนี้อธิบายแนวคิดและขั้นตอนการทำงานของระบบยืนยันตัวตน (Login/Logout) และการจัดการเซสชัน (Session) ของแอปพลิเคชัน โดยใช้แนวทางแบบ Token-based และมีการตรวจสอบความถูกต้องของเซสชันฝั่งเซิร์ฟเวอร์

## ภาพรวม

ขั้นตอนการยืนยันตัวตนจะใช้ "Session Token" ที่สร้างขึ้นจากฝั่ง Backend หลังจากผู้ใช้เข้าสู่ระบบสำเร็จ Token นี้จะถูกส่งมาให้ Frontend เพื่อจัดเก็บไว้ในเครื่องของผู้ใช้ และในการเรียกใช้งาน API ที่ต้องมีการยืนยันตัวตนทุกครั้ง Frontend จะต้องส่ง Token นี้กลับไปใน `Authorization` Header เพื่อให้ Backend ตรวจสอบความถูกต้องจากค่าที่เก็บไว้ในฐานข้อมูล

- **การสร้าง Token**: ระบบจะสร้าง Token ขึ้นมาใหม่โดยใช้กระบวนการสุ่มที่ปลอดภัยในเชิง Cryptography
- **การจัดเก็บ Token (ฝั่ง Backend)**: Token ที่สร้างขึ้นจะถูก **แฮช (Hash)** ด้วย `SHA-256` ก่อน แล้วจึงนำค่าแฮชที่ได้ไปเก็บไว้ในคอลัมน์ `session_token` ของตาราง `users` **โดยระบบจะไม่เก็บ Token ตัวจริงไว้ในเซิร์ฟเวอร์เด็ดขาด**
- **การจัดเก็บ Token (ฝั่ง Frontend)**: Token **ตัวจริง (ที่ยังไม่ถูกแฮช)** จะถูกจัดเก็บไว้ใน `localStorage` ของเบราว์เซอร์

---

## ขั้นตอนการเข้าสู่ระบบ (Login Flow)

กระบวนการนี้ใช้เพื่อยืนยันตัวตนผู้ใช้และจ่าย Session Token ให้

1.  **ผู้ใช้ส่งข้อมูลเข้าระบบ**:
    -   ผู้ใช้กรอกอีเมล/ชื่อผู้ใช้ และรหัสผ่านในหน้าฟอร์ม Login (`Login.vue`)
    -   Frontend ส่ง `POST` request ไปยัง `/api/login.php` พร้อมกับข้อมูลที่ใช้ในการเข้าระบบ

2.  **Backend ตรวจสอบข้อมูล**:
    -   สคริปต์ `/api/login.php` รับข้อมูลที่ส่งมา
    -   ค้นหาข้อมูลผู้ใช้จากตาราง `users` ด้วยอีเมลหรือชื่อผู้ใช้ที่ได้รับ
    -   ใช้ฟังก์ชัน `password_verify()` เพื่อเปรียบเทียบรหัสผ่านที่ผู้ใช้กรอกกับรหัสผ่านที่ถูกแฮชและเก็บไว้ในฐานข้อมูลอย่างปลอดภัย
    -   หากข้อมูลไม่ถูกต้อง ระบบจะตอบกลับด้วยสถานะ `401 Unauthorized`

3.  **Backend สร้างและจัดเก็บ Token**:
    -   หากข้อมูลถูกต้อง Backend จะสร้าง Token ใหม่ที่ปลอดภัยแบบสุ่ม (เช่น `bin2hex(random_bytes(32))`)
    -   นำ Token ที่สร้างขึ้นไปแฮชด้วย `SHA-256`
    -   อัปเดตข้อมูลในตาราง `users` โดยเก็บ **ค่าแฮชของ Token** ลงในคอลัมน์ `session_token` ของผู้ใช้นั้นๆ

4.  **Backend ตอบกลับไปยัง Frontend**:
    -   Backend ส่งสถานะ `200 OK` กลับไป พร้อมกับข้อมูล:
        -   สถานะ `success`
        -   ข้อมูลผู้ใช้ (ยกเว้นรหัสผ่าน)
        -   **Token ตัวจริง (ที่ยังไม่ถูกแฮช)**

5.  **Frontend จัดเก็บ Token และเปลี่ยนหน้า**:
    -   Frontend รับข้อมูลที่ Backend ส่งมา
    -   ล้าง Token เก่า (ถ้ามี) และจัดเก็บ Token ตัวจริงที่ได้รับใหม่ไว้ใน `localStorage` ของเบราว์เซอร์
    -   เรียกใช้ฟังก์ชัน `refresh()` จาก `useAuth.js` เพื่อส่ง request ไปยัง `/api/me.php` สำหรับดึงข้อมูลผู้ใช้ล่าสุดมาเก็บไว้ใน State ส่วนกลาง
    -   เปลี่ยนเส้นทาง (Redirect) ผู้ใช้ไปยังหน้า Dashboard ที่เหมาะสม (`/admin/dashboard` หรือ `/user/dashboard`)

---

## ขั้นตอนการเรียกใช้ API ที่ต้องยืนยันตัวตน (Authenticated Request Flow)

หลังจากเข้าสู่ระบบแล้ว ทุกการเรียกใช้ API ที่มีการป้องกันจะทำงานตามขั้นตอนนี้

1.  **Frontend ส่ง Request**:
    -   มีการเรียกใช้ API ผ่าน `axios` instance ที่ตั้งค่าไว้แล้ว (`services/http.ts`)
    -   `request interceptor` ที่ตั้งค่าไว้จะอ่านค่า Token จาก `localStorage` โดยอัตโนมัติ

2.  **Interceptor เพิ่ม Authorization Header**:
    -   Interceptor จะเพิ่ม Header `Authorization: Bearer <token>` เข้าไปในทุก Request

3.  **Backend ตรวจสอบ Token**:
    -   สคริปต์ฝั่ง Backend (เช่น `/api/me.php`) ได้รับ Request
    -   ดึงค่า Token ออกมาจาก `Authorization` header
    -   นำ Token ที่ได้รับไปแฮชด้วย `SHA-256`
    -   ค้นหาผู้ใช้ในตาราง `users` ที่มีค่าในคอลัมน์ `session_token` ตรงกับ **ค่าแฮชของ Token** ที่คำนวณได้

4.  **Backend ประมวลผล Request**:
    -   **กรณี Token ถูกต้อง**: Backend จะประมวลผล Request ตามตรรกะหลักต่อไป (เช่น ดึงข้อมูล, ลบไฟล์) แล้วส่งผลลัพธ์กลับไป
    -   **กรณี Token ไม่ถูกต้อง**: Backend จะตอบกลับด้วยสถานะ `401 Unauthorized` หรือ `403 Forbidden`

5.  **Frontend จัดการ Response (กรณี Token ไม่ถูกต้อง)**:
    -   `response interceptor` ใน `services/http.ts` จะตรวจจับ Error ที่มีสถานะเป็น `401` หรือ `403`
    -   เมื่อตรวจพบ Interceptor จะเรียกใช้ฟังก์ชัน `setToken(null)` ซึ่งจะลบ Token ออกจาก `localStorage` และล้างค่า State ของผู้ใช้อัตโนมัติ
    -   เมื่อ State ของผู้ใช้เปลี่ยนเป็น `null` ระบบ `router guard` จะทำงานและเปลี่ยนเส้นทางผู้ใช้กลับไปยังหน้า Login โดยอัตโนมัติ (Auto-logout)

---

## ขั้นตอนการออกจากระบบ (Logout Flow)

กระบวนการนี้จะทำให้ Session Token ที่ใช้งานอยู่สิ้นสุดลงทั้งฝั่ง Client และ Server

1.  **ผู้ใช้กดปุ่ม Logout**:
    -   ผู้ใช้กดปุ่มออกจากระบบในหน้า UI
    -   Frontend เรียกใช้ฟังก์ชัน `logout()` จาก `useAuth.js`

2.  **Frontend ส่ง Logout Request**:
    -   ฟังก์ชัน `logout()` จะส่ง `POST` request ไปยัง `/api/logout.php`
    -   Request นี้จะแนบ `Authorization` header ที่มี Session Token ปัจจุบันไปด้วย

3.  **Backend ทำให้ Token ใช้งานไม่ได้**:
    -   สคริปต์ `/api/logout.php` รับ Request และดึง Token ออกมา
    -   นำ Token ไปแฮช
    -   ค้นหาผู้ใช้ที่มี `session_token` ตรงกับค่าแฮช แล้วอัปเดตให้คอลัมน์ `session_token` มีค่าเป็น `NULL`

4.  **Frontend ล้างข้อมูลเซสชันฝั่งตัวเอง**:
    -   ไม่ว่า Backend จะตอบกลับมาอย่างไร ฟังก์ชัน `logout()` ใน `useAuth.js` จะทำงานต่อโดย:
        -   ลบ Token ออกจาก `localStorage` (`setToken(null)`)
        -   ลบข้อมูลการแนะนำชื่อผู้ใช้ล่าสุด (`ku_ids` และ `ku_last_id`) ออกจาก `localStorage`
        -   กำหนดให้ค่า State ของผู้ใช้ในระบบเป็น `null`
    -   จากนั้นผู้ใช้จะถูกเปลี่ยนเส้นทางกลับไปยังหน้า Login

---

## ไฟล์หลักที่เกี่ยวข้อง

-   **Frontend**:
    -   `frontend/src/views/Login.vue`: หน้าฟอร์ม Login และตรรกะการเข้าระบบเบื้องต้น
    -   `frontend/src/composables/useAuth.js`: จัดการ State ของผู้ใช้, การจัดเก็บ Token, และมีฟังก์ชัน `login`, `logout`, `refresh`
    -   `frontend/src/services/http.ts`: `axios` instance ส่วนกลางที่มี `request` และ `response` interceptors
    -   `frontend/src/router/index.ts`: ตั้งค่า Router และมี `beforeEach` guard สำหรับป้องกัน Route และตรวจสอบการยืนยันตัวตน

-   **Backend**:
    -   `backend/api/login.php`: จัดการการตรวจสอบข้อมูลผู้ใช้และการสร้าง Token
    -   `backend/api/logout.php`: ทำให้ Session Token ในฐานข้อมูลใช้งานไม่ได้
    -   `backend/api/me.php`: ตรวจสอบ Token และส่งข้อมูลผู้ใช้ที่ผูกกับ Token นั้นกลับไป
    -   `backend/db_connect.php`: จัดการการเชื่อมต่อฐานข้อมูล
    -   API endpoint ที่มีการป้องกันทั้งหมด จะมีตรรกะการตรวจสอบ Token อยู่ภายใน
