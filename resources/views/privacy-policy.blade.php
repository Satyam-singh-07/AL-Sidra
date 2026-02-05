<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy | AL-SIDRA</title>
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
                radial-gradient(circle at 5% 15%, rgba(26, 77, 46, 0.03) 0%, transparent 25%),
                radial-gradient(circle at 95% 85%, rgba(197, 160, 89, 0.03) 0%, transparent 25%);
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            padding: 50px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '🔒';
            position: absolute;
            font-size: 15rem;
            opacity: 0.03;
            right: -50px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
            font-weight: 400;
        }

        /* Main Content */
        .privacy-container {
            padding: 60px 0;
        }

        .last-updated {
            text-align: center;
            color: var(--accent-gold);
            font-weight: 600;
            margin-bottom: 40px;
            padding: 12px 20px;
            background: rgba(197, 160, 89, 0.1);
            border-radius: var(--border-radius);
            border: 1px solid rgba(197, 160, 89, 0.2);
            display: inline-block;
            width: 100%;
        }

        /* Summary Card */
        .summary-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            border-top: 4px solid var(--accent-gold);
            text-align: center;
        }

        .summary-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            margin: 0 auto 20px;
            box-shadow: 0 6px 20px rgba(26, 77, 46, 0.2);
        }

        .summary-title {
            font-size: 1.8rem;
            color: var(--primary-green);
            margin-bottom: 15px;
        }

        .summary-text {
            color: var(--text-light);
            margin-bottom: 20px;
        }

        /* Privacy Principles */
        .principles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }

        .principle-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            text-align: center;
            box-shadow: var(--shadow);
            border: 1px solid rgba(26, 77, 46, 0.1);
            transition: var(--transition);
        }

        .principle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .principle-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-green);
        }

        .principle-title {
            font-size: 1.2rem;
            color: var(--primary-green);
            margin-bottom: 10px;
            font-weight: 600;
        }

        /* Data Collection Section */
        .data-collection {
            background: white;
            border-radius: var(--border-radius);
            padding: 35px;
            margin: 40px 0;
            box-shadow: var(--shadow);
            border-left: 4px solid var(--accent-gold);
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-bg);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            font-size: 1.3rem;
        }

        .data-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .data-type {
            background: var(--light-bg);
            padding: 15px;
            border-radius: 8px;
            border-left: 3px solid var(--primary-green);
        }

        .data-type-title {
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 5px;
        }

        /* Toggle Sections */
        .toggle-section {
            margin: 30px 0;
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            cursor: pointer;
            border: 1px solid rgba(26, 77, 46, 0.1);
            transition: var(--transition);
        }

        .toggle-section:hover {
            border-color: var(--accent-gold);
        }

        .toggle-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .toggle-title {
            font-size: 1.3rem;
            color: var(--primary-green);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .toggle-title::before {
            font-size: 1.2rem;
        }

        .toggle-icon {
            color: var(--accent-gold);
            font-size: 1.5rem;
            transition: var(--transition);
        }

        .toggle-content {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--light-bg);
            display: none;
        }

        .toggle-content.active {
            display: block;
        }

        .toggle-icon.rotated {
            transform: rotate(180deg);
        }

        /* Your Rights */
        .rights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .right-item {
            background: linear-gradient(135deg, rgba(26, 77, 46, 0.05), rgba(197, 160, 89, 0.05));
            padding: 20px;
            border-radius: 10px;
            border: 1px solid rgba(26, 77, 46, 0.1);
        }

        .right-title {
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Consent Section */
        .consent-box {
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            margin: 40px 0;
            text-align: center;
        }

        .consent-title {
            color: white;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }

        /* Contact */
        .contact-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            margin: 40px 0;
            box-shadow: var(--shadow);
            text-align: center;
            border: 2px solid var(--light-bg);
        }

        .contact-title {
            font-size: 1.5rem;
            color: var(--primary-green);
            margin-bottom: 20px;
        }

        .contact-methods {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin-top: 20px;
        }

        .contact-method {
            padding: 15px 25px;
            background: var(--light-bg);
            border-radius: 8px;
            min-width: 200px;
        }

        .contact-label {
            font-weight: 600;
            color: var(--primary-green);
            margin-bottom: 5px;
        }

        /* Buttons */
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

            .principles-grid,
            .rights-grid {
                grid-template-columns: 1fr;
            }

            .data-types {
                grid-template-columns: 1fr;
            }

            .contact-methods {
                flex-direction: column;
                gap: 15px;
            }

            .contact-method {
                min-width: auto;
            }

            .header::before {
                font-size: 8rem;
                right: -30px;
            }
        }

        @media (max-width: 480px) {
            .header {
                padding: 30px 0;
            }

            .toggle-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
            <p class="subtitle">Protecting Community Privacy with Trust & Transparency</p>
        </div>
    </header>

    <!-- Main Content -->
    <main class="privacy-container">
        <div class="container">
            <!-- Last Updated -->
            <div class="last-updated">
                Last Updated: January 15, 2026
            </div>

            <!-- Summary Card -->
            <div class="summary-card">
                <div class="summary-icon">🔐</div>
                <h1 class="summary-title">Our Privacy Promise</h1>
                <p class="summary-text">
                    At AL-SIDRA, we believe privacy is a sacred trust. We handle your information with the same care and
                    respect we'd expect for our own community's data.
                </p>
                <div style="color: var(--primary-green); font-weight: 500; margin-top: 20px;">
                    We don't sell your data. We don't misuse your trust. We protect our community.
                </div>
            </div>

            <!-- Privacy Principles -->
            <div class="data-collection">
                <h2 class="section-title">📋 Our Privacy Principles</h2>
                <p>Our approach to privacy is guided by Islamic values of trust (amanah) and transparency:</p>

                <div class="principles-grid">
                    <div class="principle-card">
                        <div class="principle-icon">🛡️</div>
                        <h3 class="principle-title">Protection</h3>
                        <p>We secure your data with industry-standard measures and regular security updates.</p>
                    </div>

                    <div class="principle-card">
                        <div class="principle-icon">👁️</div>
                        <h3 class="principle-title">Transparency</h3>
                        <p>We clearly explain what data we collect and why - no hidden tracking or surprises.</p>
                    </div>

                    <div class="principle-card">
                        <div class="principle-icon">🎯</div>
                        <h3 class="principle-title">Purpose</h3>
                        <p>We only collect data necessary to serve our community and improve our platform.</p>
                    </div>

                    <div class="principle-card">
                        <div class="principle-icon">🤝</div>
                        <h3 class="principle-title">Respect</h3>
                        <p>We treat your information with the dignity and respect owed to every community member.</p>
                    </div>
                </div>
            </div>

            <!-- What We Collect -->
            <div class="toggle-section" id="dataCollectionToggle">
                <div class="toggle-header">
                    <div class="toggle-title">📊 What Information We Collect</div>
                    <div class="toggle-icon">▼</div>
                </div>
                <div class="toggle-content">
                    <p>We collect only what's necessary to provide our services:</p>

                    <div class="data-types">
                        <div class="data-type">
                            <div class="data-type-title">Account Information</div>
                            <p>Name, email, phone (for account creation and verification)</p>
                        </div>

                        <div class="data-type">
                            <div class="data-type-title">Institution Details</div>
                            <p>Masjid/madrasa name, location, services (for community discovery)</p>
                        </div>

                        <div class="data-type">
                            <div class="data-type-title">Location Data</div>
                            <p>Approximate location (only with permission, to find nearby services)</p>
                        </div>

                        <div class="data-type">
                            <div class="data-type-title">Usage Information</div>
                            <p>How you use our platform (to improve features and fix issues)</p>
                        </div>
                    </div>

                    <div
                        style="margin-top: 20px; padding: 15px; background: rgba(26, 77, 46, 0.05); border-radius: 8px;">
                        <strong>Note:</strong> We <strong>never</strong> collect sensitive personal information like
                        religious practices, political views, or financial data beyond what's needed for donation
                        processing.
                    </div>
                </div>
            </div>

            <!-- How We Use Data -->
            <div class="toggle-section" id="dataUseToggle">
                <div class="toggle-header">
                    <div class="toggle-title">🎯 How We Use Your Information</div>
                    <div class="toggle-icon">▼</div>
                </div>
                <div class="toggle-content">
                    <p>Your information helps us serve our community better:</p>

                    <div class="rights-grid">
                        <div class="right-item">
                            <div class="right-title">📍 Service Discovery</div>
                            <p>Show nearby masjids and madrasas based on your location</p>
                        </div>

                        <div class="right-item">
                            <div class="right-title">🕌 Institution Management</div>
                            <p>Help masjid admins manage their profiles and community</p>
                        </div>

                        <div class="right-item">
                            <div class="right-title">📱 Platform Improvement</div>
                            <p>Fix bugs, improve features, and enhance user experience</p>
                        </div>

                        <div class="right-item">
                            <div class="right-title">🔔 Important Updates</div>
                            <p>Send notifications about your institution or account</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Sharing -->
            <div class="toggle-section" id="dataSharingToggle">
                <div class="toggle-header">
                    <div class="toggle-title">🤝 When We Share Information</div>
                    <div class="toggle-icon">▼</div>
                </div>
                <div class="toggle-content">
                    <p>We only share information in these specific situations:</p>

                    <ul style="margin: 20px 0 20px 20px;">
                        <li><strong>With Your Institution:</strong> Basic profile info with your masjid/madrasa admin
                        </li>
                        <li><strong>Service Providers:</strong> Trusted partners who help us run the platform</li>
                        <li><strong>Legal Requirements:</strong> When required by law or to protect our community</li>
                        <li><strong>With Your Consent:</strong> When you explicitly agree to share</li>
                    </ul>

                    <div
                        style="background: rgba(197, 160, 89, 0.1); padding: 15px; border-radius: 8px; margin-top: 20px;">
                        <strong>We never sell your data</strong> to advertisers or third parties. Our community's trust
                        is more valuable than any data sale.
                    </div>
                </div>
            </div>

            <!-- Your Rights -->
            <div class="data-collection">
                <h2 class="section-title">⚖️ Your Privacy Rights</h2>
                <p>You have control over your information:</p>

                <div class="rights-grid">
                    <div class="right-item">
                        <div class="right-title">👁️ Right to Access</div>
                        <p>See what information we have about you</p>
                    </div>

                    <div class="right-item">
                        <div class="right-title">✏️ Right to Correct</div>
                        <p>Update inaccurate or incomplete information</p>
                    </div>

                    <div class="right-item">
                        <div class="right-title">🗑️ Right to Delete</div>
                        <p>Request deletion of your personal data</p>
                    </div>

                    <div class="right-item">
                        <div class="right-title">🚫 Right to Opt-Out</div>
                        <p>Choose not to receive non-essential communications</p>
                    </div>
                </div>
            </div>

            <!-- Data Security -->
            <div class="toggle-section" id="securityToggle">
                <div class="toggle-header">
                    <div class="toggle-title">🔒 How We Protect Your Data</div>
                    <div class="toggle-icon">▼</div>
                </div>
                <div class="toggle-content">
                    <p>We implement multiple layers of security:</p>

                    <div style="display: grid; gap: 15px; margin: 20px 0;">
                        <div
                            style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--light-bg); border-radius: 8px;">
                            <div style="color: var(--primary-green); font-weight: bold;">🔐</div>
                            <div>
                                <strong>Encryption:</strong> All data is encrypted in transit and at rest
                            </div>
                        </div>

                        <div
                            style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--light-bg); border-radius: 8px;">
                            <div style="color: var(--primary-green); font-weight: bold;">🛡️</div>
                            <div>
                                <strong>Access Controls:</strong> Strict role-based access to sensitive data
                            </div>
                        </div>

                        <div
                            style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--light-bg); border-radius: 8px;">
                            <div style="color: var(--primary-green); font-weight: bold;">📊</div>
                            <div>
                                <strong>Regular Audits:</strong> Continuous security monitoring and testing
                            </div>
                        </div>

                        <div
                            style="display: flex; align-items: center; gap: 10px; padding: 12px; background: var(--light-bg); border-radius: 8px;">
                            <div style="color: var(--primary-green); font-weight: bold;">👥</div>
                            <div>
                                <strong>Staff Training:</strong> All team members trained in data protection
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consent Section -->
            <div class="consent-box">
                <h3 class="consent-title">Your Consent Matters</h3>
                <p>By using AL-SIDRA, you consent to our privacy practices as described here. We'll notify you of any
                    significant changes to this policy.</p>
                <p style="margin-top: 15px; opacity: 0.9;">We're committed to protecting our community's privacy with
                    the highest standards of Islamic ethics and modern security practices.</p>
            </div>

            <!-- Children's Privacy -->
            <div class="toggle-section" id="childrenToggle">
                <div class="toggle-header">
                    <div class="toggle-title">👶 Children's Privacy</div>
                    <div class="toggle-icon">▼</div>
                </div>
                <div class="toggle-content">
                    <p>AL-SIDRA is designed for community use, including madrasas and Islamic schools:</p>

                    <div
                        style="background: rgba(26, 77, 46, 0.05); padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <strong>For Madrasas & Schools:</strong> Institution administrators are responsible for
                        obtaining parental consent for students under 13 and managing their data appropriately.
                    </div>

                    <p>We comply with COPPA and other children's privacy regulations. Parents can contact us to review
                        or delete their child's information.</p>
                </div>
            </div>

            <!-- Contact Section -->
            <div class="contact-card">
                <h2 class="contact-title">📞 Privacy Questions & Support</h2>
                <p>Have questions about your privacy or need assistance with your data?</p>

                <div class="contact-methods">
                    <div class="contact-method">
                        <div class="contact-label">Email</div>
                        <div>privacy@alsidra.com</div>
                    </div>

                    <div class="contact-method">
                        <div class="contact-label">Data Protection Officer</div>
                        <div>dpo@alsidra.com</div>
                    </div>

                    <div class="contact-method">
                        <div class="contact-label">Emergency Contact</div>
                        <div>security@alsidra.com</div>
                    </div>
                </div>

                <div style="margin-top: 25px;">
                    <a href="{{ route('home') }}" class="btn btn-primary">Return to Home</a>
                    <a href="{{ route('terms-conditions') }}" class="btn btn-outline">View Terms & Conditions</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <a href="{{ route('home') }}" class="footer-logo">AL-SIDRA</a>
                <div class="footer-links">
                    <a href="{{ route('terms-conditions') }}">Terms & Conditions</a>
                    <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                    <a href="#">Cookie Policy</a>
                    <a href="#">Contact Us</a>
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2024 AL-SIDRA. Protecting Community Trust Through Technology.</p>
                <p style="margin-top: 10px; font-size: 0.85rem;">Trust • Transparency • Protection</p>
            </div>
        </div>
    </footer>

    <script>
        // Toggle functionality for sections
        document.querySelectorAll('.toggle-section').forEach(section => {
            const header = section.querySelector('.toggle-header');
            const content = section.querySelector('.toggle-content');
            const icon = section.querySelector('.toggle-icon');

            header.addEventListener('click', () => {
                content.classList.toggle('active');
                icon.classList.toggle('rotated');

                // Close other sections (optional)
                // document.querySelectorAll('.toggle-content').forEach(otherContent => {
                //     if (otherContent !== content) {
                //         otherContent.classList.remove('active');
                //         otherContent.parentElement.querySelector('.toggle-icon').classList.remove('rotated');
                //     }
                // });
            });
        });

        // Back to top button
        window.addEventListener('scroll', () => {
            const scrollBtn = document.createElement('button');
            scrollBtn.innerHTML = '🔝';
            scrollBtn.style.cssText = `
                position: fixed;
                bottom: 25px;
                right: 25px;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: linear-gradient(135deg, var(--primary-green), #2a6d46);
                color: white;
                border: none;
                cursor: pointer;
                font-size: 1.5rem;
                box-shadow: 0 6px 20px rgba(26, 77, 46, 0.3);
                display: none;
                z-index: 100;
                transition: all 0.3s ease;
            `;

            scrollBtn.onmouseover = () => {
                scrollBtn.style.transform = 'scale(1.1)';
            };

            scrollBtn.onmouseout = () => {
                scrollBtn.style.transform = 'scale(1)';
            };

            if (window.scrollY > 500) {
                if (!document.querySelector('.back-to-top')) {
                    document.body.appendChild(scrollBtn);
                    scrollBtn.classList.add('back-to-top');
                }
                document.querySelector('.back-to-top').style.display = 'flex';
                document.querySelector('.back-to-top').style.alignItems = 'center';
                document.querySelector('.back-to-top').style.justifyContent = 'center';
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

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;

                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Open all toggles on page load for better UX
        // window.addEventListener('load', () => {
        //     document.querySelectorAll('.toggle-section').forEach(section => {
        //         const content = section.querySelector('.toggle-content');
        //         const icon = section.querySelector('.toggle-icon');
        //         content.classList.add('active');
        //         icon.classList.add('rotated');
        //     });
        // });
    </script>
</body>

</html>
