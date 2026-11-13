"""Illustration data for the round 2 service pages. tools/build_art.py turns this into JSON for build_art.js.

Per service: hero = (kicker, title, sub, [6 ticked items], side title, [4 x (label, note, status)], [2 callouts])
             report = (kicker, title, [3 column heads], [4 x 3 cells], footnote, side title, [3 side items])
status: ok | wait | gold. Everything is example data and each scene carries an "Example" badge.
"""
SCENES = {
    'monthly-seo': dict(
        hero=('Monthly SEO plan', 'This month\'s work', 'SEO Growth · 1 month', ['Technical fixes', '5 pages improved', '2 articles written', 'Guest post placed', '15 listings added', 'Positions checked'],
              'Task list', [('Fix broken links', 'week 1', 'ok'), ('Rewrite service pages', 'week 2', 'ok'), ('Publish article', 'week 3', 'ok'), ('Build links', 'week 4', 'wait')], ['A month of work, listed', 'Ticked off as it is done']),
        report=('Monthly report', 'What was done', ['Task', 'Where', 'Status'], [('Broken links fixed', '12 pages', 'Done'), ('Titles rewritten', '5 pages', 'Done'), ('Article published', '/blog/…', 'Done'), ('Guest post', 'live link', 'Done')],
                '…one row for every task in the plan', 'Next month', ['2 more service pages', 'Article on pricing', 'Recheck positions'])),
    'off-page-seo': dict(
        hero=('Link building', 'Your links', '20 Profile & Citation Links', ['Business listings', 'Profile links', 'Same name and phone', 'Written descriptions', 'Built by hand', 'Live-link report'],
              'Placements', [('Directory listing', 'live link', 'ok'), ('Business profile', 'live link', 'ok'), ('Guest post · DR 30+', 'live link', 'ok'), ('Trade directory', 'submitted', 'wait')], ['Real sites, placed by hand', 'Every link listed']),
        report=('Link report', 'Every placement, with its address', ['Site', 'Live link', 'Status'], [('Yell', 'yell.com/biz/…', 'Live'), ('Bing Places', 'bing.com/maps/…', 'Live'), ('Guest post · DR 32', 'example-site.co.uk/…', 'Live'), ('Trade directory', 'checking', 'Pending')],
                '…one row for every link in the package', 'Included', ['Free replacement if not live', 'DR recorded on the day', 'Sponsored labels where required'])),
    'seo-audit': dict(
        hero=('SEO audit', 'Fix these first', 'Full SEO Audit', ['Can Google read it?', 'Phone speed', 'Titles and content', 'Links compared', 'Ranked by impact', 'Fix for each issue'],
              'Findings', [('Service pages blocked', 'high', 'gold'), ('Slow images on phones', 'high', 'gold'), ('Duplicate page titles', 'medium', 'wait'), ('Missing image text', 'low', 'wait')], ['Ranked high, medium, low', 'The fix beside each one']),
        report=('Audit report', 'Top issues and their fixes', ['Issue', 'Pages', 'Priority'], [('Pages blocked from Google', '6', 'High'), ('Images too large', '14', 'High'), ('Titles repeated', '9', 'Medium'), ('No image descriptions', '31', 'Low')],
                '…full list in the report, each with its fix', 'Start here', ['Unblock the 6 pages', 'Compress the images', 'Rewrite 9 titles'])),
    'technical-seo': dict(
        hero=('Technical SEO', 'Fixes made', 'Technical Fix Bundle', ['Backup taken', 'Redirects repaired', 'Sitemap corrected', 'Images compressed', 'Schema added', 'Re-checked after'],
              'Before and after', [('Home page load', '4.8s → 1.9s', 'ok'), ('Broken links', '23 → 0', 'ok'), ('Pages indexed', '41 → 58', 'ok'), ('Schema errors', '7 → 0', 'ok')], ['Backup first, always', 'Before and after, measured']),
        report=('Change log', 'What changed, and where', ['Change', 'Before', 'After'], [('Image sizes', '3.2 MB', '410 KB'), ('Redirect chains', '11', '0'), ('Blocked pages', '6', '0'), ('LocalBusiness schema', 'none', 'valid')],
                '…one row for every change made', 'Kept safe', ['Full backup stored', 'Changes logged with dates', 'Search Console re-checked'])),
    'on-page-seo': dict(
        hero=('On-page SEO', 'One page, one search', 'On-Page SEO · 5 Pages', ['Search term chosen', 'Title rewritten', 'Description rewritten', 'Headings restructured', 'Images described', 'Internal links added'],
              'Page title', [('Before', 'Home | Smith & Co', 'wait'), ('After', 'Boiler Repair in Leeds | Smith & Co', 'ok'), ('Target search', 'boiler repair leeds', 'gold'), ('Links added', '3 related pages', 'ok')], ['Old and new, side by side', 'A search term for every page']),
        report=('Page notes', 'Old and new for each page', ['Page', 'Target search', 'Status'], [('/boiler-repair/', 'boiler repair leeds', 'Updated'), ('/boiler-service/', 'boiler service cost', 'Updated'), ('/emergency/', 'emergency plumber leeds', 'Updated'), ('/about/', 'brand name', 'Updated')],
                '…one row for every page in the package', 'On each page', ['Title and description', 'Headings and first paragraph', 'Links to related pages'])),
    'ecommerce-seo': dict(
        hero=('Ecommerce SEO', 'Category page', 'Store SEO Setup', ['Category intro text', 'Product titles', 'Original descriptions', 'Product schema', 'Filter pages tidied', 'Image descriptions'],
              'Product schema', [('Price', 'labelled', 'ok'), ('In stock', 'labelled', 'ok'), ('Reviews', 'labelled', 'ok'), ('Duplicate variants', 'excluded', 'ok')], ['Pages shoppers land on', 'Details Google can show']),
        report=('Shop report', 'Every page we touched', ['Page', 'Target search', 'Status'], [('Walking boots', 'waterproof walking boots', 'Updated'), ('Trail shoes', 'trail running shoes', 'Updated'), ('Boot · product', 'model name + size', 'Updated'), ('Sock · product', 'merino hiking socks', 'Updated')],
                '…one row per product or category', 'Checked', ['Schema passes Google\'s test', 'No supplier copy left', 'Filters kept out of Google'])),
    'ai-seo': dict(
        hero=('AI SEO', 'What AI tools say about you', 'AI Search Visibility Check', ['Real customer questions', 'ChatGPT checked', 'Gemini checked', 'Google AI answers', 'Errors recorded', 'Fixes ranked'],
              'Question asked', [('ChatGPT', 'not mentioned', 'wait'), ('Gemini', 'mentioned, wrong town', 'gold'), ('Google AI answer', 'mentioned', 'ok'), ('After fixes', 're-checked', 'ok')], ['Asked like a customer would', 'Answers recorded with dates']),
        report=('Visibility check', 'Questions and answers', ['Question', 'Tool', 'Result'], [('Best plumber in Leeds?', 'ChatGPT', 'Not mentioned'), ('Who fixes boilers near me?', 'Gemini', 'Wrong town'), ('Boiler repair Leeds', 'Google AI', 'Mentioned'), ('Is Smith & Co any good?', 'ChatGPT', 'Outdated')],
                '…each answer saved with its date', 'First fixes', ['One clear business description', 'Organisation schema', 'Match details across profiles'])),
    'white-label-seo': dict(
        hero=('White label SEO', 'Your agency, our work', 'White Label SEO · Starter', ['Unbranded reports', 'Editable files', 'No client contact', 'Fixed cost per client', 'One order per client', 'Questions through you'],
              'Your clients', [('Client A · monthly SEO', 'report sent', 'ok'), ('Client B · audit', 'report sent', 'ok'), ('Client C · local pack', 'in progress', 'wait'), ('Your logo', 'add to report', 'gold')], ['Nothing of ours on it', 'You keep the client']),
        report=('Unbranded report', 'Ready for your logo', ['Task', 'Where', 'Status'], [('Technical fixes', '8 pages', 'Done'), ('Pages improved', '2 pages', 'Done'), ('Article', '/blog/…', 'Done'), ('Listings', '10 live links', 'Done')],
                '…delivered as an editable file', 'How it works', ['You order per client', 'We work by invite', 'You forward the report'])),
    'seo-consultancy': dict(
        hero=('SEO consultancy', 'Your next steps', '90-Day SEO Roadmap', ['Site reviewed first', 'Competitors compared', 'Straight answers', 'Steps in order', 'Who does what', 'Written summary'],
              'Roadmap', [('Weeks 1–4', 'fix and tidy', 'ok'), ('Weeks 5–8', 'key pages', 'wait'), ('Weeks 9–12', 'content and links', 'wait'), ('Review call', 'booked', 'gold')], ['A plan in order', 'In writing, every time']),
        report=('Written summary', 'Decisions and next steps', ['Step', 'Who', 'When'], [('Fix blocked pages', 'Developer', 'Week 1'), ('Rewrite 5 titles', 'You', 'Week 2'), ('Add service-area pages', 'Writer', 'Week 4'), ('Ask for reviews', 'You', 'Ongoing')],
                '…the full plan runs week by week', 'From the call', ['What to stop doing', 'What to do first', 'Questions answered by email'])),
    'ecommerce-website-development': dict(
        hero=('Online shop', 'Your shop', 'Shopify Starter Store', ['Theme in your colours', 'Products loaded', 'Payments set up', 'Delivery rates', 'Policy pages', 'Walkthrough video'],
              'Basket', [('Oak chopping board', '× 1', 'ok'), ('Delivery', 'Royal Mail', 'ok'), ('Payment', 'card', 'ok'), ('Order email', 'sent', 'ok')], ['Products, payments, delivery', 'Yours to run']),
        report=('Handover', 'What is set up', ['Part', 'Detail', 'Status'], [('Products', '20 loaded', 'Done'), ('Payments', 'your account', 'Done'), ('Delivery and tax', 'your rules', 'Done'), ('Pages', 'home, about, policies', 'Done')],
                '…plus a recorded walkthrough', 'You can now', ['Add a product', 'Handle an order', 'Issue a refund'])),
    'website-maintenance': dict(
        hero=('Website care', 'Site health', 'Growth Care · 1 Month', ['WordPress updated', 'Plugins updated', 'Weekly backups', 'Uptime checked', 'Small fixes done', 'Monthly summary'],
              'This month', [('Core and plugins', 'up to date', 'ok'), ('Last backup', 'Sunday', 'ok'), ('Contact form', 'fixed', 'ok'), ('Uptime', 'monitored', 'ok')], ['Backup before every change', 'Cause found, not patched']),
        report=('Fix note', 'What was wrong, what we did', ['Problem', 'Cause', 'Status'], [('Contact form not sending', 'mail settings', 'Fixed'), ('Blank page on mobile', 'plugin clash', 'Fixed'), ('Slow admin', 'old plugin', 'Fixed'), ('Updates overdue', '14 pending', 'Updated')],
                '…with a backup taken first', 'Included', ['Plain-English summary', 'Free re-fix within 14 days', 'What to watch for'])),
    'website-migration': dict(
        hero=('Website migration', 'Redirect map', 'SEO Website Migration', ['Old addresses listed', 'Each one redirected', 'Titles carried over', 'Forms tested', 'Tracking checked', 'Watched after launch'],
              'Old → new', [('/services/boilers', '/boiler-repair/', 'ok'), ('/about-us', '/about/', 'ok'), ('/contact-us', '/contact/', 'ok'), ('/blog/old-post', '/guides/…', 'ok')], ['Every old address accounted for', 'Tested after launch']),
        report=('Redirect map', 'Every old page, tested', ['Old address', 'New address', 'Test'], [('/services/boilers', '/boiler-repair/', 'Pass'), ('/about-us', '/about/', 'Pass'), ('/contact-us', '/contact/', 'Pass'), ('/prices.html', '/prices/', 'Pass')],
                '…one row for every address on the old site', 'After launch', ['Search Console watched', 'Errors fixed quickly', 'Summary at the end'])),
    'ppc-management': dict(
        hero=('Google Ads', 'Where the budget went', 'PPC Management · 1 Month', ['Tracking set up', 'Search terms chosen', 'Adverts written', 'Waste blocked weekly', 'Budget adjusted', 'Report in pounds'],
              'Search terms', [('emergency plumber leeds', 'enquiries', 'ok'), ('boiler repair near me', 'enquiries', 'ok'), ('plumber jobs', 'blocked', 'wait'), ('free boiler', 'blocked', 'wait')], ['Spend goes to Google, not us', 'Wasteful searches blocked']),
        report=('Monthly report', 'Spend and results', ['Search term', 'Clicks', 'Enquiries'], [('emergency plumber leeds', 'example', 'example'), ('boiler repair near me', 'example', 'example'), ('boiler service cost', 'example', 'example'), ('Blocked this month', '18 terms', '–')],
                'Figures in your report come from your own account', 'Next month', ['Test a new advert', 'Raise budget on best term', 'Add call tracking'])),
    'facebook-ads': dict(
        hero=('Facebook and Instagram ads', 'Your advert', 'Campaign Setup', ['Pixel checked', 'Audience defined', '3 ad variations', 'Budget set', 'You approve first', 'Launch checklist'],
              'Audience', [('Area', 'Leeds + 10 miles', 'ok'), ('Age', '28–55', 'ok'), ('Interest', 'home improvement', 'ok'), ('Past visitors', 'retargeted', 'gold')], ['Right people, right area', 'You approve every advert']),
        report=('Monthly report', 'Results per advert', ['Advert', 'Spend', 'Results'], [('Offer · image', 'example', 'example'), ('Before / after · image', 'example', 'example'), ('Review · image', 'example', 'example'), ('Paused', '1 advert', '–')],
                'Figures in your report come from your own account', 'Next month', ['Two new adverts', 'Test a wider area', 'Retarget visitors'])),
    'tiktok-ads': dict(
        hero=('TikTok ads', 'Script 1 of 5', 'TikTok Hooks & Scripts', ['Hook in the first line', 'Shot-by-shot notes', 'On-screen text', 'Caption written', '3 hooks per script', 'Film on a phone'],
              'Shots', [('Hook', '"Your boiler is lying to you"', 'gold'), ('Shot 1', 'show the pressure dial', 'ok'), ('Shot 2', 'the 20-second fix', 'ok'), ('Close', 'what to do next', 'ok')], ['Written to stop the scroll', 'Filmed on your phone']),
        report=('Monthly report', 'Results per video', ['Video', 'Spend', 'Results'], [('Hook A · pressure dial', 'example', 'example'), ('Hook B · price myth', 'example', 'example'), ('Hook C · before / after', 'example', 'example'), ('Paused', '1 video', '–')],
                'Figures in your report come from your own account', 'Next month', ['More of the best hook', '5 new hook ideas', 'New audience test'])),
    'content-writing': dict(
        hero=('Content writing', 'Your article', '1,000-Word SEO Article', ['Topic researched', 'Outline agreed', 'Clear headings', 'Answer near the top', 'Title and description', 'Originality checked'],
              'Outline', [('How much does a new boiler cost?', 'title', 'gold'), ('Typical price ranges', 'heading', 'ok'), ('What changes the price', 'heading', 'ok'), ('When to repair instead', 'heading', 'ok')], ['Planned around one question', 'Ready to publish']),
        report=('Delivery', 'What arrives with the article', ['Item', 'Detail', 'Status'], [('Article', '1,000 words', 'Ready'), ('Page title', 'for Google', 'Ready'), ('Description', 'for Google', 'Ready'), ('Suggested links', '3 pages', 'Ready')],
                'Delivered as a Google Doc or Word file', 'Included', ['One free revision', 'Originality check', 'You own the article'])),
    'graphic-design': dict(
        hero=('Branding', 'Your brand kit', 'Mini Brand Kit', ['Logo concepts', 'Colour palette', 'Font pairing', 'One-page guide', 'Vector files', 'PNG files'],
              'Files', [('logo.svg', 'vector', 'ok'), ('logo-dark.png', 'for dark backgrounds', 'ok'), ('brand-guide.pdf', '1 page', 'ok'), ('Canva kit', 'optional', 'gold')], ['One look, everywhere', 'Every file type you need']),
        report=('Your files', 'One folder, clearly named', ['File', 'Use', 'Format'], [('Logo · colour', 'web and print', 'SVG, PDF, PNG'), ('Logo · white', 'dark backgrounds', 'SVG, PNG'), ('Brand guide', 'colours and fonts', 'PDF'), ('Social graphics', 'sized per platform', 'PNG')],
                '…sent in one folder', 'Included', ['Up to 3 rounds of changes', 'Full ownership', 'Print-ready versions'])),
}
