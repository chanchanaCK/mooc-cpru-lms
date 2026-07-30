# MoocLMS — ระบบ LMS / Course Marketplace (สไตล์ SkillLane)

ระบบเรียนออนไลน์แบบ **B2C marketplace** สร้างด้วย **Laravel 12** + **MariaDB** + **Tailwind (Breeze)**
รันบนเครื่อง XAMPP (PHP 8.2.4) ได้ทันที

> สถานะปัจจุบัน: **เฟส 1 (เรียน) + 2 (ซื้อขาย) + 3 (สตูดิโอผู้สอน) + 4 (แบบทดสอบ & ใบประกาศ) ใช้งานได้จริง**
> ผู้เรียน: ดูคอร์ส → ตะกร้า → คูปอง → ชำระเงิน → ลงเรียน → ดูวิดีโอ/ทำแบบทดสอบ → ติดตามความคืบหน้า → รับใบประกาศ → รีวิว
> ผู้สอน: สร้าง/แก้คอร์ส → จัดการบท-บทเรียน → สร้างแบบทดสอบ → อัปโหลดปก → ลิงก์วิดีโอ → เผยแพร่ (ที่ `/studio`)

---

## 🧱 Tech Stack

| ชั้น | เทคโนโลยี |
|---|---|
| Backend | Laravel 12 (PHP ^8.2) |
| Database | MariaDB / MySQL (`mooc`) |
| Auth | Laravel Breeze (Blade) |
| Frontend | Blade + Tailwind CSS v3 + Alpine.js (Vite) |
| Video | YouTube embed (เปลี่ยนเป็น Vimeo/Bunny/ไฟล์ได้) |

> ⚠️ **หมายเหตุสำคัญเรื่องเวอร์ชัน:** เครื่องมี `composer`/`php` ของ Homebrew เป็น **8.4** แต่ Apache ของ XAMPP
> ใช้ **PHP 8.2.4** โปรเจกต์จึง **ตรึงไว้ที่ Laravel 12 + `config.platform.php = 8.2.4`** ใน `composer.json`
> และตรึง frontend เป็น **Tailwind v3 + Vite 5** (ไม่ใช้ของล่าสุดที่ยัง build ไม่ผ่าน) — อย่าอัป dependency
> โดยไม่ตรวจว่ารันบน PHP 8.2 ได้ก่อน

---

## 🚀 การติดตั้ง & รัน

ต้องมี MariaDB (XAMPP) รันอยู่ และมีฐานข้อมูล `mooc`

```bash
# 1) ติดตั้ง dependency (ใช้ composer/npm ปกติ, platform ถูกตรึงเป็น 8.2 แล้ว)
composer install
npm install

# 2) เตรียม .env (ถ้ายังไม่มี) + สร้าง key
cp .env.example .env
/Applications/XAMPP/xamppfiles/bin/php artisan key:generate

# 3) สร้างตาราง + ใส่ข้อมูลตัวอย่าง
/Applications/XAMPP/xamppfiles/bin/php artisan migrate:fresh --seed

# 4) build assets
npm run build

# 5) รันเซิร์ฟเวอร์ (ใช้ PHP ของ XAMPP)
/Applications/XAMPP/xamppfiles/bin/php artisan serve --port=8080
```

เปิด **http://127.0.0.1:8080**

> ระหว่างพัฒนา frontend ให้รัน `npm run dev` คู่กันเพื่อ hot-reload

### บัญชีทดสอบ (รหัสผ่านทั้งหมด: `password`)

| บทบาท | อีเมล |
|---|---|
| ผู้เรียน (มีคอร์สกำลังเรียน/เรียนจบ) | `student@example.com` |
| ผู้ดูแล | `admin@example.com` |
| ผู้สอน (เข้า `/studio` ได้) | `somchai@example.com`, `piya@example.com`, `wanna@example.com` |

**คูปองทดสอบ:** `WELCOME50` (ลด 50%), `MOOC20` (ลด 20%), `SAVE100` (ลด ฿100 เมื่อซื้อครบ ฿500)

---

## 🗂️ Data Model (ตารางหลัก)

```
users ──< courses (instructor_id)          courses >── categories
courses ──< sections ──< lessons
users ──< enrollments >── courses          (progress_percent, completed_at)
users ──< lesson_completions >── lessons   (ติดตามว่าดูบทไหนจบแล้ว)
users ──< reviews >── courses              (rating 1–5)

users ──< cart_items >── courses           (ตะกร้าถาวรต่อผู้ใช้)
users ──< orders ──< order_items >── courses   (คำสั่งซื้อ + snapshot ราคา/ชื่อ)
orders ──1 payments                        (gateway, ref, status)
orders >── coupons                          (percent/fixed, min_amount, used_count)
```

ตัวนับที่ cache ไว้บน `courses` (`students_count`, `lessons_count`, `rating_avg`, …)
ถูกอัปเดตผ่าน `app/Services/LearningService.php` และ seeder เพื่อให้หน้า catalog เร็ว

---

## 🧭 แผนที่ไฟล์สำคัญ

```
app/
  Models/            User, Course, Category, Section, Lesson, Enrollment, LessonCompletion, Review,
                     Coupon, CartItem, Order, OrderItem, Payment
  Services/
    LearningService.php     ← logic ลงเรียน + คำนวณ progress (deep module)
    CartService.php         ← เพิ่ม/ลบ/ยอดรวมตะกร้า
    CheckoutService.php     ← สร้าง order → charge → ลงเรียน (ใน DB transaction)
    Payment/
      PaymentGateway.php        ← interface (สลับ gateway จริงได้)
      FakePaymentGateway.php    ← driver จำลองสำหรับเดโม
      PaymentResult.php         ← DTO ผลการชำระเงิน
  Providers/AppServiceProvider.php  ← bind PaymentGateway → FakePaymentGateway
  Http/Controllers/
    HomeController, CourseController, EnrollmentController, LearnController,
    DashboardController, ReviewController, CartController, CheckoutController, OrderController
database/
  migrations/        13 ตาราง (LMS 8 + commerce 5)
  seeders/DatabaseSeeder.php   ← 8 คอร์ส + ผู้ใช้ + รีวิว + คูปอง
resources/views/
  components/site-layout.blade.php    ← layout marketplace (navbar/ตะกร้า/search/footer)
  components/learn-layout.blade.php   ← layout ห้องเรียน (โฟกัส)
  components/course-card.blade.php
  home / courses.{index,show} / dashboard / learn.lesson
  cart.index / checkout.index / orders.{index,show}
routes/web.php
```

---

## ✅ ฟีเจอร์ที่ทำแล้ว

- หน้าแรก: hero, หมวดหมู่, คอร์สยอดนิยม/มาใหม่
- Catalog: ค้นหา + กรองหมวด/ระดับ/ฟรี + เรียงลำดับ + pagination
- หน้ารายละเอียดคอร์ส: curriculum accordion, ผู้สอน, รีวิว, กล่องลงเรียน sticky, บทเรียนตัวอย่าง (preview)
- Auth: สมัคร/เข้าสู่ระบบ/โปรไฟล์ (Breeze)
- ลงทะเบียนเรียน (คอร์สฟรี)
- ห้องเรียน (player): เล่นวิดีโอ, sidebar หลักสูตร, ทำเครื่องหมายเรียนจบ + ไปบทถัดไปอัตโนมัติ
- ติดตามความคืบหน้า (% ต่อคอร์ส) + หน้า "การเรียนของฉัน"
- รีวิว & ให้ดาว
- ตะกร้า + คูปอง + checkout (payment gateway จำลองแบบ pluggable) + คำสั่งซื้อ/ใบเสร็จ
- **สตูดิโอผู้สอน** (`/studio`): สร้าง/แก้/ลบคอร์ส, จัดการบท-บทเรียน, อัปโหลดปก, ลิงก์วิดีโอ YouTube/Vimeo, เผยแพร่ (จำกัดสิทธิ์เฉพาะเจ้าของ)
- **แบบทดสอบ** (บทเรียน type=quiz): ทำข้อสอบในหน้าเรียน, ตรวจอัตโนมัติ, ผ่าน ≥70% → นับว่าเรียนจบ, ทบทวนคำตอบ/ทำใหม่ได้ + ผู้สอนสร้างคำถามในสตูดิโอ
- **ใบประกาศนียบัตร**: ออกอัตโนมัติเมื่อเรียนครบ 100% — หน้าใบประกาศสวยงาม (Noto Sans Thai, วันที่ พ.ศ.) พิมพ์/บันทึกเป็น PDF ได้

---

## 🛣️ Roadmap เฟสถัดไป

| เฟส | สิ่งที่จะเพิ่ม |
|---|---|
| ~~2. Commerce~~ ✅ | ~~ตะกร้า, checkout, คูปอง, คำสั่งซื้อ~~ (เสร็จแล้ว — gateway จำลอง, ต่อ Omise/2C2P จริงภายหลัง) |
| ~~3. Instructor Studio~~ ✅ | ~~ผู้สอนสร้าง/แก้คอร์ส, จัดการบท-บทเรียน, อัปโหลดปก, ลิงก์วิดีโอ~~ (เสร็จแล้ว) |
| ~~4. Assessment~~ ✅ | ~~ควิซ/แบบทดสอบ + ใบประกาศนียบัตร~~ (เสร็จแล้ว — ใบประกาศเป็นหน้า HTML พิมพ์เป็น PDF ได้, ไทยไม่เพี้ยน) |
| **5. Polish** | Meilisearch, ระบบแจ้งเตือน/อีเมล, แดชบอร์ดแอดมิน + รายงาน, อัปโหลดไฟล์วิดีโอ + signed URL |
| **6. B2B** | องค์กร/ทีม, มอบหมายคอร์ส, learning path, รายงานผลพนักงาน |

### สิ่งที่ต้องทำก่อนขึ้น production
- ย้ายวิดีโอออกจากการฝัง YouTube ไปเป็นผู้ให้บริการ streaming ที่มี **signed URL** (กันดาวน์โหลด)
- แยก `role` เป็นระบบสิทธิ์เต็ม (เช่น spatie/laravel-permission) เมื่อ B2B เข้ามา
- ตั้ง queue (Redis) สำหรับงาน async (encode วิดีโอ, ส่งเมล, ออกใบประกาศ)
