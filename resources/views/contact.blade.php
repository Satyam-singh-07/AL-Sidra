<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | AL-SIDRA</title>
    <link rel="icon" href="{{ asset('assets/logo.PNG') }}" type="image/x-icon">
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
            color: var(--text-dark);
            background: white;
        }

        .container {
            max-width: 900px;
            margin: auto;
            padding: 0 20px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            padding: 60px 0;
            text-align: center;
            color: white;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: 700;
            text-decoration: none;
            color: white;
        }

        .subtitle {
            margin-top: 10px;
            opacity: 0.9;
        }

        /* Section */
        .section {
            padding: 60px 0;
        }

        /* Contact Info Cards */
        .contact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 50px;
        }

        .contact-card {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            text-align: center;
            border-top: 4px solid var(--accent-gold);
        }

        .contact-card h3 {
            color: var(--primary-green);
            margin-bottom: 10px;
        }

        /* Form */
        .form-card {
            background: white;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--primary-green);
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-family: inherit;
            transition: var(--transition);
        }

        input:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-green);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            display: inline-block;
            padding: 14px 30px;
            border-radius: var(--border-radius);
            border: none;
            cursor: pointer;
            font-weight: 600;
            background: linear-gradient(135deg, var(--primary-green), #2a6d46);
            color: white;
            transition: var(--transition);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(26, 77, 46, 0.3);
        }

        /* FAQ */
        .toggle-section {
            margin-top: 40px;
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            cursor: pointer;
        }

        .toggle-header {
            display: flex;
            justify-content: space-between;
            font-weight: 600;
            color: var(--primary-green);
        }

        .toggle-content {
            margin-top: 15px;
            display: none;
            color: var(--text-light);
        }

        .toggle-content.active {
            display: block;
        }

        /* Footer */
        .footer {
            background: var(--primary-green);
            color: rgba(255, 255, 255, 0.8);
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer a {
            color: white;
            text-decoration: none;
        }

        .copyright {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
        }

        @media(max-width:768px) {
            .logo {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>

    <header class="header">
        <div class="container">
            <a href="{{ route('home') }}" class="logo">AL-SIDRA</a>
            <p class="subtitle">We're here to support our community</p>
        </div>
    </header>

    <main class="section">
        <div class="container">

            <!-- Contact Info -->
            <div class="contact-grid">
                <div class="contact-card">
                    <h3>Email</h3>
                    <p>alsidrawelfaresociety@gmail.com</p>
                </div>

                <div class="contact-card">
                    <h3>Phone</h3>
                    <p>+91 79077 58695</p>
                </div>

                <div class="contact-card">
                    <h3>Office Hours</h3>
                    <p>Mon – Fri, 9AM – 6PM</p>
                </div>
            </div>

            <!-- Contact Form -->
            {{-- <div class="form-card">
                <h2 style="color: var(--primary-green); margin-bottom: 25px;">Send Us a Message</h2>

                <form method="POST" action="{{ route('contact.submit') }}">
                    @csrf

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Subject</label>
                        <input type="text" name="subject" required>
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" required></textarea>
                    </div>

                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div> --}}

            <!-- FAQ -->
            {{-- <div class="toggle-section" onclick="toggleFaq()">
                <div class="toggle-header">
                    <span>❓ Frequently Asked Question</span>
                    <span>▼</span>
                </div>
                <div class="toggle-content" id="faqContent">
                    We usually respond within 24 hours. For urgent issues, email security@alsidra.com.
                </div>
            </div> --}}

        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            {{-- <div class="footer-content">
                <a href="{{ route('home') }}" class="footer-logo">AL-SIDRA</a>
                <div class="footer-links">
                    <a href="{{ route('terms-conditions') }}">Terms & Conditions</a>
                    <a href="{{ route('privacy-policy') }}">Privacy Policy</a>
                    <a href="#">Cookie Policy</a>
                    <a href="#">Contact Us</a>
                </div>
            </div> --}}
            <div class="copyright">
                <p>&copy; 2024 AL-SIDRA. Protecting Community Trust Through Technology.</p>
                <p style="margin-top: 10px; font-size: 0.85rem;">Trust • Transparency • Protection</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleFaq() {
            document.getElementById('faqContent').classList.toggle('active');
        }
    </script>

</body>

</html>
