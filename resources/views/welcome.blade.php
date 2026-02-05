<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AL-SIDRA | Connect Masjids, Madrasas & Community</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="{{ asset('assets/logo.PNG') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1a4d2e;
            --accent-gold: #c5a059;
            --light-bg: #f8f9fa;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-radius: 8px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: white;
            overflow-x: hidden;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Typography */
        h1,
        h2,
        h3,
        h4,
        h5 {
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: var(--primary-green);
        }

        h1 {
            font-size: 2.8rem;
            margin-bottom: 1.5rem;
        }

        h2 {
            font-size: 2.2rem;
            text-align: center;
            margin-bottom: 2.5rem;
        }

        h3 {
            font-size: 1.5rem;
            margin-bottom: 0.75rem;
        }

        p {
            margin-bottom: 1.5rem;
            color: var(--text-light);
        }

        .section-title {
            position: relative;
            padding-bottom: 1rem;
            margin-bottom: 3rem;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background-color: var(--accent-gold);
        }

        /* Buttons */
        .btn {
            display: inline-block;
            padding: 12px 28px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: var(--primary-green);
            color: white;
        }

        .btn-primary:hover {
            background-color: #143823;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-green);
            border: 2px solid var(--primary-green);
        }

        .btn-secondary:hover {
            background-color: var(--light-bg);
            transform: translateY(-2px);
        }

        .btn-accent {
            background-color: var(--accent-gold);
            color: white;
            font-weight: 600;
        }

        .btn-accent:hover {
            background-color: #b08c47;
            transform: translateY(-2px);
        }

        /* Header */
        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 15px 0;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-green);
            margin-left: 10px;
        }

        .nav-links {
            display: flex;
            list-style: none;
        }

        .nav-links li {
            margin-left: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
        }

        .nav-links a:hover {
            color: var(--primary-green);
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--primary-green);
        }

        /* Hero Section */
        .hero {
            padding: 150px 0 80px;
            background-color: var(--primary-green);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: linear-gradient(135deg, rgba(197, 160, 89, 0.1) 0%, rgba(197, 160, 89, 0) 70%);
        }

        .hero h1 {
            color: white;
        }

        .hero p {
            color: rgba(255, 255, 255, 0.85);
            font-size: 1.2rem;
            max-width: 700px;
            margin-bottom: 2.5rem;
        }

        .hero-btns {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Section Styling */
        section {
            padding: 80px 0;
        }

        .section-light {
            background-color: var(--light-bg);
        }

        /* Problem Section */
        .problem-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-top: 2rem;
        }

        .problem-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--accent-gold);
        }

        .problem-icon {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            color: var(--accent-gold);
        }

        /* Features Section */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
        }

        .feature-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--accent-gold);
        }

        /* Roles Section */
        .roles-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 2rem;
        }

        .role-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 25px;
            width: 220px;
            text-align: center;
            box-shadow: var(--shadow);
            border-bottom: 3px solid var(--primary-green);
        }

        .role-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(26, 77, 46, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: var(--primary-green);
            font-size: 1.5rem;
        }

        /* Why Al-Sidra Section */
        .why-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .why-card {
            background-color: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--primary-green);
        }

        .why-icon {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-green);
        }

        /* Vision Section */
        .vision-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid rgba(26, 77, 46, 0.1);
        }

        .vision-text {
            font-size: 1.4rem;
            color: var(--primary-green);
            font-weight: 500;
            margin-bottom: 0;
        }

        /* CTA Section */
        .cta-section {
            background-color: var(--primary-green);
            color: white;
            text-align: center;
            padding: 80px 0;
        }

        .cta-section h2 {
            color: white;
        }

        .cta-section p {
            color: rgba(255, 255, 255, 0.85);
            max-width: 700px;
            margin: 0 auto 2.5rem;
        }

        /* Footer */
        footer {
            background-color: #0c2c1c;
            color: rgba(255, 255, 255, 0.7);
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            display: block;
        }

        .footer-links h4 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.8rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            h1 {
                font-size: 2.3rem;
            }

            h2 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }

            .nav-links {
                position: fixed;
                top: 70px;
                left: 0;
                width: 100%;
                background-color: white;
                flex-direction: column;
                align-items: center;
                padding: 20px 0;
                box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
                transform: translateY(-100%);
                opacity: 0;
                transition: var(--transition);
                z-index: 999;
            }

            .nav-links.active {
                transform: translateY(0);
                opacity: 1;
            }

            .nav-links li {
                margin: 15px 0;
            }

            .hero-btns {
                flex-direction: column;
                align-items: flex-start;
            }

            .hero-btns .btn {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
            }

            .roles-container {
                justify-content: center;
            }
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 2rem;
            }

            section {
                padding: 60px 0;
            }

            .hero {
                padding: 130px 0 60px;
            }

            .role-card {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>
</head>

<body>
    <!-- Header & Navigation -->
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="#" class="logo">
                    <div class="logo-text">AL-SIDRA</div>
                </a>

                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    ☰
                </button>

                <ul class="nav-links" id="navLinks">
                    <li><a href="#problem">The Problem</a></li>
                    <li><a href="#features">Features</a></li>
                    <li><a href="#roles">Roles</a></li>
                    <li><a href="#why">Why AL-SIDRA</a></li>
                    <li><a href="{{ route('terms-conditions') }}">Terms & Conditions</a></li>
                    <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
                    <li><a href="#cta" class="btn btn-secondary" style="padding: 8px 20px;">Get Started</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>One Platform to Connect Masjids, Madrasas, and the Community</h1>
            <p>AL-SIDRA brings structure, transparency, and accessibility to Islamic community services. Connect
                masjids, madrasas, and local initiatives with people—all in one place.</p>
            <p>No chaos. No scattered systems. Just clarity.</p>
            <div class="hero-btns">
                <a href="#" class="btn btn-accent">Get App</a>
                <a href="#features" class="btn btn-secondary">Discover Nearby Services</a>
            </div>
        </div>
    </section>

    <!-- Problem Section -->
    <section id="problem" class="section-light">
        <div class="container">
            <h2 class="section-title">The Problem We're Fixing</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 2rem;">Today, most community services run on
                scattered systems, leading to poor visibility, no accountability, and limited reach.</p>

            <div class="problem-cards">
                <div class="problem-card">
                    <div class="problem-icon">💬</div>
                    <h3>WhatsApp Dependency</h3>
                    <p>Important information gets lost in endless group chats with no structure or organization.</p>
                </div>

                <div class="problem-card">
                    <div class="problem-icon">📄</div>
                    <h3>Paper Records</h3>
                    <p>Manual records are prone to errors, difficult to search, and impossible to access remotely.</p>
                </div>

                <div class="problem-card">
                    <div class="problem-icon">🗣️</div>
                    <h3>Word-of-Mouth</h3>
                    <p>Information spreads inconsistently, leaving many community members out of the loop.</p>
                </div>

                <div class="problem-card">
                    <div class="problem-icon">📱</div>
                    <h3>Unorganized Social Media</h3>
                    <p>Critical updates get buried in feeds, with no centralized place for important community
                        information.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features">
        <div class="container">
            <h2 class="section-title">What AL-SIDRA Does</h2>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📍</div>
                    <h3>Find Nearby Masjids & Madrasas</h3>
                    <p>Location-aware discovery of Islamic institutions based on distance, gender-specific needs, and
                        services.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🕌</div>
                    <h3>Manage Operations</h3>
                    <p>Admins can manage profiles, staff, services, and publish updates—all in one organized dashboard.
                    </p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🎓</div>
                    <h3>Structured Education & Content</h3>
                    <p>Centralized educational content, announcements, and learning materials—no more lost files or
                        random links.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🤝</div>
                    <h3>Community Help & Support</h3>
                    <p>Verified help requests with transparent donation tracking and clear status updates.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Role-Based Access</h3>
                    <p>Different permissions for platform admins, members, and users. Everyone sees only what they
                        need.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Roles Section -->
    <section id="roles" class="section-light">
        <div class="container">
            <h2 class="section-title">Built for Every Role</h2>
            <p style="text-align: center; max-width: 800px; margin: 0 auto 2rem;">AL-SIDRA is designed with clear role
                separation. Everyone sees only what they need—no confusion, no misuse.</p>

            <div class="roles-container">
                <div class="role-card">
                    <div class="role-icon">⚙️</div>
                    <h3>Platform Owners</h3>
                    <p>Full system control and oversight across all institutions and services.</p>
                </div>

                <div class="role-card">
                    <div class="role-icon">🕌</div>
                    <h3>Masjid / Madrasa Admins</h3>
                    <p>Manage their institutions, staff, content, and community interactions.</p>
                </div>

                <div class="role-card">
                    <div class="role-icon">👨‍🏫</div>
                    <h3>Members</h3>
                    <p>Teachers, trainers, and managers with access to relevant tools and content.</p>
                </div>

                <div class="role-card">
                    <div class="role-icon">👤</div>
                    <h3>Users</h3>
                    <p>Discover, learn, and participate in community services and activities.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why AL-SIDRA Section -->
    <section id="why">
        <div class="container">
            <h2 class="section-title">Why AL-SIDRA is Different</h2>

            <div class="why-cards">
                <div class="why-card">
                    <div class="why-icon">📍</div>
                    <h3>Location-Based Discovery</h3>
                    <p>Users can instantly find masjids and madrasas near them with accurate distance information.</p>
                </div>

                <div class="why-card">
                    <div class="why-icon">🔐</div>
                    <h3>Role-Based Access Control</h3>
                    <p>Granular permissions ensure everyone has appropriate access—no more, no less.</p>
                </div>

                <div class="why-card">
                    <div class="why-icon">📈</div>
                    <h3>Scalable, API-First Platform</h3>
                    <p>Built to grow into mobile apps, analytics, and integrations with other community tools.</p>
                </div>

                <div class="why-card">
                    <div class="why-icon">❤️</div>
                    <h3>Built for Real Community Needs</h3>
                    <p>Designed with input from masjid committees, madrasa administrators, and community leaders.</p>
                </div>
            </div>

            <p
                style="text-align: center; margin-top: 3rem; font-weight: 500; color: var(--primary-green); font-size: 1.1rem;">
                This is not a static website. It's a community operating system.
            </p>
        </div>
    </section>

    <!-- Vision Section -->
    <section class="section-light">
        <div class="container">
            <div class="vision-content">
                <h2>Our Vision</h2>
                <p class="vision-text">"Empowering Islamic communities with technology that is simple, transparent,
                    accessible, and scalable."</p>
                <p>AL-SIDRA is about connection, structure, and trust—building stronger communities through technology
                    designed for real needs.</p>
            </div>
        </div>
    </section>

    <!-- Final CTA Section -->
    <section id="cta" class="cta-section">
        <div class="container">
            <h2>Build a Stronger, Connected Community</h2>
            <p>Join masjid committees, madrasa administrators, community organizations, and NGOs already using AL-SIDRA
                to bring structure and transparency to their services.</p>
            <a href="#" class="btn btn-accent">Get Application Now</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-about">
                    <a href="#" class="footer-logo">AL-SIDRA</a>
                    <p>One platform to connect Masjids, Madrasas, and the Muslim community. Bringing structure,
                        transparency, and accessibility to Islamic community services.</p>
                </div>

                <div class="footer-links">
                    <h4>Platform</h4>
                    <ul>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#roles">Roles</a></li>
                        <li><a href="#why">Why AL-SIDRA</a></li>
                        <li><a href="#cta">Get Started</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="{{ route('privacy-policy') }}">Privacy Policy</a></li>
                        <li><a href="{{ route('terms-conditions') }}">Terms of Conditions</a></li>
                    </ul>
                </div>
            </div>

            <div class="copyright">
                <p>&copy; 2023 AL-SIDRA. All rights reserved. Empowering Islamic communities with modern technology.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');

        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('active');
            mobileMenuBtn.textContent = navLinks.classList.contains('active') ? '✕' : '☰';
        });

        // Close mobile menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
                mobileMenuBtn.textContent = '☰';
            });
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();

                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>

</html>
