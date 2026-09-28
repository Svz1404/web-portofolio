<?php
/**
 * Language & Localization (i18n) Helper
 * Default: English (en) with Indonesian (id) support
 */

if (!ob_get_level()) {
    ob_start();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Early language detection before any HTML is sent
if (isset($_GET['lang'])) {
    $selectedLang = strtolower(trim($_GET['lang']));
    if (in_array($selectedLang, ['en', 'id'])) {
        $_SESSION['app_lang'] = $selectedLang;
        if (!headers_sent()) {
            @setcookie('app_lang', $selectedLang, time() + (86400 * 30), '/');
        }
    }
}

// Determine Current Language (Default: 'en')
function get_current_lang() {
    static $resolvedLang = null;
    if ($resolvedLang !== null) {
        return $resolvedLang;
    }

    if (isset($_SESSION['app_lang']) && in_array($_SESSION['app_lang'], ['en', 'id'])) {
        $resolvedLang = $_SESSION['app_lang'];
        return $resolvedLang;
    }

    if (isset($_COOKIE['app_lang']) && in_array($_COOKIE['app_lang'], ['en', 'id'])) {
        $_SESSION['app_lang'] = $_COOKIE['app_lang'];
        $resolvedLang = $_COOKIE['app_lang'];
        return $resolvedLang;
    }

    // Default language is English
    $resolvedLang = 'en';
    return $resolvedLang;
}

function current_lang() {
    return get_current_lang();
}

// Translations Dictionary
global $translations;
$translations = [
    'en' => [
        // Navbar
        'nav_home' => 'Home',
        'nav_about' => 'About',
        'nav_skills' => 'Skills',
        'nav_experience' => 'Experience',
        'nav_projects' => 'Projects',
        'nav_certified' => 'Certified',
        'nav_contact' => 'Contact',
        'nav_view_cv' => 'View / Print CV',
        'nav_admin' => 'Admin',

        // Hero
        'hero_badge' => 'Official Portfolio',
        'hero_status' => 'Available for Work & Collaboration',
        'hero_title_dual' => 'Programmer / Welder',
        'hero_phone_label' => 'Phone / WhatsApp',
        'hero_email_label' => 'Email Address',
        'hero_location_label' => 'Location',
        'hero_linkedin_label' => 'LinkedIn Profile',
        'btn_view_projects' => 'View Projects',
        'btn_view_certs' => 'View Certificates',
        'btn_cv_print' => 'CV Format (Print / PDF)',
        'btn_contact_me' => 'Contact Me',

        // Stats
        'stat_exp_years' => '7+ Years',
        'stat_exp_desc' => 'Industrial & IT Experience',
        'stat_projects' => 'Projects',
        'stat_projects_desc' => 'Automation & Fabrication',
        'stat_certs' => 'Certifications',
        'stat_certs_desc' => 'Welder 6G & Software',
        'stat_disciplines' => '2 Disciplines',
        'stat_disciplines_desc' => 'Welder + Programmer',

        // About
        'about_badge' => 'Complete Biography',
        'about_title' => 'About Me',
        'about_subtitle' => 'A unique convergence of heavy industrial engineering (6G Welder) and modern software craftsmanship (Node.js & Web Automation).',
        'about_welder_title' => 'Combination Welder Specialist',
        'about_welder_intro' => 'Experienced across international-standard welding processes, including:',
        'about_welder_f1' => '<strong>GTAW (TIG/Argon):</strong> High-precision 6G pipe and plate welding meeting strict radiography (X-Ray) acceptance criteria.',
        'about_welder_f2' => '<strong>SMAW (Stick):</strong> Offshore heavy structural welding, high-pressure piping, and 3G, 4G, and 6G qualifications.',
        'about_welder_f3' => '<strong>GMAW / FCAW:</strong> Heavy equipment chassis and structural fabrication at Caterpillar Indonesia.',
        'about_welder_f4' => '<strong>Testing Compliance:</strong> Versed in NDT, Visual Inspection, Penetrant Testing (PT), and Radiographic Testing (RT).',
        
        'about_prog_title' => 'Programmer & Web Automation',
        'about_prog_intro' => 'Software Engineering background dedicated to building digital automation pipelines and modern applications:',
        'about_prog_f1' => '<strong>Web Automation & Scraping:</strong> Developing web crawlers, data extraction bots, and browser automations using <strong>Node.js</strong> and Puppeteer.',
        'about_prog_f2' => '<strong>Web Development:</strong> Crafting full-stack web applications with <strong>PHP, MySQL, and HTML5</strong>.',
        'about_prog_f3' => '<strong>Desktop Applications:</strong> Experienced with <strong>VB.Net and C#</strong> for POS cashiers and inventory management systems.',
        'about_prog_f4' => '<strong>Work Ethics:</strong> Deep computer proficiency, fast adaptability, and unwavering dedication to project accountability.',

        // Skills
        'skills_badge' => 'Skills & Competencies',
        'skills_title' => 'Technical Skills & Languages',
        'skills_subtitle' => 'Demonstrated proficiency backed by real-world field track record and certified qualifications.',
        'skills_tech_heading' => 'Technical & Industrial Skills',
        'skills_lang_heading' => 'Languages',
        'skills_value_heading' => 'Performance Value Proposition',
        'skills_value_text' => 'Capable of bridging industrial manufacturing rigor with digital software automation efficiency. High work ethic, ASME/ISO-level precision, and swift mastery of modern programming frameworks.',

        // Experience & Education
        'exp_badge' => 'Career Journey',
        'exp_title' => 'Work Experience & Education',
        'exp_subtitle' => 'Professional progression spanning heavy manufacturing industries to digital software development.',
        'exp_heading' => 'Work Experience',
        'edu_heading' => 'Education & Training',
        'cv_callout_title' => 'Looking for the Official CV Layout?',
        'cv_callout_desc' => 'Dual-column modern CV design complete with blue wave curves matching the original screenshot.',
        'cv_callout_btn' => 'Open CV Layout (Print / PDF)',

        // Projects
        'proj_badge' => 'Work Portfolio',
        'proj_title' => 'Projects & Achievements',
        'proj_subtitle' => 'A curated collection of real projects in Web Automation, Full-stack Web & Desktop Apps, and Industrial Fabrication.',
        'proj_filter_all' => 'All Projects',
        'proj_btn_details' => 'Project Details',
        'proj_btn_live' => 'Live Demo',
        'proj_btn_github' => 'GitHub',
        'proj_photos_count' => 'Photos',
        'proj_gallery_title' => 'Project Photo Gallery',
        'proj_gallery_hint' => 'Click thumbnails or arrows to inspect detailed welding & project photos',
        'proj_photo_counter' => 'Photo',
        'proj_photo_of' => 'of',
        'proj_empty_title' => 'No Projects Added Yet',
        'proj_empty_desc' => 'Projects and welding portfolio will appear here once added in the admin panel.',

        // Certified
        'cert_badge' => 'Verified Credentials',
        'cert_title' => 'Certificates & Licenses',
        'cert_subtitle' => 'Officially accredited certifications verifying competence in oil & gas welding and software technology.',
        'cert_btn_view' => 'View Certificate Proof',
        'cert_modal_verify' => 'Verify / View Certificate Proof',
        'cert_year_na' => 'Year N/A',
        'cert_photos_count' => 'Pages / Sides',
        'cert_gallery_hint' => 'Click thumbnails or arrows to inspect front & back sides of this certificate',
        'cert_photo_counter' => 'Page / Side',
        'cert_photo_of' => 'of',

        // Contact
        'contact_badge' => 'Get In Touch',
        'contact_title' => 'Let’s Connect & Collaborate',
        'contact_subtitle' => 'Open for full-time opportunities, industrial welding contracts, and freelance web automation & software development.',
        'contact_info_title' => 'Contact Information',
        'contact_info_desc' => 'Reach out anytime via WhatsApp, phone, email, or LinkedIn. Prompt responses guaranteed!',
        'contact_form_title' => 'Send a Message',
        'contact_form_desc' => 'Your message will be delivered to the admin inbox, and you can also forward it directly to my WhatsApp.',
        'form_name' => 'Full Name *',
        'form_name_ph' => 'e.g. John Doe',
        'form_email' => 'Email Address *',
        'form_email_ph' => 'john@company.com',
        'form_subject' => 'Subject / Topic',
        'form_subject_ph' => 'Welder Job Offer / Web Scraping Project',
        'form_message' => 'Your Message *',
        'form_message_ph' => 'Write your message, project scope, or inquiry here...',
        'btn_send_form' => 'Send Message',
        'btn_send_wa' => 'Forward to WhatsApp',
        'msg_success' => 'Thank you! Your message has been sent successfully to Saputra.',

        // Footer
        'footer_desc' => 'Bridging the worlds of Information Technology (Software Development & Web Automation) and Heavy Manufacturing (6G Welder GTAW/SMAW/GMAW).',
        'footer_rights' => 'All rights reserved.',
        'footer_orig_cv' => 'Original CV Format',
        'footer_admin_login' => 'Admin Login',

        // CV Page
        'cv_back_btn' => 'Back to Portfolio',
        'cv_print_btn' => 'Print / Save PDF (A4)',
        'cv_edu_title' => 'EDUCATION',
        'cv_skills_title' => 'SKILLS',
        'cv_lang_title' => 'LANGUAGES',
        'cv_about_title' => 'ABOUT ME',
        'cv_exp_title' => 'WORK EXPERIENCE',
    ],

    'id' => [
        // Navbar
        'nav_home' => 'Beranda',
        'nav_about' => 'About',
        'nav_skills' => 'Skills',
        'nav_experience' => 'Pengalaman',
        'nav_projects' => 'Project',
        'nav_certified' => 'Certified',
        'nav_contact' => 'Kontak',
        'nav_view_cv' => 'Lihat / Cetak CV',
        'nav_admin' => 'Admin',

        // Hero
        'hero_badge' => 'Portofolio Resmi',
        'hero_status' => 'Siap Bekerja & Berkolaborasi',
        'hero_title_dual' => 'Programmer / Welder',
        'hero_phone_label' => 'Telepon / WA',
        'hero_email_label' => 'Alamat Email',
        'hero_location_label' => 'Lokasi',
        'hero_linkedin_label' => 'Profil LinkedIn',
        'btn_view_projects' => 'Lihat Project',
        'btn_view_certs' => 'Lihat Sertifikat',
        'btn_cv_print' => 'Tampilan CV (Print / PDF)',
        'btn_contact_me' => 'Hubungi Saya',

        // Stats
        'stat_exp_years' => '7+ Tahun',
        'stat_exp_desc' => 'Pengalaman Industri & IT',
        'stat_projects' => 'Proyek',
        'stat_projects_desc' => 'Automation & Fabrikasi',
        'stat_certs' => 'Sertifikasi',
        'stat_certs_desc' => 'Welder 6G & Software',
        'stat_disciplines' => '2 Disiplin',
        'stat_disciplines_desc' => 'Welder + Programmer',

        // About
        'about_badge' => 'Biodata Lengkap',
        'about_title' => 'Tentang Saya',
        'about_subtitle' => 'Kombinasi unik antara penguasaan rekayasa industri manufaktur (Welder 6G) dan teknologi perangkat lunak modern (Node.js & Web Automation).',
        'about_welder_title' => 'Spesialis Welder Kombinasi',
        'about_welder_intro' => 'Berpengalaman dalam berbagai proses pengelasan berstandar internasional, meliputi:',
        'about_welder_f1' => '<strong>GTAW (TIG/Argon):</strong> Pengelasan pipa posisi 6G dan plat presisi tinggi berstandar radiografi (X-Ray).',
        'about_welder_f2' => '<strong>SMAW (Stick):</strong> Pengelasan konstruksi lepas pantai, pipa tekanan tinggi, serta posisi 3G, 4G, dan 6G.',
        'about_welder_f3' => '<strong>GMAW / FCAW:</strong> Pengelasan rangka dan struktur alat berat di Caterpillar Indonesia.',
        'about_welder_f4' => '<strong>Kualifikasi Pengujian:</strong> Terbiasa dengan uji NDT, Visual Inspection, Penetrant Test (PT), dan Radiography Test (RT).',
        
        'about_prog_title' => 'Programmer & Web Automation',
        'about_prog_intro' => 'Latar belakang pendidikan Rekayasa Perangkat Lunak dengan pengalaman membangun otomasi dan aplikasi digital:',
        'about_prog_f1' => '<strong>Web Automation & Scraping:</strong> Mengembangkan bot crawler, data extractor, dan scraping menggunakan <strong>Node.js</strong> dan Puppeteer.',
        'about_prog_f2' => '<strong>Web Development:</strong> Membangun backend dan sistem web terintegrasi menggunakan <strong>PHP, MySQL, dan HTML5</strong>.',
        'about_prog_f3' => '<strong>Desktop Application:</strong> Terbiasa dengan <strong>VB.Net dan C#</strong> untuk sistem kasir POS dan inventaris toko.',
        'about_prog_f4' => '<strong>Karakter Kerja:</strong> Berpengetahuan luas dunia komputer, cepat beradaptasi, dan memiliki rasa tanggung jawab tinggi.',

        // Skills
        'skills_badge' => 'Kemampuan & Keahlian',
        'skills_title' => 'Skills & Penguasaan Bahasa',
        'skills_subtitle' => 'Tingkat kemahiran teknis yang teruji melalui pengalaman lapangan dan sertifikasi keahlian.',
        'skills_tech_heading' => 'Technical & Industrial Skills',
        'skills_lang_heading' => 'Bahasa',
        'skills_value_heading' => 'Nilai Unggulan Kinerja',
        'skills_value_text' => 'Mampu menjembatani kebutuhan teknis manufaktur dengan efisiensi otomatisasi digital. Memiliki etos kerja tinggi, ketelitian pengelasan X-ray standard, dan kecepatan adaptasi bahasa pemrograman baru.',

        // Experience & Education
        'exp_badge' => 'Jejak Langkah Karir',
        'exp_title' => 'Pengalaman Kerja & Pendidikan',
        'exp_subtitle' => 'Rekam jejak profesional dari berbagai perusahaan industri manufaktur hingga pengembangan perangkat lunak.',
        'exp_heading' => 'Pengalaman Kerja',
        'edu_heading' => 'Pendidikan & Pelatihan',
        'cv_callout_title' => 'Ingin Melihat Format CV Asli?',
        'cv_callout_desc' => 'Format CV dua kolom berdesain modern lengkap dengan grafik kurva sesuai gambar asli.',
        'cv_callout_btn' => 'Buka Tampilan CV (Print / PDF)',

        // Projects
        'proj_badge' => 'Portofolio Karya',
        'proj_title' => 'Proyek & Hasil Karya',
        'proj_subtitle' => 'Koleksi proyek nyata di bidang Web Automation, Aplikasi Web & Desktop, serta Fabrikasi Industri Pengelasan.',
        'proj_filter_all' => 'Semua Proyek',
        'proj_btn_details' => 'Detail Proyek',
        'proj_btn_live' => 'Live Demo',
        'proj_btn_github' => 'GitHub',
        'proj_photos_count' => 'Foto',
        'proj_gallery_title' => 'Galeri Foto Proyek',
        'proj_gallery_hint' => 'Klik thumbnail atau tombol panah untuk melihat seluruh foto pengelasan & proyek',
        'proj_photo_counter' => 'Foto',
        'proj_photo_of' => 'dari',
        'proj_empty_title' => 'Belum Ada Proyek Ditambahkan',
        'proj_empty_desc' => 'Dokumentasi proyek pengelasan dan portofolio karya akan ditampilkan di sini setelah ditambahkan melalui panel admin.',

        // Certified
        'cert_badge' => 'Kualifikasi Resmi',
        'cert_title' => 'Sertifikasi & Lisensi',
        'cert_subtitle' => 'Bukti keabsahan kompetensi standar pengelasan industri migas/manufaktur dan teknologi perangkat lunak.',
        'cert_btn_view' => 'Lihat Bukti Sertifikat',
        'cert_modal_verify' => 'Verifikasi / Buka Sertifikat',
        'cert_year_na' => 'Tahun N/A',
        'cert_photos_count' => 'Halaman / Sisi',
        'cert_gallery_hint' => 'Klik thumbnail atau tombol panah untuk melihat halaman depan & belakang (timbal balik) sertifikat ini',
        'cert_photo_counter' => 'Halaman / Sisi',
        'cert_photo_of' => 'dari',

        // Contact
        'contact_badge' => 'Hubungi Langsung',
        'contact_title' => 'Mari Terhubung & Bekerjasama',
        'contact_subtitle' => 'Terbuka untuk kesempatan kerja penuh waktu, proyek kontrak fabrikasi, maupun freelance web automation & software dev.',
        'contact_info_title' => 'Informasi Kontak',
        'contact_info_desc' => 'Hubungi saya kapan saja melalui WhatsApp, telepon, email, atau jaringan LinkedIn. Respon cepat terjamin!',
        'contact_form_title' => 'Kirimkan Pesan Anda',
        'contact_form_desc' => 'Pesan Anda akan tersimpan di inbox admin dan Anda juga dapat langsung meneruskannya ke WhatsApp saya.',
        'form_name' => 'Nama Lengkap *',
        'form_name_ph' => 'Contoh: Budi Santoso',
        'form_email' => 'Alamat Email *',
        'form_email_ph' => 'contoh@perusahaan.com',
        'form_subject' => 'Subjek / Topik',
        'form_subject_ph' => 'Tawaran Pekerjaan Welder / Web Scraping Project',
        'form_message' => 'Pesan Anda *',
        'form_message_ph' => 'Tuliskan pesan, rincian pekerjaan atau pertanyaan Anda...',
        'btn_send_form' => 'Kirim Pesan Form',
        'btn_send_wa' => 'Teruskan ke WhatsApp',
        'msg_success' => 'Terima kasih! Pesan Anda telah berhasil dikirimkan ke Saputra.',

        // Footer
        'footer_desc' => 'Kombinasi keahlian di bidang Teknologi Informasi (Software Development & Web Automation) dan Industri Manufaktur (Welder 6G GTAW/SMAW/GMAW).',
        'footer_rights' => 'Hak cipta dilindungi.',
        'footer_orig_cv' => 'Format CV Asli',
        'footer_admin_login' => 'Admin Login',

        // CV Page
        'cv_back_btn' => 'Kembali ke Portofolio',
        'cv_print_btn' => 'Cetak / Simpan PDF (A4)',
        'cv_edu_title' => 'PENDIDIKAN',
        'cv_skills_title' => 'SKILLS',
        'cv_lang_title' => 'BAHASA',
        'cv_about_title' => 'TENTANG SAYA',
        'cv_exp_title' => 'PENGALAMAN KERJA',
    ]
];

// Helper to translate key
function __($key, $default = '') {
    global $translations;
    $lang = get_current_lang();
    if (isset($translations[$lang][$key])) {
        return $translations[$lang][$key];
    }
    if (isset($translations['en'][$key])) {
        return $translations['en'][$key];
    }
    return !empty($default) ? $default : $key;
}

// Helper to get translated bio
function get_localized_bio($profile) {
    $lang = get_current_lang();
    if ($lang === 'en') {
        if (!empty($profile['bio_en'])) {
            return $profile['bio_en'];
        }
        return "I am a Combination Welder experienced in GTAW, SMAW, GMAW, and FCAW processes. Beyond fabrication and welding, I also possess strong capabilities as a software programmer with a primary focus on web automation and web scraping using Node.js. Familiar with programming languages including PHP, VB.Net, C#, Node.js, and HTML. Possessing extensive computer knowledge, highly adaptable, and dedicated to delivering excellence and accountability in every project.";
    }
    return $profile['bio'];
}

// Helper to get translated experience
function get_localized_exp($exp) {
    $lang = get_current_lang();
    if ($lang === 'en') {
        return [
            'date_range' => !empty($exp['date_range_en']) ? $exp['date_range_en'] : $exp['date_range'],
            'company' => $exp['company'],
            'role' => !empty($exp['role_en']) ? $exp['role_en'] : $exp['role'],
            'details' => !empty($exp['details_en']) ? $exp['details_en'] : $exp['details'],
            'sort_order' => $exp['sort_order'] ?? 0
        ];
    }
    return [
        'date_range' => $exp['date_range'],
        'company' => $exp['company'],
        'role' => $exp['role'],
        'details' => $exp['details'],
        'sort_order' => $exp['sort_order'] ?? 0
    ];
}

// Helper to get translated education
function get_localized_edu($edu) {
    $lang = get_current_lang();
    if ($lang === 'en') {
        return [
            'year_range' => $edu['year_range'],
            'institution' => $edu['institution'],
            'major' => !empty($edu['major_en']) ? $edu['major_en'] : $edu['major'],
            'sort_order' => $edu['sort_order'] ?? 0
        ];
    }
    return [
        'year_range' => $edu['year_range'],
        'institution' => $edu['institution'],
        'major' => $edu['major'],
        'sort_order' => $edu['sort_order'] ?? 0
    ];
}

// Helper to get translated certificate
function get_localized_cert($cert) {
    $lang = get_current_lang();
    if ($lang === 'en') {
        return [
            'id' => $cert['id'],
            'title' => !empty($cert['title_en']) ? $cert['title_en'] : $cert['title'],
            'issuer' => $cert['issuer'],
            'issue_date' => $cert['issue_date'],
            'credential_id' => $cert['credential_id'],
            'credential_url' => $cert['credential_url'],
            'image' => $cert['image'],
            'description' => !empty($cert['description_en']) ? $cert['description_en'] : $cert['description'],
            'featured' => $cert['featured'] ?? 1,
            'sort_order' => $cert['sort_order'] ?? 0
        ];
    }
    return $cert;
}

// Helper to get translated project
function get_localized_proj($proj) {
    $lang = get_current_lang();
    if ($lang === 'en') {
        return [
            'id' => $proj['id'],
            'title' => !empty($proj['title_en']) ? $proj['title_en'] : $proj['title'],
            'category' => $proj['category'],
            'description' => !empty($proj['description_en']) ? $proj['description_en'] : $proj['description'],
            'tech_stack' => $proj['tech_stack'],
            'live_url' => $proj['live_url'],
            'github_url' => $proj['github_url'],
            'image' => $proj['image'],
            'featured' => $proj['featured'] ?? 1,
            'sort_order' => $proj['sort_order'] ?? 0
        ];
    }
    return $proj;
}

// Helper to get translated language name
function get_localized_language_name($name) {
    $lang = get_current_lang();
    $clean = trim((string)$name);
    $map = [
        'indonesia' => ['id' => 'Indonesia', 'en' => 'Indonesian'],
        'indonesian' => ['id' => 'Indonesia', 'en' => 'Indonesian'],
        'inggris' => ['id' => 'Inggris', 'en' => 'English'],
        'english' => ['id' => 'Inggris', 'en' => 'English'],
        'polish' => ['id' => 'Polandia', 'en' => 'Polish'],
        'poland' => ['id' => 'Polandia', 'en' => 'Polish'],
        'polandia' => ['id' => 'Polandia', 'en' => 'Polish'],
    ];
    $lower = strtolower($clean);
    if (isset($map[$lower])) {
        return $map[$lower][$lang] ?? $clean;
    }
    return $clean;
}

// Helper to render language toggle switcher
function render_lang_switcher($class = '') {
    $current = get_current_lang();
    
    // Build URL maintaining other query parameters if any
    $params = $_GET;
    
    $params['lang'] = 'en';
    $enUrl = '?' . http_build_query($params);
    
    $params['lang'] = 'id';
    $idUrl = '?' . http_build_query($params);

    $html = '<div class="lang-switch-wrap ' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '">';
    $html .= '<a href="' . htmlspecialchars($enUrl, ENT_QUOTES, 'UTF-8') . '" class="lang-btn ' . ($current === 'en' ? 'active' : '') . '" title="English (Default)">EN</a>';
    $html .= '<span class="lang-sep">/</span>';
    $html .= '<a href="' . htmlspecialchars($idUrl, ENT_QUOTES, 'UTF-8') . '" class="lang-btn ' . ($current === 'id' ? 'active' : '') . '" title="Bahasa Indonesia">ID</a>';
    $html .= '</div>';

    return $html;
}
