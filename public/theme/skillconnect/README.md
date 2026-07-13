# SkillConnect Moodle Theme

This package is a complete **Moodle 5.1+** theme based on Boost. It recreates the supplied SkillConnect design, including:

- Responsive header and mobile menu
- Hero banner
- Three impact statistic cards
- Three featured-program cards
- Volunteer call-to-action banner
- Four-column footer and newsletter form
- Matching login and registration styling
- Matching internal Moodle pages, course pages, dashboard and administration pages
- Admin-editable content, images, colours, links, statistics and contact details
- English and Bangla administration labels

## Correct folder

Copy the folder named `skillconnect` to:

`C:\xampp\htdocs\moodle\public\theme\skillconnect`

The final path must contain:

`C:\xampp\htdocs\moodle\public\theme\skillconnect\version.php`

Do not create an extra nested folder such as `skillconnect\skillconnect\version.php`.

## Installation

1. Stop Apache in XAMPP.
2. Back up or rename the existing `C:\xampp\htdocs\moodle\public\theme\skillconnect` folder.
3. Copy this package's `skillconnect` folder into `C:\xampp\htdocs\moodle\public\theme\`.
4. Start Apache and MariaDB.
5. Open Moodle as administrator. Moodle will show the plugin upgrade page.
6. Complete the upgrade.
7. Go to **Site administration → Appearance → Themes → Theme selector**.
8. Select **SkillConnect** as the default theme.
9. Purge caches: **Site administration → Development → Purge caches → Purge all caches**.

## Editing without changing code

Go to:

**Site administration → Appearance → Themes → SkillConnect**

From there you can edit the logo, pink colour, hero image, volunteer image, all home-page text, statistics, program links, contact details, social links and custom SCSS.

## Main source files

- `templates/header.mustache` — header and navigation
- `templates/frontpage.mustache` — home-page sections
- `templates/footer.mustache` — footer
- `templates/login.mustache` — login/registration wrapper
- `templates/general.mustache` — all normal Moodle pages
- `scss/skillconnect.scss` — complete visual styling
- `layout/*.php` — passes Moodle output into templates
- `settings.php` — admin-editable theme settings
- `lib.php` — SCSS, images, links and template context
- `pix/hero.jpg` — default hero artwork cropped from the supplied reference image
- `pix/volunteers.jpg` — default volunteer artwork cropped from the supplied reference image

## Links

Default links are safe Moodle links:

- Programs: home-page featured-program section
- Courses: Moodle course list
- Volunteer: Moodle account signup page
- Contact: footer contact section

Change them from the theme settings page when your separate Programs, Volunteer or Contact pages are ready.

## Newsletter form

The newsletter box is included visually. Set **Newsletter form action URL** to your real form-processing endpoint. Until then its default action is `#`.

## Troubleshooting

- If Moodle shows a coding error, verify that the site is Moodle 5.1 or newer.
- If styling does not change, purge all Moodle caches and hard-refresh the browser with `Ctrl + F5`.
- If an image does not update, upload it again in the SkillConnect theme settings and purge caches.
- Never edit Moodle core files or the Boost theme. Only edit files inside `theme\skillconnect`.
