# OpportunityHub

**Team 08 — CSE 3522, Database Management Systems Laboratory**
Zobaer Ahmed (0112230327) · Wahidul Alam Rahi (0112410111) · Anjan Kundu (0112410500)

A database-driven web platform combining **Bangladesh Jobs + Bangladesh Internships + Global Scholarships**.

---

## Setup Instructions (XAMPP)

### 1. Copy the project
Place the `opportunityhub` folder inside your XAMPP htdocs directory:

```
C:\xampp\htdocs\opportunityhub\
```

The folder name **must be `opportunityhub`** (this matches `BASE_URL` in `config/database.php`).
If you rename it, update `BASE_URL` in that file to match.

### 2. Start XAMPP
Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Import the database
1. Open http://localhost/phpmyadmin
2. Click the **Import** tab (do not create a database first — the SQL file creates it).
3. Choose file: `opportunityhub/database/opportunityhub.sql`
4. Click **Go**. This creates the `opportunityhub` database with all tables and sample data.

> **If you already have an `opportunityhub` database from the earlier version of this
> project**, `opportunityhub.sql` will drop those tables and rebuild everything from
> scratch — any existing data is lost. To keep your existing users and scholarships
> instead, select the `opportunityhub` database in phpMyAdmin and import
> `database/jobs_module_upgrade.sql`, which only adds the new jobs/internships tables.
> Import one file or the other, never both.

#### If you get "Table 'opportunityhub.postings' doesn't exist"
The import stopped partway. phpMyAdmin aborts at the first error, and the usual cause is that
the database already existed, so `CREATE TABLE users` failed with "table already exists" and
everything after it was skipped. Re-import `database/opportunityhub.sql` — it now drops the
old tables first, so it always runs to completion.

### 4. Run the one-time setup script
Open http://localhost/opportunityhub/setup.php

This creates the admin account and sets the demo employer passwords. Delete `setup.php` afterwards.

### 5. Open the site
http://localhost/opportunityhub/

---

## Login Credentials

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@opportunityhub.com | admin123 |
| Employer (approved org) | hr@bracbank.com | employer123 |
| Employer (approved org) | hr@brainstation23.com | employer123 |
| Employer (pending org) | hr@pathao.com | employer123 |
| University (approved) | admissions@oxford.ac.uk | university123 |
| University (approved) | scholarships@harvard.edu | university123 |
| Student | register your own at `pages/register.php` | — |

---

## Folder Structure

```
opportunityhub/
├── index.php                      Redirects to pages/index.php
├── setup.php                      One-time admin/demo account setup (delete after use)
├── README.md
├── config/
│   └── database.php               DB credentials, BASE_URL, session start
├── database/
│   ├── opportunityhub.sql         Full schema + sample data (drops and rebuilds)
│   └── jobs_module_upgrade.sql    Adds only the jobs module to an existing database
├── includes/
│   ├── functions.php              All backend logic (auth, postings, orgs, applications)
│   ├── header.php                 Shared navbar
│   ├── footer.php                 Shared footer
│   ├── admin-nav.php              Admin section navigation
│   ├── employer-nav.php           Employer section navigation
│   └── posting-list.php           Shared jobs/internships listing + filters
├── css/style.css
├── js/script.js
├── uploads/
│   └── resumes/                   Uploaded CVs (script execution blocked via .htaccess)
└── pages/
    ├── index.php  login.php  register.php  logout.php  dashboard.php  profile.php
    ├── jobs.php  internships.php  posting-details.php
    ├── apply-job.php  my-job-applications.php
    ├── save-posting.php  saved-opportunities.php
    ├── scholarships.php  scholarship-details.php  save-scholarship.php
    ├── saved-scholarships.php  applications.php  apply.php
    ├── employer/
    │   ├── dashboard.php  organization.php
    │   ├── post-opportunity.php  edit-posting.php  posting-action.php
    │   ├── my-postings.php  posting-form-fields.php
    │   ├── applications.php  update-application-status.php
    └── admin/
        ├── dashboard.php
        ├── manage-users.php  user-action.php  toggle-user-status.php
        ├── manage-organizations.php  organization-action.php
        ├── manage-postings.php  edit-posting.php  posting-action.php
        ├── expired-postings.php  manage-job-applications.php
        ├── manage-categories.php
        └── manage-scholarships.php  add-scholarship.php  edit-scholarship.php
            delete-scholarship.php  scholarship-form-fields.php
```

---

## Database Schema

| Table | Purpose |
|-------|---------|
| `users` | All accounts: student, graduate, employer, admin |
| `organizations` | Employer company profiles with Pending/Approved/Rejected/Suspended status |
| `categories` | Shared job/internship categories |
| `postings` | Jobs **and** internships (`posting_type` distinguishes them) |
| `job_applications` | Student applications to postings, with status workflow |
| `saved_postings` | Bookmarked jobs/internships |
| `scholarships` | Global scholarships |
| `saved_scholarships` | Bookmarked scholarships |
| `applications` | Scholarship application tracking |

All foreign keys use `ON DELETE CASCADE` or `SET NULL`. Indexes are on lookup columns
(`posting_type`, `posting_status`, `application_deadline`, `location`) plus a FULLTEXT
index for search.

---

## Key Behaviours

**Automatic expiry.** `application_deadline` is the single source of truth. `sweepExpiredPostings()`
runs on every browse/detail/dashboard load and flips any Active/Pending posting whose deadline has
passed to `Expired`. Expired postings disappear from student browsing, reject new applications
(enforced server-side in `apply-job.php`, not just in the UI), and remain visible to the owning
employer and to admin. Extending the deadline on an expired posting revives it.

**Organization approval.** A new employer's organization starts as `Pending`. Its postings are
saved as `Pending` and are not publicly visible until an admin approves the organization.
Once approved, that employer's new postings publish directly as `Active`.

**Application workflow.** `Applied → Under Review → Shortlisted → Interview → Accepted / Rejected`.
Only the organization owning the posting can change a status.

**Security.**
- All queries use prepared statements.
- Passwords hashed with bcrypt (`password_hash`).
- Session-based auth with `requireLogin()`, `requireAdmin()`, `requireEmployer()`.
- CSRF tokens on every state-changing POST form.
- Output escaped through `e()`.
- IDs from GET/POST are never trusted: `postingBelongsToOrganization()` and
  `getOrgApplicationById()` verify ownership before any read or write, so
  `applications.php?posting_id=123` for another organization's posting is rejected.
- Resume uploads validated by extension, size (2MB), and real MIME type via `finfo`,
  stored under a randomised filename, with script execution blocked by `uploads/.htaccess`.

---

## Testing the Expiry Rule

The sample data includes **"QA Engineer (Expired Demo)"** (Brain Station 23) with a deadline
10 days in the past. After import it will not appear on `jobs.php`, will show as `Expired`,
and visiting its detail page shows the expired notice with the Apply button disabled.
It appears in Admin → Expired Posts.
