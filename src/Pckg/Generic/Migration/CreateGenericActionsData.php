<?php namespace Pckg\Generic\Migration;

use Derive\Content\Controller\Content;
use Pckg\Collection;
use Pckg\Generic\Entity\Routes;
use Pckg\Generic\Record\Action;
use Pckg\Generic\Record\ActionsMorph;
use Pckg\Generic\Record\Content as ContentRecord;
use Pckg\Generic\Record\Route;
use Pckg\Migration\Migration;

class CreateGenericActionsData extends Migration
{

    public function up()
    {
        $actions = [
            [
                'slug'   => 'derive-content-company',
                'class'  => Content::class,
                'method' => 'company',
            ],
        ];

        (new Collection($actions))->each(function($action) {
            $actionRecord = Action::getOrNew(['slug' => $action['slug']]);

            if (!$actionRecord->isSaved()) {
                $actionRecord->setAndSave($action);
            }
        });

        $routes = [
            [
                'layout_id' => 2,
                'url'       => '/terms-and-conditions',
                'slug'      => 'terms-and-conditions',
                'title'     => 'Terms and Conditions',
                'morph'     => [
                    'action'  => 'derive-content-company',
                    'content' => '<h2>Introduction</h2>
<div style="text-align: justify;">The following Terms and Conditions define how You make use of our website and services. All visitors of this website, whether as guests or registered users, are referred to as Users. Please read these Terms and Conditions carefully before You start to use the site and before You book any services. By using our site, You indicate that You accept these Terms and Conditions, You understand that You are bound by them and You agree to abide by them. If You do not agree to these Terms and Conditions, please refrain from using our site or booking any services.<br /><br /></div>
<div style="text-align: justify;">The {{ config(\'site.contact.name\') }} website and online booking system is available at the web address {{ config(\'url\') }} (hereinafter referred to as Website) is held and operated by:</div>
<div style="text-align: justify;">{{ company.long_name }}</div>
<div style="text-align: justify;">{{ company.address_line1 }}</div>
<div style="text-align: justify;">{{ company.address_line2 }}</div>
<div style="text-align: justify;">{{ company.address_line3 }}</div>
<div style="text-align: justify;">{{ company.country.title }}</div>
<div style="text-align: justify;">VAT: {{ company.vat_number }}</div>
<div style="text-align: justify;">Business number: {{ company.business_number }}</div>
<div style="text-align: justify;">{{ company.note1 }}</div>
<div style="text-align: justify;">{{ company.note2 }}</div>
<div style="text-align: justify;">(hereinafter referred to as &ldquo;{{ company.short_name }}&rdquo; or &ldquo;us&rdquo; or &ldquo;we&rdquo;).</div>
<div style="text-align: justify;"><br />Every time a service is booked on our Website, a contract is formed between You and {{ company.short_name }}. This contract is subject to these Terms and Conditions. If any service is described with provisions different from these Terms and Conditions, the service-specific provisions will be in power.</div>
<div style="text-align: justify;"><br />We may revise these Terms and Conditions at any time without prior notice. You are expected to check them from time to time to take notice of any changes we made, as they are binding on You as a User. Some of the provisions contained in these Terms and Conditions may also be superseded by provisions or notices published elsewhere on our site.&nbsp;<span style="font-weight: 400;"><span style="font-weight: 400;">We aim to update our Website regularly, and may change the content at any time. If the need arises, we may suspend access to our Website, or close it indefinitely.<br /><br /></span></span>
<h2>Access</h2>
<span style="font-weight: 400;"><span style="font-weight: 400;">We reserve the right to withdraw or amend the service we provide on our Website without prior notice. We will not be liable if for any reason our site is unavailable at any time or for any period. From time to time, we may restrict access to some parts of our site, or our entire site, to users who have registered with us. If you choose, or you are provided with, a user identification code, password or any other piece of information as part of our security procedures, you must treat such information as confidential, and you must not disclose it to any third party. We have the right to disable any user identification code or password, whether chosen by you or allocated by us, at any time, if in our opinion you have failed to comply with any of the provisions of these terms of use. You are also responsible for ensuring that all persons who access our site through your internet connection are aware of these terms, and that they comply with them.<br /><br /></span></span>
<h2>Intellectual Property Rights</h2>
<p><span style="font-weight: 400;">We are the owner or the licensee of all intellectual property rights in our site, and in the material published on it. Those works are protected by copyright laws and treaties around the world. All such rights are reserved.</span></p>
<p><span style="font-weight: 400;">You may print off one copy, and may download extracts, of any page(s) from our site for your personal reference and you may draw the attention of others within your organisation to material posted on our site.</span></p>
<p><span style="font-weight: 400;">You must not modify the paper or digital copies of any materials you have printed off or downloaded in any way, and you must not use any illustrations, photographs, video or audio sequences or any graphics separately from any accompanying text.</span></p>
<span style="font-weight: 400;">If you print off, copy or download any part of our site in breach of these terms of use, your right to use our site will cease immediately and you must, at our option, return or destroy any copies of the materials you have made.</span></div>
<h2 style="text-align: justify;"><br />The service</h2>
<div style="text-align: justify;">Services sold on our Website are normally more items packaged together in one service package (hereinafter Service). These items might include, but are not limited to: event tickets, festival tickets, travel opportunities, accommodation, transfers, merchandise and similar. Be it one item or more items packaged together, the service always additionally includes our organization, administration and handling.<br /><br /></div>
<h2 style="text-align: justify;">Booking of services</h2>
<div style="text-align: justify;">To make a booking for Your selected, please follow the instructions on our website. A quick overview of the booking process is as follows. On Step 1 of the booking process read carefully the description of all services, select Your service(s) and number of persons/units for each service. On Step 2 input Your personal details, personal details of Your friends if needed, possibly choose Your extras for each service and possibly input a discount code. On Step 3 review Your booking summary and choose a payment plan, if available. On Step 4 choose what You pay and by which payment method.</div>
<div style="text-align: justify;"><br />By making a booking You become a registered User with access to Your User account profile page. Access credentials shall be sent to You over email during the first booking You have made. The Users can manage and change their access credentials, bookings and personal information.</div>
<div style="text-align: justify;"><br />All services&nbsp;are payable in full on booking. Some services can be paid in instalments (i.e. payment plans), but only if indicated on Step 3 of the booking with a drop-down menu to select number of instalments.</div>
<div style="text-align: justify;"><br />Please note that only by completing the booking process the booking has not yet been confirmed. A booking is confirmed only after a complete or partial payment has been made, depending on the availability of payment in instalments. However, if the complete payment has not been made prior to the service start date, the booking shall be deemed invalid and {{ company.short_name }} has no obligation to perform the service(s). Non regarding of availability of payment in instalments, the complete payment must be made prior to service start date. A contract with us will only exist once we have issued a booking confirmation setting out the full details of booking. We will only issue Your booking confirmation once full payment has been received. Please check Your booking confirmation carefully and notify us immediately at {{ config(\'site.contact.email\') }} if anything on the booking confirmation appears to be incorrect or incomplete.</div>
<div style="text-align: justify;"><br />The booking is valid if You have properly and correctly filled in all the necessary information. If the booking is successfuly processed the customer is informed by email. Providing the correct personal details, especially the email address(es), is critical for a successful and valid booking. A booking with incorrect personal details of Users shall be deemed invalid.</div>
<div style="text-align: justify;">A booking can be made for more than one person, where the person who made the booking is the contract holder and responsible contact person for all his friends, unless otherwise agreed in writing. All persons a booking is made for are considered registered Users.</div>
<div style="text-align: justify;"><br />A booking can be made for one or more persons. The User who makes the booking will be deemed to be the Lead Booker for the booking and will be listed as the contract holder on the Booking Confirmation. The Lead Booker will be responsible for ensuring that all payments under the contract are made (although payments can be accepted from other names on the booking) as well as advising us of any amendments or cancellations. We will only accept amendments and cancellations notified to us by the Lead Booker. The Lead Booker confirms that he/she: has read these Terms and Conditions, will notify all Users on the booking to read these Terms and Conditions, and is authorised to and does agree to the contract on behalf of all parties named in the booking; consents to our use of information in accordance with these Terms and Conditions and our Privacy Policy; is over 18 years of age, and where services with age restrictions, declares that he/she and all members of the party are of the appropriate age to book those services.</div>
<div style="text-align: justify;"><br />All services, at prices indicated, are subject to availability. Booking fee, if applicable, is a separate service and is not part of the price.</div>
<h2 style="text-align: justify;"><br />Prices of services</h2>
<div style="text-align: justify;">Full details of what is included in the price of services is shown on our Website. Anything not specifically described on our website as being included in the price is extra and can be added to the booking. Base price of service with added extras, for each person on the booking, amounts to the total value of the booking.<br /><br /></div>
<div style="text-align: justify;">We reserve the right to change the prices of services advertised on our website at any time before You book. The final price of Your service will be confirmed before You proceed with the booking, together with any applicable taxes and fees.</div>
<div style="text-align: justify;"><br />Normally, bookings are paid in advance. In accordance with applicable legislation due to the advance payments all prices for products and services have been reduced upfront by the value of the interest calculated at the rate valid for tied savings deposits.</div>
<h2 style="text-align: justify;"><br />Payments</h2>
<div style="text-align: justify;">In order to make a booking, You must pay the total or partial value of Your booking, depending on the availability of payments in instalments, at the time of booking, together with the booking fee. The booking is confirmed when we receive a payment from You.</div>
<div style="text-align: justify;">The Booking Confirmation however will be issued only after the payment for full value of the booking has been received on our bank account(s).<br /><br /></div>
<div style="text-align: justify;">We accept most credit and debit cards as well as other forms of payment including PayPal. Please check the details on our Website. We take all reasonable care to ensure that our website is secure and to ensure that it is not possible for any third party to access Your payment or other personal information. However we cannot be held liable in the event that any third party obtains personal data or information in an unauthorised manner, unless due to our own negligence. By entering Your payment details, You confirm that the payment details belong to You.</div>
<div style="text-align: justify;"><br />For any refunds that are possibly granted to Users we will carry them out within 30 days of the approval of a refund.<br /><br /></div>
<h2 style="text-align: justify;"><br />Booking fee</h2>
<div style="text-align: justify;">In order to keep our booking system safe, updated and stable, You might be charged a booking fee when making Your booking. If a booking fee charge applies it will be clearly stated on Step 3 of the booking process along with the value of the booking fee. The booking fee is a separate service charged only for confirmed bookings at a fixed or flexible rate (e.g. percentage of total value of booking). You will never be charged twice with a booking fee. In case of amendments or cancellations of Your booking the booking fee will always be kept by us. By agreeing to these Terms and Conditions You forfeit the right to demand a refund of the booking fee(s).<br /><br /><br /></div>
<h2 style="text-align: justify;">Cancellations by You</h2>
<div style="text-align: justify;">Please note that Services booked on our Website are entirely non-refundable. Also, the &ldquo;cooling off period&rdquo; of 14 days free cancellation possibility granted by the European Union legislature does not apply for any items included in the Services (i.e. event tickets, hotel bookings, transfer and similar events and tourism related services).</div>
<div style="text-align: justify;"><br />In some exceptional cases, such as severe illness or injury or death in near family, we shall approve a cancellation and refund of Your currently paid amount, reduced for the booking fee and possibly costs of items strictly bound to Your name or booking. In these cases please submit supporting evidence.</div>
<div style="text-align: justify;"><br />We strongly recommend You take out cancellation insurance for Your booking. In this case, if the reason You are cancelling is covered by that insurance, You should be able to obtain reimbursement of any cancellation charges from Your insurance company (after payment of any deductible). However, You must pay us the full value of Your booking and obtain written confirmation of Your cancellation from us before making any insurance claim.<br /><br /></div>
<h2 style="text-align: justify;"><br />Changes or cancellations by us</h2>
<div style="text-align: justify;">We will try to keep changes to Your booking to a minimum and we will advise You of any changes to Your booking at the earliest possible date.<br /><br /></div>
<div style="text-align: justify;">If Your booking is not confirmed (You have not paid us anything yet) and we have to make a change to the service You have booked, we will notify You and You have the right to accept the amended service and complete payment to confirm Your booking, or to reject the amended service and thus render Your booking void. In either case we shall have no further liability to You.<br /><br /></div>
<div style="text-align: justify;">If Your booking is confirmed (You have paid us in part or in full, depending on the availability of payment in instalments) and we have to make a significant change such as a change of date or significant change of location or a significant change in category of Your ticket, or if we have to cancel any part of Your booking, we will notify You immediately with the details of the revised service and the revised price and You will have the choice to either: accept the revised service at the revised price and possibly pay the difference; or (if we are able to offer one to You) take a substitute service of equivalent or superior quality for the same price as the original service; or (if we are able to offer one to You) take a substitute package of lower quality than the original package; or cancel the original package and obtain a full refund of currently paid amount reduced for the booking fee. You must inform us of Your choice as soon as possible. We do not regard an upgrade to a higher category of service, a change of festival line-up or similar minor change as a significant change.<br /><br /></div>
<div style="text-align: justify;">If we cancel Your booking or have to make a significant change to Your contract, You are entitled to receive compensation, where appropriate. We will not be obliged to compensate You where the booking is cancelled by reason of unusual or unforeseeable circumstances beyond our control or that of our suppliers, the consequences of which could not have been avoided even if due care had been exercised (i.e. force majuere). These circumstances might include but are not limited to, for example, war, riot, industrial dispute, terrorist activity and its consequences, natural or nuclear disaster, fire, adverse weather conditions, epidemics and pandemics and unavoidable technical problems with transport.<br /><br /><br /></div>
<h2 style="text-align: justify;">Complaints and claims</h2>
<div style="text-align: justify;">If You have a complaint or claim in relation to any part of the service You must state this at the time the issue occurs, as most problems can be solved on the spot. If the matter cannot be resolved locally at the time, then please write an email to our customer services department at {{ config(\'site.contact.email\') }} within 3 days of receiving the service. This will help us to quickly identify Your concerns and speed up our response to You. If You fail to follow this procedure, we will have been deprived of the opportunity to investigate and rectify Your complaint and this may affect Your rights under Your contract.<br /><br /></div>
<h2 style="text-align: justify;"><br />Notifying and informing You</h2>
<div style="text-align: justify;">For the purpose of keeping You informed about performing all services in Your booking, we will notify You at least 24 hours before start of service about all details You need to know. If You will have further questions You are obliged to ask us over email before the service starts.<br /><br /></div>
<h2 style="text-align: justify;"><br />Privacy and data processing</h2>
<div style="text-align: justify;">In order to process Your booking and performing the service(s) meet Your requirements, we need to use the personal information You provide such as name, surname, address, phone number and any special needs/dietary requirements etc. We shall ensure that proper security measures are in place to protect Your information and we will comply with aplicable legislation. Please see further details of our <a href="/privacy-policy" target="_blank">Privacy and Cookies policy</a>.<br /><br /></div>
<div style="text-align: justify;">In order to process Your booking we might pass Your personal information on to the relevant suppliers of Your service such as the event&nbsp;organiser or promoter, hotel, and transport provider. We may also need to provide Your personal information to security or credit checking companies, credit and debit card companies, regulatory or public authorities such as customs or immigration if required by them, or as required by law. We may have to send Your personal information to countries outside the European Economic Area (EEA) where controls on data protection may not be as strong as the legal requirements in this country. By making a booking, You consent to this use of Your personal information.<br /><br /><br />
<h2>Viruses, hacking and other offences</h2>
<p><span style="font-weight: 400;">You must not misuse our site by knowingly introducing viruses, trojans, worms, logic bombs or other material that is malicious or technologically harmful. You must not attempt to gain unauthorised access to our site, the server on which our site is stored or any server, computer or database connected to our site.</span></p>
<p><span style="font-weight: 400;">By breaching this provision, you would commit a criminal offence. We will report any such breach to the relevant law enforcement authorities and we will co-operate with those authorities by disclosing your identity to them. In the event of such a breach, your right to use our site will cease immediately.</span></p>
<span style="font-weight: 400;">We will not be liable for any loss or damage caused by a distributed denial-of-service attack, viruses or other technologically harmful material that may infect your computer equipment, computer programs, data or other proprietary material due to your use of our site or to your downloading of any material posted on it, or on any website linked to it.<br /><br /></span><br />
<h2>Breach of Terms and Conditions</h2>
<span style="font-weight: 400;">If we consider that a breach of these Terms and Conditions has occurred, we may take such action as we deem appropriate, including, without limitation, all or any of the following actions: Immediate, temporary or permanent withdrawal of your right to use our site; Immediate, temporary or permanent removal of any posting or material uploaded by you to our site; issue of a warning to you; legal proceedings against you for reimbursement of all costs on an indemnity basis (including, but not limited to, reasonable administrative and legal costs) resulting from the breach; further legal action against you; disclosure of such information to law enforcement authorities as we feel is necessary. We exclude liability for actions taken in response to breaches of this policy.<br /><br /></span></div>
<h2 style="text-align: justify;"><br />Governing law and jurisdiction</h2>
This contract and any dispute, claim or other matter of any description that arises out of or in connection with this contract is governed by and shall be construed in accordance with law of {{ company.country.title }}. The courts of {{ company.country.title }} shall have jurisdiction to decide any dispute or claim that arises out of or in connection with this contract.<br /><br /><br /><br /><span>-</span>',
                ],
            ],
            [
                'layout_id' => 2,
                'url'       => '/privacy-policy',
                'slug'      => 'privacy-policy',
                'title'     => 'Privacy policy',
                'morph'     => [
                    'action'  => 'derive-content-company',
                    'content' => '<h2><b>Introduction</b></h2>
<p><span style="font-weight: 400;">This policy (together with our <a href="/terms-and-conditions" target="_blank">Terms and Conditions</a>) defines the basis on which any personal data is collected from you, or that you provide to us, will be processed. Please read the following carefully to understand our views and practices regarding your personal data.</span></p>
<p><span style="font-weight: 400;">The {{ config(\'site.contact.name\') }} website and online booking system is available at the web address {{ config(\'url\') }} (hereinafter referred to as Website) is held and operated by:</span></p>
<p><span style="font-weight: 400;">{{ company.long_name }}</span></p>
<p><span style="font-weight: 400;">{{ company.address_line1 }}</span></p>
<p><span style="font-weight: 400;">{{ company.address_line2 }}</span></p>
<p><span style="font-weight: 400;">{{ company.address_line3 }}</span></p>
<p><span style="font-weight: 400;">{{ company.country.title }}</span></p>
<p><span style="font-weight: 400;">VAT: {{ company.vat_number }}</span></p>
<p><span style="font-weight: 400;">Business number: {{ company.business_number }}</span></p>
<p><span style="font-weight: 400;">{{ company.note1 }}</span></p>
<p><span style="font-weight: 400;">{{ company.note2 }}</span></p>
<p><span style="font-weight: 400;">(hereinafter &ldquo;</span><span style="font-weight: 400;">{{ company.short_name }}</span><span style="font-weight: 400;">&rdquo; or &ldquo;us&rdquo; or &ldquo;we&rdquo;).</span></p>
<p><span style="font-weight: 400;">Hereinafter, all the services accessible through the Website are referred to as the &ldquo;Service&rdquo;. All visitors of the Website, whether guests or registered users, are hereinafter referred to as "User". </span><span style="font-weight: 400;">We may revise this Privacy Policy at any time without prior notice.<br /><br /></span></p>
<h2><b>Legislature</b></h2>
<p><span style="font-weight: 400;">{{ company.short_name }} as the data controller</span><span style="font-weight: 400;"> guarantees all data of Users are processed in accordance with the EU Data Protection Directive 2016.<br /><br /></span></p>
<h2><b>Information we may collect from you</b></h2>
<p><span style="font-weight: 400;">We may collect and process the following data about you:</span></p>
<p><span style="font-weight: 400;">Information that you provide by filling in forms on our Website. This includes information provided at the time of registering to use our site, subscribing to our newsletters, posting reviews or other material on our site or requesting further services. We may also ask you for information when you enter a competition or promotion and when you report a problem with our site.</span></p>
<p><span style="font-weight: 400;">If you contact us, we may keep a record of that correspondence.</span></p>
<p><span style="font-weight: 400;">We may also ask you to complete surveys that we use for research purposes, although you do not have to respond to them.</span></p>
<p><span style="font-weight: 400;">Details of bookings that you carry out through our site and of the fulfilment of your bookings.</span></p>
<p><span style="font-weight: 400;">Details of your visits to our site including, but not limited to, traffic data, location data, logs and other communication data, whether this is required for our own billing purposes or otherwise and the resources that you access.<br /><br /></span></p>
<h2><b>Method for collection of personal details</b></h2>
<p><span style="font-weight: 400;">Information is collected based on forms or questionnaires that need to be filled in. Information we collect based is provided voluntarily. The User is obliged to provide only information needed to make a booking.</span></p>
<p><span style="font-weight: 400;">Minors who want to use the Service need to be supervised by at least one of their parents. &nbsp;{{ company.short_name }} is not liable if minors use the Service without the required parental control.+<br /><br /></span></p>
<h2><b>Required and optional personal details</b></h2>
<p><span style="font-weight: 400;">In the context of using the Service, Users can be invited to fill out forms or questionnaires in which certain fields are required/obligatory to carry out the Service. This will be clearly indicated. The other fields are optional.</span></p>
<p><span style="font-weight: 400;">The aim of the optional fields is to allow {{ company.short_name }} to contact the Users more quickly and/or to obtain information that is relevant in the context of the Service.<br /><br /></span></p>
<h2><b>IP addresses</b></h2>
<p><span style="font-weight: 400;">We may collect information about your computer, including where available your IP address, operating system and browser type, for system administration and to report aggregate information to our advertisers. This is statistical data about our users\' browsing actions and patterns, and does not identify any individual.<br /><br /></span></p>
<h2><b>Cookies</b></h2>
<p><span style="font-weight: 400;">While using the Service, we may use cookies, originating from the domain </span><span style="font-weight: 400;">{{ config(\'url\') }} </span><span style="font-weight: 400;">and all its subdomains. We use cookies to distinguish you from other users of our website. This helps us to provide you with a good experience when you browse our website and also allows us to improve our site.</span></p>
<p><span style="font-weight: 400;">A cookie is a small amount of data, which often includes an anonymous unique identifier that is sent to the browser from a website\'s computer and stored on your computer\'s hard drive if you agree.</span></p>
<p><span style="font-weight: 400;">Cookies can be ours or third party. We do not store any personal details in our cookies that can be used to identify individuals, however we do use cookies for tracking purposes.</span></p>
<p><span style="font-weight: 400;">Although cookies are required to be able to fully use the Service and all of its features, Users can always refuse to accept cookies.</span></p>
<p><span style="font-weight: 400;">We will ask you for your consent to our use of cookies in accordance and agreement with this policy. If you do not consent, but then continue to view our site or make a booking on our site, you will be deemed to have consented to us setting cookies in accordance with this policy.<br /><br /></span></p>
<h2><b>Storage and transmission</b></h2>
<p><span style="font-weight: 400;">The data that we collect from you normally will not be, but may be, transferred to, and stored at, a destination outside the European Economic Area ("EEA"). It may also be processed by staff operating outside the EEA who work for us or for one of our suppliers. Such staff maybe engaged in, among other things, the fulfilment of your bookings, the processing of your payment details and the provision of support services. By submitting your personal data, you agree to this transfer, storing or processing. We will take all steps reasonably necessary to ensure that your data is treated securely and in accordance with this privacy policy. All information you provide to us is stored on our secure servers. Any payment transactions will be encrypted. Where we have given you (or where you have chosen) a password that enables you to access certain parts of our site, you are responsible for keeping this password confidential. We expect that you will not share this password with any other person or service and will not support any loss incurred through its sharing. Unfortunately, the transmission of information via the internet is not completely secure. Although we will do our best to protect your personal data, we cannot guarantee the security of your data transmitted to our site; any transmission is at your own risk. Once we have received your information, we will use procedures and security features to try to prevent unauthorised access.<br /><br /></span></p>
<h2><b>Goals and use</b></h2>
<p><span style="font-weight: 400;">Personal details can only be collected and processed by {{ company.short_name }} for one of the following goals:</span></p>
<ul>
<li style="font-weight: 400;"><span style="font-weight: 400;">To ensure that content from our site is presented in the most effective manner for you and for your computer</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">to meet contractual obligations and rights between {{ company.short_name }} and User;</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">for the registration and participation in the Service (including but not limited to Contests);</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">for research and statistical goals;</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">to enable {{ company.short_name }} or its partners to contact Users concerning the Service or additional related Services;</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">to improve the content and quality of the Service;</span></li>
<li style="font-weight: 400;"><span style="font-weight: 400;">to draw up collective profiles (not of individual people);</span></li>
</ul>
<p><span style="font-weight: 400;">If you do not want us to use your data in this way, please either tick the relevant box situated on the form on which we collect your data, unsubscribe to any emails your receive or notify us at {{ config(\'site.contact.email\') }}.<br /><br /></span></p>
<h2><b>Right of access to and correction of your personal details</b></h2>
<p><span style="font-weight: 400;">You have the right to access information held about you. Users are able at all times to check the state of their personal details collected and processed by {{ company.short_name }} if they sign in to their account or send us an email to </span><span style="font-weight: 400;">{{ config(\'site.contact.email\') }}</span><span style="font-weight: 400;">.</span></p>
<p><span style="font-weight: 400;">The request to access and check the Users\' information must arrive from the same email address the User signed up with. {{ company.short_name }} will inform the User on the state of your personal details as soon as possible.</span></p>
<p><span style="font-weight: 400;">In case of a mistake, inaccuracy or hiatus regarding your personal details, the User can ask us to correct or change this information.</span></p>
<p><span style="font-weight: 400;">If the User believes that the Service does not respect the law regarding the protection of personal details, he can contact us via the contact details mentioned in article 10 of the present Privacy Policy.<br /><br /></span></p>
<h2><b>Consent and removal</b></h2>
<p><span style="font-weight: 400;">By using the Service, Users explicitly agree with the Privacy Policy of {{ company.short_name }}. If Users want their personal details to be removed from our database, please contact us by e-mail or regular post using the contact details mentioned in article 10 of the present Privacy Policy. We undertake to remove personal details as soon as possible, except of course if it concerns information that needs to be kept in pursuance of the law or an agreement.<br /><br /></span></p>
<h2><b>Property and disclosure</b></h2>
<p><span style="font-weight: 400;">All information and personal data gathered in relation to the Service, belong to {{ company.short_name }} and shall only be used in accordance with the principles and provisions specified in the present Privacy Policy.</span></p>
<p><span style="font-weight: 400;">We may disclose your personal information to third parties:</span></p>
<p><span style="font-weight: 400;">In the event that we sell or buy any business or assets, in which case we may disclose your personal data to the prospective seller or buyer of such business or assets.</span></p>
<p><span style="font-weight: 400;">If we or substantially all of our assets are acquired by a third party, in which case personal data held by us about our customers will be one of the transferred assets.</span></p>
<p><span style="font-weight: 400;">If we are under a duty to disclose or share your personal data in order to comply with any legal obligation, or in order to enforce or apply our terms of use or our booking conditions or to protect the rights, property, or safety of {{ company.short_name }}, our customers, or others. This includes exchanging information with other companies and organisations for the purposes of fraud protection and credit risk reduction.<br /><br /></span></p>
<h2><b>Miscellaneous</b></h2>
<p><span style="font-weight: 400;">This Privacy Policy is without prejudice to {{ company.short_name }} right to take further reaching actions vis-&agrave;-vis certain users of the Service, based on an agreement, the law, regulations, etc. Users agree that {{ company.short_name }} may periodically update this Privacy Policy without prior notice. Any questions regarding this privacy policy should be addressed to {{ config(\'site.contact.email\') }}.</span></p>',
                ],
            ],
        ];

        (new Collection($routes))->each(function($route) {
            $routeRecord = (new Routes())->joinTranslations()->where('slug', $route['slug'])->one();

            if ($routeRecord) {
                return;
            }

            $routeRecord = Route::create([
                                             'route'     => $route['url'],
                                             'slug'      => $route['slug'],
                                             'title'     => $route['title'],
                                             'layout_id' => $route['layout_id'],
                                         ]);

            $action = Action::getOrFail(['slug' => $route['morph']['action']]);

            $content = ContentRecord::create(['content' => $route['morph']['content']]);

            ActionsMorph::create([
                                     'action_id'   => $action->id,
                                     'content_id'  => $content->id,
                                     'morph_id'    => Routes::class,
                                     'poly_id'     => $routeRecord->id,
                                     'variable_id' => 1,
                                 ]);
        });
    }

}