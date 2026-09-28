<?php
/**
 * Database Connection & Migration Helper
 */

require_once __DIR__ . '/config.php';

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            try {
                if (DB_DRIVER === 'sqlite') {
                    $dbFile = DB_SQLITE_FILE;
                    $dbDir = dirname($dbFile);
                    if (!file_exists($dbDir)) {
                        @mkdir($dbDir, 0777, true);
                    }

                    // On Vercel / serverless read-only filesystem, copy database to /tmp for write/lock access
                    $isServerless = isset($_ENV['VERCEL']) || isset($_SERVER['VERCEL']) || !is_writable($dbDir);
                    if ($isServerless && file_exists($dbFile)) {
                        $tmpDb = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'masputra.db';
                        if (!file_exists($tmpDb) || filemtime($dbFile) > filemtime($tmpDb)) {
                            @copy($dbFile, $tmpDb);
                        }
                        if (file_exists($tmpDb)) {
                            $dbFile = $tmpDb;
                        }
                    }

                    $isNew = !file_exists($dbFile) || filesize($dbFile) === 0;
                    self::$pdo = new PDO("sqlite:" . $dbFile);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

                    // Initialize tables and seed
                    self::initSqliteTables();
                } else {
                    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
                    self::$pdo = new PDO($dsn, DB_USER, DB_PASS);
                    self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                }
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }

    private static function initSqliteTables() {
        $db = self::$pdo;

        // Create Tables
        $db->exec("
            CREATE TABLE IF NOT EXISTS admins (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                name TEXT NOT NULL,
                email TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS profile (
                id INTEGER PRIMARY KEY,
                full_name TEXT NOT NULL,
                title TEXT NOT NULL,
                phone TEXT,
                email TEXT,
                address TEXT,
                linkedin TEXT,
                github TEXT,
                bio TEXT,
                avatar TEXT,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS skills (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                percentage INTEGER NOT NULL,
                category TEXT DEFAULT 'skill',
                sort_order INTEGER DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS education (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                year_range TEXT NOT NULL,
                institution TEXT NOT NULL,
                major TEXT NOT NULL,
                sort_order INTEGER DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS experience (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                date_range TEXT NOT NULL,
                company TEXT NOT NULL,
                role TEXT NOT NULL,
                details TEXT,
                sort_order INTEGER DEFAULT 0
            );

            CREATE TABLE IF NOT EXISTS certificates (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                title_en TEXT,
                issuer TEXT NOT NULL,
                issue_date TEXT,
                credential_id TEXT,
                credential_url TEXT,
                image TEXT,
                description TEXT,
                description_en TEXT,
                featured INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                title_en TEXT,
                category TEXT NOT NULL,
                description TEXT,
                description_en TEXT,
                tech_stack TEXT,
                live_url TEXT,
                github_url TEXT,
                image TEXT,
                featured INTEGER DEFAULT 1,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS project_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL,
                image TEXT NOT NULL,
                caption TEXT,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS messages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                subject TEXT,
                message TEXT NOT NULL,
                is_read INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Ensure bio_en column exists
        try {
            $db->exec("ALTER TABLE profile ADD COLUMN bio_en TEXT");
        } catch (Exception $e) {
            // Already exists, ignore
        }

        // Ensure experience translation columns exist
        try {
            $db->exec("ALTER TABLE experience ADD COLUMN date_range_en TEXT");
        } catch (Exception $e) {}
        try {
            $db->exec("ALTER TABLE experience ADD COLUMN role_en TEXT");
        } catch (Exception $e) {}
        try {
            $db->exec("ALTER TABLE experience ADD COLUMN details_en TEXT");
        } catch (Exception $e) {}

        // Ensure education translation column exists
        try {
            $db->exec("ALTER TABLE education ADD COLUMN major_en TEXT");
        } catch (Exception $e) {}

        // Ensure certificates translation columns exist
        try {
            $db->exec("ALTER TABLE certificates ADD COLUMN title_en TEXT");
        } catch (Exception $e) {}
        try {
            $db->exec("ALTER TABLE certificates ADD COLUMN description_en TEXT");
        } catch (Exception $e) {}

        // Ensure project translation columns and project_images table exist
        try {
            $db->exec("ALTER TABLE projects ADD COLUMN title_en TEXT");
        } catch (Exception $e) {}
        try {
            $db->exec("ALTER TABLE projects ADD COLUMN description_en TEXT");
        } catch (Exception $e) {}

        $db->exec("
            CREATE TABLE IF NOT EXISTS project_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER NOT NULL,
                image TEXT NOT NULL,
                caption TEXT,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS certificate_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                certificate_id INTEGER NOT NULL,
                image TEXT NOT NULL,
                caption TEXT,
                sort_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");
        try {
            $db->exec("CREATE INDEX IF NOT EXISTS idx_project_images_pid ON project_images (project_id)");
            $db->exec("CREATE INDEX IF NOT EXISTS idx_certificate_images_cid ON certificate_images (certificate_id)");
        } catch (Exception $e) {}

        // Seed Admin if not exists
        $adminCheck = $db->query("SELECT COUNT(*) as count FROM admins")->fetch();
        if ($adminCheck['count'] == 0) {
            $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO admins (username, password, name, email) VALUES (?, ?, ?, ?)");
            $stmt->execute(['admin', $defaultPassword, 'Saputra', 'masputra1404@gmail.com']);
        }

        // Seed Profile if not exists
        $profileCheck = $db->query("SELECT COUNT(*) as count FROM profile WHERE id = 1")->fetch();
        if ($profileCheck['count'] == 0) {
            $stmt = $db->prepare("
                INSERT INTO profile (id, full_name, title, phone, email, address, linkedin, github, bio, avatar)
                VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $bioText = "Saya adalah seorang Welder Kombinasi dengan pengalaman dalam proses GTAW, SMAW, GMAW, dan FCAW. Selain bidang fabrikasi dan pengelasan, saya juga memiliki kemampuan sebagai programmer dengan fokus pada automation web & scraping menggunakan Node.js. Saya familiar dengan berbagai bahasa pemrograman seperti PHP, VB, C#, Node.js, dan HTML. Berpengetahuan luas tentang dunia komputer, cepat beradaptasi, dan memiliki rasa tanggung jawab tinggi dalam setiap pekerjaan.";
            $stmt->execute([
                'SAPUTRA',
                'Programmer / Welder',
                '+628 1277 900210',
                'masputra1404@gmail.com',
                'Griya Laguna Mas c3 18',
                'https://www.linkedin.com/in/masputra1404/',
                'https://github.com/masputra1404',
                $bioText,
                'assets/images/avatar.png'
            ]);
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS system_settings (
                key TEXT PRIMARY KEY,
                value TEXT
            )
        ");

        $seededCheck = $db->query("SELECT value FROM system_settings WHERE key = 'is_seeded' LIMIT 1")->fetch();
        $isSeeded = !empty($seededCheck['value']);

        // Seed Initial Demo Data Only on First Installation
        if (!$isSeeded) {
            $initialSkills = [
                ['Welding', 100, 'skill', 1],
                ['Node JS', 85, 'skill', 2],
                ['VB. Net', 80, 'skill', 3],
                ['PHP & HTML', 85, 'skill', 4],
                ['C#', 75, 'skill', 5],
                ['Microsoft Office', 85, 'skill', 6],
                ['Typing', 100, 'skill', 7],
                ['Computer', 100, 'skill', 8],
                ['Indonesia', 100, 'bahasa', 9],
                ['Inggris', 75, 'bahasa', 10]
            ];
            $stmt = $db->prepare("INSERT INTO skills (name, percentage, category, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($initialSkills as $s) {
                $stmt->execute($s);
            }

            // Seed Education
            $initialEdu = [
                ['(2021 - 2021)', 'PT. GLOBAL SARANA INTERNUSA', 'WELDER GTAW + SMAW 6G', 1],
                ['(2021 - 2021)', 'PT. GLOBAL SARANA INTERNUSA', 'WELDER SMAW 3G + 4G', 2],
                ['(2014 - 2017)', 'SMK MULTISTUDI HIGHSCHOOL', 'Rekayasa Perangkat Lunak', 3]
            ];
            $stmt = $db->prepare("INSERT INTO education (year_range, institution, major, sort_order) VALUES (?, ?, ?, ?)");
            foreach ($initialEdu as $e) {
                $stmt->execute($e);
            }

            // Seed Experience
            $initialExp = [
                [
                    'JANUARI 2026 – JUNE 2026',
                    'CATERPILLAR INDONESIA',
                    'Welder GMAW',
                    "• WELDER GMAW\n• Pengelasan presisi komponen alat berat sesuai standar mutu internasional Caterpillar.",
                    1
                ],
                [
                    'MARET 2024 – OKTOBER 2025',
                    'PT. TOYO KANETSU INDONESIA',
                    'Welder Kombinasi',
                    "• Welder GTAW + SMAW 6G\n• Welder GTAW + GMAW 6G\n• Fabrikasi tangki dan bejana tekan (pressure vessel) berstandar lolos uji radiografi (X-ray).",
                    2
                ],
                [
                    'AGUSTUS 2023 – FEBRUARI 2024',
                    'PT. ERAJAYA GOPTI ABADI',
                    'Welder GTAW + SMAW 6G',
                    "• Welder GTAW + SMAW 6G\n• Pekerjaan pengelasan struktur baja dan perpipaan bertekanan tinggi.",
                    3
                ],
                [
                    'SEPTEMBER 2021 – DESEMBER 2021',
                    'PT. KHEE',
                    'Welder SMAW 6G',
                    "• Welder SMAW 6G\n• Pengelasan struktur berat konstruksi lepas pantai dan fabrikasi industri.",
                    4
                ],
                [
                    'DESEMBER 2021 – JULY 2023',
                    'FREELANCER',
                    'Web Automation & Developer',
                    "• Web Automation & Scrapping menggunakan Node.js dan Puppeteer\n• Data Mining & integrasi API otomatis\n• Community Manager teknis & administrasi server",
                    5
                ],
                [
                    'SEPTEMBER 2019 – SEPTEMBER 2021',
                    'PT. MATRICK PACK',
                    'Operator Blowing',
                    "• Operator Blowing mesin industri\n• Monitoring dan pemeliharaan mesin produksi kemasan plastik.",
                    6
                ],
                [
                    'JULI 2017 – AGUSTUS 2019',
                    'PT. PANCA JAYA ABADI',
                    'Operator Warehouse',
                    "• Operator Warehouse\n• Manajemen inventaris barang, input data komputer, dan logistik pergudangan.",
                    7
                ]
            ];
            $stmt = $db->prepare("INSERT INTO experience (date_range, company, role, details, sort_order) VALUES (?, ?, ?, ?, ?)");
            foreach ($initialExp as $ex) {
                $stmt->execute($ex);
            }

            // Seed Certificates
            $initialCerts = [
                [
                    'Sertifikasi Welder 6G (GTAW + SMAW)',
                    'PT. Global Sarana Internusa & Badan Sertifikasi',
                    '2021',
                    'CERT-WELD-6G-091',
                    '#',
                    'assets/images/cert-welder.jpg',
                    'Sertifikasi kompetensi pengelasan posisi pipa 6G untuk proses TIG (GTAW) dan Stick (SMAW) dengan pengujian visual dan NDT/Radiografi.',
                    1,
                    1
                ],
                [
                    'Sertifikasi Welder SMAW 3G + 4G Plat',
                    'PT. Global Sarana Internusa',
                    '2021',
                    'CERT-WELD-34G-042',
                    '#',
                    'assets/images/cert-welder.jpg',
                    'Kualifikasi pengelasan plat posisi vertikal naik (3G) dan overhead (4G) standar fabrikasi baja industri.',
                    1,
                    2
                ],
                [
                    'Ijazah & Sertifikat Kejuruan Rekayasa Perangkat Lunak',
                    'SMK Multistudi High School',
                    '2017',
                    'RPL-MHS-2017-088',
                    '#',
                    'assets/images/cert-code.jpg',
                    'Kompetensi kejuruan bidang Rekayasa Perangkat Lunak: Pemrograman dasar, Database Relasional, Object Oriented Programming, dan Web Dev.',
                    1,
                    3
                ],
                [
                    'Web Automation & Scraping Specialist (Node.js)',
                    'Online Tech Certification',
                    '2022',
                    'DEV-NODE-AUTO-554',
                    '#',
                    'assets/images/cert-code.jpg',
                    'Penguasaan otomatisasi browser menggunakan Puppeteer/Playwright, parsing data HTML/JSON, crawling bot, dan bypass proteksi.',
                    1,
                    4
                ]
            ];
            $stmt = $db->prepare("INSERT INTO certificates (title, issuer, issue_date, credential_id, credential_url, image, description, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($initialCerts as $c) {
                $stmt->execute($c);
            }

            // Seed Projects
            $initialProjects = [
                [
                    'Automated E-Commerce & Marketplace Scraper',
                    'Web Automation',
                    'Sistem bot scraper multi-thread berbasis Node.js untuk mengekstrak data katalog produk, harga real-time, dan stok dari berbagai marketplace terkemuka dengan sistem anti-blocking proxy rotation.',
                    'Node.js, Puppeteer, Cheerio, Express, MongoDB',
                    'https://github.com/masputra1404',
                    'https://github.com/masputra1404',
                    'assets/images/proj-automation.jpg',
                    1,
                    1
                ],
                [
                    'High Pressure Pipeline & Vessel 6G Welding Project',
                    'Welding & Fabrikasi',
                    'Proyek pengelasan presisi tinggi pada jaringan perpipaan tekanan tinggi dan bejana tekan (pressure vessel) berstandar ASME Section IX dengan hasil uji 100% lolos radiografi (X-Ray).',
                    'GTAW (Argon/TIG), SMAW (Stick), ASME IX, Carbon & Stainless Steel',
                    '#',
                    '#',
                    'assets/images/proj-welding.jpg',
                    1,
                    2
                ],
                [
                    'Sistem Manajemen Gudang & Inventaris Real-Time',
                    'Web Development',
                    'Aplikasi web manajemen pergudangan untuk melacak stok masuk/keluar, reporting otomatis, generate barcode, dan notifikasi stok menipis.',
                    'PHP, MySQL, Bootstrap 5, JavaScript, Chart.js',
                    '#',
                    'https://github.com/masputra1404',
                    'assets/images/proj-inventory.jpg',
                    1,
                    3
                ],
                [
                    'Telegram Trading & Price Alert Bot',
                    'Web Automation',
                    'Bot otomatisasi yang memantau pergerakan harga komoditas dan mengirimkan sinyal instan via Telegram API setiap kali terjadi anomali harga.',
                    'Node.js, Telegram Bot API, Axios, Cron',
                    '#',
                    'https://github.com/masputra1404',
                    'assets/images/proj-bot.jpg',
                    1,
                    4
                ],
                [
                    'Aplikasi Kasir & Pembukuan Desktop',
                    'Desktop Application',
                    'Software aplikasi kasir POS desktop untuk toko retail dengan fitur pencetakan struk thermal, barcode scanner, dan laporan laba rugi.',
                    'VB.Net, C#, Microsoft SQL Server, Crystal Reports',
                    '#',
                    'https://github.com/masputra1404',
                    'assets/images/proj-desktop.jpg',
                    1,
                    5
                ],
                [
                    'Fabrikasi Struktur Heavy Equipment Frame',
                    'Welding & Fabrikasi',
                    'Pekerjaan pengelasan GMAW pada struktur rangka alat berat Caterpillar dengan kontrol distorsi dan uji penetrant test (PT).',
                    'GMAW (MIG/MAG), Solid Wire ER70S-6, Heavy Plate Steel',
                    '#',
                    '#',
                    'assets/images/proj-welding2.jpg',
                    1,
                    6
                ]
            ];
            $stmt = $db->prepare("INSERT INTO projects (title, category, description, tech_stack, live_url, github_url, image, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($initialProjects as $p) {
                $stmt->execute($p);
            }

            // Mark as permanently seeded so emptied/deleted records never respawn
            $db->prepare("INSERT OR REPLACE INTO system_settings (key, value) VALUES ('is_seeded', '1')")->execute();
        }
    }
}
