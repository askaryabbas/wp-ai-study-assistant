=== WP AI Study Assistant ===
Contributors: askaryabbas
Requires at least: 6.0
Tested up to: 6.8.2
Stable tag: 1.0.0
Requires PHP: 8.4
License: GPLv2 or later
Tags: blocks, editor, ai, openai, study, accordion, flashcards, seo

== Description ==

**WP AI Study Assistant** is a modern WordPress plugin that brings the power of AI directly into your editor and frontend.

It's designed for students, educators, bloggers, and anyone who wants to quickly generate study materials or SEO content. With this plugin you can:

- **AI Q&A Accordion Block** – Paste in source text inside the block editor and instantly generate up to 5 question/answer pairs. They are rendered as a clean, accessible accordion.
- **AI Flashcards Shortcode** – Add `[wpai_flashcards_3d]` to any page or post and turn your AI-generated questions into interactive 3D flip cards for study and review.
- **AI Meta Description Panel** – From the editor sidebar, click a button to generate a concise SEO-friendly meta description, automatically inserted into the post excerpt.

Behind the scenes, the plugin demonstrates best practices for WordPress development: Settings API, REST API with proper capability checks and nonces, transients for caching, i18n, Gutenberg block development, and integration with JavaScript via `apiFetch`.

== Features ==

- Gutenberg block: **AI Q&A Accordion**
- Shortcode: **AI Flashcards** (`[wpai_flashcards_3d]`)
- Editor sidebar: **AI Meta Description**
- Settings page for provider, model, API key, and cache TTL
- Secure REST endpoints with capability checks and nonces
- Standards-compliant code following WordPress Coding Standards
- Internationalization ready

== Installation ==

1. Upload the plugin to your WordPress installation and activate it.
2. Go to **Settings → WP AI Assistant** and enter your OpenAI API key and preferred model.
3. In the block editor:
   - Insert the **AI Q&A Accordion** block, paste your source text, and click *Generate*.
   - Use the **AI Meta Description** panel in the editor sidebar to generate SEO descriptions.
4. On the frontend, add `[wpai_flashcards_3d]` shortcode to generate interactive flashcards.

== Security & Standards ==

- REST API endpoints require `edit_posts` capability.
- Nonces are enforced via `wp.apiFetch` and `wp_localize_script`.
- Inputs are sanitized, and outputs are properly escaped.
- Code is written to be PHPCS/WPCS-friendly.

== Frequently Asked Questions ==

= Do I need an OpenAI API key? =
Yes. By default the plugin integrates with OpenAI. Enter your key in the settings screen.

= Can I change the AI provider? =
Yes. The code uses a provider adapter pattern, so it can be extended for other AI providers.

= Who is this plugin for? =
It's designed as both a practical assistant (study aid, SEO helper) and as a clean code sample for demonstrating how to integrate AI with WordPress.

== Changelog ==

= 1.0.0 =
* Initial release with AI Q&A Accordion block, AI Flashcards shortcode, and AI Meta Description panel.
