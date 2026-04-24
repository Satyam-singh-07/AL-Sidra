<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Donate - Support AL-SIDRA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="{{ asset('assets/logo.PNG') }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root {
            --primary-green: #1a4d2e;
            --accent-gold: #c5a059;
            --light-bg: #f8f9fa;
            --text-dark: #333;
            --text-light: #6c757d;
            --border-radius: 16px;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            color: white;
            background-color: var(--primary-green);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow-x: hidden;
        }

        /* Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="M50 0L61.2 38.8H100L70.6 61.2L81.8 100L50 77.6L18.2 100L29.4 61.2L0 38.8H38.8L50 0Z" fill="white" opacity="0.04"/></svg>');
            background-size: 80px 80px;
            z-index: -1;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
            flex: 1;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--accent-gold);
            font-size: 2.5rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
        }

        .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .hindi-text {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 20px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            font-size: 0.95rem;
            text-align: center;
            border-left: 4px solid var(--accent-gold);
        }

        .donation-card {
            background-color: rgba(255, 255, 255, 0.06);
            border-radius: 24px;
            padding: 30px;
            border: 1px solid rgba(197, 160, 89, 0.2);
            box-shadow: var(--shadow);
        }

        .form-group {
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: rgba(255, 255, 255, 0.9);
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-gold);
            font-size: 1.2rem;
        }

        input[type="number"] {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background-color: rgba(255, 255, 255, 0.05);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            outline: none;
            transition: var(--transition);
        }

        input[type="number"]:focus {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .donation-types {
            display: flex;
            gap: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 12px;
        }

        .type-option {
            flex: 1;
            position: relative;
        }

        .type-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .type-label {
            display: block;
            padding: 10px;
            text-align: center;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .type-option input:checked + .type-label {
            background-color: var(--accent-gold);
            color: var(--primary-green);
            font-weight: 700;
        }

        .preset-amounts {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .preset-btn {
            padding: 10px 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            color: white;
        }

        .preset-btn:hover, .preset-btn.active {
            background-color: var(--accent-gold);
            color: var(--primary-green);
        }

        .donate-btn {
            width: 100%;
            padding: 18px;
            background-color: var(--accent-gold);
            color: var(--primary-green);
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 20px;
        }

        .donate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(197, 160, 89, 0.3);
        }

        .footer-note {
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.4);
            font-size: 0.8rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 576px) {
            .donation-card {
                padding: 20px;
            }
            h1 {
                font-size: 1.6rem;
            }
        }
        
        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<body>
    <a href="{{ route('home') }}" class="back-home">
        <i class="fas fa-arrow-left"></i> Home
    </a>

    <div class="container">
        <div class="header">
            <div class="icon-circle">
                <i class="fas fa-heart"></i>
            </div>
            <h1>Support AL-SIDRA</h1>
            <p class="subtitle">Your contribution helps us provide better services to the community.</p>
        </div>

        <div class="hindi-text">
            <p>🌅 सुबह का सदक़ा: पूरा दिन हर बला और बीमारी से हिफ़ाज़त।</p>
            <p style="margin-top: 10px; font-size: 0.85rem; opacity: 0.8;">रक़म अहम नहीं, आपकी नीयत और पाबंदी अहम है! आप चाहें तो सिर्फ़ ₹1 का सदक़ा करें, लेकिन रोज़ करें ताकि आपका नाम अल्लाह के नेक बंदों में शुमार हो। अल सिदरा का मक़सद बड़ा फंड जमा करना नहीं, बल्कि आपको "रोज़ाना सदक़ा" की बरकत से जोड़ना है।</p>
        </div>

        <div class="donation-card">
            <div class="form-group">
                <label>Enter Amount (INR)</label>
                <div class="input-wrapper">
                    <i class="fas fa-rupee-sign input-icon"></i>
                    <input type="number" id="amount" placeholder="0.00" min="1" step="any">
                </div>
                <div class="preset-amounts">
                    <button class="preset-btn" data-amount="5">₹5</button>
                    <button class="preset-btn" data-amount="10">₹10</button>
                    <button class="preset-btn" data-amount="50">₹50</button>
                    <button class="preset-btn" data-amount="100">₹100</button>
                </div>
            </div>

            <div class="form-group">
                <label>Donation Type</label>
                <div class="donation-types">
                    <div class="type-option">
                        <input type="radio" name="donation_type" id="type_sidra" value="support al sidra" checked>
                        <label for="type_sidra" class="type-label">Support Al Sidra</label>
                    </div>
                    <div class="type-option">
                        <input type="radio" name="donation_type" id="type_nafila" value="sadka e nafila">
                        <label for="type_nafila" class="type-label">Sadka e Nafila</label>
                    </div>
                </div>
            </div>

            <button type="button" class="donate-btn" id="donateBtn">Donate Now</button>
        </div>

        <p class="footer-note">Secure Payment Powered by Razorpay</p>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const amountInput = document.getElementById('amount');
            const presetBtns = document.querySelectorAll('.preset-btn');
            const donateBtn = document.getElementById('donateBtn');

            // Handle preset amounts
            presetBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const amount = this.getAttribute('data-amount');
                    amountInput.value = amount;
                    
                    presetBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Handle manual input
            amountInput.addEventListener('input', function() {
                presetBtns.forEach(btn => {
                    if (btn.getAttribute('data-amount') === this.value) {
                        btn.classList.add('active');
                    } else {
                        btn.classList.remove('active');
                    }
                });
            });

            // Handle Donation
            donateBtn.addEventListener('click', async function() {
                const amount = amountInput.value.trim();
                const donationType = document.querySelector('input[name="donation_type"]:checked').value;

                if (!amount || isNaN(amount) || parseFloat(amount) <= 0) {
                    alert('Please enter a valid amount');
                    return;
                }

                donateBtn.disabled = true;
                donateBtn.innerText = 'Processing...';

                try {
                    // 1. Create Order
                    const response = await fetch("{{ route('donation.create-order') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            amount: amount,
                            donation_type: donationType
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.message || 'Could not create order');
                    }

                    // 2. Open Razorpay Checkout
                    const options = {
                        "key": data.key,
                        "amount": data.amount,
                        "currency": "INR",
                        "name": "Al Sidra Donation",
                        "description": "Support our mission - " + donationType,
                        "image": "{{ asset('assets/logo.PNG') }}",
                        "order_id": data.order_id,
                        "handler": async function (response){
                            // 3. Verify Payment
                            const verifyRes = await fetch("{{ route('donation.verify') }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    razorpay_order_id: response.razorpay_order_id,
                                    razorpay_payment_id: response.razorpay_payment_id,
                                    razorpay_signature: response.razorpay_signature
                                })
                            });

                            const verifyData = await verifyRes.json();
                            if (verifyData.success) {
                                alert('Thank you for your donation!');
                                location.reload();
                            } else {
                                alert('Payment verification failed. Please contact support.');
                            }
                        },
                        "prefill": {
                            "name": "",
                            "email": "",
                            "contact": ""
                        },
                        "theme": {
                            "color": "#1a4d2e"
                        },
                        "modal": {
                            "ondismiss": function(){
                                donateBtn.disabled = false;
                                donateBtn.innerText = 'Donate Now';
                            }
                        }
                    };

                    const rzp = new Razorpay(options);
                    rzp.open();

                } catch (error) {
                    console.error(error);
                    alert('Error: ' + error.message);
                    donateBtn.disabled = false;
                    donateBtn.innerText = 'Donate Now';
                }
            });
        });
    </script>
</body>
</html>
