<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Program;
use App\Models\Question;
use App\Models\Review;
use App\Models\Section;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * NOTE: video IDs below are placeholder YouTube IDs (Big Buck Bunny, a
     * Creative Commons film) — replace with your real course videos.
     */
    private string $sampleVideo = 'aqz-KE-bpKQ';

    public function run(): void
    {
        // ---- Accounts -----------------------------------------------------
        $admin = User::create([
            'name' => 'ผู้ดูแลระบบ',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $student = User::create([
            'name' => 'ผู้เรียนทดสอบ',
            'email' => 'student@example.com',
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
        ]);

        $registrar = User::create([
            'name' => 'นายทะเบียน คลังหน่วยกิต',
            'email' => 'registrar@example.com',
            'password' => 'password',
            'role' => 'registrar',
            'email_verified_at' => now(),
        ]);

        $instructors = collect([
            ['name' => 'อ. สมชาย ใจดี', 'email' => 'somchai@example.com', 'headline' => 'Full-stack Developer ประสบการณ์ 10+ ปี', 'bio' => 'พัฒนาเว็บและสอนโปรแกรมมิ่งให้กว่า 20,000 คน เชี่ยวชาญ PHP, Laravel และ JavaScript'],
            ['name' => 'อ. ปิยะ ดีไซน์', 'email' => 'piya@example.com', 'headline' => 'Senior UX/UI Designer', 'bio' => 'อดีต Lead Designer บริษัทเทคโนโลยีชั้นนำ หลงใหลในการออกแบบที่ใช้งานง่าย'],
            ['name' => 'อ. วรรณา มาร์เก็ต', 'email' => 'wanna@example.com', 'headline' => 'Digital Marketing Strategist', 'bio' => 'ที่ปรึกษาการตลาดออนไลน์ให้แบรนด์ระดับประเทศ'],
        ])->mapWithKeys(fn ($data) => [
            $data['email'] => User::create([...$data, 'password' => 'password', 'role' => 'instructor', 'email_verified_at' => now()]),
        ]);

        // ---- Categories ---------------------------------------------------
        $categories = collect([
            ['name' => 'เขียนโปรแกรม', 'slug' => 'programming', 'icon' => 'bi-code-slash'],
            ['name' => 'ออกแบบ', 'slug' => 'design', 'icon' => 'bi-palette'],
            ['name' => 'ธุรกิจ', 'slug' => 'business', 'icon' => 'bi-graph-up-arrow'],
            ['name' => 'การตลาด', 'slug' => 'marketing', 'icon' => 'bi-megaphone'],
            ['name' => 'ข้อมูล & AI', 'slug' => 'data-ai', 'icon' => 'bi-robot'],
            ['name' => 'ภาษา', 'slug' => 'language', 'icon' => 'bi-translate'],
        ])->mapWithKeys(function ($data, $i) {
            return [$data['slug'] => Category::create([...$data, 'sort_order' => $i])];
        });

        // ---- Coupons ------------------------------------------------------
        Coupon::insert([
            ['code' => 'WELCOME50', 'type' => 'percent', 'value' => 50, 'min_amount' => 0, 'max_uses' => null, 'used_count' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'MOOC20', 'type' => 'percent', 'value' => 20, 'min_amount' => 0, 'max_uses' => null, 'used_count' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SAVE100', 'type' => 'fixed', 'value' => 100, 'min_amount' => 500, 'max_uses' => 100, 'used_count' => 0, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ---- Courses ------------------------------------------------------
        foreach ($this->courseCatalog() as $data) {
            $this->createCourse(
                $data,
                $instructors[$data['instructor']],
                $categories[$data['category']],
            );
        }

        // ---- Sample quiz (Laravel course) --------------------------------
        $this->seedSampleQuiz();

        // ---- Enrolments, progress & reviews -------------------------------
        $courses = Course::all();

        // Random learners (kept separate from the demo student so the student's
        // dashboard & credit bank stay predictable).
        $learners = collect(range(1, 6))->map(fn ($n) => User::create([
            'name' => "ผู้เรียน {$n}",
            'email' => "learner{$n}@example.com",
            'password' => 'password',
            'role' => 'student',
            'email_verified_at' => now(),
        ]));

        foreach ($learners as $learner) {
            foreach ($courses->random(rand(2, 4)) as $course) {
                $this->enrollWithProgress($learner, $course, rand(0, 100));
                $this->addReview($learner, $course);
            }
        }

        // The demo student gets a fixed set of enrolments so the dashboard and
        // credit bank are deterministic: 2 (JS) + 3 (Python) + 2 (UI/UX) = 7 credits.
        $this->enrollWithProgress($student, $courses->first(), 40);   // Laravel — in progress, no credit yet
        $this->enrollWithProgress($student, $courses->get(1), 100);   // JavaScript (CS102, 2 credits)
        $this->enrollWithProgress($student, Course::where('slug', 'python-data-analysis')->first(), 100);      // DA201, 3 credits
        $this->enrollWithProgress($student, Course::where('slug', 'ui-ux-design-fundamentals')->first(), 100); // DS101, 2 credits
        $this->addReview($student, $courses->get(1));

        // ---- Certificates for completed courses --------------------------
        $certificates = app(CertificateService::class);
        Enrollment::where('progress_percent', '>=', 100)->get(['user_id', 'course_id'])
            ->each(function (Enrollment $e) use ($certificates) {
                $user = User::find($e->user_id);
                $course = Course::find($e->course_id);
                if ($user && $course) {
                    $certificates->issueFor($user, $course);
                }
            });

        // ---- Credit bank: deposit credits for completed credit-bearing courses ----
        $creditBank = app(\App\Services\CreditBankService::class);
        Enrollment::where('progress_percent', '>=', 100)->get(['user_id', 'course_id'])
            ->each(function (Enrollment $e) use ($creditBank) {
                $user = User::find($e->user_id);
                $course = Course::find($e->course_id);
                if ($user && $course) {
                    $creditBank->depositForCourse($user, $course);
                }
            });

        // ---- Qualification types & levels (admin-managed reference data) --
        $this->seedQualifications();

        // ---- Programs (หลักสูตรสะสมหน่วยกิต) ------------------------------
        $this->seedPrograms($registrar, $student);

        // ---- Credit transfer / RPL sample requests -----------------------
        $this->seedTransfers($student, $registrar);

        // ---- Demo organization (B2B) -------------------------------------
        $orgService = app(\App\Services\OrgService::class);
        $org = $orgService->createOrganization($student, 'บริษัท ตัวอย่าง จำกัด', 10);

        foreach (['learner1', 'learner2', 'learner3'] as $email) {
            if ($u = User::where('email', "{$email}@example.com")->first()) {
                $orgService->addMember($org, $u, 'member');
            }
        }

        // Assign two courses to everyone in the org.
        $orgService->assignCourse($org, $courses->first(), $student);          // Laravel
        $orgService->assignCourse($org, $courses->get(1), $student);           // JavaScript

        // Assign an upskill program to the whole team (members auto-enrol).
        if ($teamProgram = Program::where('type', 'micro')->first()) {
            $orgService->assignProgram($org, $teamProgram, $student);
        }

        $this->refreshCounters();
    }

    private function seedSampleQuiz(): void
    {
        $course = Course::where('slug', 'laravel-12-beginner')->first();

        if (! $course) {
            return;
        }

        $section = $course->sections()->create(['title' => 'แบบทดสอบท้ายคอร์ส', 'sort_order' => 99]);
        $quiz = $section->lessons()->create([
            'course_id' => $course->id,
            'title' => 'แบบทดสอบความรู้ Laravel',
            'type' => 'quiz',
            'sort_order' => 99,
            'is_preview' => false,
        ]);

        $questions = [
            ['คำสั่งใดใช้สร้าง migration ใน Laravel?', ['php artisan make:migration', 'php artisan create:migration', 'composer make:migration', 'laravel new:migration'], 0],
            ['Eloquent คืออะไร?', ['ORM สำหรับจัดการฐานข้อมูล', 'เทมเพลตเอนจิน', 'ระบบ routing', 'ตัวจัดการ session'], 0],
            ['ไฟล์ใดใช้กำหนดเส้นทาง (route) ของเว็บ?', ['routes/web.php', 'config/app.php', 'app/Kernel.php', 'public/index.php'], 0],
        ];

        foreach ($questions as $qi => [$text, $opts, $correct]) {
            $q = $quiz->questions()->create(['text' => $text, 'sort_order' => $qi]);
            foreach ($opts as $oi => $optText) {
                $q->options()->create(['text' => $optText, 'is_correct' => $oi === $correct, 'sort_order' => $oi]);
            }
        }
    }

    /** Default qualification types & levels (ประเภท/ระดับคุณวุฒิ) — admin-editable. */
    private function seedQualifications(): void
    {
        $types = [
            ['slug' => 'certificate', 'name' => 'สัมฤทธิบัตร'],
            ['slug' => 'micro', 'name' => 'ประกาศนียบัตรไมโครเครดิเชียล'],
            ['slug' => 'diploma', 'name' => 'อนุปริญญา'],
            ['slug' => 'degree', 'name' => 'ปริญญา'],
        ];
        foreach ($types as $i => $t) {
            \App\Models\QualificationType::create([...$t, 'sort_order' => $i, 'is_active' => true]);
        }

        $levels = [
            ['level' => 1, 'name' => 'ประกาศนียบัตรวิชาชีพ (ปวช.)'],
            ['level' => 2, 'name' => 'ประกาศนียบัตรวิชาชีพชั้นสูง (ปวส.)'],
            ['level' => 3, 'name' => 'อนุปริญญา'],
            ['level' => 4, 'name' => 'ปริญญาตรี'],
            ['level' => 5, 'name' => 'ประกาศนียบัตรบัณฑิต'],
            ['level' => 6, 'name' => 'ปริญญาโท'],
            ['level' => 7, 'name' => 'ประกาศนียบัตรบัณฑิตชั้นสูง'],
            ['level' => 8, 'name' => 'ปริญญาเอก'],
        ];
        foreach ($levels as $i => $l) {
            \App\Models\QualificationLevel::create([...$l, 'sort_order' => $i]);
        }
    }

    /** Sample credit-bank programs + demo student enrolments. */
    private function seedPrograms(User $registrar, User $student): void
    {
        $creditBank = app(\App\Services\CreditBankService::class);

        $configs = [
            [
                'title' => 'ประกาศนียบัตรนักพัฒนาเว็บมืออาชีพ',
                'subtitle' => 'เส้นทางสู่ Full-stack Developer — สะสมหน่วยกิตจากคอร์สเขียนโปรแกรม',
                'type' => 'certificate', 'nqf_level' => 4, 'required_credits' => 8, 'duration_months' => 12,
                'courses' => [['laravel-12-beginner', 'required'], ['modern-javascript', 'required'], ['react-zero-to-production', 'required']],
            ],
            [
                'title' => 'ไมโครเครดิท: การตลาดดิจิทัลสำหรับคนทำงาน',
                'subtitle' => 'Upskill ทักษะการตลาดออนไลน์แบบเก็บสะสมหน่วยกิต',
                'type' => 'micro', 'nqf_level' => 2, 'required_credits' => 4, 'duration_months' => 4,
                'courses' => [['digital-marketing-101', 'required'], ['business-english', 'required']],
            ],
            [
                'title' => 'ประกาศนียบัตรวิทยาการข้อมูลเบื้องต้น',
                'subtitle' => 'Reskill สู่สายข้อมูล ด้วยการวิเคราะห์ข้อมูลด้วย Python',
                'type' => 'certificate', 'nqf_level' => 4, 'required_credits' => 3, 'duration_months' => 6,
                'courses' => [['python-data-analysis', 'required'], ['ui-ux-design-fundamentals', 'elective']],
            ],
        ];

        $programs = [];
        foreach ($configs as $cfg) {
            $program = Program::create([
                'title' => $cfg['title'],
                'slug' => Program::generateUniqueSlug($cfg['title']),
                'subtitle' => $cfg['subtitle'],
                'description' => "หลักสูตรสะสมหน่วยกิตตามระบบธนาคารหน่วยกิต เรียนคอร์สในหลักสูตรให้ผ่านเพื่อสะสมหน่วยกิต เมื่อครบเกณฑ์จะได้รับคุณวุฒิโดยอัตโนมัติ",
                'type' => $cfg['type'],
                'nqf_level' => $cfg['nqf_level'],
                'required_credits' => $cfg['required_credits'],
                'duration_months' => $cfg['duration_months'],
                'status' => 'published',
                'owner_id' => $registrar->id,
            ]);

            $sort = 0;
            foreach ($cfg['courses'] as [$slug, $requirement]) {
                if ($course = Course::where('slug', $slug)->first()) {
                    $program->courses()->attach($course->id, ['requirement' => $requirement, 'sort_order' => $sort++]);
                }
            }

            $programs[] = $program;
        }

        // Demo student: Web Dev (in progress) + Data Science (auto-completes from banked credits).
        $creditBank->enrollProgram($student, $programs[0]);
        $creditBank->enrollProgram($student, $programs[2]);
    }

    /** Sample credit-transfer (RPL) requests for the demo student. */
    private function seedTransfers(User $student, User $registrar): void
    {
        $creditBank = app(\App\Services\CreditBankService::class);

        // Approved — banks 3 transfer credits into the student's bank.
        $approved = $student->transferRequests()->create([
            'source_type' => 'institution',
            'source_name' => 'มหาวิทยาลัยเทคโนโลยีตัวอย่าง',
            'course_name' => 'สถิติสำหรับนักวิเคราะห์ข้อมูล',
            'credits_requested' => 3,
            'evidence_note' => 'ผ่านรายวิชาระดับปริญญาตรี เกรด A ปีการศึกษา 2566',
            'status' => 'pending',
        ]);
        $creditBank->approveTransfer($approved, $registrar, 3.0, 'A', 'หลักฐานครบถ้วน อนุมัติเต็มจำนวน');

        // Pending — awaits registrar review.
        $student->transferRequests()->create([
            'source_type' => 'experience',
            'source_name' => 'บริษัท เทคสตาร์ท จำกัด',
            'course_name' => 'การพัฒนาเว็บจากประสบการณ์ทำงาน',
            'credits_requested' => 2,
            'evidence_note' => 'ทำงานตำแหน่ง Junior Developer 1 ปี พร้อมแฟ้มผลงาน',
            'status' => 'pending',
        ]);
    }

    private function createCourse(array $data, User $instructor, Category $category): Course
    {
        $course = Course::create([
            'instructor_id' => $instructor->id,
            'category_id' => $category->id,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'subtitle' => $data['subtitle'],
            'description' => $data['description'],
            'price' => $data['price'],
            'level' => $data['level'],
            'status' => 'published',
            'published_at' => now()->subDays(rand(1, 120)),
            // Credit bank (คลังหน่วยกิต)
            'course_code' => $data['course_code'] ?? null,
            'credit_bearing' => $data['credit_bearing'] ?? false,
            'credits' => $data['credits'] ?? 0,
            'learning_hours' => $data['learning_hours'] ?? 0,
            'grading_method' => $data['grading_method'] ?? 'pass_fail',
            'pass_threshold' => $data['pass_threshold'] ?? 70,
        ]);

        $order = 0;
        foreach ($data['sections'] as $sIndex => $sectionData) {
            $section = Section::create([
                'course_id' => $course->id,
                'title' => $sectionData['title'],
                'sort_order' => $sIndex,
            ]);

            foreach ($sectionData['lessons'] as $lIndex => $lesson) {
                Lesson::create([
                    'section_id' => $section->id,
                    'course_id' => $course->id,
                    'title' => $lesson['title'],
                    'type' => 'video',
                    'video_provider' => 'youtube',
                    'video_id' => $this->sampleVideo,
                    'duration_seconds' => $lesson['seconds'] ?? rand(240, 900),
                    'is_preview' => $order === 0, // first lesson is a free preview
                    'sort_order' => $order++,
                ]);
            }
        }

        return $course;
    }

    private function enrollWithProgress(User $user, Course $course, int $targetPercent): void
    {
        Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['progress_percent' => 0],
        );

        $lessons = $course->lessons()->orderBy('sort_order')->get();
        $take = (int) round($lessons->count() * $targetPercent / 100);

        foreach ($lessons->take($take) as $lesson) {
            LessonCompletion::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                ['course_id' => $course->id, 'completed_at' => now()],
            );
        }

        $percent = $lessons->count() > 0 ? (int) round($take / $lessons->count() * 100) : 0;
        Enrollment::where('user_id', $user->id)->where('course_id', $course->id)->update([
            'progress_percent' => $percent,
            'completed_at' => $percent >= 100 ? now() : null,
        ]);
    }

    private function addReview(User $user, Course $course): void
    {
        $comments = [
            'สอนเข้าใจง่ายมาก แนะนำเลยครับ', 'เนื้อหาแน่น คุ้มค่ามาก', 'ผู้สอนอธิบายละเอียด ตัวอย่างชัดเจน',
            'ได้ความรู้ไปใช้จริงได้เลย', 'ชอบมากครับ รอคอร์สต่อไป', null,
        ];

        Review::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['rating' => rand(4, 5), 'comment' => $comments[array_rand($comments)]],
        );
    }

    private function refreshCounters(): void
    {
        Course::all()->each(function (Course $course) {
            $course->update([
                'lessons_count' => $course->lessons()->count(),
                'duration_minutes' => (int) round($course->lessons()->sum('duration_seconds') / 60),
                'students_count' => $course->enrollments()->count(),
                'rating_count' => $course->reviews()->count(),
                'rating_avg' => round((float) $course->reviews()->avg('rating'), 2),
            ]);
        });
    }

    /** The demo course catalog. */
    private function courseCatalog(): array
    {
        $lorem = 'คอร์สนี้ออกแบบมาสำหรับผู้ที่ต้องการพัฒนาทักษะอย่างเป็นระบบ ตั้งแต่พื้นฐานจนถึงการนำไปใช้งานจริง '
            . "พร้อมตัวอย่างและแบบฝึกหัดในทุกบทเรียน\n\nสิ่งที่คุณจะได้เรียนรู้:\n• พื้นฐานที่จำเป็นทั้งหมด\n"
            . "• เทคนิคจากประสบการณ์จริงของผู้สอน\n• การประยุกต์ใช้กับโปรเจกต์ของคุณเอง";

        return [
            [
                'title' => 'เริ่มต้นเขียนเว็บด้วย Laravel 12', 'slug' => 'laravel-12-beginner',
                'subtitle' => 'สร้างเว็บแอปพลิเคชันตั้งแต่ศูนย์จนใช้งานได้จริง', 'category' => 'programming',
                'instructor' => 'somchai@example.com', 'price' => 0, 'level' => 'beginner', 'description' => $lorem,
                'course_code' => 'CS101', 'credit_bearing' => true, 'credits' => 3, 'learning_hours' => 45, 'grading_method' => 'graded',
                'sections' => [
                    ['title' => 'ปูพื้นฐาน', 'lessons' => [
                        ['title' => 'แนะนำคอร์สและการติดตั้ง', 'seconds' => 360],
                        ['title' => 'โครงสร้างโปรเจกต์ Laravel', 'seconds' => 540],
                        ['title' => 'Routing และ Controller', 'seconds' => 720],
                    ]],
                    ['title' => 'ฐานข้อมูลและ Eloquent', 'lessons' => [
                        ['title' => 'Migration และ Schema', 'seconds' => 600],
                        ['title' => 'Eloquent Model และความสัมพันธ์', 'seconds' => 840],
                        ['title' => 'สร้าง CRUD สมบูรณ์', 'seconds' => 900],
                    ]],
                ],
            ],
            [
                'title' => 'JavaScript สมัยใหม่ ES6+', 'slug' => 'modern-javascript',
                'subtitle' => 'เข้าใจ JavaScript อย่างลึกซึ้งเพื่องานจริง', 'category' => 'programming',
                'instructor' => 'somchai@example.com', 'price' => 590, 'level' => 'intermediate', 'description' => $lorem,
                'course_code' => 'CS102', 'credit_bearing' => true, 'credits' => 2, 'learning_hours' => 30, 'grading_method' => 'graded',
                'sections' => [
                    ['title' => 'พื้นฐานภาษา', 'lessons' => [
                        ['title' => 'let, const และ scope', 'seconds' => 420],
                        ['title' => 'Arrow function และ this', 'seconds' => 480],
                    ]],
                    ['title' => 'Asynchronous', 'lessons' => [
                        ['title' => 'Promise และ async/await', 'seconds' => 660],
                        ['title' => 'Fetch API', 'seconds' => 540],
                    ]],
                ],
            ],
            [
                'title' => 'ออกแบบ UI/UX ให้ผู้ใช้รัก', 'slug' => 'ui-ux-design-fundamentals',
                'subtitle' => 'หลักการออกแบบและการใช้ Figma', 'category' => 'design',
                'instructor' => 'piya@example.com', 'price' => 0, 'level' => 'beginner', 'description' => $lorem,
                'course_code' => 'DS101', 'credit_bearing' => true, 'credits' => 2, 'learning_hours' => 30, 'grading_method' => 'pass_fail',
                'sections' => [
                    ['title' => 'หลักการออกแบบ', 'lessons' => [
                        ['title' => 'Design Thinking เบื้องต้น', 'seconds' => 480],
                        ['title' => 'ทฤษฎีสีและ Typography', 'seconds' => 600],
                    ]],
                    ['title' => 'ลงมือทำใน Figma', 'lessons' => [
                        ['title' => 'เครื่องมือพื้นฐาน Figma', 'seconds' => 720],
                        ['title' => 'ออกแบบ Mobile App', 'seconds' => 900],
                    ]],
                ],
            ],
            [
                'title' => 'ปั้นแบรนด์ให้ปังด้วย Digital Marketing', 'slug' => 'digital-marketing-101',
                'subtitle' => 'กลยุทธ์การตลาดออนไลน์ครบวงจร', 'category' => 'marketing',
                'instructor' => 'wanna@example.com', 'price' => 890, 'level' => 'beginner', 'description' => $lorem,
                'course_code' => 'MK101', 'credit_bearing' => true, 'credits' => 2, 'learning_hours' => 30, 'grading_method' => 'pass_fail',
                'sections' => [
                    ['title' => 'ภาพรวมการตลาดออนไลน์', 'lessons' => [
                        ['title' => 'Marketing Funnel', 'seconds' => 540],
                        ['title' => 'เข้าใจกลุ่มเป้าหมาย', 'seconds' => 480],
                    ]],
                    ['title' => 'ช่องทางหลัก', 'lessons' => [
                        ['title' => 'Facebook & Instagram Ads', 'seconds' => 780],
                        ['title' => 'SEO เบื้องต้น', 'seconds' => 660],
                    ]],
                ],
            ],
            [
                'title' => 'วิเคราะห์ข้อมูลด้วย Python', 'slug' => 'python-data-analysis',
                'subtitle' => 'Pandas, NumPy และการทำ Visualization', 'category' => 'data-ai',
                'instructor' => 'somchai@example.com', 'price' => 1290, 'level' => 'intermediate', 'description' => $lorem,
                'course_code' => 'DA201', 'credit_bearing' => true, 'credits' => 3, 'learning_hours' => 45, 'grading_method' => 'graded',
                'sections' => [
                    ['title' => 'เริ่มต้นกับข้อมูล', 'lessons' => [
                        ['title' => 'ติดตั้งและ Jupyter Notebook', 'seconds' => 420],
                        ['title' => 'พื้นฐาน Pandas', 'seconds' => 840],
                    ]],
                    ['title' => 'การนำเสนอข้อมูล', 'lessons' => [
                        ['title' => 'Matplotlib และ Seaborn', 'seconds' => 720],
                        ['title' => 'สร้าง Dashboard', 'seconds' => 900],
                    ]],
                ],
            ],
            [
                'title' => 'เริ่มต้นทำธุรกิจ SME ยุคดิจิทัล', 'slug' => 'sme-business-starter',
                'subtitle' => 'วางแผนธุรกิจและการเงินเบื้องต้น', 'category' => 'business',
                'instructor' => 'wanna@example.com', 'price' => 0, 'level' => 'beginner', 'description' => $lorem,
                'sections' => [
                    ['title' => 'วางรากฐานธุรกิจ', 'lessons' => [
                        ['title' => 'Business Model Canvas', 'seconds' => 600],
                        ['title' => 'วิเคราะห์คู่แข่ง', 'seconds' => 540],
                    ]],
                ],
            ],
            [
                'title' => 'ภาษาอังกฤษเพื่อการทำงาน', 'slug' => 'business-english',
                'subtitle' => 'สื่อสารมั่นใจในที่ทำงาน', 'category' => 'language',
                'instructor' => 'piya@example.com', 'price' => 490, 'level' => 'beginner', 'description' => $lorem,
                'course_code' => 'EN101', 'credit_bearing' => true, 'credits' => 2, 'learning_hours' => 30, 'grading_method' => 'pass_fail',
                'sections' => [
                    ['title' => 'การสนทนาพื้นฐาน', 'lessons' => [
                        ['title' => 'แนะนำตัวอย่างมืออาชีพ', 'seconds' => 420],
                        ['title' => 'อีเมลภาษาอังกฤษ', 'seconds' => 540],
                    ]],
                ],
            ],
            [
                'title' => 'React.js จากพื้นฐานสู่ Production', 'slug' => 'react-zero-to-production',
                'subtitle' => 'สร้าง Single Page Application ระดับมืออาชีพ', 'category' => 'programming',
                'instructor' => 'somchai@example.com', 'price' => 1490, 'level' => 'advanced', 'description' => $lorem,
                'course_code' => 'CS301', 'credit_bearing' => true, 'credits' => 3, 'learning_hours' => 45, 'grading_method' => 'graded',
                'sections' => [
                    ['title' => 'พื้นฐาน React', 'lessons' => [
                        ['title' => 'Component และ JSX', 'seconds' => 600],
                        ['title' => 'State และ Props', 'seconds' => 720],
                    ]],
                    ['title' => 'จัดการ State ขั้นสูง', 'lessons' => [
                        ['title' => 'useReducer และ Context', 'seconds' => 840],
                        ['title' => 'เชื่อมต่อ API', 'seconds' => 780],
                    ]],
                ],
            ],
        ];
    }
}
