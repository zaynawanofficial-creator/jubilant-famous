<?php
/**
 * IamFamous's own wording for package groups, merged over group-content.php by IFM_Importer::content().
 * Scope, quantities, turnaround and revisions match the catalogue; only the wording is ours.
 * Keys: sanitize_title( group ) => fields; by_sku keys are the package code without the "if-" prefix.
 *
 * Copy rules: UK spelling, plain English, no ranking / follower / lead guarantees, nothing we do not deliver.
 *
 * @package IamFamousCore
 */

defined( 'ABSPATH' ) || exit;

return array(
	'local-seo-packages'    => array(
		'intro'        => 'For businesses that serve a town, a city or a patch of postcodes. We sort out your Google Business Profile, list you correctly in the UK directories people and Google check, and tune your site for the places you cover.',
		'brief'        => 'local',
		'exclusions'   => "A promised position in the map results (Google decides that)\nFake reviews, paid reviews or asking only happy customers to review you\nFees charged by paid directories\nGoogle's own verification step (video, phone or postcard), which only you can complete",
		'requirements' => "Business name, address, phone number and opening hours exactly as you want them shown\nManager access to your Google Business Profile, by invite\nThe services you offer and the towns or postcodes you cover\nYour website address, if you have one",
		'by_sku'       => array(
			'local-seo-local-seo-packages-1' => array(
				'ideal_for'    => 'One location, profile needs sorting out',
				'deliverables' => "Your Google Business Profile checked line by line and corrected\nPrimary and secondary categories, services and attributes set\nA business description written for customers, with your main services and area\n5 Google posts written and published\nA short list of photos to add and customer questions to answer",
			),
			'local-seo-local-seo-packages-2' => array(
				'ideal_for'    => 'New or small local businesses',
				'featured'     => true,
				'deliverables' => "Everything in Google Business Profile Optimisation\n20 UK directory listings with matching name, address and phone number\nYour home page tuned for your service and area: title, headings, contact details\nLocalBusiness schema added to your site\nA report with the live link to every listing",
			),
			'local-seo-local-seo-packages-3' => array(
				'ideal_for'    => 'Busy towns and cities with strong competitors',
				'deliverables' => "Everything in Local SEO Starter\n40 UK directory listings in total\n2 location or service-area pages written and optimised\nA review request plan with message templates you can send\nA side-by-side comparison with the businesses ranking in your map results",
			),
			'local-seo-local-seo-packages-4' => array(
				'ideal_for'    => 'Keeping your profile active month to month',
				'deliverables' => "4 Google posts written and published\n10 new directory listings\nReply templates for new reviews\nPosition checks for 10 local search terms\nA monthly report with the next steps",
			),
		),
	),
	'website-packages'      => array(
		'intro'        => 'A WordPress website sized to your business, from a single landing page to ten pages. Built mobile-first, with your wording and photos in place, a working enquiry form and a recorded walkthrough so you can change things yourself.',
		'brief'        => 'web',
		'ideal_for'    => array(
			1  => 'One offer, one page, one clear action',
			2  => 'Sole traders: home page plus contact',
			5  => 'Most small businesses',
			10 => 'Several services or locations',
		),
		'featured'     => array( 5 ),
		'deliverables' => "WordPress pages designed and built to your brief, tested on phones first\nYour text, photos and logo added (you send them; writing is a separate package)\nAn enquiry form that emails you, tested before handover\nPage titles, descriptions, headings and a sitemap set up for search\nImages compressed and caching switched on\nA recorded walkthrough showing how to edit every page",
		'exclusions'   => "Your domain name, hosting and any paid theme or plugin licences\nWriting the page text (order Content Writing, or send your own)\nOnline shops, booking systems and custom features, which we quote separately\nUpdates and changes after handover (see Website Maintenance)",
		'requirements' => "Your logo and brand colours, or tell us you need them\nThe text for each page, or notes we can work from\nAccess to your hosting or WordPress by invite, never a shared password\nTwo or three websites you like, and what you like about them",
	),
	'social-media-packages' => array(
		'intro'        => 'Regular, on-brand posting for the platforms your customers use, without you finding the time each week. Start with a plan you post yourself, or hand over the designing, scheduling and replies.',
		'brief'        => 'social',
		'exclusions'   => "Buying followers, likes or views, which we never do\nAd spend and paid campaigns (separate Facebook and TikTok ads packages)\nFilming or photography at your premises\nA promised number of followers or enquiries",
		'requirements' => "Which platforms you want covered\nYour logo, colours, photos and how you like to sound\nThe products, services or offers to feature this month\nAccess through each platform's business tools, never a shared password",
		'by_sku'       => array(
			'social-media-management-social-media-packages-1' => array(
				'ideal_for'    => 'You post it, we plan it',
				'deliverables' => "A one-month content plan built around your offers and dates\n12 post ideas, each with a finished caption\nHashtags and the best days and times to post\nA posting calendar you can follow or hand to a colleague",
			),
			'social-media-management-social-media-packages-2' => array(
				'ideal_for'    => 'Ready-made posts to schedule yourself',
				'featured'     => true,
				'deliverables' => "12 posts designed in your colours and fonts\nA caption and hashtags for each one\nSized correctly for each platform you choose\nFiles ready to upload or schedule",
			),
			'social-media-management-social-media-packages-3' => array(
				'ideal_for'    => 'Hands-off posting on 1–2 platforms',
				'deliverables' => "12 posts designed, captioned and scheduled for you\nHashtags on every post\nReplies to comments and messages on weekdays\nA monthly report on reach and engagement",
			),
			'social-media-management-social-media-packages-4' => array(
				'ideal_for'    => 'Hands-off posting on 2–3 platforms',
				'deliverables' => "20 posts designed, captioned and scheduled for you\n4 short-video scripts you can film on a phone\nReplies to comments and messages on weekdays\nA monthly report and a planning call",
			),
		),
	),
);
