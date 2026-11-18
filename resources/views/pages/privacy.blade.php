@extends('pages._shell')
@section('heading', 'गोपनीयता नीति / Privacy Policy')
@section('sub', 'आपकी जानकारी आपकी अमानत है। Digital Personal Data Protection Act, 2023 (DPDP Act 2023) के अनुसार।')

@section('page')
<section>
    <h2>1. हम कौन सी जानकारी लेते हैं / What we collect</h2>
    <ul>
        <li>नाम, फ़ोन नंबर, शहर, गोत्र, बायोडाटा विवरण और आपकी अपलोड की गई फ़ोटो।</li>
        <li>ऐप उपयोग से जुड़ी बुनियादी जानकारी (जैसे लॉगिन समय, नोटिफिकेशन सेटिंग)।</li>
        <li>हम <strong>आधार नंबर नहीं माँगते और न ही सेव करते हैं</strong>। We do not collect or store Aadhaar numbers.</li>
    </ul>
</section>
<section>
    <h2>2. सहमति / Consent (DPDP Act 2023)</h2>
    <p>पंजीकरण करके आप स्पष्ट रूप से सहमति देते हैं कि आपकी जानकारी केवल रिश्ता खोजने और समाज के मंच के उद्देश्य से उपयोग हो। आप किसी भी समय सहमति वापस ले सकते हैं।</p>
    <p>By registering you give free, specific and informed consent for processing your data solely for matrimonial matching and community features. You may withdraw consent at any time.</p>
</section>
<section>
    <h2>3. आपकी प्राइवेसी कैसे सुरक्षित है / How we protect you</h2>
    <ul>
        <li>आपका संपर्क नंबर तब तक छिपा रहता है जब तक आप किसी का इंटरेस्ट स्वीकार नहीं करते।</li>
        <li>फ़ोटो ब्लर और वॉटरमार्क का विकल्प उपलब्ध है।</li>
        <li>प्रोफ़ाइल केवल अप्रूव्ड सदस्यों को दिखती है। Profiles are visible only to approved members.</li>
        <li>हम आपका डेटा बेचते या विज्ञापनदाताओं को नहीं देते।</li>
    </ul>
</section>
<section>
    <h2>4. आयु / Age requirement</h2>
    <p>लड़की के लिए न्यूनतम 18 वर्ष और लड़के के लिए 21 वर्ष। इससे कम आयु के प्रोफ़ाइल स्वीकार नहीं किए जाते। Minors are not permitted.</p>
</section>
<section>
    <h2>5. आपके अधिकार और डेटा हटाना / Your rights and data deletion</h2>
    <ul>
        <li>अपनी जानकारी देखने, सुधारने और अपडेट करने का अधिकार।</li>
        <li>अपना खाता और सारा डेटा हटाने का अनुरोध ऐप में खाता सेटिंग से या एडमिन को संपर्क करके करें। अनुरोध के बाद उचित समय में डेटा स्थायी रूप से हटा दिया जाता है (कानूनी रूप से आवश्यक रिकॉर्ड को छोड़कर)।</li>
        <li>You may request access, correction and erasure of your personal data, and nominate someone to exercise your rights, as per the DPDP Act 2023.</li>
    </ul>
</section>
<section>
    <h2>6. सुरक्षा और शिकायत / Security and grievance</h2>
    <p>हम पासवर्ड एन्क्रिप्टेड रखते हैं और उचित सुरक्षा उपाय अपनाते हैं। किसी भी शिकायत के लिए एडमिन से ऐप के माध्यम से संपर्क करें। हम शिकायतों का शीघ्र समाधान करेंगे।</p>
</section>
<section>
    <h2>7. नीति में बदलाव</h2>
    <p>नीति बदलने पर इसी पेज पर अपडेट किया जाएगा। देखें भी: <a href="{{ route('page.terms') }}">नियम और शर्तें</a>।</p>
</section>
@endsection
