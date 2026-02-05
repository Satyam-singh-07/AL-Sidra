<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions | AL-SIDRA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #1a4d2e;
            --accent-gold: #c5a059;
            --light-bg: #f8f9fa;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-radius: 12px;
            --shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
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
            background-image:
                radial-gradient(circle at 10% 20%, rgba(26, 77, 46, 0.03) 0%, transparent 20%),
                radial-gradient(circle at 90% 80%, rgba(197, 160, 89, 0.03) 0%, transparent 20%);
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background-color: var(--primary-green);
            padding: 40px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-gold), #e8d6b0, var(--accent-gold));
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Content */
        .terms-container {
            padding: 60px 0;
        }

        .last-updated {
            text-align: center;
            color: var(--accent-gold);
            font-weight: 600;
            margin-bottom: 40px;
            padding: 10px;
            background: rgba(197, 160, 89, 0.1);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--accent-gold);
        }

        /* Theme Cards */
        .theme-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(26, 77, 46, 0.1);
            position: relative;
            transition: var(--transition);
        }

        .theme-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .theme-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(26, 77, 46, 0.2);
        }

        .theme-title {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-bg);
        }

        .theme-content ul {
            list-style: none;
            padding-left: 0;
        }

        .theme-content li {
            margin-bottom: 12px;
            padding-left: 24px;
            position: relative;
        }

        .theme-content li::before {
            content: "•";
            color: var(--accent-gold);
            font-size: 1.5rem;
            position: absolute;
            left: 0;
            top: -5px;
        }

        /* Highlight Box */
        .highlight-box {
            background: linear-gradient(135deg, rgba(26, 77, 46, 0.05), rgba(197, 160, 89, 0.05));
            border-radius: var(--border-radius);
            padding: 25px;
            margin: 40px 0;
            border: 1px solid rgba(197, 160, 89, 0.2);
            text-align: center;
        }

        .highlight-box h3 {
            color: var(--primary-green);
            margin-bottom: 15px;
        }

        /* Roles Section */
        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .role-item {
            text-align: center;
            padding: 15px;
            background: var(--light-bg);
            border-radius: var(--border-radius);
            border-top: 3px solid var(--accent-gold);
        }

        .role-name {
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 5px;
        }

        .role-desc {
            font-size: 0.9rem;
            color: var(--text-light);
        }

        /* CTA Section */
        .cta-section {
            text-align: center;
            padding: 40px 0;
        }

        .btn {
            display: inline-block;
            padding: 14px 32px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            margin: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            color: white;
            box-shadow: 0 4px 15px rgba(26, 77, 46, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 77, 46, 0.3);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-green);
            border: 2px solid var(--primary-green);
        }

        .btn-outline:hover {
            background: rgba(26, 77, 46, 0.05);
            transform: translateY(-3px);
        }

        /* Footer */
        .footer {
            background-color: var(--primary-green);
            color: rgba(255, 255, 255, 0.8);
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .footer-logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
        }

        .footer-links {
            display: flex;
            gap: 20px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: white;
        }

        .copyright {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
            }

            .logo {
                font-size: 2rem;
            }

            .theme-card {
                padding: 20px;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-links {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 30px 0;
            }

            .roles-grid {
                grid-template-columns: 1fr;
            }

            .btn {
                display: block;
                width: 100%;
                margin: 10px 0;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <a href="{{ route('home') }}" class="logo">AL-SIDRA</a>
            <p class="subtitle">Community Platform Terms & Conditions</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="terms-container">
        <div class="container">
            <!-- Last Updated -->
            <div class="last-updated">
                Last Updated: January 15, 2026
            </div>

            <!-- Introduction Card -->
            <div class="theme-card">
                <div class="theme-icon">📜</div>
                <h2 class="theme-title">Welcome to Our Community</h2>
                <div class="theme-content">
                    <p>By using AL-SIDRA, you join a trusted community of Islamic institutions and members. These terms
                        help us maintain trust, clarity, and respect for everyone.</p>
                    <p><strong>Simple rule:</strong> If you use our platform, you agree to these terms. Please read them
                        carefully.</p>
                </div>
            </div>

            <!-- Your Responsibilities -->
            <div class="theme-card">
                <div class="theme-icon">🤝</div>
                <h2 class="theme-title">Your Responsibilities</h2>
                <div class="theme-content">
                    <ul>
                        <li>Provide accurate information about yourself and your institution</li>
                        <li>Keep your account password secure</li>
                        <li>Use the platform for its intended community purposes</li>
                        <li>Respect other users and their institutions</li>
                        <li>Follow Islamic principles of honesty and respect</li>
                        <li>Report any issues or concerns to us</li>
                    </ul>
                </div>
            </div>

            <!-- Community Guidelines -->
            <div class="theme-card">
                <div class="theme-icon">❤️</div>
                <h2 class="theme-title">Our Community Promise</h2>
                <div class="theme-content">
                    <p>AL-SIDRA is built on Islamic values of trust, transparency, and community support. We all agree
                        to:</p>
                    <ul>
                        <li>Treat every member with respect and dignity</li>
                        <li>Use appropriate language and content</li>
                        <li>Protect community privacy and information</li>
                        <li>Support masjids and madrasas with integrity</li>
                        <li>Resolve differences respectfully</li>
                    </ul>
                </div>
            </div>

            <!-- Roles & Access -->
            <div class="theme-card">
                <div class="theme-icon">👥</div>
                <h2 class="theme-title">Roles & Access Levels</h2>
                <div class="theme-content">
                    <p>Different roles, different responsibilities:</p>

                    <div class="roles-grid">
                        <div class="role-item">
                            <div class="role-name">Platform Admin</div>
                            <div class="role-desc">Full system oversight and support</div>
                        </div>
                        <div class="role-item">
                            <div class="role-name">Masjid Admin</div>
                            <div class="role-desc">Manages institution profile and staff</div>
                        </div>
                        <div class="role-item">
                            <div class="role-name">Madrasa Staff</div>
                            <div class="role-desc">Teachers and content managers</div>
                        </div>
                        <div class="role-item">
                            <div class="role-name">Community Member</div>
                            <div class="role-desc">Discovers and participates</div>
                        </div>
                    </div>

                    <p style="margin-top: 20px;"><strong>Important:</strong> You may only access areas appropriate to
                        your role.</p>
                </div>
            </div>

            <!-- Content & Sharing -->
            <div class="theme-card">
                <div class="theme-icon">📚</div>
                <h2 class="theme-title">Content & Sharing</h2>
                <div class="theme-content">
                    <ul>
                        <li>You own what you share (photos, posts, educational content)</li>
                        <li>By sharing, you allow AL-SIDRA to display it to your community</li>
                        <li>Keep content appropriate and beneficial</li>
                        <li>Respect copyright and others' work</li>
                        <li>No false information or harmful content</li>
                    </ul>
                </div>
            </div>

            <!-- Donations & Support -->
            <div class="theme-card">
                <div class="theme-icon">🤲</div>
                <h2 class="theme-title">Donations & Community Support</h2>
                <div class="theme-content">
                    <p>When using donation features:</p>
                    <ul>
                        <li>Institutions are responsible for proper fund use</li>
                        <li>AL-SIDRA facilitates but doesn't manage funds</li>
                        <li>Transparency is required for all charity activities</li>
                        <li>Follow local charity regulations</li>
                        <li>Provide receipts to donors when possible</li>
                    </ul>
                </div>
            </div>

            <!-- Privacy & Data -->
            <div class="theme-card">
                <div class="theme-icon">🔒</div>
                <h2 class="theme-title">Privacy & Your Information</h2>
                <div class="theme-content">
                    <ul>
                        <li>We protect your data as described in our Privacy Policy</li>
                        <li>Institution admins must protect their community's data</li>
                        <li>We don't sell your personal information</li>
                        <li>Location data is only used to find nearby services</li>
                        <li>You control what information is public</li>
                    </ul>
                </div>
            </div>

            <!-- Important Notes -->
            <div class="highlight-box">
                <h3>Important Things to Know</h3>
                <p>AL-SIDRA is provided "as is" - we do our best but can't guarantee perfection.</p>
                <p>We may update these terms occasionally - we'll notify you of important changes.</p>
                <p>For serious violations, we may need to suspend accounts to protect our community.</p>
            </div>

            <!-- Contact -->
            <div class="theme-card">
                <div class="theme-icon">📞</div>
                <h2 class="theme-title">Questions & Contact</h2>
                <div class="theme-content">
                    <p>If you have questions about these terms:</p>
                    <ul>
                        <li>Email: <strong>support@alsidra.com</strong></li>
                        <li>Website: <strong>alsidra.com/help</strong></li>
                        <li>Institution inquiries: <strong>institutions@alsidra.com</strong></li>
                    </ul>
                    <p>We're here to help build stronger Islamic communities together.</p>
                </div>
            </div>

            <!-- Agreement CTA -->
            <div class="cta-section">
                <h3 style="color: var(--primary-green); margin-bottom: 20px;">By using AL-SIDRA, you agree to these
                    terms</h3>
                <p style="margin-bottom: 30px; color: var(--text-light);">Help us maintain a trusted, respectful
                    community platform.</p>
                <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <a href="{{ route('home') }}" class="footer-logo">AL-SIDRA</a>
                <div class="footer-links">
                    <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                    <a href="#">Community Guidelines</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 AL-SIDRA. Building stronger Islamic communities through technology.</p>
                <p style="margin-top: 10px; font-size: 0.85rem;">Connection • Structure • Trust</p>
            </div>
        </div>
    </footer>

    <script>
        // Add subtle animations to cards when they come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Apply initial styles and observe
        document.querySelectorAll('.theme-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.transitionDelay = `${index * 0.1}s`;
            observer.observe(card);
        });

        // Simple back to top functionality
        window.addEventListener('scroll', () => {
            const scrollBtn = document.createElement('button');
            scrollBtn.innerHTML = '↑';
            scrollBtn.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-green), #2a6d46);
                color: white;
                border: none;
                cursor: pointer;
                font-size: 1.2rem;
                box-shadow: 0 4px 15px rgba(26, 77, 46, 0.3);
                display: none;
                z-index: 100;
                transition: all 0.3s ease;
            `;

            if (window.scrollY > 500) {
                if (!document.querySelector('.back-to-top')) {
                    document.body.appendChild(scrollBtn);
                    scrollBtn.classList.add('back-to-top');
                }
                document.querySelector('.back-to-top').style.display = 'block';
            } else {
                if (document.querySelector('.back-to-top')) {
                    document.querySelector('.back-to-top').style.display = 'none';
                }
            }
        });

        // Add click handler for back to top
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('back-to-top')) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    </script>
</body>

</html>
