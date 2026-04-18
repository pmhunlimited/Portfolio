# Technical Blueprint: Cyber-Pulse Portfolio Engine

This document outlines the architecture of the Cyber-Pulse Portfolio Engine (React/Firebase) and provides a 100% feature-parity roadmap for converting it to a **Vanilla PHP + MySQL** environment.

---

## 1. Application Architecture (Current)

### Frontend (React + Vite)
- **Framework**: React 18 with TypeScript.
- **Styling**: Tailwind CSS 4.0 with customized @theme variables (`sharp-orange`, `glossy-purple`).
- **Icons**: Lucide-react for all interface iconography.
- **Animation**: `motion` (motion/react) for grid entry and transitions.
- **Rendering**: client-side SPA (Single Page Application).

### Backend & Persistence (Firebase)
- **Database**: Firestore (NoSQL).
- **Schema**:
    - `/settings/global`: UI configuration (titles, hero text).
    - `/projects/{id}`: Deep data objects including tech stacks and media arrays.
- **Security**: Granular Firestore Rules (ABAC) restricting write access to `philmorehost@gmail.com`.

### AI Integration Layer
- **Service**: `geminiService.ts`
- **Model**: `gemini-1.5-flash`
- **Logic**: Analyzes external websites by fetching HTML tags and produces structured JSON for project descriptions.

---

## 2. PHP Migration Strategy (Vanilla 100%)

To maintain the exact UI/UX, you must replace React components with PHP includes and migrate NoSQL data to a Relational Database.

### Database Schema (MySQL)

```sql
-- Tables for 1:1 Parity
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    content TEXT,
    site_url VARCHAR(500),
    thumbnail_url VARCHAR(500),
    project_type ENUM('web', 'app') DEFAULT 'web',
    wa_message TEXT,
    speed INT DEFAULT 98,
    security INT DEFAULT 100,
    inquiries_count INT DEFAULT 0,
    is_pinned BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE project_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    media_url VARCHAR(500),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);

CREATE TABLE tech_stacks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    name VARCHAR(100),
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

### Authentication Migration
- **Current**: Google Popup Auth.
- **PHP**: Implement a standard `session_start()` based login. Use `password_hash()` for the admin password stored in a `.env` or config file.

### Media Engine (PHP Edition)
- Replace `MediaRenderer.tsx` with a PHP logic block:
```php
<?php
function render_media($url, $class) {
    $ext = pathinfo($url, PATHINFO_EXTENSION);
    if (in_array($ext, ['mp4', 'webm', 'mov'])) {
        return "<video src='$url' class='$class' autoplay loop muted playsinline></video>";
    }
    return "<img src='$url' class='$class' referrerpolicy='no-referrer' loading='lazy'>";
}
?>
```

---

## 3. UI/UX Preservation Guide

To keep the design **untouched**, do not rewrite the CSS. Use the compiled `index.css` from the React project:
1. Copy all `@tailwindcss` utility classes.
2. Maintain the `glass`, `glass-purple`, and `text-glow-orange` utilities in your global `styles.css`.
3. Use HTML structures that mirror the JSX in `Home.tsx` and `ProjectDetail.tsx`.

---

## 4. Feature Parity Checklist
| Feature | React Implementation | PHP Equivalent |
| :--- | :--- | :--- |
| Dynamic Filtering | JS `filter()` on State | SQL `WHERE project_type = ?` |
| AI Content Gen | Gemini SDK (Frontend) | Gemini API (cURL in PHP) |
| WhatsApp Payload | Template Strings | `urlencode()` in PHP |
| In-line Delete | Firebase `deleteDoc` | `DELETE FROM projects` via PDO |
