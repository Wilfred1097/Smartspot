SmartSpot is a parking management system built to enhance efficiency and accuracy in parking operations. It integrates YOLO for real-time vehicle detection and a web-based application for managing parking spaces and staff. Below is a comprehensive overview of the project, including its features and security measures:

Key Features:
1. Vehicle Detection:
Utilizes YOLO (You Only Look Once) for real-time vehicle detection and tracking.

2. Web Application:
A user-friendly web app for managing parking operations.
Supports parking staff management and real-time vehicle monitoring.

3. Customizable Parking Lot Layout:
Allows customization of parking lot coordinates for better visualization.

4. Real-Time Updates:
Offers live updates on vacant and occupied parking spaces.

5. Streamlined Operations:
Simplifies parking operations for administrators.
Ensures accurate and efficient tracking of parking spaces.

Security Measures:

1. Email Security with PHPMailer:
The project relies on PHPMailer for sending OTPs (One-Time Passwords) to users.
Secure SMTP configurations such as TLS are used for encrypted communication.
Emails include confidentiality notices, advising users not to share their OTPs.

2. Vulnerability Protections in PHPMailer:
PHPMailer mitigates various vulnerabilities, including:
Remote Code Execution (RCE) risks through lang_path.
Header injection in email attachments.
URL scheme filtering to block malicious inputs, like phar:// paths.
Past vulnerabilities have been addressed in updates, ensuring safer operations.

3. OTP Confidentiality:
OTPs are securely generated and sent, emphasizing non-disclosure for user protection.

4. Dependency Security:
Dependencies are managed using Composer, ensuring updated and verified packages.
Encourages periodic updates to avoid vulnerabilities in third-party libraries.

5. Best Practices:
Secure file operations prevent shell command injections.
Proper input validation and sanitization across operations.
Namespaced PHP classes prevent unintended code execution.

6. Proactive Measures:
Developers are encouraged to disclose vulnerabilities responsibly.
The repository currently has no active security alerts, indicating compliance with secure coding practices.

SmartSpot is a robust, secure, and efficient solution for modern parking management challenges. It combines advanced machine learning techniques with strong security practices to deliver a reliable and user-friendly experience.
