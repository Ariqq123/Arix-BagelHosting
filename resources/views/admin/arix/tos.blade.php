@extends('layouts.arix', ['navbar' => 'tos', 'sideEditor' => true])

@section('title')
    Arix Terms of Service
@endsection

@section('content')
    <form action="{{ route('admin.arix.tos') }}" method="POST">
        <div class="header">
            <p>Terms of Service Editor</p>
            <span class="description-text">Edit the content displayed on the public /tos page. HTML is fully supported and rendered raw. Leave empty to hide the navbar link and return 404 on /tos.</span>
        </div>
        <div class="input-field">
            <label for="arix:tos_content">Terms of Service content</label>
            <div class="flex gap-2 mb-2">
                <button type="button" onclick="loadTosTemplate()" class="button button-secondary text-sm">Load BagelHosting Template</button>
            </div>
            <textarea id="arix:tos_content" name="arix:tos_content" rows="25" class="w-full font-mono p-4 bg-gray-900 border border-gray-700 rounded-2xl text-gray-100">{{ old('arix:tos_content', $tos_content ?? '') }}</textarea>
            <small>HTML allowed. Raw output on the public page. Use for legal terms, privacy policy, or server rules. The template provides a complete starting point.</small>
        </div>
        <div class="floating-button">
            {!! csrf_field() !!}
            <button type="submit" class="button button-primary">Save changes</button>
        </div>
    </form>

    <script>
    function loadTosTemplate() {
        const template = `# BAGELHOSTING TOS

1. Acceptance of Terms

By accessing or using Bagel Hosting services, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use our services.

2. Description of Services

Bagel Hosting provides game server hosting, virtual private server (VPS) hosting, and related services. We reserve the right to modify or discontinue services at any time.

3. Account Registration

To use our services, you must:
- Be at least 18 years old or have parental consent
- Provide accurate and complete information
- Maintain the security of your account credentials
- Notify us immediately of any unauthorized access

4. Acceptable Use

You agree not to use our services for:
- Illegal activities or content
- Distributed denial of service (DDoS) attacks
- Malware distribution or hosting
- Unauthorized access to systems (hacking)
- Spam or unsolicited commercial content
- Child exploitation or inappropriate content

Violation of these terms may result in immediate termination of service without refund.

5. Payment and Billing

Services are billed in advance on a subscription basis. Payments are non-refundable except as stated in our refund policy. Failure to pay may result in service suspension. We reserve the right to change pricing with 30 days notice.

6. Refund Policy

We offer a 48-hour money-back guarantee for new customers. Refunds are processed within 5-10 business days. This does not apply to domain registrations or add-on services.

7. Service Level Agreement (SLA)

We guarantee 99.9% uptime for our hosting services. If we fail to meet this guarantee, you may be eligible for service credits as determined on a case-by-case basis.

8. Limitation of Liability

Bagel Hosting shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of our services.

9. Termination

We may terminate or suspend your account immediately for violations of these terms. You may cancel your subscription at any time through your control panel.

10. Changes to Terms

We reserve the right to modify these terms at any time. Continued use of our services after changes constitutes acceptance of the new terms.

11. Contact Information

For questions about these terms, contact us at: legal@bagelsmp.tech

By using Bagel Hosting, you acknowledge that you have read, understood, and agree to these Terms of Service.

---
*BagelHosting TOS Template — customize as needed.*`;
        document.getElementById('arix:tos_content').value = template;
    }
    </script>
@endsection
