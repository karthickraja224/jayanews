<?php
// includes/lang-strings.php
// Public-facing UI strings. Uses the same $_SESSION['lang'] convention
// ('en' / 'ta') as the admin panel's lang switcher, so it stays consistent
// if both apps ever share a session/domain.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['lang']) || !in_array($_SESSION['lang'], ['en', 'ta'])) {
    $_SESSION['lang'] = 'ta'; // default Tamil for the public site
}

$GLOBALS['_fe_lang'] = [
    'nav_home'        => ['en' => 'Home',        'ta' => 'முகப்பு'],
    'skip_content'    => ['en' => 'Skip to content', 'ta' => 'உள்ளடக்கத்திற்கு செல்ல'],
    'nav_categories'  => ['en' => 'Categories',  'ta' => 'பிரிவுகள்'],
    'breaking'        => ['en' => 'Breaking',    'ta' => 'முக்கிய செய்தி'],
    'live'            => ['en' => 'LIVE',        'ta' => 'நேரலை'],
    'search_ph'       => ['en' => 'Search news…','ta' => 'செய்தி தேடுங்கள்…'],
    'search_title'    => ['en' => 'Search results for', 'ta' => 'தேடல் முடிவுகள்'],
    'search_empty'    => ['en' => 'No stories matched your search.', 'ta' => 'உங்கள் தேடலுக்கு பொருந்தும் செய்திகள் இல்லை.'],
    'trending'        => ['en' => 'Trending Now', 'ta' => 'டிரெண்டிங்'],
    'editors_picks'   => ['en' => "Editor's Desk", 'ta' => 'ஆசிரியர் பக்கம்'],
    'latest'          => ['en' => 'Latest',       'ta' => 'சமீபத்திய செய்திகள்'],
    'view_all'        => ['en' => 'View all',     'ta' => 'அனைத்தையும் காண'],
    'read_more'       => ['en' => 'Read more',    'ta' => 'மேலும் படிக்க'],
    'min_read'        => ['en' => 'min read',     'ta' => 'நிமிட வாசிப்பு'],
    'views'           => ['en' => 'views',        'ta' => 'பார்வைகள்'],
    'by'              => ['en' => 'By',           'ta' => 'எழுதியவர்'],
    'in'              => ['en' => 'in',           'ta' => 'பிரிவு'],
    'tags'            => ['en' => 'Tags',         'ta' => 'குறிச்சொற்கள்'],
    'source'          => ['en' => 'Source',       'ta' => 'மூலம்'],
    'share'           => ['en' => 'Share',        'ta' => 'பகிரு'],
    'copy_link'       => ['en' => 'Copy link',    'ta' => 'இணைப்பை நகலெடு'],
    'related'         => ['en' => 'Related Stories', 'ta' => 'தொடர்புடைய செய்திகள்'],
    'comments'        => ['en' => 'Comments',     'ta' => 'கருத்துகள்'],
    'no_comments'     => ['en' => 'Be the first to share your thoughts.', 'ta' => 'முதலில் கருத்து தெரிவிக்கவும்.'],
    'leave_comment'   => ['en' => 'Leave a comment', 'ta' => 'கருத்து தெரிவிக்க'],
    'your_name'       => ['en' => 'Your name',    'ta' => 'உங்கள் பெயர்'],
    'your_email'      => ['en' => 'Your email (optional, not published)', 'ta' => 'மின்னஞ்சல் (வெளியிடப்படாது)'],
    'contact_email_ph'=> ['en' => 'Your email', 'ta' => 'உங்கள் மின்னஞ்சல்'],
    'your_comment'    => ['en' => 'Your comment', 'ta' => 'உங்கள் கருத்து'],
    'submit_comment'  => ['en' => 'Post comment', 'ta' => 'கருத்தை பதிவிடு'],
    'comment_pending' => ['en' => 'Thanks — your comment has been submitted and will appear after review.', 'ta' => 'நன்றி — உங்கள் கருத்து சரிபார்ப்புக்குப் பின் வெளியிடப்படும்.'],
    'comment_error'   => ['en' => 'Please fill in your name and comment.', 'ta' => 'பெயர் மற்றும் கருத்தை நிரப்பவும்.'],
    'follow_us'        => ['en' => 'Follow Us',    'ta' => 'எங்களை பின்தொடரவும்'],
    'whatsapp_channel'=> ['en' => 'Join our WhatsApp Channel', 'ta' => 'எங்கள் WhatsApp சேனலில் இணையுங்கள்'],
    'quick_links'     => ['en' => 'Quick Links',  'ta' => 'விரைவு இணைப்புகள்'],
    'about_us'        => ['en' => 'About Us',     'ta' => 'எங்களை பற்றி'],
    'contact_us'      => ['en' => 'Contact Us',   'ta' => 'தொடர்பு கொள்ள'],
    'privacy_policy'  => ['en' => 'Privacy Policy', 'ta' => 'தனியுரிமைக் கொள்கை'],
    'terms'           => ['en' => 'Terms & Conditions', 'ta' => 'விதிமுறைகள் மற்றும் நிபந்தனைகள்'],
    'all_rights'      => ['en' => 'All rights reserved.', 'ta' => 'அனைத்து உரிமைகளும் பாதுகாக்கப்பட்டவை.'],
    'get_in_touch'    => ['en' => 'Get in touch',  'ta' => 'எங்களை தொடர்பு கொள்ளுங்கள்'],
    'address'         => ['en' => 'Address',       'ta' => 'முகவரி'],
    'phone'           => ['en' => 'Phone',         'ta' => 'தொலைபேசி'],
    'email'           => ['en' => 'Email',         'ta' => 'மின்னஞ்சல்'],
    'send_message'    => ['en' => 'Send message',  'ta' => 'அனுப்பு'],
    'your_message'    => ['en' => 'Your message',  'ta' => 'உங்கள் செய்தி'],
    'subject'         => ['en' => 'Subject',       'ta' => 'தலைப்பு'],
    'message_sent'    => ['en' => "Thanks for reaching out — we'll get back to you soon.", 'ta' => 'நன்றி — நாங்கள் விரைவில் தொடர்பு கொள்வோம்.'],
    'message_error'   => ['en' => 'Please fill in all required fields.', 'ta' => 'அனைத்து விவரங்களையும் நிரப்பவும்.'],
    'page_not_found'  => ['en' => 'Page not found', 'ta' => 'பக்கம் கிடைக்கவில்லை'],
    'not_found_msg'   => ['en' => "The story or page you're looking for has moved or no longer exists.", 'ta' => 'நீங்கள் தேடும் செய்தி அல்லது பக்கம் கிடைக்கவில்லை.'],
    'back_home'       => ['en' => 'Back to homepage', 'ta' => 'முகப்புக்கு திரும்ப'],
    'load_more'       => ['en' => 'Load more',     'ta' => 'மேலும் காண்க'],
    'prev'            => ['en' => 'Prev',          'ta' => 'முந்தைய'],
    'next'            => ['en' => 'Next',          'ta' => 'அடுத்தது'],
    'page_of'         => ['en' => 'Page %d of %d', 'ta' => 'பக்கம் %d / %d'],
    'no_news_yet'     => ['en' => 'No stories published in this category yet.', 'ta' => 'இந்த பிரிவில் இன்னும் செய்திகள் இல்லை.'],
    'just_now'        => ['en' => 'Just now',      'ta' => 'சற்றுமுன்'],
    'min_ago'         => ['en' => '%d min ago',    'ta' => '%d நிமிடம் முன்பு'],
    'hr_ago'          => ['en' => '%d hr ago',     'ta' => '%d மணி நேரம் முன்பு'],
    'days_ago'        => ['en' => '%d days ago',   'ta' => '%d நாட்கள் முன்பு'],
    'wire'            => ['en' => 'JP WIRE',       'ta' => 'JP WIRE'],
    'tagline_fallback'=> ['en' => 'Tamil Nadu’s fast, fair, fearless newsroom.', 'ta' => 'தமிழகத்தின் வேகமான, நம்பகமான செய்தி தளம்.'],
];

function t($key) {
    $lang = $_SESSION['lang'] ?? 'ta';
    return $GLOBALS['_fe_lang'][$key][$lang] ?? $GLOBALS['_fe_lang'][$key]['en'] ?? $key;
}

function currentLang() {
    return $_SESSION['lang'] ?? 'ta';
}
