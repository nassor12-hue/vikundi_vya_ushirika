<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vikundi vya Ushirika - Attendance System</title>
    <style>
        /* Reset default margins and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling with white background as requested */
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            line-height: 1.6;
        }

        /* Header styling with green background as requested */
        header {
            background-color: #28a745; /* Green color */
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .feature-card {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-10px) scale(1.05) rotate(1deg);
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}


        /* Navigation container */
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        /* Logo and title styling */
        .logo h1 {
            font-size: 1.8rem;
            font-weight: bold;
        }

        /* Navigation links styling */
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        /* Main content area */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Hero section styling */
        .hero {
            text-align: center;
            margin-bottom: 3rem;
        }

        .hero h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .hero p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Features section */
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .feature-card {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .feature-card h3 {
            color: #28a745;
            margin-bottom: 1rem;
        }

        /* Call to action buttons */
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 3rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: transform 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        /* Footer styling */
        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .hero h2 {
                font-size: 2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header section with green background as requested -->
    <header>

        <div class="nav-container">
            <!-- Logo and system title -->
            <div class="logo">
                <h1>Vikundi vya Ushirika</h1>
            </div>
            
            <!-- Navigation links - About and Login as requested -->
            <nav>
                <ul class="nav-links">
                    <li><a href="about.php">About</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
        
    </header>

    <!-- Main content area with white background -->
    <main>
        <!-- Hero section introducing the system -->
        <section class="hero">

    </marquee direction="left"> <h2>Welcome to Our Attendance Management System</h2> </marquee>
            <p>Streamline your organization's attendance tracking and training management with our comprehensive solution.</p>
        
        </section>

        <!-- Features section highlighting system capabilities -->
        <section class="features">

            <div class="feature-card">
                <h3>Admin Dashboard</h3>
                <p>Complete control over user management, attendance tracking, and training assignments. Generate comprehensive reports and manage all system operations.</p>
            </div>
            
            <div class="feature-card">
                <h3>Employee Portal</h3>
                <p>Employees can view their attendance records, training assignments, and access personalized reports through their dedicated dashboard.</p>
            </div>
            
            <div class="feature-card">
                <h3>Training Management</h3>
                <p>Efficiently manage training programs, assign participants, and track completion status with our integrated training module.</p>
            </div>

        </section>

        <!-- Call to action buttons -->
        <section class="cta-buttons">

            <a href="about.php" class="btn btn-secondary">Learn More</a>
            <a href="login.php" class="btn btn-primary">Get Started</a>
            
        </section>
    </main>

    <!-- Footer section -->
    <footer>
        <p>&copy; 2025 Vikundi vya Ushirika Attendance System. All rights reserved.</p>
    </footer>
</body>
</html>
