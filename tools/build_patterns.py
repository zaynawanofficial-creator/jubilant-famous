"""Builds the IamFamous.uk pages as block patterns, the footer part and the site manifest.

Output:
  wp-content/themes/iamfamous/patterns/page-*.php    (one editable block pattern per page)
  wp-content/themes/iamfamous/patterns/hero-insights.php
  wp-content/themes/iamfamous/parts/footer.html
  wp-content/themes/iamfamous/inc/site-manifest.php  (pages the Setup screen creates)
Run: python3 tools/build_patterns.py   (from the repo root or from tools/)

Content rules (CLAUDE.md, LESSONS_LEARNED.md section E):
  - Original copy only. Nothing is reused from SEOBooster.uk.
  - No invented clients, results, reviews, years, team sizes or guarantees.
  - Prices are never typed here: [ifm_from ...] and [ifm_packages ...] read them from WooCommerce.
  - UK spelling, second person, short paragraphs.
Round 1 (theme 1.0.0): Home, Services hub, Local SEO, Website Development, Social Media Management,
Free growth check, Contact.
Round 2 (theme 1.1.0): the other 17 service pages (data in services_seo.py and services_more.py).
Still to come: Packages, About, FAQ, legal pages and guides.
"""
import os
import sys
from blocks import (group, heading, para, eyebrow, lead, ul, buttons, image, columns, column, details,
                    shortcode, join, wrap)

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.join(HERE, '..', 'wp-content', 'themes', 'iamfamous')
PAT = os.path.join(ROOT, 'patterns')
os.makedirs(PAT, exist_ok=True)

GROWTH = '/free-growth-check/'
EMAIL = 'support@iamfamous.uk'

# ---------------------------------------------------------------- services
# Live service pages (round 1): slug == product_cat slug.
LIVE = {
    'local-seo': dict(
        name='Local SEO', icon='local', art='art-local.webp', group='seo',
        short='Show up when people nearby search for what you do: a sorted Google Business Profile, correct UK directory listings and a site tuned for your area.',
        points=['Google Business Profile set up properly', 'Listings on the UK directories that matter', 'A report with every live link']),
    'website-development': dict(
        name='Website Development', icon='webdev', art='art-webdev.webp', group='web',
        short='A WordPress website built for phones first, with your wording in place, a working enquiry form and a walkthrough so you can edit it yourself.',
        points=['1 to 10 pages at a fixed price', 'Two rounds of design changes', 'Yours to keep and edit']),
    'social-media-management': dict(
        name='Social Media Management', icon='social', art='art-social.webp', group='ads',
        short='Regular posts in your colours and your voice. Take a plan and post it yourself, or hand over the designing, scheduling and replies.',
        points=['A month of posts planned ahead', 'Designed, captioned and scheduled', 'Real followers only, never bought']),
}

LEAD = list(LIVE)  # the three lead offers keep their illustrated cards

# Round 2 services: full page data in services_seo.py / services_more.py.
sys.path.insert(0, HERE)
from services_seo import SERVICES as _SEO
from services_more import SERVICES as _MORE
ROUND2 = _SEO + _MORE
for _s in ROUND2:
    LIVE[_s['slug']] = dict(name=_s['name'].replace('&', '&amp;'), icon=_s['icon'], art=f"art-{_s['slug']}.webp", group=_s['group'], short=_s['short'], points=[])

GROUPS = [
    ('seo', 'Get found on Google', 'For when people search for what you sell and find someone else.'),
    ('web', 'Websites', 'A site that loads fast on a phone and makes it easy to get in touch or buy.'),
    ('ads', 'Ads and social media', 'Reach people now with adverts, and stay visible where your customers already spend their time.'),
    ('content', 'Words and design', 'The wording and visuals that make everything else look professional.'),
]
ORDER = ['local-seo', 'monthly-seo', 'off-page-seo', 'seo-audit', 'technical-seo', 'on-page-seo', 'ecommerce-seo', 'ai-seo', 'white-label-seo', 'seo-consultancy',
         'website-development', 'ecommerce-website-development', 'website-maintenance', 'website-migration',
         'social-media-management', 'ppc-management', 'facebook-ads', 'tiktok-ads', 'content-writing', 'graphic-design']
assert sorted(ORDER) == sorted(LIVE), set(ORDER) ^ set(LIVE)


def img(file, alt='', cls=''):
    return image(file, alt, cls)


def svc_card(slug):
    s = LIVE[slug]
    return group(
        img(s['art'], '', 'ifm-card__art'),
        heading(f'<a href="/services/{slug}/">{s["name"]}</a>', 3),
        para(s['short']),
        ul(s['points'], 'ifm-ticks ifm-ticks--sm'),
        para(f'[ifm_from service="{slug}" prefix="From " fallback="Fixed price, agreed in writing"]', 'ifm-price-from'),
        para(f'<a href="/services/{slug}/">See {s["name"]} packages →</a>', 'ifm-card__more'),
        cls='ifm-card ifm-card--svc', layout='flow')


def mini_card(slug):
    """Compact service card: icon, name, one line, from-price, link."""
    s = LIVE[slug]
    plain = s['name'].replace('&amp;', 'and')
    return group(
        img(f'icon-{s["icon"]}.svg', '', 'ifm-card__icon'),
        heading(f'<a href="/services/{slug}/">{s["name"]}</a>', 3),
        para(s['short']),
        para(f'[ifm_from service="{slug}" prefix="From " fallback="Fixed price, agreed in writing"]', 'ifm-price-from'),
        para(f'<a href="/services/{slug}/">See packages<span class="screen-reader-text"> for {plain}</span> →</a>', 'ifm-card__more'),
        cls='ifm-card ifm-card--mini', layout='flow')


def card(title, text, icon=None, cls='ifm-card'):
    parts = [img(f'icon-{icon}.svg', '', 'ifm-card__icon')] if icon else []
    return group(*parts, heading(title, 3), para(text) if isinstance(text, str) else text, cls=cls, layout='flow')


def section(*children, cls='', anchor='', tag='section'):
    return group(*children, align='full', cls=('ifm-section ' + cls).strip(), anchor=anchor, tag=tag)


def head(eb, title, sub=None, center=False):
    parts = [eyebrow(eb) if eb else '', heading(title), lead(sub) if sub else '']
    return group(*parts, cls='ifm-section-head' + (' ifm-center' if center else ''), layout='flow', align='wide')


def grid(*children, n=4, cls=''):
    """Fixed-column responsive grid. CSS: .ifm-grid--N (N columns on desktop, 2 on tablets, 1 on phones)."""
    return group(*children, align='wide', cls=f'ifm-grid ifm-grid--{n} {cls}'.strip(), layout='flow')


def steps(items):
    """Numbered steps. Numbers are typed out (CSS counters break inside WordPress grid layouts)."""
    return grid(*[group(para(f'{i + 1:02d}', 'ifm-step__n'), heading(t, 3), para(d), cls='ifm-card ifm-step', layout='flow')
                  for i, (t, d) in enumerate(items)], n=4, cls='ifm-steps')


def faq(items):
    return group(*[details(q, a) for q, a in items], cls='ifm-faq', layout='constrained')


def cta(title='Not sure where to start?',
        text='Send us your website or your Google listing. We will look at how you appear on Google, on your site and on social media, and email back the three things we would fix first. It is free and there is nothing to sign.',
        btns=(('Get my free growth check', GROWTH), ('See all services', '/services/', 'outline'))):
    return section(group(columns(column(heading(title), para(text), buttons(*btns), width='62%'),
                                 column(img('art-growth.webp', '', 'ifm-cta__art'), width='38%', cls='ifm-hide-mobile'),
                                 align='', center=True, cls='ifm-cta__cols'),
                         align='wide', cls='ifm-cta', layout='flow'), cls='ifm-section--tight')


def hero(eb, title, sub, btns, checks, art, alt, crumbs=False, cls=''):
    left = column(*([shortcode('[ifm_breadcrumbs]')] if crumbs else []), eyebrow(eb), heading(title, 1), lead(sub), buttons(*btns),
                  ul(checks, 'ifm-checks'), width='54%')
    right = column(img(art, alt, 'ifm-hero__art'), width='46%')
    return group(columns(left, right, cls='ifm-hero__cols', center=True), align='full', cls=('ifm-hero ' + cls).strip(), tag='section')


def page_hero(art, alt, *blocks):
    left = column(shortcode('[ifm_breadcrumbs]'), *blocks, width='56%')
    right = column(img(art, alt, 'ifm-hero__art'), width='44%')
    return group(columns(left, right, cls='ifm-hero__cols', center=True), align='full', cls='ifm-hero ifm-hero--page', tag='section')


def proof(art, alt, eb, title, text, bullets, note='Example layout. Yours shows your own business, links and dates.', cls='', flip=False):
    """Split section: what the delivery looks like + what it contains."""
    a = column(img(art, alt, 'ifm-proof__art'), width='50%')
    b = column(eyebrow(eb), heading(title), para(text), ul(bullets, 'ifm-ticks'), para(note, 'ifm-muted'), width='50%')
    return section(columns(*((b, a) if flip else (a, b)), cls='ifm-split' + (' ifm-split--flip' if flip else ''), center=True), cls=cls)


PAGES = []


def write_pattern(name, title, content, cat='iamfamous-pages', post_types='page', inserter='yes'):
    hdr = (f"<?php\n/**\n * Title: {title.replace('&amp;', '&')}\n * Slug: iamfamous/{name}\n * Categories: {cat}\n"
           f" * Post Types: {post_types}\n * Inserter: {inserter}\n * Description: Generated by tools/build_patterns.py\n */\n?>\n")
    with open(os.path.join(PAT, f'{name}.php'), 'w') as f:
        f.write(hdr + content + '\n')


def page(slug, title, content, excerpt, seo_title, parent='', template='landing', menu_order=0):
    assert len(excerpt) <= 158, (slug, len(excerpt))
    PAGES.append(dict(slug=slug, title=title, parent=parent, template=template, excerpt=excerpt, pattern=f'iamfamous/page-{slug}', order=menu_order, seo_title=seo_title))
    write_pattern(f'page-{slug}', f'Page: {title}', content)


HOW_STEPS = [
    ('Pick a package', 'Each one lists what you get, what you do not, how long it takes and what it costs. No call needed.'),
    ('Tell us about your business', 'A short form on the package page asks for the basics: your website, your area, your logins by invite. Two minutes, before you pay.'),
    ('Pay in pounds', 'Check out securely in one step. No VAT is added and nothing renews unless you order again.'),
    ('Get work you can check', 'We deliver inside the time shown on the package, with live links, files or a report. Changes are included where the package says so.'),
]

# ================================================================ HOME
home = join(
    hero('Fixed prices · UK small businesses',
         'Get seen. Get chosen. <span class="ifm-hl">Get famous.</span>',
         'Local SEO, websites and social media for UK small businesses. Pick a fixed-price package, tell us about your business and get work you can check for yourself.',
         [('See services', '/services/'), ('Free growth check', GROWTH, 'outline')],
         ['No contracts', 'Priced in pounds, no VAT added', 'A report with every order'],
         'hero-home.webp', 'Three example panels: a Google map result for a local business, a phone showing a small business website, and a planned week of social media posts', cls='ifm-hero--home'),
    group(group(
        group(img('icon-pound.svg', ''), para('<strong>The price is the price</strong>Shown on the package before you pay. No VAT, no setup fee.'), layout='flow'),
        group(img('icon-scope.svg', ''), para('<strong>A written scope</strong>What is included, what is not and what we need from you.'), layout='flow'),
        group(img('icon-report.svg', ''), para('<strong>Proof you can open</strong>Live links, files and reports instead of a vague summary.'), layout='flow'),
        group(img('icon-shield.svg', ''), para('<strong>Refund before we start</strong>Change your mind before work begins and get it all back.'), layout='flow'),
        align='wide', cls='ifm-trust', layout='flow'), align='full', cls='ifm-trustbar', layout='constrained', tag='section'),
    section(head('Start here', 'Three jobs that bring in customers',
                 'Most small businesses need the same three things first: to be found nearby, to have a website that turns visitors into enquiries, and to look active where their customers scroll.'),
            grid(*[svc_card(s) for s in LIVE], n=3, cls='ifm-grid--lead')),
    section(head('Most popular first order', 'Local SEO packages',
                 'Pick a tier on the left to see what it includes. Every package is a one-off order: there is no contract and nothing renews by itself.', center=True),
            group(shortcode('[ifm_packages service="local-seo"]'), align='wide', layout='flow'),
            para('<a href="/services/local-seo/">How our local SEO works →</a>', 'ifm-center ifm-card__more'),
            cls='ifm-section--cream', anchor='packages'),
    proof('report-local.webp', 'Example delivery report: a table of directory listings, each with a live link and a status, above a list of changes made to a Google Business Profile',
          'What you get back', 'Every order ends with something you can open',
          'You should never have to take our word for it. When an order is finished you get the links, the files or the report, and you can click through and check each line.',
          ['A live link for every listing or page we create', 'Before and after notes for every change we make', 'Design and website files handed over to you', 'Sent by email and saved in your account']),
    section(head('How it works', 'Order in minutes, without a sales call', None, center=True), steps(HOW_STEPS), cls='ifm-section--cream'),
    section(columns(
        column(eyebrow('Why IamFamous'), heading('Built for small budgets and busy owners'),
               lead('You run the business. You should not need to learn marketing jargon to buy help with it.'),
               ul(['<strong>Plain English.</strong> If we have to use a technical term, we explain it in one line.',
                   '<strong>Buy one thing at a time.</strong> Start with a single package and add more when it has paid for itself.',
                   '<strong>You keep everything.</strong> Your website, your listings and your social accounts stay in your name.',
                   '<strong>We ask by invite.</strong> We work through user invites and business tools, so your passwords stay with you.'], 'ifm-ticks'),
               width='55%'),
        column(group(heading('What we will not do', 3),
                     ul(['Sell followers, likes or views. They are fake and they get accounts restricted.',
                         'Promise a position on Google. Nobody controls Google, and anyone who says they do is guessing.',
                         'Tie you into a contract or renew anything without you ordering it.',
                         'Start extra work without telling you the price first.'], 'ifm-crosses'),
                     cls='ifm-panel', layout='flow'), width='45%'),
        cls='ifm-split', center=True)),
    section(head('More services', 'Everything else a small business gets asked about',
                 'Twenty services in all, each sold the same way: a package, a price, a delivery time and a list of what you get.'),
            grid(*[mini_card(x) for x in ['monthly-seo', 'seo-audit', 'off-page-seo', 'ppc-management', 'facebook-ads', 'ecommerce-website-development', 'content-writing', 'graphic-design']], n=4),
            para('<a href="/services/">See all 20 services →</a>', 'ifm-center ifm-card__more'),
            cls='ifm-section--rose'),
    section(head('Questions', 'Before you order', None, center=True), faq([
        ('Do I have to sign a contract?', 'No. Every package is a single order. Monthly packages cover one month and stop unless you order another.'),
        ('Is VAT added at checkout?', 'No. IamFamous is not VAT registered, so no VAT is added. The price on the package is the amount you pay.'),
        ('How soon does work start?', 'Each package shows its delivery time. That time starts once we have the details we ask for on the package page, so filling in the form properly is the fastest way to get going.'),
        ('Will this get me to the top of Google?', 'We cannot promise a position and neither can anyone else. What we do promise is the work listed on the package, done properly, with proof you can check.'),
        ('What if I change my mind?', 'Tell us before we start and you get a full refund. Once work is under way we will agree what has been done and refund the rest fairly.'),
        ('Do you need my passwords?', 'No. We ask you to invite us as a user or manager on Google, WordPress or your social accounts. You can remove us whenever you like.'),
    ])),
    cta(),
)
page('home', 'Home', home,
     'Fixed-price local SEO, websites and social media for UK small businesses. Clear packages in pounds, no contracts and a report you can check on every order.',
     'IamFamous | Local SEO, Websites &amp; Social Media for UK Small Businesses'.replace('&amp;', '&'))

# ================================================================ SERVICES HUB
hub_sections = []
for i, (key, title, sub) in enumerate(GROUPS):
    slugs = [x for x in ORDER if LIVE[x]['group'] == key]
    hub_sections.append(section(head('', title, sub), grid(*[mini_card(x) for x in slugs], n=3), cls='ifm-section--cream' if i % 2 == 0 else '', anchor=f'group-{key}'))
hub = join(
    page_hero('art-services.webp', 'Example service menu with four groups (Google, websites, ads and social, words and design) beside a package card with a price and a Choose button',
              eyebrow('Services'), heading('Marketing help you can buy like anything else', 1),
              lead('Every service is sold as a package with a price, a delivery time and a list of what you get. Choose one below, or ask us which would make the biggest difference first.'),
              buttons(('Start with Local SEO', '/services/local-seo/'), ('Free growth check', GROWTH, 'outline')),
              ul(['Fixed prices in pounds', 'One-off orders, no contract', 'A report with every order'], 'ifm-checks')),
    section(head('Start here', 'The three most small businesses need first', None), grid(*[svc_card(x) for x in LEAD], n=3, cls='ifm-grid--lead')),
    *hub_sections,
    section(head('Good to know', 'How to choose'), grid(
        card('Nobody can find you', 'Start with <a href="/services/local-seo/">Local SEO</a>. It is the cheapest way to appear when people nearby search for your trade.', 'local'),
        card('People visit but do not get in touch', 'Your website is the problem. A <a href="/services/website-development/">new site</a> built around one clear action usually fixes it.', 'webdev'),
        card('You look closed online', 'An empty feed puts people off. <a href="/services/social-media-management/">Social media management</a> keeps your pages current.', 'social'),
        n=3), cls='ifm-section--rose'),
    cta(),
)
page('services', 'Services', hub,
     'Twenty fixed-price marketing services for UK small businesses: SEO, websites, ads, social media, content and design. Priced in pounds, no contracts.',
     'Digital Marketing Services for UK Small Businesses | IamFamous', menu_order=1)


# ================================================================ SERVICE PAGES
LEADKEY = {}  # page slug -> key in the lead form's service list, where they differ
def service_page(slug, eb, h1, sub, checks, for_list, not_for, pk_title, pk_sub, whatis, deliver, features, process, needs, faqs, related, excerpt, seo_title, order):
    s = LIVE[slug]
    content = join(
        hero(eb, h1, sub, [('See packages', '#packages'), ('Ask a question', f'{GROWTH}?service={LEADKEY.get(slug, slug)}', 'outline')], checks,
             s['art'], deliver['hero_alt'], crumbs=True),
        section(columns(
            column(group(heading('A good fit if…', 3), ul(for_list, 'ifm-ticks'), cls='ifm-card', layout='flow')),
            column(group(heading('Probably not for you if…', 3), ul(not_for, 'ifm-crosses'), cls='ifm-card', layout='flow')),
        ), cls='ifm-section--tight'),
        section(head('Packages', pk_title, pk_sub, center=True),
                group(shortcode(f'[ifm_packages service="{slug}"]'), align='wide', layout='flow'),
                para(f'Need something bigger or different? <a href="{GROWTH}?service={slug}">Ask for a fixed price</a>.', 'ifm-center ifm-muted'),
                cls='ifm-section--cream', anchor='packages'),
        section(columns(column(eyebrow('The basics'), heading(whatis[0]), width='40%'),
                        column(*[para(x) for x in whatis[1]], width='60%'), cls='ifm-split ifm-whatis')),
        proof(deliver['art'], deliver['alt'], deliver['eb'], deliver['title'], deliver['text'], deliver['bullets'], cls='ifm-section--rose'),
        section(head('What you get', features[0], None),
                grid(*[card(t, d, ic) for (t, d, ic) in features[1]], n=3)),
        section(head('Process', 'From order to delivery', None, center=True), steps(process), cls='ifm-section--cream'),
        section(columns(
            column(eyebrow('Before we start'), heading('What we need from you'),
                   para('You add these on the package page before you pay, so we can start straight away. Please do not send passwords. If we need access we ask for an invite.'),
                   width='45%'),
            column(group(ul(needs, 'ifm-ticks'), cls='ifm-panel', layout='flow'), width='55%'),
            cls='ifm-split', center=True)),
        section(head('Questions', f'{s["name"]}: your questions answered', None, center=True), faq(faqs), cls='ifm-section--cream'),
        section(head('Goes well with', 'Often ordered together', None),
                grid(*[(svc_card(r) if r in LEAD else mini_card(r)) for r in related], n=2, cls='ifm-grid--lead')),
        cta(),
    )
    page(slug, s['name'].replace('&amp;', '&'), content, excerpt, seo_title, parent='services', menu_order=ORDER.index(slug) + 1)


# ---------------------------------------------------------------- Local SEO
service_page(
    'local-seo', 'Local SEO',
    'Local SEO that puts your business on the map',
    'When someone nearby searches for a plumber, a salon or a café, Google shows a map and three businesses. We do the work that gives you a fair shot at being one of them: your Google Business Profile, your directory listings and your website, all saying the same thing.',
    ['[ifm_from service="local-seo" prefix="Packages from " fallback="Fixed price, agreed in writing"]', 'One-off orders, no contract', 'A live link for every listing'],
    ['You serve customers in a town, a city or a set of postcodes',
     'Your Google listing is unclaimed, half-filled or showing the wrong details',
     'Competitors appear on the map and you do not',
     'You have moved, changed number or rebranded and old details are still online'],
    ['You only sell online and do not serve a local area',
     'You want reviews written for you. We will not do that, and Google removes them',
     'You need a guaranteed position by a certain date'],
    'Choose your local SEO package',
    'Start with your Google Business Profile, or take a package that adds directory listings and work on your website. The monthly option keeps things moving after that.',
    ('What is local SEO?', [
        'Local SEO is the work that helps a business appear when people search for something near them: "electrician near me", "dentist in Leeds", "best breakfast Brighton". Google answers these searches with a map and a short list of businesses, usually above the normal results.',
        'Google has said it chooses that list using three things: how relevant you are to the search, how close you are to the person searching, and how well known and trusted your business is. You cannot change where you are. The other two you can improve.',
        'Relevance comes mostly from your Google Business Profile: the right categories, services, opening hours and description. Trust comes from the same name, address and phone number appearing on well-known UK directories, from genuine reviews, and from a website that clearly says what you do and where.',
        'None of this is complicated. It is fiddly and slow to do properly, which is why most small businesses never finish it. That is the job we take off your hands.']),
    dict(art='report-local.webp',
         hero_alt='Example Google Business Profile card for a local business with categories, opening hours and a call button, beside a tracker of UK directory listings marked live',
         alt='Example delivery report: a table of directory listings, each with a live link and a status, above a list of changes made to a Google Business Profile',
         eb='Your report', title='Every listing, with a link you can click',
         text='When your order is finished you get a spreadsheet and a short summary. Nothing is hidden behind a login and nothing needs a tool to read.',
         bullets=['Each directory listing with its live link and the date it went up',
                  'The exact name, address and phone number used, so future listings match',
                  'What we changed on your Google Business Profile, before and after',
                  'A short list of things only you can do, such as adding photos']),
    ('What the work covers', [
        ('Google Business Profile', 'Categories, services, hours, description and posts set up so Google and customers understand what you do.', 'local'),
        ('UK directory listings', 'Your business added to established UK directories with the same details on every one.', 'link'),
        ('Your website, tuned for your area', 'Page titles, headings and contact details that say what you do and where you do it.', 'page'),
        ('Local business code', 'A small piece of code (called schema) that tells Google your address, hours and phone number.', 'wrench'),
        ('A review plan', 'Message templates and a simple routine for asking real customers for reviews, within Google rules.', 'chat'),
        ('A report you can check', 'Every link and every change written down and sent to you.', 'report'),
    ]),
    [('You order', 'Choose a package and fill in the short form with your business details and areas covered.'),
     ('We check what exists', 'We look at your current Google listing and search for old or duplicate listings before adding anything.'),
     ('We do the work', 'Profile, listings and website changes are made by hand, using the same details everywhere.'),
     ('You get the report', 'A spreadsheet of live links and a summary of changes, plus anything you need to do yourself.')],
    ['Business name, address, phone number and opening hours, exactly as you want them shown',
     'Manager access to your Google Business Profile, by invite',
     'The services you offer and the towns or postcodes you cover',
     'Your website address, if you have one',
     'An email address you can check, for directory verification messages'],
    [('How long until I show up on the map?', 'The work is delivered within the time shown on your package. How quickly Google reacts varies: some profile changes show within days, while the effect of new listings builds over several weeks. We cannot promise a position, because Google decides that.'),
     ('I do not have a Google Business Profile yet. Can you create one?', 'Yes. We set it up with you. Google will then ask you to verify the business, usually by a short video, a phone call or a postcard. Only you can do that step, and we will tell you what to expect.'),
     ('I work from home or travel to customers. Does local SEO still work?', 'Yes. Google lets you hide your address and list the areas you cover instead. We set your profile up as a service-area business.'),
     ('Which directories do you use?', 'Established UK directories and map services that accept your type of business, such as Yell, Bing Places and Apple Maps, plus trade-specific sites where they exist. The full list is in your report.'),
     ('Do I need a website?', 'No, the Google Business Profile package works without one. A website helps though, and the Starter and Growth packages include changes to yours. If you do not have one, see our <a href="/services/website-development/">website packages</a>.'),
     ('Can you get me more reviews?', 'We give you a plan and the wording to ask your real customers. We never write, buy or filter reviews. It breaks Google rules and consumer law, and it is your listing that gets suspended.'),
     ('What happens after a one-off package?', 'Nothing, unless you want more. Your listings stay live. If you want regular posts, new listings and position checks, the monthly package covers one month at a time.')],
    ['website-development', 'off-page-seo'],
    'Local SEO for UK small businesses: Google Business Profile, UK directory listings and local website fixes at a fixed price, with a live-link report.',
    'Local SEO Services UK | Google Business Profile &amp; Listings | IamFamous'.replace('&amp;', '&'), 1)

# ---------------------------------------------------------------- Website Development
service_page(
    'website-development', 'Websites',
    'A small business website that wins you enquiries',
    'A clear, fast WordPress website with your wording and photos in place, an enquiry form that reaches your inbox and a walkthrough so you can change things yourself. Choose the number of pages and the price is fixed.',
    ['[ifm_from service="website-development" prefix="Websites from " fallback="Fixed price, agreed in writing"]', 'Two rounds of design changes', 'Yours to keep and edit'],
    ['You have no website, or one you are embarrassed to send people to',
     'Your current site is slow or hard to use on a phone',
     'You want to update text and photos yourself without paying each time',
     'You need a fixed cost you can plan for'],
    ['You need an online shop. That is a separate ecommerce package, so ask us for a price',
     'You need a booking system, members area or other custom feature built in',
     'You want us to write all the text as well. Ask us to add content writing to your order'],
    'Choose your website package',
    'Packages go by number of pages. A page is one screen with its own address, such as Home, About, Services or Contact. If you are unsure, five pages suits most small businesses.',
    ('What does a small business website need?', [
        'A small business website has one job: turn a visitor into an enquiry, a booking or a sale. Most visitors arrive on a phone, give the page a few seconds and decide whether to stay.',
        'So the first screen has to answer three questions without scrolling. What do you do? Where do you do it? How do I get in touch? Everything after that exists to back those answers up: your services, your prices or price guide, photos of real work, and reviews if you have them.',
        'We build on WordPress because it runs a large share of the world\'s websites, any developer can work on it, and you can edit it yourself from a browser. You are not locked in to us or to a monthly website-builder fee.',
        'Speed matters as much as looks. A page that takes too long on a phone signal loses people before they have read a word, so we compress images, switch on caching and test on a real phone before we hand over.']),
    dict(art='report-webdev.webp',
         hero_alt='Example small business website shown on a phone and a laptop, with the business name, a headline, a call button and an enquiry form labelled',
         alt='Example handover checklist for a new website: pages built, enquiry form tested, phone layout checked, search basics set and walkthrough video recorded',
         eb='Handover', title='You get the keys, and a walkthrough',
         text='At the end you own the website outright. We hand over the logins, a recorded walkthrough of how to edit each page and a checklist of what was tested.',
         bullets=['Admin login in your name, on your own hosting',
                  'A recorded walkthrough of editing text, photos and pages',
                  'The enquiry form tested with a real message to your inbox',
                  'A list of what was checked on phone, tablet and desktop']),
    ('Included in every website', [
        ('Designed for phones first', 'Every page is laid out for a small screen and then scaled up, because that is where most visitors are.', 'webdev'),
        ('Your wording and photos', 'You send the text and images and we fit them in, tidy them up and tell you what is missing.', 'pen'),
        ('A working enquiry form', 'Connected to your email and tested before handover, with spam protection.', 'chat'),
        ('Search basics done', 'Page titles, descriptions, headings and a sitemap, so Google can read and list your pages.', 'page'),
        ('Fast loading', 'Images compressed and caching switched on.', 'spark'),
        ('A walkthrough video', 'A recording of how to change text, swap photos and add a page, made on your own site.', 'video'),
    ]),
    [('You order', 'Choose the number of pages and tell us about your business, your colours and sites you like.'),
     ('We design the first page', 'You see the home page first. This is where most of the feedback happens.'),
     ('We build the rest', 'The other pages follow the agreed look. You get two rounds of changes across the site.'),
     ('Handover', 'We test it, put it live on your hosting and send your logins and walkthrough.')],
    ['Your logo and brand colours, or tell us you need them',
     'The text for each page, or notes we can work from',
     'Photos of your work, premises or products, if you have them',
     'Access to your hosting or WordPress by invite. If you have no hosting yet, we will tell you what to buy',
     'Two or three websites you like, and what you like about them'],
    [('Is hosting and a domain name included?', 'No. You buy those in your own name so the site belongs to you. Hosting is a yearly cost paid to the hosting company. We can tell you what to buy and set it up with you.'),
     ('Who writes the text?', 'You send the wording and we fit it to the design and tidy it up. If you would rather not write it, ask us to add content writing to your order and we will quote a fixed price.'),
     ('How many changes can I ask for?', 'Two rounds of design changes are included. A round is one list of changes sent together. After handover, small edits are easy to make yourself using the walkthrough.'),
     ('Can I add pages later?', 'Yes. You can add them yourself in WordPress, or order extra pages from us. Nothing has to be rebuilt.'),
     ('Will my website appear on Google?', 'We set up the basics Google needs to read and list your pages. Where you appear depends on your competition and on work beyond the build. For a local business, <a href="/services/local-seo/">Local SEO</a> is the natural next step.'),
     ('I already have a website. Can you redesign it?', 'Yes. Choose the package that matches the number of pages and tell us the current address in the order form. We keep your existing page addresses where we can, so links to your old pages still work.'),
     ('What happens after handover?', 'The site is yours and there is no ongoing fee to us. WordPress needs updates now and then. If you would rather not do them, ask us about a maintenance package.')],
    ['local-seo', 'website-maintenance'],
    'Fixed-price WordPress websites for UK small businesses, from 1 to 10 pages. Built for phones, yours to edit and keep, with two rounds of changes.',
    'Small Business Website Design UK | Fixed-Price WordPress Sites | IamFamous', 2)

# ---------------------------------------------------------------- Social Media Management
service_page(
    'social-media-management', 'Social media',
    'Social media management without the daily scramble',
    'Posting now and then, when you remember, makes a business look closed. We plan a month ahead, design the posts in your colours, write the captions and, on the managed packages, schedule them and reply to comments.',
    ['[ifm_from service="social-media-management" prefix="Packages from " fallback="Fixed price, agreed in writing"]', 'One month at a time', 'Real followers only'],
    ['Your last post was weeks or months ago',
     'You know what you want to say but not what to post',
     'You want your pages to look consistent and professional',
     'You would rather spend your evenings on something else'],
    ['You want followers, likes or views added to your account. We do not sell them',
     'You need adverts run. Ask us about Facebook and TikTok ads instead',
     'You need someone filming at your premises every week'],
    'Choose your social media package',
    'Two packages give you content to post yourself. Two are fully managed for a month, on one to three platforms. Each covers a single month and stops unless you order again.',
    ('What does social media management include?', [
        'Social media management is the routine behind a business page: deciding what to post, making it, publishing it at a sensible time and answering the people who respond. Done well, it keeps you in front of people who already know you and gives new customers a reason to trust you.',
        'For a small business, the goal is rarely to go viral. It is to look open, active and good at what you do when someone checks you out before calling. A steady run of useful, on-brand posts does that better than an occasional burst.',
        'We start with a monthly plan: what to post, on which platform and when, built around your offers, your seasons and the questions customers ask you. Posts are designed in your colours and fonts with captions written in your voice.',
        'We work on Facebook, Instagram, TikTok and LinkedIn, and we can post to your Google Business Profile as well. You approve the month before anything goes out.']),
    dict(art='report-social.webp',
         hero_alt='Example monthly content calendar with posts planned on set days and labelled by type (offer, tip, behind the scenes, review), beside a designed post with its caption',
         alt='Example monthly social media report: posts published, reach and engagement for the month labelled as examples, and a list of next month\'s planned posts',
         eb='Your month, on one page', title='See what went out and how it did',
         text='Managed packages end with a short report. It shows what was posted, the reach and engagement figures taken from the platforms themselves, and what we plan to change next month.',
         bullets=['Every post published, with its date and link',
                  'Reach and engagement as reported by each platform',
                  'Which posts did best, and why we think so',
                  'The plan for next month, ready for your approval']),
    ('What the work covers', [
        ('A monthly plan', 'A calendar of what goes out and when, built around your offers and key dates.', 'calendar'),
        ('Designed posts', 'Graphics in your colours and fonts, sized correctly for each platform.', 'palette'),
        ('Captions in your voice', 'Written to sound like you, with relevant hashtags.', 'pen'),
        ('Scheduling', 'On managed packages we publish for you at the times your audience is online.', 'spark'),
        ('Replies on weekdays', 'Managed packages include answering comments and messages, passing sales questions to you.', 'chat'),
        ('A monthly report', 'What went out, how it performed and what we will do next.', 'report'),
    ]),
    [('You order', 'Choose a package and tell us your platforms, your offers and how you like to sound.'),
     ('We plan the month', 'You receive the content plan and the first designs to approve or change.'),
     ('We make and publish', 'Posts are finished and, on managed packages, scheduled. Replies are handled on weekdays.'),
     ('You get the report', 'A one-page summary of the month and the plan for the next one, if you want it.')],
    ['Which platforms you want covered',
     'Your logo, colours and any photos or videos you already have',
     'The products, services or offers to feature this month',
     'How you like to sound: friendly, formal, funny, straight to the point',
     'Access through each platform\'s business tools, never a shared password'],
    [('Which platforms should I be on?', 'The ones your customers use. For most local businesses that is Facebook and Instagram. LinkedIn suits business-to-business services and TikTok suits visual trades. Tell us what you sell and we will suggest where to focus.'),
     ('Will I get more followers?', 'Regular, useful posts usually grow a page over time, and we report the real numbers. We do not promise a figure and we never buy followers. Bought followers do not become customers and they put your account at risk.'),
     ('Do I approve posts before they go out?', 'Yes. You see the plan and the designs first. Nothing is published without your approval.'),
     ('Who answers comments and messages?', 'On the managed packages we reply on weekdays using answers agreed with you. Anything about prices, bookings or complaints is passed to you the same day.'),
     ('Can you use my own photos and videos?', 'Yes, and we recommend it. Real photos of your work do better than stock images. Send what you have and we will build them into the designs.'),
     ('Do you film videos?', 'Not on site. The Growth package includes short-video scripts written so you can film them on a phone, and we can edit what you send.'),
     ('What happens after the month ends?', 'Nothing renews automatically. Everything we made is yours to keep and reuse. Order another month when you want one.')],
    ['facebook-ads', 'graphic-design'],
    'Social media management for UK small businesses: monthly content plans, designed posts, scheduling and replies at a fixed monthly price. No contract.',
    'Social Media Management UK | Fixed-Price Monthly Packages | IamFamous', 3)

# ---------------------------------------------------------------- Round 2: the other 17 services
from scenes_round2 import SCENES as _SC
for d in ROUND2:
    sc = _SC[d['slug']]
    plain = d['name']
    hero_alt = f"Example {plain} illustration: {sc['hero'][1].lower()} with {', '.join(x.lower() for x in sc['hero'][3][:4])}, beside a panel headed {sc['hero'][4].lower()}"
    rep_alt = f"Example {sc['report'][0].lower()} for {plain}: a table with the columns {', '.join(c.lower() for c in sc['report'][2])}, and a short list headed {sc['report'][5].lower()}"
    service_page(
        d['slug'], d['eb'], d['h1'], d['sub'],
        [f'[ifm_from service="{d["slug"]}" prefix="Packages from " fallback="Fixed price, agreed in writing"]'] + d['checks'],
        d['fit'], d['notfit'], d['pk_title'], d['pk_sub'], d['whatis'],
        dict(art=f"report-{d['slug']}.webp", hero_alt=hero_alt.replace('&', 'and').replace('"', ''), alt=rep_alt.replace('&', 'and').replace('"', ''),
             eb=d['deliver'][0], title=d['deliver'][1], text=d['deliver'][2], bullets=d['deliver'][3]),
        ('What the work covers', d['features']),
        [('You order', 'Choose a package and fill in the short form on the package page. It takes a couple of minutes.'),
         d['process'][0], d['process'][1],
         ('You get the proof', 'The report or files arrive by email and in your account, inside the delivery time on your package.')],
        d['needs'], d['faqs'], d['related'], d['excerpt'], d['seo_title'], 0)

# ================================================================ FREE GROWTH CHECK
growth = join(
    page_hero('art-growth.webp', 'Example growth check: three cards for Google listing, website and social media, each with a short finding and a suggested first fix',
              eyebrow('Free growth check'), heading('Find out what to fix first', 1),
              lead('Tell us about your business and where you want more customers from. We look at how you appear on Google, on your website and on social media, then email you the three things we would fix first.'),
              ul(['Free, with nothing to sign', 'A reply within one working day', 'Useful even if you never order'], 'ifm-checks')),
    section(columns(
        column(group(shortcode('[ifm_lead_form type="quote"]'), layout='flow'), width='60%'),
        column(group(heading('What you get back', 3),
                     ul(['How your business shows up when someone searches for your trade and town',
                         'What a first-time visitor sees on your website, on a phone',
                         'How active and consistent your social pages look',
                         'The three fixes we would do first, and the package that covers each'], 'ifm-ticks'),
                     cls='ifm-panel', layout='flow'),
               group(heading('Need a price instead?', 3),
                     para('Use the same form. Say what you need and we will reply with a fixed price and a written scope. Nothing is charged until you accept it.'),
                     cls='ifm-card', layout='flow'),
               width='40%'),
        cls='ifm-split ifm-split--top'), cls='ifm-section--tight'),
)
page('free-growth-check', 'Free Growth Check', growth,
     'Free growth check for UK small businesses: we review your Google listing, website and social media and email the three fixes we would make first.',
     'Free Growth Check for Your Business | IamFamous', menu_order=7)

# ================================================================ CONTACT
contact = join(
    page_hero('art-growth.webp', 'Example growth check: three cards for Google listing, website and social media, each with a short finding and a suggested first fix',
              eyebrow('Contact'), heading('Talk to the team', 1),
              lead(f'Questions about a package, an order or something you cannot see on the site? Send a message here or email <a href="mailto:{EMAIL}">{EMAIL}</a>. We reply within one working day.'),
              ul(['Monday to Friday', 'A person replies, not a bot', 'Please do not send passwords'], 'ifm-checks')),
    section(columns(
        column(group(shortcode('[ifm_lead_form type="contact"]'), layout='flow'), width='60%'),
        column(group(heading('Quick answers', 3),
                     ul(['<strong>Already ordered?</strong> Add files and follow progress from <a href="/my-account/orders/">your account</a>.',
                         f'<strong>Want a price?</strong> Use the <a href="{GROWTH}">growth check form</a> and tell us what you need.',
                         '<strong>Not sure what to order?</strong> Start with the <a href="/services/">services page</a>.'], 'ifm-ticks'),
                     cls='ifm-panel', layout='flow'),
               group(heading('Email', 3), para(f'<a href="mailto:{EMAIL}">{EMAIL}</a>'), para('IamFamous works online with businesses across the UK.', 'ifm-muted'),
                     cls='ifm-card', layout='flow'),
               width='40%'),
        cls='ifm-split ifm-split--top'), cls='ifm-section--tight'),
)
page('contact', 'Contact', contact,
     'Contact IamFamous about packages, orders or a custom job. Send a message or email support@iamfamous.uk and a member of the team replies.',
     'Contact IamFamous | Questions, Orders &amp; Custom Work'.replace('&amp;', '&'), menu_order=8)

# ================================================================ INSIGHTS HERO (blog index)
write_pattern('hero-insights', 'Hero: Insights',
              page_hero('art-services.webp', 'Example service menu with four groups beside a package card',
                        eyebrow('Insights'), heading('Plain-English marketing guides for small businesses', 1),
                        lead('How to get found on Google, what a website really needs and what to post on social media, written from the jobs we do every week.'),
                        ul(['Step-by-step checklists', 'Jargon explained in one line', 'Checked against current Google guidance'], 'ifm-checks')),
              cat='iamfamous-sections', post_types='', inserter='no')

# ================================================================ FOOTER PART
PAYMARKS = ('<svg class="ifm-paymarks" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 474 40" width="474" height="40" role="img" aria-label="Payment methods: Visa, Mastercard, American Express, PayPal, Apple Pay and Google Pay">'
            '<g transform="translate(0 0)"><rect width="64" height="40" rx="8" fill="#fff"/><text x="32" y="26" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="17" font-weight="900" font-style="italic" fill="#1A1F71">VISA</text></g>'
            '<g transform="translate(72 0)"><rect width="64" height="40" rx="8" fill="#fff"/><circle cx="26" cy="20" r="11" fill="#EB001B"/><circle cx="38" cy="20" r="11" fill="#F79E1B" fill-opacity=".9"/></g>'
            '<g transform="translate(144 0)"><rect width="64" height="40" rx="8" fill="#fff"/><rect x="8" y="9" width="48" height="22" rx="3" fill="#2E77BC"/><text x="32" y="25" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="900" fill="#fff">AMEX</text></g>'
            '<g transform="translate(216 0)"><rect width="78" height="40" rx="8" fill="#fff"/><text x="39" y="26" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="900" font-style="italic"><tspan fill="#003087">Pay</tspan><tspan fill="#009CDE">Pal</tspan></text></g>'
            '<g transform="translate(302 0)"><rect width="82" height="40" rx="8" fill="#fff"/><text x="41" y="26" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="700" fill="#000">Apple Pay</text></g>'
            '<g transform="translate(392 0)"><rect width="82" height="40" rx="8" fill="#fff"/><text x="41" y="26" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="15" font-weight="700"><tspan fill="#4285F4">G</tspan><tspan fill="#3C4043"> Pay</tspan></text></g></svg>')


def html(markup):
    return f'<!-- wp:html -->\n{markup}\n<!-- /wp:html -->'


def fcol(title, items):
    return column(heading(title, 3, cls='ifm-footer__title'), ul(items, 'ifm-footer__links'))


footer = group(
    columns(
        column(html('<a class="ifm-footer__logo" href="/"><img src="/wp-content/themes/iamfamous/assets/images/logo-on-dark.svg" width="186" height="28" alt="IamFamous" loading="lazy"></a>'),
               para('Fixed-price local SEO, websites and social media for UK small businesses. Clear packages, no contracts and a report you can check on every order.', 'ifm-footer__about'),
               para(f'<a href="mailto:{EMAIL}">{EMAIL}</a>', 'ifm-footer__contact'),
               buttons(('Free growth check', GROWTH), cls='ifm-footer__cta'), width='28%'),
        fcol('Get found', ['<a href="/services/local-seo/">Local SEO</a>', '<a href="/services/monthly-seo/">Monthly SEO</a>', '<a href="/services/off-page-seo/">Link Building</a>',
                           '<a href="/services/seo-audit/">SEO Audit</a>', '<a href="/services/technical-seo/">Technical SEO</a>']),
        fcol('Websites', ['<a href="/services/website-development/">Website Development</a>', '<a href="/services/ecommerce-website-development/">Ecommerce Websites</a>',
                          '<a href="/services/website-maintenance/">Website Maintenance</a>', '<a href="/services/website-migration/">Website Migration</a>', '<a href="/services/content-writing/">Content Writing</a>']),
        fcol('Ads &amp; social', ['<a href="/services/social-media-management/">Social Media</a>', '<a href="/services/ppc-management/">Google Ads</a>', '<a href="/services/facebook-ads/">Facebook Ads</a>',
                                  '<a href="/services/tiktok-ads/">TikTok Ads</a>', '<a href="/services/graphic-design/">Graphic Design</a>']),
        fcol('Get started', ['<a href="/services/">All 20 services</a>', f'<a href="{GROWTH}">Free growth check</a>', '<a href="/contact/">Contact</a>', '<a href="/my-account/">My account</a>', '<a href="/basket/">Basket</a>']),
        cls='ifm-footer__cols'),
    group(para('<strong>Secure checkout.</strong> Card details go straight to the payment provider and are never stored on this site.', 'ifm-footer__secure'),
          html(PAYMARKS), align='wide', cls='ifm-footer__pay', layout='flex'),
    group(para('© [ifm_year] IamFamous. All rights reserved.'),
          para('Prices in pounds. No VAT added. One-off orders, no contract.', 'ifm-footer__legal'), align='wide', cls='ifm-footer__bottom', layout='flex'),
    align='full', cls='ifm-footer', bg='ink', text='on-dark')
# Footer flex groups: the reference markup uses justifyContent space-between.
footer = footer.replace('"layout":{"type":"flex","flexWrap":"wrap"}}', '"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}}')
with open(os.path.join(ROOT, 'parts', 'footer.html'), 'w') as f:
    f.write(footer + '\n')

# ================================================================ manifest
def php_str(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


lines = ["<?php", "/**", " * Generated by tools/build_patterns.py — pages created by the Setup screen.", " *", " * @package IamFamousTheme", " */", "",
         "defined( 'ABSPATH' ) || exit;", "", "return array(", "\t'pages' => array("]
for p in PAGES:
    lines.append("\t\tarray( " + ', '.join(f"'{k}' => {php_str(str(v)) if not isinstance(v, int) else v}" for k, v in p.items()) + " ),")
lines += ["\t),", "\t'posts' => array(", "\t),", ");", ""]
with open(os.path.join(ROOT, 'inc', 'site-manifest.php'), 'w') as f:
    f.write('\n'.join(lines))
print(f'{len(PAGES)} pages written to {os.path.abspath(PAT)}')
