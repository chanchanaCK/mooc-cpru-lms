<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonCompletion;
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
        $learners = collect([$student])->concat(
            collect(range(1, 6))->map(fn ($n) => User::create([
                'name' => "ผู้เรียน {$n}",
                'email' => "learner{$n}@example.com",
                'password' => 'password',
                'role' => 'student',
                'email_verified_at' => now(),
            ]))
        );

        foreach ($learners as $learner) {
            foreach ($courses->random(rand(2, 4)) as $course) {
                $this->enrollWithProgress($learner, $course, rand(0, 100));
                $this->addReview($learner, $course);
            }
        }

        // Make sure the demo student has predictable data on the dashboard.
        $this->enrollWithProgress($student, $courses->first(), 40);
        $this->enrollWithProgress($student, $courses->get(1), 100);

        // ---- Certificates for completed courses --------------------------
        $certificates = app(CertificateService::class);
        Enrollment::where('progress_percent', '>=', 100)->with(['user', 'course'])->get()
            ->each(fn ($e) => $e->course && $certificates->issueFor($e->user, $e->course));

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
