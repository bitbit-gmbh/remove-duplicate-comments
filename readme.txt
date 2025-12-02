=== Remove Duplicate Comments ===
Contributors: bitbit
Donate link: https://www.bitbit.de
Tags: comments, duplicate, cleanup, maintenance, tools
Requires at least: 6.7
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 8.1
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

Finds duplicate comments based on identical content and moves older copies to the trash. Beginner-friendly with live progress display.

== Description ==

Duplicate comments can accumulate in WordPress databases over time due to spam, user errors, or plugin conflicts. This plugin provides a simple and safe solution to identify comments with identical content and move older duplicates to the trash, keeping the newest version.

**Key Features:**

* **Easy to Use**: Simple interface under Tools menu
* **Safe Processing**: Batch processing (100 comments at a time) prevents server timeouts
* **Live Progress**: Real-time display of processed comments and trashed duplicates
* **Flexible Filtering**: Choose which comment statuses to check (Approved, Pending, Spam, Trash)
* **Safe Cleanup**: Moves duplicates to the trash so you can review them before permanent deletion
* **Beginner Friendly**: Clear backup warning and intuitive interface
* **Internationalization Ready**: Fully translatable with German translation included

**Important Note**

**Always backup your database before using this plugin!** The plugin moves duplicate comments to the trash so you can review or restore them before permanent deletion.

**How It Works**

* Duplicates are identified by exact match of comment content WITHIN THE SAME POST
* The newest comment (highest comment_ID) is kept
* Older duplicates are moved to the WordPress trash so you can restore or delete them later
* Processing happens in batches for reliability
* Duplicates are only detected when they occur on the same post/page

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/remove-duplicate-comments/` directory, or install directly through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to Tools > Remove Duplicate Comments to access the plugin interface
4. **Important**: Create a complete database backup before proceeding
5. Select which comment statuses you want to check for duplicates (Approved is pre-selected)
6. Click 'Find & Move Duplicates to Trash' button
7. Monitor the live progress display showing processed comments and trashed duplicates
8. Wait for completion message confirming the operation finished successfully

**Minimum Requirements**

* WordPress 6.7 or higher
* PHP 8.1 or higher
* Administrator access (manage_options capability)

== Frequently Asked Questions ==

= What happens to my comments when I run this plugin? =

The plugin scans your selected comment statuses for duplicates based on identical content. It keeps the newest comment (highest ID) and moves older duplicates to the WordPress trash so you can review them before deciding on permanent deletion.

= How does the plugin identify duplicates? =

Comments are considered duplicates if they have exactly the same content (comment_content field) AND appear on the same post. The comment author, date, or other metadata doesn't matter - only the text content and the post ID are compared. This ensures that the same comment text on different posts is NOT considered a duplicate.

= Which comment is kept when duplicates are found? =

The newest comment (the one with the highest comment_ID) is always kept. All older duplicates are moved to the trash.

= Can I restore the trashed comments? =

Yes. Duplicate comments are moved to the standard WordPress trash. You can restore them or permanently delete them later from the Comments screen.

= Does the plugin work with large databases? =

Yes! The plugin uses batch processing (100 comments per batch) to prevent server timeouts and memory issues. The live progress display shows you exactly what's happening.

= What comment statuses can I check? =

You can select any combination of: Approved, Pending, Spam, and Trash. By default, Approved comments are pre-selected.

= Will this slow down my website? =

No, the plugin only runs when you manually trigger it from the admin interface. It doesn't affect your website's frontend performance at all.

= Is the plugin compatible with multisite? =

The plugin works on individual sites within a multisite network. Each site's comments are processed separately.

== Screenshots ==

1. Admin interface showing backup warning, status checkboxes, and start button
2. Live progress display with processed/trashed counters and progress bar
3. Success message after completion showing total results
4. Plugin menu location under WordPress Tools menu

== Changelog ==

= 1.1.0 =
* Initial release
* Find and remove duplicate comments based on identical content
* Batch processing with live progress display
* Support for Approved, Pending, Spam, and Trash comment statuses
* Internationalization support with German translation
* WordPress 6.7+ and PHP 8.1+ compatibility
* Apache 2.0 license

== Upgrade Notice ==

= 1.1.0 =
Initial release. Please backup your database before using this plugin.

== Support ==

For support inquiries, please visit https://www.bitbit.de or use the WordPress.org support forums.

== Privacy Policy ==

This plugin does not collect, store, or transmit any user data. All processing happens locally on your WordPress installation.