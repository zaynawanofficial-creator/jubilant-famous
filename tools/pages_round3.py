"""Round 3 pages: Plans, How it works, About, FAQ, Reviews, five legal pages and the first guides.

build_patterns.py calls build(g) with its own namespace, so the block helpers and page() are shared.
Copy rules as everywhere: original, UK spelling, plain English, nothing invented, no guarantees.
Legal pages are written to match how the site actually works. They are not legal advice: the owner
should have them checked. The trader is Mehreen, a sole trader trading as IamFamous (owner, 16 Nov 2026).
"""

UPDATED = '15 November 2026'
ADDRESS = '27 Old Gloucester Street, London, WC1N 3AX, United Kingdom'

# Review tags shown on each service page (priority order); pages not listed use 'general'.
REVIEW_TAGS = {
    'local-seo': 'seo,general', 'monthly-seo': 'monthly-seo,seo', 'off-page-seo': 'off-page-seo,seo', 'seo-audit': 'seo,general', 'technical-seo': 'seo,general',
    'on-page-seo': 'seo,general', 'ecommerce-seo': 'seo,general', 'ai-seo': 'seo,general', 'white-label-seo': 'seo,general', 'seo-consultancy': 'seo,general',
    'website-development': 'website-development', 'ecommerce-website-development': 'website-development', 'website-maintenance': 'website-development,general', 'website-migration': 'website-development,general',
    'social-media-management': 'social-media-management', 'ppc-management': 'ppc-management', 'facebook-ads': 'ppc-management,general', 'tiktok-ads': 'general',
    'content-writing': 'content-writing', 'graphic-design': 'general',
}

# Focus keyword per page, for Rank Math.
KEYWORDS = {
    'home': 'digital marketing for small businesses', 'services': 'digital marketing services', 'local-seo': 'local SEO services', 'monthly-seo': 'monthly SEO plans', 'off-page-seo': 'link building services',
    'seo-audit': 'SEO audit', 'technical-seo': 'technical SEO services', 'on-page-seo': 'on-page SEO services', 'ecommerce-seo': 'ecommerce SEO services', 'ai-seo': 'AI SEO',
    'white-label-seo': 'white label SEO', 'seo-consultancy': 'SEO consultant', 'website-development': 'small business website design', 'ecommerce-website-development': 'ecommerce website design',
    'website-maintenance': 'WordPress maintenance', 'website-migration': 'website migration service', 'social-media-management': 'social media management', 'ppc-management': 'Google Ads management',
    'facebook-ads': 'Facebook ads management', 'tiktok-ads': 'TikTok ads management', 'content-writing': 'content writing services', 'graphic-design': 'logo design',
    'plans': 'digital marketing plans', 'how-it-works': 'how to order', 'about': 'IamFamous', 'faq': 'IamFamous FAQ', 'reviews': 'IamFamous reviews', 'free-growth-check': 'free growth check', 'contact': 'contact IamFamous',
    'terms': 'terms of service', 'refund-policy': 'refund policy', 'privacy-policy': 'privacy policy', 'cookie-policy': 'cookie policy', 'accessibility': 'accessibility statement',
}

COVERS = {
    'google-business-profile-checklist': ('Local SEO guide', 'Google Business Profile checklist', '12 things to get right'),
    'small-business-website-checklist': ('Website guide', 'What a small business website needs', 'Page by page'),
    'social-media-post-ideas-small-business': ('Social media guide', '20 post ideas for small businesses', 'With a simple weekly routine'),
    'how-to-ask-customers-for-reviews': ('Reviews guide', 'How to ask customers for reviews', 'And stay within UK rules'),
    'google-ads-vs-facebook-ads': ('Advertising guide', 'Google Ads or Facebook ads?', 'Which to try first'),
    'how-seo-is-priced': ('SEO guide', 'How SEO is priced', 'Hourly, retainer or fixed plan'),
    'what-is-a-citation-local-seo': ('Local SEO guide', 'What is a citation?', 'Name, address and phone explained'),
}


def build(g):
    group, heading, para, ul, buttons, columns, column = g['group'], g['heading'], g['para'], g['ul'], g['buttons'], g['columns'], g['column']
    eyebrow, lead, shortcode, join, details = g['eyebrow'], g['lead'], g['shortcode'], g['join'], g['details']
    rail = g['rail']
    section, head, grid, card, steps, faq, cta, page_hero, page, img = g['section'], g['head'], g['grid'], g['card'], g['steps'], g['faq'], g['cta'], g['page_hero'], g['page'], g['img']
    mini_card, svc_card, LIVE, ORDER, GROUPS, LEAD, GROWTH, EMAIL, HOW_STEPS = g['mini_card'], g['svc_card'], g['LIVE'], g['ORDER'], g['GROUPS'], g['LEAD'], g['GROWTH'], g['EMAIL'], g['HOW_STEPS']
    table, write_pattern, POSTS = g['table'], g['write_pattern'], g['POSTS']
    mail = f'<a href="mailto:{EMAIL}">{EMAIL}</a>'

    # ============================================================ PACKAGES
    price_rows = [[f'<a href="/services/{s}/">{LIVE[s]["name"]}</a>', LIVE[s]['short'],
                   f'[ifm_from service="{s}" fallback="On request"]'] for s in ORDER]
    plans = join(
        page_hero('art-services.webp', 'Example service menu with four groups beside a plan card with a Choose button',
                  eyebrow('Plans and prices'), heading('Every plan and price in one place', 1),
                  lead('All our work is sold as plans with a fixed price in pounds. No VAT is added, there is no setup fee and nothing renews unless you order again. Start with a bundle, or jump to the service you need.'),
                  buttons(('See the bundles', '#bundles'), ('Prices by service', '#prices', 'outline')),
                  ul(['Fixed prices in pounds', 'No VAT added', 'One-off orders, no contract'], 'ifm-checks')),
        section(head('Starter bundles', 'Buy the first steps together and save',
                     'Each bundle combines plans that small businesses usually order one after another. You pay less than buying them separately, and each part is delivered exactly as described on its own page.', center=True),
                group(shortcode('[ifm_packages service="bundles"]'), align='wide', layout='flow'),
                cls='ifm-section--cream', anchor='bundles'),
        section(head('Prices by service', 'Starting prices for all 20 services',
                     'Each service has several plans. The price shown is the lowest one. Open the service to compare its plans side by side.'),
                group(table(['Service', 'What it covers', 'From'], price_rows, cls='ifm-table ifm-table--prices'), align='wide', layout='flow'),
                anchor='prices'),
        section(head('Most ordered', 'The three plans most businesses start with', None),
                rail(*[svc_card(s) for s in LEAD], n=3, cls='ifm-rail--lead'), cls='ifm-section--rose'),
        section(head('Questions', 'About our prices', None, center=True), faq([
            ('Are these the full prices?', 'Yes. The price on a plan is what you pay at checkout. We are not VAT registered, so no VAT is added, and there are no setup or admin fees.'),
            ('Why are some services priced "from"?', 'Each service has several plans of different sizes. "From" is the price of the smallest. The service page lists every plan with its own price.'),
            ('Is there anything else to pay for?', 'Only things you buy from someone else in your own name: a domain name, hosting, paid plugins, or advertising spend paid to Google, Meta or TikTok. We tell you about these on the plan page.'),
            ('Do monthly plans renew?', 'No. A monthly plan covers one month. If you want another month, you place another order. There is no subscription and no contract.'),
            ('Can I get a price for something that is not listed?', f'Yes. Use the <a href="{GROWTH}">free growth check form</a> to tell us what you need, and we will reply within one working day with a fixed price and a written scope.'),
            ('How do bundles work?', 'A bundle is one order containing two or three plans at a reduced price. After checkout we ask for the details each part needs. Everything else works exactly like a single plan.'),
        ]), cls='ifm-section--cream'),
        cta(),
    )
    page('plans', 'Plans', plans,
         'All IamFamous plans and prices: starter bundles and starting prices for 20 marketing services. Fixed prices in pounds, no VAT, no contract.',
         'Digital Marketing Plans & Prices UK | IamFamous', menu_order=2)

    # ============================================================ HOW IT WORKS
    how = join(
        page_hero('art-growth.webp', 'Example growth check listing three things to fix first',
                  eyebrow('How it works'), heading('How ordering works, from choosing to delivery', 1),
                  lead('You can order everything on this site without a phone call. Here is exactly what happens at each step, what we need from you and what you can expect from us.'),
                  ul(['Order online in minutes', 'A reply within one working day', 'Full refund before work starts'], 'ifm-checks')),
        section(head('The four steps', 'From plan to proof', None, center=True), steps(HOW_STEPS)),
        section(columns(
            column(eyebrow('Step by step'), heading('What happens after you pay'),
                   para('The moment payment goes through you get an order number and a receipt by email. The answers you gave on the plan page are already attached to the order, so most jobs can begin without any further questions.'),
                   para('If something is missing or unclear, we email you within one working day. You can add logos, photos and documents at any time from your account.'),
                   para('The delivery time shown on your plan starts once we have everything we asked for. When the work is finished, the report or files arrive by email and are saved in your account.'), width='55%'),
            column(group(heading('Order statuses you will see', 3),
                         ul(['<strong>Awaiting details:</strong> we need something from you before we can start.',
                             '<strong>Details received:</strong> we have what we need and the delivery time is running.',
                             '<strong>Ready for review:</strong> the work is done and waiting for you to check.',
                             '<strong>Delivered:</strong> finished, with your report or files in your account.'], 'ifm-ticks'),
                         cls='ifm-panel', layout='flow'), width='45%'),
            cls='ifm-split', center=True), cls='ifm-section--cream'),
        section(head('Changes and refunds', 'If something is not right', None),
                rail(card('Changes are built in', 'Each plan says how many rounds of changes it includes. A round is one list of changes sent together. Tell us what to alter and we rework it.', 'pen'),
                     card('Refund before we start', 'Change your mind before work begins and you get all of your money back. Email us with your order number.', 'shield'),
                     card('Fair refund after we start', 'If we have already started, we agree what has been done and refund the rest. The <a href="/refund-policy/">refund policy</a> explains how.', 'pound'),
                     n=3)),
        section(columns(
            column(eyebrow('Access'), heading('How we get access without your passwords'),
                   para('We never ask for a password. Every platform we work on lets you invite another person with their own login, and that is how we work.'), width='45%'),
            column(group(ul(['<strong>Google Business Profile:</strong> add us as a manager.',
                             '<strong>WordPress, Shopify, Wix:</strong> invite us as a user or staff member.',
                             '<strong>Google Ads, Meta, TikTok:</strong> grant access through their business tools.',
                             '<strong>Search Console and Analytics:</strong> add us as a user.',
                             'You can remove our access at any time, and we suggest you do when the job is finished.'], 'ifm-ticks'), cls='ifm-panel', layout='flow'), width='55%'),
            cls='ifm-split', center=True), cls='ifm-section--rose'),
        section(head('Questions', 'Ordering: your questions answered', None, center=True), faq([
            ('Do I need to speak to anyone before ordering?', f'No. Everything you need to decide is on the plan page. If you would like advice first, the <a href="{GROWTH}">free growth check</a> is the quickest way to get it.'),
            ('When does the delivery time start?', 'When we have the details asked for on the plan page. If you fill in the form fully when you order, that is straight after payment.'),
            ('How do I pay?', 'At a secure checkout, in pounds. Your card details go straight to the payment provider and are never stored on this site.'),
            ('Can I order more than one plan?', 'Yes. Add as many as you like to your basket and pay once. Each plan keeps its own details and delivery time.'),
            ('What if I ordered the wrong thing?', 'Email us before work starts and we will swap it or refund you in full.'),
        ]), cls='ifm-section--cream'),
        cta(),
    )
    page('how-it-works', 'How It Works', how,
         'How ordering works at IamFamous: choose a plan, send your details, pay securely and receive work you can check. Changes, refunds and access explained.',
         'How It Works: Ordering, Delivery & Refunds | IamFamous', menu_order=3)

    # ============================================================ ABOUT
    about = join(
        page_hero('art-services.webp', 'Example service menu with four groups beside a plan card with a Choose button',
                  eyebrow('About IamFamous'), heading('A marketing agency you can buy from like a shop', 1),
                  lead('IamFamous is a UK digital marketing agency for small businesses. We sell local SEO, websites, social media, advertising, content and design as fixed-price plans that you order online.'),
                  ul(['UK small businesses', 'Fixed prices in pounds', 'Work you can check'], 'ifm-checks')),
        section(columns(
            column(eyebrow('Why we work this way'), heading('Marketing without the mystery'),
                   para('Buying marketing as a small business is harder than it should be. Prices are hidden behind a call. Monthly retainers run on with little to show for them. Reports are full of charts and short on what was actually done.'),
                   para('We set IamFamous up to do the opposite. Every service is a plan with a price, a delivery time and a list of what you get. You can read it, compare it and order it without talking to a salesperson.'),
                   para('When the job is finished you get proof: the live links, the files, the before and after. If a plan is not right for you, the page says so.'), width='58%'),
            column(group(heading('IamFamous at a glance', 3),
                         ul(['<strong>What we are:</strong> a digital marketing agency.',
                             '<strong>Who runs it:</strong> IamFamous is the trading name of Mehreen, a sole trader.',
                             '<strong>Who we work for:</strong> small businesses across the United Kingdom.',
                             '<strong>What we sell:</strong> 20 services as fixed-price plans.',
                             '<strong>How you buy:</strong> online, in pounds, with no contract.',
                             '<strong>VAT:</strong> not registered, so none is added.',
                             f'<strong>Email:</strong> {mail}',
                             f'<strong>Postal address:</strong> {ADDRESS}'], 'ifm-ticks'),
                         cls='ifm-panel', layout='flow'), width='42%'),
            cls='ifm-split')),
        section(head('What we do', 'Four kinds of work', None),
                rail(*[card(t, f'{sub} <a href="/services/#group-{k}">See these services →</a>', ic) for (k, t, sub), ic in zip(GROUPS, ['local', 'webdev', 'megaphone', 'pen'])], n=4),
                cls='ifm-section--cream'),
        section(columns(
            column(eyebrow('How we work'), heading('The rules we hold ourselves to'),
                   ul(['<strong>The price is published.</strong> You see it before you speak to anyone.',
                       '<strong>The scope is written down.</strong> What is included, what is not, and what we need from you.',
                       '<strong>People do the work.</strong> Software helps with research and checks. A person is responsible for what you receive.',
                       '<strong>You own the result.</strong> Websites, listings, designs and accounts stay in your name.',
                       '<strong>We reply within one working day.</strong> Monday to Friday, by email.'], 'ifm-ticks'), width='55%'),
            column(group(heading('What we will not do', 3),
                         ul(['Sell followers, likes or views.', 'Promise a position on Google or a number of sales.', 'Write, buy or filter reviews.', 'Ask for your passwords.', 'Tie you into a contract.'], 'ifm-crosses'),
                         cls='ifm-panel', layout='flow'), width='45%'),
            cls='ifm-split', center=True)),
        section(head('What customers say', 'In their own words', 'Feedback sent to us by customers. We publish it without names.'),
                group(shortcode('[ifm_reviews service="general,seo,website-development" count="6"]'), align='wide', layout='flow'),
                para('<a href="/reviews/">Read all customer feedback →</a>', 'ifm-center ifm-card__more'), cls='ifm-section--rose'),
        section(head('Contact', 'How to reach us', None),
                rail(card('Email', f'{mail}. We reply within one working day, Monday to Friday.', 'chat'),
                     card('Post', f'{ADDRESS}. We work online, so this address is for post only.', 'local'),
                     card('Not sure what you need?', f'Ask for a <a href="{GROWTH}">free growth check</a> and we will tell you what we would fix first.', 'spark'), n=3)),
        cta(),
    )
    page('about', 'About', about,
         'IamFamous is a UK digital marketing agency selling fixed-price plans to small businesses: local SEO, websites, social media, ads, content and design.',
         'About IamFamous | UK Digital Marketing Agency for Small Businesses', menu_order=4)

    # ============================================================ FAQ
    faq_groups = [
        ('Ordering', [
            ('How do I order?', 'Choose a service, pick a plan, fill in the short form on the plan page and pay at the checkout. You get an order number and a receipt by email.'),
            ('Do I need an account?', 'An account is created for you at checkout. You use it to follow your order, add files and download what we deliver.'),
            ('Can I talk to someone first?', f'Yes. Email {mail} or use the <a href="{GROWTH}">free growth check form</a>. We reply within one working day.'),
            ('Do you work with businesses outside the UK?', 'Our plans are written and priced for UK businesses. If you are elsewhere, ask first and we will tell you honestly whether we are a good fit.'),
        ]),
        ('Prices and payment', [
            ('Is VAT added to your prices?', 'No. IamFamous is not VAT registered, so the price shown is the price you pay.'),
            ('Which payment methods do you accept?', 'Card payments at a secure checkout. Your card details go to the payment provider and are never stored on this site.'),
            ('Are there contracts or subscriptions?', 'No. Every plan is a single order. Monthly plans cover one month and stop unless you order another.'),
            ('Will the price change after I order?', 'No. If you want something outside the plan, we tell you the price first and only go ahead if you agree.'),
        ]),
        ('Delivery and changes', [
            ('How long does delivery take?', 'Each plan shows its own delivery time. It starts when we have the details we asked for.'),
            ('How will I receive the work?', 'By email, and saved in your account. Depending on the plan you get live links, files or a written report.'),
            ('What if I want changes?', 'Each plan lists the changes included. Send one list of what you would like altered and we rework it.'),
            ('What do you need from me?', 'It depends on the plan and is listed on each plan page. We ask for access by invite and never for a password.'),
            ('Can I get my order faster?', 'Most one-off plans offer express delivery as an optional extra on the plan page. Your order goes to the front of the queue and we aim to deliver in half the usual time. If it takes longer than the standard delivery time, the express fee is refunded.'),
        ]),
        ('Refunds', [
            ('Can I cancel?', 'Yes. Cancel before work starts and you get a full refund. After work has started, we refund the part that has not been done.'),
            ('What if I am not happy with the result?', f'Tell us what is wrong. We put it right within the changes included in your plan. If we cannot deliver what the plan lists, you are refunded for the part not delivered. See the <a href="/refund-policy/">refund policy</a>.'),
            ('How long do refunds take?', 'We send refunds to the original payment method within 5 working days of agreeing them. Your bank may take a few more days to show it.'),
        ]),
        ('Results', [
            ('Do you guarantee a position on Google?', 'No. Nobody controls Google, so nobody can honestly promise a position. We guarantee that the work listed on your plan is done and checked.'),
            ('How long does SEO take to work?', 'It varies with your site and your competition. Some changes show in days. Most SEO work builds over several months.'),
            ('Do you use AI to do the work?', 'We use software for research and checks, as most agencies do. A person does the work and is responsible for everything you receive.'),
            ('Do you sell followers or likes?', 'No. Bought followers do not become customers and can get an account restricted.'),
        ]),
    ]
    faq_page = join(
        page_hero('art-growth.webp', 'Example growth check listing three things to fix first',
                  eyebrow('FAQ'), heading('Frequently asked questions', 1),
                  lead(f'Straight answers about ordering, prices, delivery, refunds and results. If your question is not here, email {mail} and we will reply within one working day.'),
                  ul([f'<a href="#faq-{t.lower().replace(" ", "-")}">{t}</a>' for t, _ in faq_groups], 'ifm-jump')),
        *[section(group(heading(t, 2, anchor=f'faq-{t.lower().replace(" ", "-")}'), faq(items), cls='ifm-faq-page', layout='constrained'), cls='ifm-section--tight' + (' ifm-section--cream' if i % 2 else ''))
          for i, (t, items) in enumerate(faq_groups)],
        cta(),
    )
    page('faq', 'FAQ', faq_page,
         'Answers about ordering, prices, VAT, payment, delivery times, changes, refunds and results at IamFamous, the UK agency with fixed-price plans.',
         'FAQ: Ordering, Prices, Delivery & Refunds | IamFamous', menu_order=5)

    # ============================================================ REVIEWS
    reviews = join(
        page_hero('art-growth.webp', 'Example growth check listing three things to fix first',
                  eyebrow('Reviews'), heading('What customers say about IamFamous', 1),
                  lead('Feedback sent to us by customers, grouped by the service it is about. We publish it word for word and without names.'),
                  ul(['Published word for word', 'Shown without names', 'We never write or buy reviews'], 'ifm-checks')),
        section(group(shortcode('[ifm_reviews count="0"]'), align='wide', layout='flow', cls='ifm-reviews-page')),
        section(columns(
            column(eyebrow('Our approach'), heading('How we handle reviews'),
                   para('Every quote on this page was sent to us by a customer. We do not edit the wording, we do not pay for feedback and we do not write reviews ourselves.'),
                   para('Names are left off this page. If you are a customer and would like your feedback added, changed or removed, email us and we will do it.'), width='55%'),
            column(group(heading('Share your experience', 3), para(f'Worked with us? Email {mail} with your order number and tell us how it went, good or bad.'),
                         buttons(('Email us', f'mailto:{EMAIL}')), cls='ifm-panel', layout='flow'), width='45%'),
            cls='ifm-split', center=True), cls='ifm-section--cream'),
        cta(),
    )
    page('reviews', 'Reviews', reviews,
         'Customer feedback about IamFamous, grouped by service: SEO, websites, Google Ads, social media, content and link building. Published word for word.',
         'IamFamous Reviews | Customer Feedback by Service', menu_order=6)

    # ============================================================ LEGAL
    def legal(slug, title, summary, glance, sections, excerpt, seo_title, order):
        toc = ul([f'<a href="#{a}">{i + 1}. {h}</a>' for i, (a, h, _) in enumerate(sections)], 'ifm-toc')
        body = []
        for i, (a, h, blocks) in enumerate(sections):
            body.append(heading(f'<span>{i + 1:02d}</span> {h}', 2, 'ifm-legal__h', anchor=a))
            body.extend(blocks)
        content = join(
            page_hero('art-growth.webp', '', eyebrow('Legal'), heading(title, 1), lead(summary),
                      ul([f'Last updated: {UPDATED}', 'Applies to iamfamous.uk', 'Plain-English summary first'], 'ifm-checks')),
            section(columns(
                column(group(heading('On this page', 3), toc, cls='ifm-legal__toc', layout='flow'), width='30%', cls='ifm-legal__aside'),
                column(group(heading('At a glance', 3), ul(glance, 'ifm-ticks'), cls='ifm-panel ifm-legal__glance', layout='flow'),
                       group(*body, cls='ifm-legal__body', layout='flow'),
                       group(heading('Questions about this page?', 3), para(f'Email {mail} with your question and, if it is about an order, your order number. A person will reply within one working day.'),
                             buttons(('Contact us', '/contact/'), ('Read the FAQ', '/faq/', 'outline')), cls='ifm-card ifm-legal__contact', layout='flow'),
                       width='70%'),
                cls='ifm-legal')),
        )
        page(slug, title, content, excerpt, seo_title, menu_order=order)

    P = para
    who = f'This website is run by Mehreen, a sole trader trading as IamFamous ("we", "us"). Our postal address is {ADDRESS} and our email is {mail}.'

    legal('terms', 'Terms of Service',
          'These terms apply when you order a plan from iamfamous.uk. They say what you get, what we need from you, how payment, delivery, changes and refunds work, and where each of us stands if something goes wrong.',
          ['You buy a plan with a written scope at a fixed price in pounds.', 'No VAT is added. Nothing renews automatically.', 'The delivery time starts when we have the details we asked for.', 'Full refund before work starts; a fair partial refund after.', 'We guarantee the work listed, not rankings, followers or sales.'],
          [('who-we-are', 'Who we are', [P(who), P('"You" means the person or business placing the order. If you order for a business, you confirm you are allowed to do so.')]),
           ('the-contract', 'How the contract is made', [P('A plan page describes what is included, what is not, the delivery time, the changes included and the price. When you place an order and we send your order confirmation by email, a contract is formed on these terms and on the description of the plan you bought.'),
                                                        P('If a plan page and these terms disagree, the plan page applies to that plan.')]),
           ('prices', 'Prices and payment', [P('Prices are in pounds sterling. We are not registered for VAT, so no VAT is added. Payment is taken in full at checkout through a third-party payment provider. We do not see or store your card details.'),
                                             P('Some plans rely on things you buy from others in your own name, such as a domain name, hosting, paid software or advertising spend. Those costs are not included and are paid by you directly to the supplier.'),
                                             P('Monthly plans cover one month of work. They are not subscriptions and do not renew.')]),
           ('your-part', 'What we need from you', [P('Each plan lists what we need. You agree to provide it accurately and promptly, and to give access by user invite where access is needed. Please never send us a password.'),
                                                   P('You confirm you have the right to use the text, images, logos and other material you send us, and that our using it for your order will not infringe anyone else\'s rights.')]),
           ('delivery', 'Delivery times', [P('The delivery time shown on a plan starts when we have everything listed under "What we need from you". If we are waiting for something from you, the delivery time is paused until we receive it.'),
                                           P('Delivery times are our honest estimate of working days, Monday to Friday, excluding UK public holidays. If we expect to be late, we will tell you and explain why. If we are more than 10 working days late for reasons within our control, you may cancel the undelivered part and be refunded for it.')]),
           ('changes', 'Changes and revisions', [P('Each plan states the changes or revisions included. A round of changes is one list sent together. Requests outside the scope of the plan are quoted separately, and we only go ahead if you accept the price.')]),
           ('extras', 'Optional extras', [P('Some plans offer optional extras at checkout. An extra round of changes adds one round to those included in the plan. Express delivery moves your order to the front of our queue, and we aim to deliver in half the stated delivery time. If an express order takes longer than the standard delivery time shown on the plan, we refund the express fee. Extras are not offered on monthly plans.')]),
           ('results', 'What we do and do not promise', [P('We promise to carry out the work listed on your plan with reasonable care and skill, and to deliver what the plan describes.'),
                                                        P('We do not promise results that depend on others. This includes positions in Google or other search engines, mentions by AI tools, numbers of visitors, followers, enquiries or sales, and whether a third-party site keeps, labels or indexes a link. Search engines, advertising platforms and other websites make their own decisions and change their rules without notice.')]),
           ('links', 'Links and listings on other websites', [P('Where a plan includes links or listings, we replace free of charge any that are not live when we deliver. Guest posts removed within 90 days of publication are replaced. After that, placements are outside our control.')]),
           ('ownership', 'Who owns the work', [P('Once you have paid in full, you own the final work we create for you, such as your website, designs and written content. Material you gave us stays yours. We keep the right to use our own methods, templates and tools, and third-party software, fonts and images stay subject to their own licences.'),
                                               P('We will not show your work in our own marketing or name you as a customer without asking first.')]),
           ('cancelling', 'Cancelling and refunds', [P('You can cancel before work starts and receive a full refund. Once work has started, refunds follow our <a href="/refund-policy/">refund policy</a>, which forms part of these terms.'),
                                                     P('If you are a consumer, you also have a legal right to cancel within 14 days of your order. By placing an order and sending your project details, you ask us to start work within that period. If you then cancel within the 14 days, you pay for the work done up to the point you told us. Once a service has been fully carried out, the right to cancel ends.')]),
           ('liability', 'Our responsibility to you', [P('Nothing in these terms limits liability that cannot legally be limited, including liability for death or personal injury caused by negligence, or for fraud. If you are a consumer, nothing here affects your legal rights.'),
                                                       P('Subject to that, we are not liable for loss of profit, business or data, or for losses we could not reasonably have foreseen, and our total liability for an order is limited to the price you paid for it. We are not responsible for the actions of third parties such as search engines, advertising platforms, hosting companies or publishers.')]),
           ('ending', 'Ending an order early', [P('We may stop work and refund the part not delivered if we cannot get the information or access we need after asking twice, if you ask us to do something unlawful or against a platform\'s rules, or if you are abusive to our team.')]),
           ('general', 'Data, complaints and the law', [P('We handle personal information as described in our <a href="/privacy-policy/">privacy policy</a>.'),
                                                         P(f'If you are unhappy, email {mail} with your order number. We reply within one working day and aim to resolve complaints within 10 working days.'),
                                                         P('These terms are governed by the law of England and Wales, and the courts of England and Wales have jurisdiction. If you are a consumer living in Scotland or Northern Ireland, you may also bring a claim in your local courts.'),
                                                         P('We may update these terms. The version in force when you order applies to that order.')])],
          'IamFamous terms of service: how orders, prices, delivery, changes, refunds, ownership and liability work for plans bought on iamfamous.uk.',
          'Terms of Service | IamFamous', 20)

    legal('refund-policy', 'Refund Policy',
          'We want refunds to be simple and fair. If we have not started, you get everything back. If we have, you pay only for what has been done.',
          ['Full refund if you cancel before work starts.', 'After work starts, you pay only for the work done.', 'Links not live at delivery are replaced free.', 'Refunds are sent within 5 working days of being agreed.', 'Your legal rights are not affected.'],
          [('before', 'Before work starts', [P(f'Email {mail} with your order number at any time before we start and we refund the full amount. We normally start within one working day of receiving your details, so tell us quickly if you have changed your mind.')]),
           ('after', 'After work has started', [P('If you cancel after we have started, we work out the share of the plan that has been completed and refund the rest. We tell you how we worked it out. For example, if two of five pages of a website have been built, we refund the price of the other three.'),
                                                P('Work that is finished and delivered is not refundable, except as set out below.')]),
           ('monthly', 'Monthly plans', [P('A monthly plan covers 30 days of work. If you cancel part-way through, we refund the tasks not yet done. Monthly plans do not renew, so there is nothing to cancel at the end of the month.')]),
           ('not-delivered', 'If we do not deliver what the plan lists', [P('Tell us what is missing or wrong. We put it right at no cost. If we cannot deliver something the plan lists, we refund that part of the price.'),
                                                                             P('Links and listings that are not live when we deliver are replaced free. Guest posts removed within 90 days of publication are replaced.')]),
           ('not-refundable', 'What we cannot refund', [P('We cannot refund money you paid to someone else, such as advertising spend, domain names, hosting or software licences. We also cannot refund a plan because a search engine, AI tool or social platform did not respond as hoped, since we do not promise results that depend on them.')]),
           ('consumers', 'Your legal rights', [P('If you buy as a consumer, you have a legal right to cancel within 14 days of ordering. Because you ask us to begin within that period when you send your project details, you pay for the work done up to the time you cancel, and the right ends once the service has been fully carried out. Nothing in this policy reduces your rights under consumer law.')]),
           ('how', 'How to ask for a refund', [P(f'Email {mail} with your order number and a sentence saying what you would like. We reply within one working day. Once a refund is agreed, we send it to the original payment method within 5 working days. Your bank may take a few more days to show it.')])],
          'IamFamous refund policy: full refund before work starts, a fair partial refund after, free replacement of links not live at delivery, and how to ask.',
          'Refund Policy | IamFamous', 21)

    legal('privacy-policy', 'Privacy Policy',
          'This policy explains what personal information we collect when you use iamfamous.uk or order from us, why we collect it, who we share it with and what your rights are.',
          ['We collect only what we need to reply to you and carry out your order.', 'We never sell personal information.', 'Card details go to the payment provider, not to us.', 'Optional cookies are used only if you allow them.', 'You can ask to see, correct or delete your information.'],
          [('who', 'Who is responsible', [P(who + ' We are the controller of the personal information described here.')]),
           ('what', 'What we collect', [ul(['<strong>Enquiries:</strong> your name, email address, website, the service you ask about and your message.',
                                            '<strong>Orders:</strong> your name, email, billing details, what you bought and the order history.',
                                            '<strong>Project details:</strong> the answers and files you give us so we can do the work, such as business details, logos and access invitations.',
                                            '<strong>Your account:</strong> your login email and an encrypted password.',
                                            '<strong>Technical data:</strong> a scrambled, shortened form of your IP address kept briefly to stop spam through our forms, and standard server logs kept by our hosting company.'], 'ifm-ticks'),
                                        P('We do not collect card numbers. We do not use advertising trackers or analytics cookies on this site.')]),
           ('why', 'Why we use it and our legal basis', [ul(['<strong>To carry out your order</strong> and keep you informed about it (performance of a contract).',
                                                             '<strong>To reply to your enquiry</strong> (steps taken at your request before a contract, and our legitimate interest in answering people who contact us).',
                                                             '<strong>To keep accounting records</strong> (a legal obligation).',
                                                             '<strong>To protect the site</strong> from spam and abuse (our legitimate interest in keeping it secure).'], 'ifm-ticks'),
                                                        P('We do not send marketing emails unless you have asked for them, and we do not make automated decisions about you.')]),
           ('sharing', 'Who we share it with', [P('We share personal information only with suppliers who help us run the business, and only as far as they need it: our website hosting company, the payment provider you pay through, our email provider, and the platforms your order involves when you have given us access to them. We may also share information when the law requires it.'),
                                                P('Some of these suppliers may process information outside the United Kingdom. Where they do, we rely on the safeguards UK data protection law allows, such as adequacy decisions or standard contractual clauses.')]),
           ('keeping', 'How long we keep it', [ul(['<strong>Orders and invoices:</strong> 6 years, because tax law requires it.', '<strong>Project details and files:</strong> for the life of the order and up to 12 months after, so we can help with follow-up questions. Ask and we will delete them sooner.',
                                                    '<strong>Enquiries that did not become orders:</strong> up to 12 months.', '<strong>Spam-protection data:</strong> a matter of minutes.'], 'ifm-ticks')]),
           ('rights', 'Your rights', [P('You can ask us for a copy of your information, ask us to correct or delete it, ask us to limit how we use it, object to our using it, and ask for it in a portable form. To use any of these rights, email us. We reply within one month and there is no charge.'),
                                      P('If you are unhappy with how we have handled your information, please tell us first. You also have the right to complain to the Information Commissioner\'s Office at ico.org.uk.')]),
           ('security', 'How we protect it', [P('The site uses an encrypted connection. Access to orders and files is limited to the people who need it, and uploaded files are stored outside the public part of the website. No system is perfectly secure, so please do not send us passwords or sensitive personal information that the order does not need.')]),
           ('cookies', 'Cookies', [P('This site uses the cookies needed for the basket, checkout and login, and remembers your cookie choice. Optional cookies are used only with your permission. Details are in our <a href="/cookie-policy/">cookie policy</a>.')]),
           ('changes', 'Changes to this policy', [P('If we change how we use personal information, we will update this page and change the date at the top.')])],
          'IamFamous privacy policy: what personal information we collect, why, who we share it with, how long we keep it and how to use your rights.',
          'Privacy Policy | IamFamous', 22)

    legal('cookie-policy', 'Cookie Policy',
          'A cookie is a small file a website saves in your browser. This site needs a few cookies to work, and asks before using any others. At the moment it uses no advertising or analytics cookies at all.',
          ['Essential cookies are always on, because the basket and login need them.', 'Optional cookies are used only if you choose "Allow all".', 'No advertising or analytics cookies are in use today.', 'You can change your choice at any time with "Cookie settings" in the footer.', 'Nothing is shared with advertisers.'],
          [('what', 'What cookies are', [P('Cookies let a website remember things between pages, such as what is in your basket or that you are logged in. Essential cookies are the ones a site cannot work without. UK law allows these without asking for consent. Anything else needs your permission first, which is what the cookie banner is for.')]),
           ('choice', 'Your choice', [P('The first time you visit, a small banner asks whether you want "Essential only" or "Allow all". We remember your answer for 180 days in a cookie called ifm_consent. To change it, use the <a href="#cookie-settings">Cookie settings</a> link here or in the footer.'), P('No optional cookies are in use at the moment, so both choices currently give you the same site. If we add analytics or similar tools in future, they will load only for visitors who chose "Allow all", and this page will list them.')]),
           ('which', 'The cookies this site uses', [g['table'](['Cookie', 'What it does', 'How long it lasts'],
                                                             [['woocommerce_cart_hash, woocommerce_items_in_cart', 'Remember what is in your basket', 'Until you close the browser'],
                                                              ['wp_woocommerce_session_*', 'Links your basket to your visit', 'Up to 2 days'],
                                                              ['wordpress_logged_in_*, wordpress_sec_*', 'Keep you logged in to your account', 'Until you log out, or up to 14 days'],
                                                              ['wordpress_test_cookie', 'Checks whether your browser accepts cookies', 'Until you close the browser'],
                                                              ['ifm_consent', 'Remembers your cookie choice', '180 days']], cls='ifm-table')]),
           ('third', 'Payment providers', [P('When you pay, the payment provider may set its own cookies to process the payment securely and prevent fraud. Those are governed by the provider\'s own cookie policy.')]),
           ('control', 'How to control cookies', [P('You can block or delete cookies in your browser settings. If you block the cookies above, you will still be able to read the site, but the basket, checkout and account will not work.')]),
           ('changes', 'If this changes', [P('If we add analytics or other optional cookies, we will list them here before they are switched on.')])],
          'The cookies used on iamfamous.uk: essential cookies for the basket, checkout and login, your cookie choice, and no advertising or analytics cookies.',
          'Cookie Policy | IamFamous', 23)

    legal('accessibility', 'Accessibility Statement',
          'We want everyone to be able to use this website. This page says what we have done, what we know is not perfect yet and how to tell us about a problem.',
          ['We aim to meet WCAG 2.2 level AA.', 'The menu, plan picker and forms work with a keyboard.', 'Text contrast was checked when the colours were chosen.', 'Motion is switched off if your device asks for less.', 'Tell us about a problem and we reply within one working day.'],
          [('aim', 'Our aim', [P('We aim to meet the Web Content Accessibility Guidelines (WCAG) version 2.2 at level AA, the standard most widely used in the UK.')]),
           ('done', 'What we have done', [ul(['Pages use proper headings, lists and landmarks so screen readers can move around them.', 'The menu, the plan picker, the comparison table and the forms can all be used with a keyboard, and the focus position is always visible.',
                                              'Colour combinations were checked for contrast, and colour is never the only way information is shown.', 'Text can be enlarged to 200% without losing content.',
                                              'Animations are turned off when your device is set to reduce motion.', 'Illustrations have text descriptions, and decorative images are hidden from screen readers.'], 'ifm-ticks')]),
           ('limits', 'Known limitations', [ul(['The basket, checkout and account pages are provided by WooCommerce. We style them, but we have not been able to test every step with assistive technology.',
                                                'Illustrations are images of example screens. Their descriptions summarise them, but not every label is read out.',
                                                'Some older blog posts may contain images without descriptions.'], 'ifm-ticks'),
                                            P('We tested the site with keyboard-only use and automated checks on phone, tablet and desktop sizes. We have not yet commissioned an independent audit.')]),
           ('help', 'If you need information in another way', [P(f'If you cannot use part of this site, email {mail} and tell us what you need. We can take an order, send a plan description or answer questions by email.')]),
           ('report', 'Reporting a problem', [P(f'If you find something that does not work for you, email {mail} with the page address and what happened. We reply within one working day and will tell you what we are going to do about it.')])],
          'Accessibility statement for iamfamous.uk: our WCAG 2.2 AA aim, what has been done, known limitations and how to report a problem.',
          'Accessibility Statement | IamFamous', 24)

    # ============================================================ GUIDES (posts)
    def takeaways(items):
        return group(heading('Key points', 3), ul(items, 'ifm-ticks'), cls='ifm-panel ifm-takeaways', layout='flow')

    def inline_cta(title, text, label, url):
        return group(heading(title, 3), para(text), buttons((label, url)), cls='ifm-inline-cta', layout='flow')

    def post(slug, title, content, excerpt, category='Guides'):
        POSTS.append(dict(slug=slug, title=title, excerpt=excerpt, category=category, pattern=f'iamfamous/post-{slug}'))
        write_pattern(f'post-{slug}', f'Article: {title}', content, cat='iamfamous-sections', post_types='post', inserter='no')

    H = heading
    post('google-business-profile-checklist', 'Google Business Profile checklist for UK small businesses', join(
        takeaways(['Your Google Business Profile is free and decides much of what people see when they search for you nearby.', 'Categories, services and opening hours matter more than anything else you fill in.', 'Keep your name, address and phone number identical everywhere online.', 'Ask real customers for reviews. Never buy them or offer rewards.']),
        P('When someone searches for a business like yours nearby, Google usually shows a map with three businesses above the normal results. The information in that box comes from your Google Business Profile. It is free, and for most local trades it brings more calls than the website does.'),
        P('This checklist covers the twelve things worth getting right, in the order we work through them on a <a href="/services/local-seo/">Local SEO</a> order.'),
        H('Claim and verify the profile'),
        P('Search for your business name on Google Maps. If a listing exists, claim it. If not, create one at business.google.com. Google will then ask you to prove the business is yours, most often by recording a short video of your premises, signage or tools, and sometimes by phone or postcard. Only the owner can do this step.'),
        H('Get the basics exactly right'),
        ul(['<strong>Business name:</strong> use your real trading name. Adding extra words such as your town or service breaks Google\'s rules and can get a profile suspended.',
            '<strong>Address or service area:</strong> if customers visit you, show the address. If you travel to them, hide it and list the towns or postcodes you cover.',
            '<strong>Phone number:</strong> use a number that is answered in working hours.',
            '<strong>Opening hours:</strong> keep them accurate, and add special hours for bank holidays.',
            '<strong>Website:</strong> link to the most relevant page, which is not always the home page.']),
        H('Choose the right categories'),
        P('Your primary category is the strongest signal you give Google about what you do. Pick the most specific one that fits: "Emergency plumber" beats "Plumber" if emergencies are your main work. Then add secondary categories for your other real services. Do not add categories for work you do not offer.'),
        H('List your services and write the description'),
        P('Add each service you offer, with a short description. Then write the business description. You have up to 750 characters. Say what you do, where you do it and what makes you a sensible choice, in plain words. The first two sentences matter most because the rest is often hidden behind "More".'),
        inline_cta('Rather have this done for you?', 'Our Google Business Profile Optimisation plan covers every step on this page, with a report showing what was changed.', 'See Local SEO plans', '/services/local-seo/#plans'),
        H('Add photos that show real work'),
        P('Upload your logo, a cover photo and pictures of your premises, your team at work and finished jobs. Real photos, even from a phone, do better than stock images. Add new ones regularly, since a profile with recent photos looks like a business that is open.'),
        H('Keep your details the same everywhere'),
        P('Google compares your profile with what it finds on directories such as Yell, Bing Places and Apple Maps, and on your own website. If the name, address or phone number differ, it trusts them less. Decide on one exact version and use it everywhere. This is what a citation or directory listing plan does.'),
        H('Ask for reviews the right way'),
        P('Reviews influence both where you appear and whether people call. Ask every happy customer, soon after the job, and send them the direct link from your profile. Reply to every review, including the bad ones, calmly and briefly.'),
        P('Do not offer discounts or gifts for reviews, do not ask only the customers you think will be positive, and never buy reviews. All three break Google\'s rules, and fake reviews are also against UK consumer law.'),
        H('Post updates'),
        P('Google lets you publish short posts on your profile: an offer, a finished job, a seasonal reminder. One a week is plenty. Posts show customers you are active and give Google fresh information about what you do.'),
        H('Check it every month'),
        P('Profiles drift. Customers suggest edits, hours change, photos get added by the public. Once a month, log in, check the details, answer new reviews and look at the performance figures to see which searches are finding you.'),
        H('Frequently asked questions'),
        g['faq']([('Is Google Business Profile free?', 'Yes. Creating, verifying and managing a profile costs nothing. Be wary of anyone who phones claiming to be from Google and asking for payment to keep your listing.'),
                  ('How long does verification take?', 'Video verification is usually reviewed within a few working days. Postcards take longer. Your profile may not be fully visible until verification is complete.'),
                  ('Can I have a profile without a shop or office?', 'Yes. If you travel to your customers, set the profile up as a service-area business and hide your address.'),
                  ('Why has my profile been suspended?', 'The most common reasons are extra keywords in the business name, an address that is not a real place of business, or duplicate listings. Fix the cause before asking Google to reinstate it.')]),
        inline_cta('Not sure how your profile looks?', 'Ask for a free growth check. We will look at your Google listing, website and social pages and tell you the three things we would fix first.', 'Get my free growth check', GROWTH),
    ), 'A 12-point Google Business Profile checklist for UK small businesses: verification, categories, services, photos, reviews, posts and consistent details.', 'Local SEO')

    post('small-business-website-checklist', 'What should a small business website include?', join(
        takeaways(['A small business website has one job: turn a visitor into an enquiry, a booking or a sale.', 'The first screen must say what you do, where, and how to get in touch.', 'Five pages are enough for most small businesses.', 'Speed and a working enquiry form matter more than clever design.']),
        P('Most visitors to a small business website arrive on a phone, look at the first screen for a few seconds and decide whether to stay. A good site makes that decision easy. This guide goes through what to include, page by page, and what you can safely leave out.'),
        H('The first screen'),
        P('Before anyone scrolls, they should be able to answer three questions: what do you do, where do you do it, and how do I contact you? That means a plain headline ("Garden design in Bristol" is better than "Welcome to our website"), one sentence of detail and one clear button, such as "Get a free quote" or "Call now".'),
        H('The five pages most businesses need'),
        ul(['<strong>Home:</strong> who you are, what you offer and the main action, with short routes to everything else.',
            '<strong>Services:</strong> what you do, described the way a customer would search for it. Give each main service its own section or page.',
            '<strong>Prices or a price guide:</strong> even a "from" figure saves both sides time and builds trust.',
            '<strong>About:</strong> who is behind the business, where you are based and why someone should choose you. Real photos help.',
            '<strong>Contact:</strong> a form, your phone number, your email, your opening hours and the area you cover.']),
        P('A sole trader can start with fewer. A business with several services or locations will need more, usually one page for each service and each area served.'),
        H('Things every page should have'),
        ul(['Your phone number or main button visible without scrolling.', 'Text large enough to read on a phone without zooming.', 'Photos of your real work, premises or products.', 'A clear page title that says what the page is about, because that is what shows in Google.', 'A link back to the home page and to the contact page.']),
        inline_cta('Need a website built?', 'Our website plans run from one page to ten at a fixed price, built for phones first, with two rounds of design changes.', 'See website plans', '/services/website-development/#plans'),
        H('An enquiry form that works'),
        P('A surprising number of small business forms send messages to an old address or straight to spam. Keep the form short: name, contact details and message. Test it yourself after launch and again every few months. Add a note saying how quickly you reply, and then reply that quickly.'),
        H('Speed'),
        P('A slow page loses people before they read a word. The usual cause is oversized images. Photos straight from a camera can be twenty times larger than they need to be. Compress them, use modern image formats and switch on caching. You can test any page free with Google\'s PageSpeed Insights.'),
        H('The legal basics for a UK business website'),
        P('UK businesses must show certain details on their website. A sole trader should show their own name and a business address. A limited company must show its registered name, company number and registered office. You also need a privacy policy if you collect personal details through a form, and you must ask for consent before using non-essential cookies such as analytics. If you sell online, terms and a refund policy are expected.'),
        H('What you can leave out'),
        P('Sliders, autoplay video, pop-ups, music, a news section you will never update and long "mission statements" all slow a site down or get in the way. If something does not help a visitor decide or get in touch, leave it out.'),
        H('Frequently asked questions'),
        g['faq']([('How many pages does a small business website need?', 'Most need about five: home, services, prices, about and contact. Add a page for each main service or area once the basics are in place.'),
                  ('Should I build it myself?', 'You can, with a website builder, if you have the time. The usual weak spots on self-built sites are speed, wording and the enquiry form, so test those carefully.'),
                  ('Do I need a blog?', 'Only if you will keep it up. A few useful guides that answer customers\' questions are worth more than a news page last updated two years ago.'),
                  ('Which platform should I use?', 'WordPress suits most small businesses because you own it and any developer can work on it. A hosted builder is simpler if you want no technical upkeep at all.')]),
        inline_cta('Want a second opinion on your current site?', 'Send us the address. We will look at it on a phone, the way your customers do, and tell you what we would change first.', 'Get my free growth check', GROWTH),
    ), 'What a small business website should include: the first screen, the five pages most businesses need, forms, speed and the UK legal basics.', 'Websites')

    post('social-media-post-ideas-small-business', '20 social media post ideas for small businesses', join(
        takeaways(['Post regularly and usefully, and do not chase viral hits.', 'Mix four kinds of post: helpful, proof, personal and offers.', 'Three posts a week is enough for most small businesses.', 'Real photos from your phone do better than stock images.']),
        P('The hardest part of social media for a small business is not the posting. It is knowing what to post. This list gives you twenty ideas that work for almost any trade, grouped into four types, plus a simple weekly routine for using them.'),
        H('Helpful posts'),
        P('These answer the questions customers ask you every week. They show you know your trade and they are the posts people save and share.'),
        ul(['1. A quick tip related to your trade.', '2. The answer to a question you were asked this week.', '3. A common mistake and how to avoid it.', '4. A seasonal reminder: boilers in autumn, gardens in spring.', '5. What something typically costs and what changes the price.']),
        H('Proof posts'),
        P('People trust what they can see. Proof posts show the work and the customers behind it.'),
        ul(['6. A before and after photo.', '7. A finished job, with a line about what was involved.', '8. A customer review, with their permission.', '9. A short video of a job in progress.', '10. A problem you were called in to fix.']),
        H('Personal posts'),
        P('Small businesses have an advantage over big ones: real people. Use it.'),
        ul(['11. Who you are and why you started.', '12. Meet a member of the team.', '13. A look behind the scenes: the van, the workshop, the kitchen.', '14. A local event, charity or supplier you support.', '15. A milestone, such as an anniversary or a new piece of kit.']),
        H('Offer posts'),
        P('Keep these to about one post in five. A feed that only sells gets ignored.'),
        ul(['16. A limited-time offer with a clear end date.', '17. A new service or product.', '18. Availability: "two slots left this month".', '19. A reminder of something people forget you do.', '20. A simple call to action: how to book, call or order.']),
        inline_cta('No time to make the posts?', 'We can plan a month of content for you to post, or design, schedule and manage it all.', 'See social media plans', '/services/social-media-management/#plans'),
        H('A simple weekly routine'),
        P('You do not need to post every day. Three good posts a week, every week, beats a burst of ten followed by a month of silence. A routine that works for most small businesses:'),
        ul(['<strong>Monday:</strong> a helpful post.', '<strong>Wednesday:</strong> a proof post.', '<strong>Friday:</strong> a personal post, with an offer in its place every other week.']),
        P('Plan a month at a time. Sit down once, choose twelve ideas from the list above and note which photos you need. Then take those photos during normal working days. Most platforms let you schedule posts in advance for free through their business tools.'),
        H('Which platforms to use'),
        P('Go where your customers are. For most local businesses that means Facebook and Instagram. LinkedIn suits businesses that sell to other businesses. TikTok suits visual trades and younger audiences. It is better to look after one or two pages well than to spread yourself across five.'),
        H('What to avoid'),
        ul(['Buying followers or likes. They are not real customers and can get your account restricted.', 'Posting only offers.', 'Stock photos when you could use your own.', 'Ignoring comments and messages. Reply within a day.', 'Arguing with unhappy customers in public. Reply once, politely, and move it to a private message.']),
        H('Frequently asked questions'),
        g['faq']([('How often should a small business post?', 'Two or three times a week is enough for most. Being consistent matters more than how often.'),
                  ('What time should I post?', 'When your customers are online, which is often early morning, lunchtime or evening. Each platform\'s business tools show when your own followers are active.'),
                  ('Do hashtags still matter?', 'A few relevant ones help people find a post, especially local ones such as your town and trade. Long lists of generic hashtags do not.'),
                  ('Should I use the same post on every platform?', 'You can reuse the idea, but adjust the size and wording. What reads well on LinkedIn is usually too formal for Instagram.')]),
        inline_cta('Want to know how your pages look to a customer?', 'Ask for a free growth check and we will review your social pages, Google listing and website.', 'Get my free growth check', GROWTH),
    ), '20 social media post ideas for small businesses in four groups (helpful, proof, personal, offers), with a simple weekly posting routine.', 'Social media')

    post('how-to-ask-customers-for-reviews', 'How to ask customers for reviews (and stay within UK rules)', join(
        takeaways(['Ask every customer, soon after the job, and make it one tap to do.', 'Never pay for reviews, offer rewards or ask only the happy customers.', 'Fake reviews are banned under UK consumer law, not just by Google.', 'Reply to every review, good or bad.']),
        P('Reviews do two jobs for a local business. They help Google decide who to show on the map, and they help a stranger decide whether to call you. Most happy customers will leave one if you ask at the right moment and make it easy. This guide covers how to ask, what to say and what the rules do not allow.'),
        H('When to ask'),
        P('Ask when the customer is most pleased: straight after the job is finished, the order has arrived or the problem is fixed. A week later the moment has passed. If you see customers in person, ask in person and then send the link so they do not have to search for you.'),
        H('Make it one tap'),
        P('In your Google Business Profile, choose "Ask for reviews" to get a short link that opens the review box directly. Put that link in a text message or email, on your invoices and receipts, and as a QR code on a card or at the till. Every extra step you remove means more reviews.'),
        H('What to say'),
        P('Keep the message short, personal and free of pressure. For example: "Thanks for choosing us today. If you have a minute, a Google review really helps a small business like ours. Here is the link." Do not suggest what to write or which star rating to give.'),
        inline_cta('Want this set up for you?', 'Our Local SEO Growth plan includes a review request plan with message templates you can send.', 'See Local SEO plans', '/services/local-seo/#plans'),
        H('What the rules do not allow'),
        ul(['<strong>Paying for reviews or offering rewards.</strong> No discounts, gifts or prize draws in return for a review.', '<strong>Review gating.</strong> Asking only the customers you expect to be positive, or sending unhappy ones somewhere else first.', '<strong>Writing your own.</strong> That includes staff, family and friends posing as customers.', '<strong>Buying reviews.</strong> From anyone, anywhere.', '<strong>Hiding the bad ones.</strong> If you show reviews on your own website, you should not publish only the favourable ones in a misleading way.']),
        P('These are Google\'s rules, and breaking them can get reviews removed or a profile suspended. They are also UK law. Since April 2025 the Digital Markets, Competition and Consumers Act has banned fake reviews and reviews that hide the fact they were paid for, and businesses are expected to take reasonable steps to make sure the reviews they publish are genuine.'),
        H('Reply to every review'),
        P('Thank people for good reviews, briefly and by name. For a bad review, reply once, calmly: acknowledge the problem, say what you have done or will do, and invite them to contact you directly. Future customers read your replies as closely as the reviews themselves. Never argue, and never reveal private details about the customer.'),
        H('If a review is fake or abusive'),
        P('You can report a review to Google from your profile if it breaks their policies, for example if it is spam, offensive or clearly about a different business. Google will not remove a review just because it is negative.'),
        H('Frequently asked questions'),
        g['faq']([('How many reviews do I need?', 'There is no magic number. A steady flow of recent reviews matters more than a large total collected years ago.'),
                  ('Can I ask customers to mention my town or service?', 'It is better not to tell people what to write. Reviews in customers\' own words are more convincing, and scripted ones can look fake.'),
                  ('Can I copy Google reviews on to my website?', 'You can quote them. Keep the wording exact, do not pick only the best in a misleading way, and make sure they are genuine.'),
                  ('Should I use Trustpilot as well?', 'Start with Google, because that is where local customers look. Add another platform only if your customers use it.')]),
        inline_cta('Not sure how your reviews look to a new customer?', 'Ask for a free growth check and we will look at your Google listing, website and social pages.', 'Get my free growth check', GROWTH),
    ), 'How to ask customers for Google reviews: when to ask, what to say, how to make it one tap, and what Google and UK law do not allow.', 'Local SEO')

    post('google-ads-vs-facebook-ads', 'Google Ads or Facebook ads: which should a small business try first?', join(
        takeaways(['Google Ads reaches people who are already searching for what you sell.', 'Facebook and Instagram ads reach people who were not looking, by area and interest.', 'Urgent and searched-for services usually suit Google. Visual and impulse buys usually suit Facebook and Instagram.', 'Whichever you choose, set up tracking first and start small.']),
        P('Small businesses often ask which advertising platform is better. Neither is. They work in different ways and suit different kinds of purchase. The useful question is how your customers buy.'),
        H('How Google Ads works'),
        P('You choose search terms, and your advert appears when someone types them into Google. You pay when they click. The strength is intent: a person searching "emergency locksmith near me" wants one now. The weakness is that you can only reach people who are already searching, and popular search terms can be expensive.'),
        H('How Facebook and Instagram ads work'),
        P('You choose an audience by location, age and interests, and your advert appears in their feed, stories or reels. Nobody asked to see it, so it has to catch the eye. The strength is reach: you can put a new product or offer in front of people who have never heard of you. The weakness is that most of them are not ready to buy today.'),
        H('Which suits your business'),
        ul(['<strong>People need you urgently</strong> (plumber, locksmith, vet): Google Ads.', '<strong>People research before buying</strong> (solicitor, builder, accountant): Google Ads, with a page that answers their questions.', '<strong>People buy when they see it</strong> (food, fashion, gifts, beauty): Facebook and Instagram.', '<strong>Something new that nobody searches for yet:</strong> Facebook and Instagram.', '<strong>A local event or offer with a deadline:</strong> Facebook and Instagram, targeted by area.', '<strong>You sell to other businesses:</strong> Google Ads first.']),
        inline_cta('Want it set up properly?', 'We build and manage Google Ads and Facebook ads campaigns for small budgets. You pay the platform directly for ad spend.', 'See Google Ads plans', '/services/ppc-management/#plans'),
        H('What you need before spending anything'),
        ul(['<strong>Tracking.</strong> Know which clicks became calls, forms or sales. Without it you are guessing.', '<strong>A page that matches the advert.</strong> If the advert says "boiler servicing in Leeds", the page should too.', '<strong>A way to respond quickly.</strong> Enquiries from adverts go cold fast.', '<strong>A budget you can afford to learn with.</strong> The first weeks show you what works. Treat them as a test.']),
        H('Can you use both?'),
        P('Yes, and many businesses do. A common pattern is Google Ads to catch people searching, and Facebook or Instagram adverts shown to people who visited your website and did not buy. Start with one, get it paying for itself, then add the other.'),
        H('Common mistakes'),
        ul(['Sending every click to the home page.', 'Choosing broad search terms that attract people looking for jobs or free advice.', 'Boosting posts and calling it advertising.', 'Changing everything after three days, before there is enough data to judge.', 'Never checking which searches or audiences actually produced customers.']),
        H('Frequently asked questions'),
        g['faq']([('Which is cheaper?', 'It depends on your trade and area. A click on Google often costs more, but the person is closer to buying. Compare the cost of a customer, not the cost of a click.'),
                  ('How long before I know if it is working?', 'Give a new campaign about a month with tracking in place before you judge it.'),
                  ('Do I need a big budget?', 'No. Both platforms let you set a small daily limit. It is better to target one service in one area well than to spread a small budget thinly.'),
                  ('What about TikTok?', 'It can work well for visual products and younger audiences. See our <a href="/services/tiktok-ads/">TikTok ads</a> page.')]),
        inline_cta('Not sure where your budget should go?', 'Tell us what you sell and where. We will say which platform we would test first, and why.', 'Get my free growth check', GROWTH),
    ), 'Google Ads or Facebook ads for a small business: how each works, which kinds of business suit which, and what to set up before spending.', 'Advertising')

    post('how-seo-is-priced', 'How SEO is priced: hourly, monthly retainer or fixed plan', join(
        takeaways(['SEO is sold three ways: by the hour, on a monthly retainer or as fixed-price plans.', 'Whichever you choose, ask what will actually be done and how you will see it.', 'Be wary of guaranteed positions, long contracts and prices that are never written down.', 'Start with the one job most likely to matter, then build from there.']),
        P('Prices for SEO vary enormously, and the reason is simple: "SEO" is not one thing. It covers fixing a website, rewriting pages, writing articles, building links and more. Before comparing prices, it helps to understand the three ways the work is sold.'),
        H('Hourly or day rates'),
        P('You pay for time, usually from a freelancer or consultant. This suits one-off advice, a specific problem or a second opinion. The risk is that you do not know the total in advance, so agree a limit and ask for a short written summary of what was done.'),
        H('Monthly retainers'),
        P('You pay a fixed amount each month and the agency works on your site continuously. This is the traditional model and suits businesses that need steady, ongoing work. The risks are the ones small businesses complain about most: contracts of six or twelve months, vague monthly reports and no clear list of what was done.'),
        H('Fixed-price plans'),
        P('You buy a defined piece of work at a published price: an audit, a set of pages improved, a number of directory listings, a month of defined tasks. You know the cost and the scope before you start. The limit is that a plan covers what it lists and nothing more, so it suits jobs that can be clearly described.'),
        P('This is how we sell SEO. Our <a href="/services/local-seo/">Local SEO</a> plans start [ifm_from service="local-seo" prefix="from " fallback="at a fixed price"], and <a href="/services/monthly-seo/">Monthly SEO</a> is sold one month at a time.'),
        H('What changes the price'),
        ul(['<strong>The size of your site.</strong> Fifty pages take longer to check and improve than five.', '<strong>How competitive your market is.</strong> A solicitor in a city needs more work than a village cafe.', '<strong>How much is done for you.</strong> Advice costs less than hands-on work.', '<strong>Content and links.</strong> Writing and placing them takes real time, so plans that include them cost more.', '<strong>The state of the site.</strong> A slow, broken site needs fixing before anything else will help.']),
        inline_cta('Want to see real prices?', 'Every plan we sell has its price, delivery time and scope on the page.', 'See plans and prices', '/plans/'),
        H('Questions to ask before you pay'),
        ul(['What exactly will be done this month?', 'How will I see the work: links, a change log, a report?', 'Who does the work?', 'Is there a contract, and how do I stop?', 'What do you need from me?', 'What will you not promise?']),
        H('Warning signs'),
        ul(['A guaranteed position on Google. Nobody controls Google.', 'Hundreds of links for very little money. These are usually automated and can do harm.', 'A long contract before any work has been shown.', 'Reports full of charts with no list of tasks.', 'Being asked for your passwords.']),
        H('Where to start on a small budget'),
        P('Pick the one job most likely to matter. For a local trade, that is usually your Google Business Profile and directory listings. For a site that gets visitors but few enquiries, it is the pages themselves. If you do not know what is wrong, an audit tells you where to spend first.'),
        H('Frequently asked questions'),
        g['faq']([('Is cheap SEO a waste of money?', 'Not if it is a clearly defined job done properly. Be suspicious when a low price is attached to a big promise.'),
                  ('Do I have to pay every month?', 'No. One-off jobs such as an audit or a profile clean-up are useful by themselves. Ongoing work helps most in competitive markets.'),
                  ('How long until it pays for itself?', 'It varies with your market. Some changes show within weeks, and most SEO builds over several months.'),
                  ('Can I do it myself?', 'Much of it, yes, if you have the time. Our guides cover the basics, and a consultation can give you a plan to follow.')]),
        inline_cta('Not sure what you need?', 'Ask for a free growth check and we will tell you the three things we would fix first.', 'Get my free growth check', GROWTH),
    ), 'How SEO is priced for small businesses: hourly rates, monthly retainers and fixed-price plans, what changes the price and questions to ask.', 'SEO')

    post('what-is-a-citation-local-seo', 'What is a citation? Name, address and phone consistency explained', join(
        takeaways(['A citation is any online mention of your business name, address and phone number.', 'Google compares citations with your own profile to decide whether to trust your details.', 'Consistent details matter more than a large number of listings.', 'Fix the well-known directories first, then add trade-specific ones.']),
        P('If you have looked into local SEO, you will have met the word "citation" and the letters NAP. Both are simpler than they sound, and getting them right is one of the cheapest ways to help a local business appear on Google Maps.'),
        H('What a citation is'),
        P('A citation is a mention of your business details on another website: a directory such as Yell, a map service such as Apple Maps or Bing Places, a review site, a trade body or a local chamber of commerce. It usually includes your name, address and phone number, and often your website, opening hours and a description.'),
        H('What NAP means'),
        P('NAP stands for name, address and phone number. "NAP consistency" means those three things appear in exactly the same form wherever your business is listed.'),
        H('Why consistency matters'),
        P('Google wants to show businesses that really exist, at the address and phone number shown. It checks your Google Business Profile against what it finds elsewhere. When everything matches, it can be confident. When one site has your old phone number and another has a different spelling of your name, it has less reason to trust any of them.'),
        P('It matters to customers too. An old number on a directory is a lost call.'),
        H('What counts as inconsistent'),
        ul(['An old address or phone number after a move.', 'Different versions of the name: "Smith Plumbing", "Smith Plumbing Ltd", "Smiths Plumbers".', 'A mobile number in one place and a landline in another.', 'A tracking phone number used on some listings.', 'Duplicate listings for the same business on one site.']),
        P('Small formatting differences, such as "Street" and "St", are not worth worrying about. Different numbers, names and addresses are.'),
        inline_cta('Want your listings sorted out?', 'Local SEO Starter includes 20 UK directory listings with matching details, and a report with the live link to each.', 'See Local SEO plans', '/services/local-seo/#plans'),
        H('How to check your own citations'),
        ul(['Decide the one correct version of your name, address and phone number. Write it down.', 'Search Google for your business name, then your phone number, then any old phone numbers.', 'Open each result and note where the details are wrong.', 'Claim and correct the listings, starting with the best-known sites.', 'Remove or merge duplicates where the site allows it.']),
        H('Which directories matter'),
        P('Start with the places people and search engines actually use: Google Business Profile, Bing Places, Apple Maps, Yell and Facebook. Then add directories specific to your trade or town, where a listing also brings real customers. A few dozen good, accurate listings do more than hundreds of obscure ones.'),
        H('If you do not have a public address'),
        P('Many trades work from home or travel to customers. You can hide your address on Google and list the areas you serve. On directories that insist on an address, use the same one every time, or choose directories that support service-area businesses.'),
        H('Frequently asked questions'),
        g['faq']([('How many citations do I need?', 'There is no fixed number. Cover the main sites accurately, then add relevant trade and local directories.'),
                  ('Do citations help me rank?', 'They support your position in local results by confirming your details. They work alongside your Google profile, reviews and website. Nobody can promise a position.'),
                  ('I have moved. What should I do?', 'Update your Google Business Profile first, then your website, then every listing you can find. Search for the old address and phone number to catch the ones you have forgotten.'),
                  ('Are paid directories worth it?', 'Occasionally, for a respected trade body. Most of the benefit comes from free listings on well-known sites.')]),
        inline_cta('Want to know how consistent your details are?', 'Ask for a free growth check and we will look at how your business appears across Google and the main directories.', 'Get my free growth check', GROWTH),
    ), 'What a citation is, what NAP consistency means and why it matters for local SEO, with a simple way to check and fix your own listings.', 'Local SEO')
