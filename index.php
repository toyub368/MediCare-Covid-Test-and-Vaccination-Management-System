<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCare Pro | Advanced COVID-19 Healthcare Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Professional Medical Color Palette */
            --primary-blue: #1e40af;
            --primary-blue-light: #3b82f6;
            --primary-blue-dark: #1e3a8a;
            --secondary-teal: #0891b2;
            --secondary-teal-light: #06b6d4;
            --accent-emerald: #059669;
            --accent-emerald-light: #10b981;
            --warning-amber: #d97706;
            --error-red: #dc2626;
            
            /* Neutral Professional Palette */
            --neutral-50: #f8fafc;
            --neutral-100: #f1f5f9;
            --neutral-200: #e2e8f0;
            --neutral-300: #cbd5e1;
            --neutral-400: #94a3b8;
            --neutral-500: #64748b;
            --neutral-600: #475569;
            --neutral-700: #334155;
            --neutral-800: #1e293b;
            --neutral-900: #0f172a;
            
            /* Glass Effects */
            --glass-bg: rgba(255, 255, 255, 0.95);
            --glass-border: rgba(255, 255, 255, 0.2);
            --glass-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            
            /* Gradients */
            --gradient-primary: linear-gradient(135deg, var(--primary-blue) 0%, var(--secondary-teal) 100%);
            --gradient-secondary: linear-gradient(135deg, var(--accent-emerald) 0%, var(--secondary-teal) 100%);
            --gradient-hero: linear-gradient(135deg, var(--neutral-50) 0%, rgba(59, 130, 246, 0.05) 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--neutral-800);
            background: var(--neutral-50);
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        /* Premium Background Effects */
        .hero-bg {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: var(--gradient-hero);
            z-index: -2;
        }

        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(8, 145, 178, 0.1) 0%, transparent 50%);
            animation: float-bg 20s ease-in-out infinite;
        }

        @keyframes float-bg {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(1deg); }
        }

        /* Professional Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--neutral-200);
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            padding: 0.5rem 0;
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.98) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-blue) !important;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
        }

        .navbar-brand i {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-right: 12px;
            font-size: 2rem;
        }

        .nav-link {
            color: var(--neutral-700) !important;
            font-weight: 500;
            margin: 0 8px;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            opacity: 1;
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            padding-top: 100px;
            overflow: hidden;
        }

        .hero-content {
            z-index: 2;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(30, 64, 175, 0.1);
            color: var(--primary-blue);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 2rem;
            border: 1px solid rgba(30, 64, 175, 0.2);
            animation: fade-in-up 0.8s ease-out;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: 1.5rem;
            line-height: 1.1;
            animation: fade-in-up 0.8s ease-out 0.2s both;
        }

        .hero-title .highlight {
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--neutral-600);
            margin-bottom: 2.5rem;
            line-height: 1.6;
            max-width: 600px;
            animation: fade-in-up 0.8s ease-out 0.4s both;
        }

        .hero-cta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fade-in-up 0.8s ease-out 0.6s both;
        }

        .btn-primary-custom {
            background: var(--gradient-primary);
            border: none;
            padding: 16px 32px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            color: white;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.3);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .btn-primary-custom::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary-custom:hover::before {
            left: 100%;
        }

        .btn-primary-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.4);
            color: white;
        }

        .btn-secondary-custom {
            background: transparent;
            border: 2px solid var(--neutral-300);
            padding: 14px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            color: var(--neutral-700);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-secondary-custom:hover {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
        }

        .hero-visual {
            position: relative;
            animation: fade-in-right 1s ease-out 0.8s both;
        }

        .hero-image {
            width: 100%;
            max-width: 500px;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transition: transform 0.3s ease;
        }

        .hero-image:hover {
            transform: translateY(-10px);
        }

        .floating-card {
            position: absolute;
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--neutral-200);
            animation: float 6s ease-in-out infinite;
        }

        .floating-card-1 {
            top: 20%;
            right: -10%;
            animation-delay: 0s;
        }

        .floating-card-2 {
            bottom: 20%;
            left: -10%;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Trust Indicators */
        .trust-section {
            padding: 60px 0;
            background: white;
            border-top: 1px solid var(--neutral-200);
            border-bottom: 1px solid var(--neutral-200);
        }

        .trust-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--neutral-50);
            border-radius: 12px;
            border: 1px solid var(--neutral-200);
            transition: all 0.3s ease;
            height: 100%;
        }

        .trust-badge:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .trust-badge i {
            font-size: 2rem;
            color: var(--accent-emerald);
            margin-right: 12px;
        }

        .trust-text {
            font-weight: 600;
            color: var(--neutral-700);
            font-size: 0.9rem;
        }

        /* Stats Section */
        .stats-section {
            padding: 100px 0;
            background: var(--gradient-hero);
            position: relative;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--neutral-200);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .stat-card:hover::before {
            transform: scaleX(1);
        }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: rgba(30, 64, 175, 0.3);
        }

        .stat-number {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            display: block;
            font-family: 'Inter', sans-serif;
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--neutral-600);
            font-weight: 500;
        }

        .stat-icon {
            font-size: 2.5rem;
            color: var(--secondary-teal);
            margin-bottom: 1rem;
        }

        /* Services Section */
        .services-section {
            padding: 120px 0;
            background: white;
        }

        .section-header {
            text-align: center;
            margin-bottom: 5rem;
        }

        .section-badge {
            display: inline-block;
            background: rgba(8, 145, 178, 0.1);
            color: var(--secondary-teal);
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 1rem;
            border: 1px solid rgba(8, 145, 178, 0.2);
        }

        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            font-weight: 700;
            color: var(--neutral-900);
            margin-bottom: 1.5rem;
            line-height: 1.2;
        }

        .section-subtitle {
            font-size: 1.2rem;
            color: var(--neutral-600);
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .service-card {
            background: white;
            border: 1px solid var(--neutral-200);
            border-radius: 20px;
            padding: 40px 30px;
            text-align: center;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .service-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
            transition: left 0.6s ease;
        }

        .service-card:hover::before {
            left: 100%;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: rgba(30, 64, 175, 0.3);
        }

        .service-icon {
            width: 80px;
            height: 80px;
            background: var(--gradient-primary);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            transition: all 0.3s ease;
        }

        .service-icon i {
            font-size: 2rem;
            color: white;
        }

        .service-card:hover .service-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .service-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--neutral-900);
        }

        .service-description {
            color: var(--neutral-600);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .service-features {
            list-style: none;
            padding: 0;
            text-align: left;
        }

        .service-features li {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: var(--neutral-600);
            font-size: 0.9rem;
        }

        .service-features li i {
            color: var(--accent-emerald);
            margin-right: 8px;
            font-size: 0.8rem;
        }

        /* Portal Section */
        .portal-section {
            padding: 120px 0;
            background: var(--gradient-hero);
            position: relative;
        }

        .portal-card {
            background: white;
            border: 1px solid var(--neutral-200);
            border-radius: 20px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
            overflow: hidden;
            position: relative;
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .portal-card:hover::before {
            transform: scaleX(1);
        }

        .portal-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: rgba(30, 64, 175, 0.3);
        }

        .portal-header {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid var(--neutral-200);
        }

        .portal-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }

        .portal-icon.admin {
            background: linear-gradient(135deg, #dc2626, #ef4444);
        }

        .portal-icon.hospital {
            background: var(--gradient-primary);
        }

        .portal-icon.patient {
            background: var(--gradient-secondary);
        }

        .portal-icon i {
            font-size: 2rem;
            color: white;
        }

        .portal-card:hover .portal-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .portal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--neutral-900);
        }

        .portal-subtitle {
            color: var(--neutral-600);
            font-size: 0.9rem;
        }

        .portal-body {
            padding: 30px;
        }

        .portal-description {
            color: var(--neutral-600);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .portal-features {
            list-style: none;
            padding: 0;
            margin-bottom: 2rem;
        }

        .portal-features li {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            color: var(--neutral-600);
            font-size: 0.9rem;
        }

        .portal-features li i {
            color: var(--accent-emerald);
            margin-right: 10px;
            font-size: 0.8rem;
        }

        .btn-portal {
            width: 100%;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }

        .btn-portal-primary {
            background: var(--gradient-primary);
            color: white;
            border: none;
        }

        .btn-portal-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px -3px rgba(30, 64, 175, 0.3);
            color: white;
        }

        .btn-portal-secondary {
            background: transparent;
            color: var(--neutral-700);
            border: 2px solid var(--neutral-300);
        }

        .btn-portal-secondary:hover {
            background: var(--neutral-100);
            border-color: var(--neutral-400);
            color: var(--neutral-800);
        }

        .btn-portal-link {
            background: transparent;
            color: var(--primary-blue);
            border: none;
            font-size: 0.9rem;
            padding: 8px 0;
        }

        .btn-portal-link:hover {
            color: var(--primary-blue-dark);
            text-decoration: underline;
        }

        /* Contact Section */
        .contact-section {
            padding: 120px 0;
            background: white;
        }

        .contact-card {
            background: white;
            border: 1px solid var(--neutral-200);
            border-radius: 20px;
            padding: 40px;
            height: 100%;
            transition: all 0.3s ease;
        }

        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding: 20px;
            background: var(--neutral-50);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            background: rgba(30, 64, 175, 0.05);
            transform: translateX(5px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .contact-icon i {
            color: white;
            font-size: 1.2rem;
        }

        .contact-info h6 {
            font-weight: 600;
            color: var(--neutral-900);
            margin-bottom: 0.5rem;
        }

        .contact-info p {
            color: var(--neutral-600);
            margin: 0;
            line-height: 1.5;
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .map-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15);
        }

        /* Footer */
        .footer {
            background: var(--neutral-900);
            color: var(--neutral-300);
            padding: 60px 0 30px;
        }

        .footer-content {
            border-bottom: 1px solid var(--neutral-800);
            padding-bottom: 3rem;
            margin-bottom: 2rem;
        }

        .footer-brand {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
        }

        .footer-description {
            color: var(--neutral-400);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .footer-links {
            list-style: none;
            padding: 0;
        }

        .footer-links li {
            margin-bottom: 0.5rem;
        }

        .footer-links a {
            color: var(--neutral-400);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-blue-light);
        }

        .footer-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
        }

        .social-link {
            width: 40px;
            height: 40px;
            background: var(--neutral-800);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--neutral-400);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-blue);
            color: white;
            transform: translateY(-3px);
        }

        .footer-bottom {
            text-align: center;
            color: var(--neutral-500);
            font-size: 0.9rem;
        }

        /* Animations */
        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fade-in-right {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .section-title {
                font-size: 2.2rem;
            }
            
            .hero-cta {
                flex-direction: column;
            }
            
            .btn-primary-custom,
            .btn-secondary-custom {
                width: 100%;
                justify-content: center;
            }
            
            .floating-card {
                display: none;
            }
            
            .navbar-nav {
                background: white;
                border-radius: 12px;
                margin-top: 1rem;
                padding: 1rem;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }
        }

        /* Loading Animation */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 3px solid var(--neutral-200);
            border-top: 3px solid var(--primary-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--neutral-100);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gradient-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue-dark);
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="#home">
                <i class="fas fa-shield-virus"></i>
                MediCare Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#portal">Portal Access</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-bg"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <div class="hero-badge">
                            <i class="fas fa-certificate me-2"></i>
                            WHO Certified Healthcare Platform
                        </div>
                        <h1 class="hero-title">
                            Advanced <span class="highlight">COVID-19</span> Healthcare Management System
                        </h1>
                        <p class="hero-subtitle">
                            Experience the future of healthcare with our comprehensive COVID-19 testing, vaccination, and health management platform. Trusted by healthcare professionals worldwide for its reliability, security, and innovation.
                        </p>
                        <div class="hero-cta">
                            <a href="#portal" class="btn-primary-custom">
                                <i class="fas fa-calendar-check me-2"></i>
                                Schedule Appointment
                            </a>
                            <a href="#services" class="btn-secondary-custom">
                                <i class="fas fa-play me-2"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-visual">
                        <img src="https://images.pexels.com/photos/4386466/pexels-photo-4386466.jpeg?auto=compress&cs=tinysrgb&w=800" alt="Healthcare Professional" class="hero-image">
                        <div class="floating-card floating-card-1">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px;">
                                    <i class="fas fa-vial"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">RT-PCR Testing</h6>
                                    <small class="text-muted">99.8% Accuracy</small>
                                </div>
                            </div>
                        </div>
                        <div class="floating-card floating-card-2">
                            <div class="d-flex align-items-center">
                                <div class="service-icon me-3" style="width: 50px; height: 50px; background: var(--gradient-secondary);">
                                    <i class="fas fa-syringe"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Vaccination</h6>
                                    <small class="text-muted">All Approved Vaccines</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Trust Indicators -->
    <section class="trust-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="trust-badge">
                        <i class="fas fa-certificate"></i>
                        <div class="trust-text">WHO Certified</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-badge">
                        <i class="fas fa-shield-alt"></i>
                        <div class="trust-text">HIPAA Compliant</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-badge">
                        <i class="fas fa-award"></i>
                        <div class="trust-text">ISO 27001 Certified</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="trust-badge">
                        <i class="fas fa-clock"></i>
                        <div class="trust-text">24/7 Available</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="stat-card animate-on-scroll">
                        <i class="fas fa-vial stat-icon"></i>
                        <span class="stat-number" data-count="50000">0</span>
                        <div class="stat-label">Tests Completed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-on-scroll">
                        <i class="fas fa-syringe stat-icon"></i>
                        <span class="stat-number" data-count="25000">0</span>
                        <div class="stat-label">Vaccinations Administered</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-on-scroll">
                        <i class="fas fa-hospital stat-icon"></i>
                        <span class="stat-number" data-count="150">0</span>
                        <div class="stat-label">Partner Healthcare Centers</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card animate-on-scroll">
                        <i class="fas fa-users stat-icon"></i>
                        <span class="stat-number">99.9%</span>
                        <div class="stat-label">Patient Satisfaction</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <div class="section-badge">Our Services</div>
                <h2 class="section-title">Comprehensive Healthcare Solutions</h2>
                <p class="section-subtitle">
                    We provide world-class COVID-19 healthcare services with cutting-edge technology, ensuring patient safety, data security, and seamless healthcare management for individuals and institutions.
                </p>
            </div>
            
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="fas fa-vial"></i>
                        </div>
                        <h4 class="service-title">Advanced Testing Services</h4>
                        <p class="service-description">
                            State-of-the-art RT-PCR, Rapid Antigen, and Antibody testing with laboratory-grade accuracy and rapid results.
                        </p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> RT-PCR Testing (99.8% accuracy)</li>
                            <li><i class="fas fa-check"></i> Rapid Antigen Tests (15 minutes)</li>
                            <li><i class="fas fa-check"></i> Antibody Testing</li>
                            <li><i class="fas fa-check"></i> Digital Result Delivery</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="fas fa-syringe"></i>
                        </div>
                        <h4 class="service-title">Vaccination Programs</h4>
                        <p class="service-description">
                            Complete vaccination services including initial doses, boosters, and updated formulations from authorized manufacturers.
                        </p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> All Approved Vaccines</li>
                            <li><i class="fas fa-check"></i> Booster Shots</li>
                            <li><i class="fas fa-check"></i> Updated Formulations</li>
                            <li><i class="fas fa-check"></i> Digital Certificates</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <h4 class="service-title">Digital Health Records</h4>
                        <p class="service-description">
                            Secure, HIPAA-compliant digital health certificates and records accessible through our encrypted platform.
                        </p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> HIPAA Compliant Storage</li>
                            <li><i class="fas fa-check"></i> Digital Certificates</li>
                            <li><i class="fas fa-check"></i> 24/7 Access</li>
                            <li><i class="fas fa-check"></i> Secure Sharing</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-6 col-md-6">
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4 class="service-title">Smart Scheduling System</h4>
                        <p class="service-description">
                            AI-powered appointment scheduling with real-time availability, automatic reminders, and flexible rescheduling options.
                        </p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> Real-time Availability</li>
                            <li><i class="fas fa-check"></i> Automatic Reminders</li>
                            <li><i class="fas fa-check"></i> Flexible Rescheduling</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="service-card animate-on-scroll">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h4 class="service-title">Enterprise Security</h4>
                        <p class="service-description">
                            Bank-level encryption, multi-factor authentication, and full compliance with healthcare data protection regulations.
                        </p>
                        <ul class="service-features">
                            <li><i class="fas fa-check"></i> End-to-End Encryption</li>
                            <li><i class="fas fa-check"></i> Multi-Factor Authentication</li>
                            <li><i class="fas fa-check"></i> Compliance Monitoring</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Section -->
    <section id="portal" class="portal-section">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <div class="section-badge">Portal Access</div>
                <h2 class="section-title">Healthcare Portal Access</h2>
                <p class="section-subtitle">
                    Secure access to our comprehensive healthcare management system. Choose your role to access specialized tools and features designed for your specific healthcare needs.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="portal-card animate-on-scroll">
                        <div class="portal-header">
                            <div class="portal-icon admin">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h5 class="portal-title">System Administrator</h5>
                            <p class="portal-subtitle">Complete system oversight and management</p>
                        </div>
                        <div class="portal-body">
                            <p class="portal-description">
                                Comprehensive system management with advanced analytics, operational oversight, and administrative controls for healthcare facilities.
                            </p>
                            <ul class="portal-features">
                                <li><i class="fas fa-check"></i> System Analytics Dashboard</li>
                                <li><i class="fas fa-check"></i> User Management</li>
                                <li><i class="fas fa-check"></i> Operational Reports</li>
                                <li><i class="fas fa-check"></i> Security Monitoring</li>
                                <li><i class="fas fa-check"></i> Compliance Tracking</li>
                            </ul>
                            <a href="auth/login.php?role=admin" class="btn-portal btn-portal-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Admin Access
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="portal-card animate-on-scroll">
                        <div class="portal-header">
                            <div class="portal-icon hospital">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h5 class="portal-title">Healthcare Provider</h5>
                            <p class="portal-subtitle">Professional healthcare management tools</p>
                        </div>
                        <div class="portal-body">
                            <p class="portal-description">
                                Advanced tools for healthcare professionals to manage patient care, test results, vaccination records, and appointment scheduling.
                            </p>
                            <ul class="portal-features">
                                <li><i class="fas fa-check"></i> Patient Management</li>
                                <li><i class="fas fa-check"></i> Test Result Management</li>
                                <li><i class="fas fa-check"></i> Vaccination Records</li>
                                <li><i class="fas fa-check"></i> Appointment Scheduling</li>
                                <li><i class="fas fa-check"></i> Clinical Reports</li>
                            </ul>
                            <a href="auth/login.php?role=hospital" class="btn-portal btn-portal-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Provider Login
                            </a>
                            <a href="auth/register.php?role=hospital" class="btn-portal btn-portal-link">
                                Register Healthcare Facility
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="portal-card animate-on-scroll">
                        <div class="portal-header">
                            <div class="portal-icon patient">
                                <i class="fas fa-user-injured"></i>
                            </div>
                            <h5 class="portal-title">Patient Portal</h5>
                            <p class="portal-subtitle">Personal health management platform</p>
                        </div>
                        <div class="portal-body">
                            <p class="portal-description">
                                Comprehensive personal health management with appointment booking, test results, vaccination records.
                            </p>
                            <ul class="portal-features">
                                <li><i class="fas fa-check"></i> Online Appointment Booking</li>
                                <li><i class="fas fa-check"></i> Test Results Access</li>
                                <li><i class="fas fa-check"></i> Vaccination Records</li>
                                <li><i class="fas fa-check"></i> Digital Health Certificates</li>
                                <li><i class="fas fa-check"></i> Health History Tracking</li>
                            </ul>
                            <a href="auth/login.php?role=patient" class="btn-portal btn-portal-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Patient Login
                            </a>
                            <a href="auth/register.php?role=patient" class="btn-portal btn-portal-link">
                                Create Patient Account
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="container">
            <div class="section-header animate-on-scroll">
                <div class="section-badge">Contact Us</div>
                <h2 class="section-title">Professional Support Center</h2>
                <p class="section-subtitle">
                    Our dedicated healthcare support team is available 24/7 to assist with technical issues, appointment scheduling, and general inquiries. We're here to help you every step of the way.
                </p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="contact-card animate-on-scroll">
                        <h4 class="mb-4">Get In Touch</h4>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="contact-info">
                                <h6>Headquarters</h6>
                                <p>Medical Technology Center<br>123 Healthcare Boulevard, Suite 500<br>Metropolitan Health District, State 12345</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="contact-info">
                                <h6>Emergency Hotline</h6>
                                <p>+1 (555) HEALTH-1<br>+1 (555) 432-584-1</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="contact-info">
                                <h6>Professional Support</h6>
                                <p>support@medicareprocare.com<br>technical@medicareprocare.com</p>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="contact-info">
                                <h6>Service Hours</h6>
                                <p>Emergency Services: 24/7 Available<br>General Support: Mon-Fri 6AM-10PM<br>Weekend Support: 8AM-6PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="map-container animate-on-scroll">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3151.8354345093747!2d144.9537353153167!3d-37.8162792797517!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6ad65d43f0f3f5d1%3A0x5045675218ce840!2sMelbourne%20VIC%2C%20Australia!5e0!3m2!1sen!2sus!4v1234567890123" 
                            width="100%" 
                            height="500" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="row g-4">
                    <div class="col-lg-4">
                        <div class="footer-brand">MediCare Pro</div>
                        <p class="footer-description">
                            Leading the future of healthcare technology with comprehensive COVID-19 management solutions. Trusted by healthcare professionals worldwide for reliability, security, and innovation.
                        </p>
                        <div class="social-links">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h6 class="footer-title">Services</h6>
                        <ul class="footer-links">
                            <li><a href="#services">COVID Testing</a></li>
                            <li><a href="#services">Vaccination</a></li>
                            <li><a href="#services">Health Records</a></li>
                            <li><a href="#services">Scheduling</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h6 class="footer-title">Portal Access</h6>
                        <ul class="footer-links">
                            <li><a href="#portal">Admin Portal</a></li>
                            <li><a href="#portal">Healthcare Provider</a></li>
                            <li><a href="#portal">Patient Portal</a></li>
                            <li><a href="#portal">Registration</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h6 class="footer-title">Support</h6>
                        <ul class="footer-links">
                            <li><a href="#contact">Contact Us</a></li>
                            <li><a href="#">Help Center</a></li>
                            <li><a href="#">Documentation</a></li>
                            <li><a href="#">System Status</a></li>
                        </ul>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <h6 class="footer-title">Legal</h6>
                        <ul class="footer-links">
                            <li><a href="#">Privacy Policy</a></li>
                            <li><a href="#">Terms of Service</a></li>
                            <li><a href="#">HIPAA Compliance</a></li>
                            <li><a href="#">Security</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 MediCare Pro Healthcare System. All rights reserved. | Professional healthcare technology solutions | HIPAA Compliant | ISO 27001 Certified</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Professional JavaScript for enhanced functionality
        document.addEventListener('DOMContentLoaded', function() {
            
            // Hide loading overlay
            setTimeout(() => {
                const loadingOverlay = document.getElementById('loadingOverlay');
                loadingOverlay.style.opacity = '0';
                setTimeout(() => {
                    loadingOverlay.style.display = 'none';
                }, 500);
            }, 1000);

            // Enhanced navbar scroll effect
            let lastScrollTop = 0;
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                
                if (scrollTop > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
                
                // Hide/show navbar on scroll
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    navbar.style.transform = 'translateY(-100%)';
                } else {
                    navbar.style.transform = 'translateY(0)';
                }
                lastScrollTop = scrollTop;
            });

            // Smooth scrolling for navigation links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        const offsetTop = target.offsetTop - 80;
                        window.scrollTo({
                            top: offsetTop,
                            behavior: 'smooth'
                        });
                    }
                });
            });

            // Active navigation highlighting
            window.addEventListener('scroll', function() {
                let current = '';
                const sections = document.querySelectorAll('section');
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop - 100;
                    const sectionHeight = section.clientHeight;
                    if (pageYOffset >= sectionTop && pageYOffset < sectionTop + sectionHeight) {
                        current = section.getAttribute('id');
                    }
                });

                document.querySelectorAll('.nav-link').forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === '#' + current) {
                        link.classList.add('active');
                    }
                });
            });

            // Enhanced counter animation
            function animateCounters() {
                const counters = document.querySelectorAll('[data-count]');
                
                counters.forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-count'));
                    const duration = 2500;
                    const increment = target / (duration / 16);
                    let current = 0;
                    
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        
                        if (target >= 1000) {
                            counter.textContent = Math.floor(current).toLocaleString() + '+';
                        } else {
                            counter.textContent = Math.floor(current);
                        }
                    }, 16);
                });
            }

            // Enhanced Intersection Observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        // Trigger counter animation when stats section is visible
                        if (entry.target.closest('.stats-section') && !entry.target.classList.contains('counted')) {
                            entry.target.classList.add('counted');
                            setTimeout(animateCounters, 200);
                        }
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Enhanced button interactions
            document.querySelectorAll('.btn-primary-custom, .btn-secondary-custom, .btn-portal').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
                
                // Ripple effect
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.width = ripple.style.height = size + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    ripple.classList.add('ripple');
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });

            // Parallax effect for hero section
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const parallax = document.querySelector('.hero-bg');
                if (parallax) {
                    const speed = scrolled * 0.5;
                    parallax.style.transform = `translateY(${speed}px)`;
                }
            });

            // Form validation enhancement (if forms are added)
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!form.checkValidity()) {
                        e.preventDefault();
                        e.stopPropagation();
                    }
                    form.classList.add('was-validated');
                });
            });

            // Keyboard navigation support
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Tab') {
                    document.body.classList.add('keyboard-navigation');
                }
            });

            document.addEventListener('mousedown', function() {
                document.body.classList.remove('keyboard-navigation');
            });

            // Performance optimization: Lazy load images
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });

        // Additional ripple effect styles
        const style = document.createElement('style');
        style.textContent = `
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.6);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }

            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }

            .keyboard-navigation *:focus {
                outline: 2px solid var(--primary-blue) !important;
                outline-offset: 2px;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>