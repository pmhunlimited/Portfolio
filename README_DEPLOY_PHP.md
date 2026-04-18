# PHP Deployment Guide: Cyber-Pulse Architecture

This directory contains the 100% Vanilla PHP + MySQL conversion of the Cyber-Pulse Portfolio Engine.

## Requirements
- **PHP 8.0+**
- **MySQL / MariaDB**
- **Apache/Nginx** with `.htaccess` support for clean URLs (optional).

## Setup Instructions

1. **Database Setup**:
   - Create a database in your MySQL server (e.g., `cyber_pulse_portfolio`).
   - Import the `database.sql` file provided in this directory.

2. **Configuration**:
   - Edit `db.php` and update the `$host`, `$db`, `$user`, and `$pass` variables to match your server credentials.

3. **Media & Assets**:
   - Place all your project media (images/videos) in an `assets/` folder in the root directory.
   - For database storage, ensure you save the full URL or relative path (e.g., `assets/project1.mp4`).

4. **Security Tuning**:
   - In `admin.php`, search for `ELITE_ACCESS_2024` and replace it with a secure hashed password check for deployment.
   - For production, move the Gemini API Key into a secure environment variable or a local `config.php` file not accessible via the browser.

5. **Cleaning URLs (Optional)**:
   Add this `.htaccess` to your root directory for exact parity with the React `slug` system:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^project/([^/]+)$ project.php?slug=$1 [L,QSA]
   ```

## Feature Mapping
- **Rich Media**: Handled by the `render_media()` function in `functions.php`.
- **AI Integration**: Implemented via the `generate_project_pitch()` function using PHP cURL.
- **UI/UX**: Preserved using the exact Tailwind classes and layout structure from the React build.

---
*Generated for Philmore Host Portfolio Migration*
