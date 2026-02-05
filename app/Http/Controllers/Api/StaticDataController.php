<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaticDataController extends Controller
{
    public function surah()
    {
        return response()->json([
            'data' => config('surahs')
        ]);
    }

    public function pages()
    {
        return response()->json([
            'data' => config('pages')
        ]);
    }

    public function juzs()
    {
        return response()->json([
            'data' => config('juzs')
        ]);
    }

    public function hizb()
    {
        return response()->json([
            'data' => config('hizbs')
        ]);
    }

    public function stac()
    {
        return response()->json(config('stacs'));
    }

    public function privacyPolicy(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Privacy policy fetched successfully',
            'data' => [
                'title' => 'Privacy Policy - AL-SIDRA',
                'last_updated' => '2024-02-15',
                'content' => [
                    [
                        'heading' => 'Our Privacy Promise',
                        'body' => 'At AL-SIDRA, we believe privacy is a sacred trust (amanah). We handle your information with the same care and respect we\'d expect for our own community\'s data. We don\'t sell your data. We don\'t misuse your trust. We protect our community.'
                    ],
                    [
                        'heading' => 'Information We Collect',
                        'body' => 'We collect only what\'s necessary: account information (name, email, phone), institution details (masjid/madrasa name, location), approximate location (with permission for service discovery), and usage information to improve our platform. We never collect sensitive personal information like religious practices or political views.'
                    ],
                    [
                        'heading' => 'How We Use Your Information',
                        'body' => 'Your information helps us: show nearby masjids and madrasas, help institution admins manage their profiles, improve platform features, fix bugs, send important updates about your institution, and ensure the platform serves our community effectively.'
                    ],
                    [
                        'heading' => 'Data Sharing & Community Access',
                        'body' => 'We only share information: with your masjid/madrasa admin (basic profile info), with trusted service providers who help run the platform, when required by law, or with your explicit consent. Institution admins can see member information for their community management, but cannot access data from other institutions.'
                    ],
                    [
                        'heading' => 'Islamic Community Values',
                        'body' => 'AL-SIDRA is built on Islamic principles of trust (amanah), transparency (shafaafiyah), and respect (ihtiram). We treat community data with the dignity owed to every member. Institution admins are responsible for protecting their community members\' privacy according to these values.'
                    ],
                    [
                        'heading' => 'Data Security & Protection',
                        'body' => 'We implement multiple security layers: encryption for all data in transit and at rest, strict role-based access controls, regular security audits, and continuous monitoring. All team members are trained in data protection principles and Islamic ethics of confidentiality.'
                    ],
                    [
                        'heading' => 'Your Privacy Rights',
                        'body' => 'You have the right to: access your information, correct inaccurate data, request data deletion, opt-out of non-essential communications, and control your privacy settings. Institution admins have additional responsibilities for their community members\' data.'
                    ],
                    [
                        'heading' => 'Madrasa & Children\'s Privacy',
                        'body' => 'For madrasas and Islamic schools using our platform: institution administrators are responsible for obtaining parental consent for students under 13 and managing their data appropriately. We comply with COPPA and children\'s privacy regulations.'
                    ],
                    [
                        'heading' => 'Donation & Charity Data',
                        'body' => 'When processing donations through our platform: financial data is handled by secure third-party processors, institutions are responsible for proper fund use and transparency, and we facilitate but do not manage charitable funds directly.'
                    ],
                    [
                        'heading' => 'Policy Updates & Notification',
                        'body' => 'We may update this privacy policy as our platform evolves. Significant changes will be notified through the platform and email. Continued use of AL-SIDRA after updates constitutes acceptance of the revised policy.'
                    ],
                    [
                        'heading' => 'Contact & Support',
                        'body' => 'For privacy questions: email privacy@alsidra.com. For data protection concerns: contact dpo@alsidra.com. For security emergencies: security@alsidra.com. Institution admins can access additional support through their dashboard.'
                    ],
                    [
                        'heading' => 'Building Trust Together',
                        'body' => 'AL-SIDRA is more than a platform - it\'s a community built on trust. By respecting each other\'s privacy, we strengthen our Islamic communities and fulfill our responsibility to protect the information entrusted to us.'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get Terms & Conditions
     * 
     * @api {get} /api/terms-conditions Get Terms & Conditions
     * @apiName GetTermsConditions
     * @apiGroup Policies
     * @apiDescription Returns the terms and conditions for AL-SIDRA platform
     *
     * @return JsonResponse
     */
    public function termsConditions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Terms and conditions fetched successfully',
            'data' => [
                'title' => 'Terms & Conditions - AL-SIDRA',
                'last_updated' => '2024-02-15',
                'content' => [
                    [
                        'heading' => 'Welcome to Our Community',
                        'body' => 'By using AL-SIDRA, you join a trusted community of Islamic institutions and members. These terms help us maintain trust, clarity, and respect for everyone. If you use our platform, you agree to these terms.'
                    ],
                    [
                        'heading' => 'Your Responsibilities',
                        'body' => 'Provide accurate information about yourself and your institution. Keep your account password secure. Use the platform for its intended community purposes. Respect other users and their institutions. Follow Islamic principles of honesty and respect.'
                    ],
                    [
                        'heading' => 'Our Community Promise',
                        'body' => 'AL-SIDRA is built on Islamic values of trust, transparency, and community support. We all agree to treat every member with respect and dignity, use appropriate language and content, protect community privacy, and support masjids and madrasas with integrity.'
                    ],
                    [
                        'heading' => 'Roles & Access Levels',
                        'body' => 'Platform Admins have full system oversight. Masjid/Madrasa Admins manage their institution profiles and staff. Institution Staff (teachers/managers) access relevant tools. Community Members discover and participate. You may only access areas appropriate to your role.'
                    ],
                    [
                        'heading' => 'Content & Sharing Guidelines',
                        'body' => 'You own what you share (photos, posts, educational content). By sharing, you allow AL-SIDRA to display it to your community. Keep content appropriate and beneficial. Respect copyright and others\' work. No false information or harmful content.'
                    ],
                    [
                        'heading' => 'Donations & Community Support',
                        'body' => 'Institutions are responsible for proper fund use and transparency. AL-SIDRA facilitates but doesn\'t manage funds directly. Follow local charity regulations. Provide receipts to donors when possible. Maintain transparency in all charity activities.'
                    ],
                    [
                        'heading' => 'Account Management',
                        'body' => 'You must be at least 18 to create an account. Institution representatives must have proper authority. We may suspend accounts for violations of these terms. You can request account deletion at any time through our support channels.'
                    ],
                    [
                        'heading' => 'Platform Availability',
                        'body' => 'We strive to keep AL-SIDRA available 24/7 but cannot guarantee uninterrupted service. We perform maintenance and updates to improve the platform. We\'re not liable for temporary unavailability due to technical issues or maintenance.'
                    ],
                    [
                        'heading' => 'Contact & Resolution',
                        'body' => 'For questions: support@alsidra.com. For institution inquiries: institutions@alsidra.com. We prefer resolving issues through respectful dialogue in line with Islamic conflict resolution principles before any formal action.'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get Community Guidelines
     * 
     * @api {get} /api/community-guidelines Get Community Guidelines
     * @apiName GetCommunityGuidelines
     * @apiGroup Policies
     * @apiDescription Returns the community guidelines for AL-SIDRA platform
     *
     * @return JsonResponse
     */
    public function communityGuidelines(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Community guidelines fetched successfully',
            'data' => [
                'title' => 'Community Guidelines - AL-SIDRA',
                'last_updated' => '2024-02-15',
                'content' => [
                    [
                        'heading' => 'Our Shared Values',
                        'body' => 'AL-SIDRA is built on Islamic principles: Trust (Amanah), Respect (Ihtiram), Kindness (Rahma), and Unity (Wahda). Every interaction should reflect these values and strengthen our community bonds.'
                    ],
                    [
                        'heading' => 'Respectful Communication',
                        'body' => 'Use respectful language at all times. Address others with appropriate Islamic greetings and titles. Disagree respectfully without personal attacks. Remember that behind every account is a member of our Ummah.'
                    ],
                    [
                        'heading' => 'Institution Respect',
                        'body' => 'Respect the authority of masjid and madrasa administrators. Follow institution-specific rules and guidelines. Address concerns through proper channels. Support your local institutions with constructive feedback.'
                    ],
                    [
                        'heading' => 'Content Standards',
                        'body' => 'Share only beneficial content. Verify information before sharing. Credit original sources. No divisive or controversial content. Educational materials should be from reputable Islamic sources.'
                    ],
                    [
                        'heading' => 'Privacy & Confidentiality',
                        'body' => 'Protect community members\' privacy. Don\'t share others\' information without permission. Keep institutional matters within the appropriate circles. Report privacy concerns immediately.'
                    ],
                    [
                        'heading' => 'Charity & Support Etiquette',
                        'body' => 'Make charity requests with dignity. Donate with pure intention (niyyah). Respect the privacy of those receiving support. Follow through on promises and commitments.'
                    ],
                    [
                        'heading' => 'Conflict Resolution',
                        'body' => 'Resolve conflicts privately first. Involve institution admins if needed. Follow Islamic principles of reconciliation (sulh). Avoid public disputes that harm community unity.'
                    ],
                    [
                        'heading' => 'Reporting & Moderation',
                        'body' => 'Report violations through proper channels. Provide specific details when reporting. Allow time for appropriate investigation and response. Trust the moderation process.'
                    ],
                    [
                        'heading' => 'Building Together',
                        'body' => 'Every member contributes to our community\'s strength. Share knowledge generously. Welcome new members warmly. Support community initiatives. Remember we\'re all working toward the same goal: stronger Islamic communities.'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Get All Policies
     * 
     * @api {get} /api/policies Get All Platform Policies
     * @apiName GetAllPolicies
     * @apiGroup Policies
     * @apiDescription Returns all platform policies (privacy, terms, guidelines) in a single response
     *
     * @return JsonResponse
     */
    public function getAllPolicies(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'All platform policies fetched successfully',
            'data' => [
                'privacy_policy' => [
                    'title' => 'Privacy Policy - AL-SIDRA',
                    'last_updated' => '2024-02-15',
                    'endpoint' => '/api/privacy-policy'
                ],
                'terms_conditions' => [
                    'title' => 'Terms & Conditions - AL-SIDRA',
                    'last_updated' => '2024-02-15',
                    'endpoint' => '/api/terms-conditions'
                ],
                'community_guidelines' => [
                    'title' => 'Community Guidelines - AL-SIDRA',
                    'last_updated' => '2024-02-15',
                    'endpoint' => '/api/community-guidelines'
                ],
                'summary' => 'AL-SIDRA policies are designed to protect our Islamic community while providing clear guidance for platform use. All policies are based on Islamic values and modern best practices.'
            ]
        ]);
    }
}
