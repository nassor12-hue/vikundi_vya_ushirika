<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Vikundi vya Ushirika</title>
    <style>
        /* Reset default margins and padding */
        *
         {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling with gray background as requested */
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5; /* Gray background */
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Header styling with contact information */
        header {
            background-color: #28a745;
            color: white;
            padding: 1rem 0;
        }

        .header-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .contact-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: white;
        }

        /* Navigation */
        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
            margin-top: 1rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255,255,255,0.2);
        }

        /* Main content area */
        main {
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* About section styling */
        .about-container {
            background: white;
            padding: 3rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .about-header h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
        }

        .about-content {
            font-size: 1.1rem;
            color: #555;
            text-align: justify;
            margin-bottom: 2rem;
        }

        .about-content p {
            margin-bottom: 1.5rem;
        }

        /* System goals section */
        .system-goals {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            margin: 2rem 0;
        }

        .system-goals h3 {
            color: #28a745;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        /* Features list */
        .features-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }

        .feature-item {
            background: #e9ecef;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }

        .feature-item h4 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        /* Footer styling */
        footer {
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: auto;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .contact-info {
                flex-direction: column;
                text-align: center;
            }

            .about-container {
                padding: 2rem 1rem;
            }

            .about-header h2 {
                font-size: 2rem;
            }

            .features-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header with contact information as requested -->

    <header>
        <div class="header-container">
            <div class="logo">
                <h1>Vikundi vya Ushirika</h1>
            </div>
            
            <!-- Contact information as specified -->

            <div class="contact-info">
                <div class="contact-item">
                    <strong>Phone:</strong> +255673976959 / +255620829434
                </div>
                <div class="contact-item">
                    <strong>Email:</strong> vikundi@gmail.com / nassornassir@71gmail.com
                </div>
            </div>
            
            <!-- Navigation links -->
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main content area -->
    
    <main>
        <div class="about-container">
            <!-- About header section -->
            
            <div class="about-header">
                <h2>About Our System</h2>
            </div>

            <!-- About content with system purpose -->
            <div class="about-content">
                <p>
                    Welcome to the Vikundi vya Ushirika Attendance Management System. Our platform is designed to revolutionize how organizations track attendance and manage their workforce efficiently.
                </p>
                
                <!-- System goals section with purpose as requested -->
                <div class="system-goals">
                    <h3>System Purpose</h3>
                    <p>
                        <strong>The goal of the system is to eliminate challenges that arise when people distribute harvests where everyone wants to be given more, but their attendance is poor.</strong> Therefore, the system will provide a solution to show attendance at work, ensuring fair distribution based on actual participation and contribution.
                    </p>
                </div>

                <p>
                    Our comprehensive attendance tracking system ensures transparency and accountability in the workplace. By maintaining accurate records of employee attendance, we help organizations make informed decisions about resource allocation, training opportunities, and performance evaluation.
                </p>

                <p>
                    The system features separate dashboards for administrators and employees, each tailored to their specific needs and responsibilities. Administrators have full control over user management, attendance tracking, and report generation, while employees can view their personal attendance records and training assignments.
                </p>
            </div>

            <!-- Key features section -->
            <div class="features-list">
                <div class="feature-item">
                    <h4>Admin Management</h4>
                    <p>Complete user management, attendance tracking, training assignments, and comprehensive reporting capabilities.</p>
                </div>
                
                <div class="feature-item">
                    <h4>Employee Portal</h4>
                    <p>Personal dashboard for viewing attendance records, training status, and accessing individual reports.</p>
                </div>
                
                <div class="feature-item">
                    <h4>Training Management</h4>
                    <p>Systematic assignment and tracking of training programs with automatic participant selection and progress monitoring.</p>
                </div>
                
                <div class="feature-item">
                    <h4>Reporting System</h4>
                    <p>Generate detailed reports on attendance patterns, training completion, and overall organizational performance.</p>
                </div>
            </div>

            <div class="about-content">
                <p>
                    Our system promotes fairness and transparency by providing clear visibility into attendance patterns and work contributions. This helps eliminate disputes and ensures that rewards and opportunities are distributed based on merit and actual participation.
                </p>
            </div>
        </div>
    </main>

    <!-- Footer with copyright as requested -->
    <footer>
        <p>&copy; 2025 Vikundi vya Ushirika Attendance System. All rights reserved.</p>
    </footer>
</body>
</html>
