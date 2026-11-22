<?php
require_once __DIR__ . '/../core/core.php';

/*
 * The Crown Journal for visitors, with article content copied from the Wix (CMS)
 * site. Photos load from images/journal/ when they have been downloaded
 * (crowncare/tools/download_journal_images.ps1), otherwise from Wix.
 */

/* Photos: index => Wix media id. Local copies are saved as images/journal/journal-<index>.jpg */
$journalImages = [
    0 => '67839a_67f8195489d1438395d54cae37962414~mv2.webp',
    2 => '67839a_498799c1713c4764af132046303fc651~mv2.webp',
    5 => '67839a_57d950a57162443c9dd6d92ad83ddb89~mv2.jpg',
    6 => '67839a_fc4d517623984f14b384340b71dcf62e~mv2.jpg',
    7 => '67839a_81ee9ea50fbb4cd9b293554010149dea~mv2.jpg',
    9 => '67839a_ab1f2a56988148529c7c3854e9a159bd~mv2.jpg',
    12 => 'e1b2e8_83df5793b4ee4b35add7c5429fc4a462~mv2.webp',
    13 => 'e1b2e8_3c3461ac7bd34e6890117c3c3a59580e~mv2.webp',
    15 => 'e1b2e8_f66952bac70d490ca0d2d2b21fa6a8a9~mv2.webp',
];

/* For each photo: the local file if it exists, otherwise the Wix media id. */
$imageSources = [];
foreach ($journalImages as $index => $wixId) {
    $local = 'images/journal/journal-' . $index . '.jpg';
    $imageSources[$index] = is_file(__DIR__ . '/../' . $local)
        ? ['local' => BASE_URL . $local]
        : ['wix' => $wixId];
}

/** Photo URL for PHP-rendered images. */
$img = function ($index, $w, $h) use ($imageSources) {
    $s = $imageSources[$index] ?? null;
    if (!$s) {
        return '';
    }
    if (isset($s['local'])) {
        return $s['local'];
    }
    return 'https://static.wixstatic.com/media/' . $s['wix'] . '/v1/fill/w_' . $w . ',h_' . $h . ',al_c,q_82/img.jpg';
};

/* ---------- Articles ----------
   The body is trusted HTML copied from the CMS; it is passed to the script
   below as JSON and shown in the reader when a card is clicked. */
$posts = [
    [
        'id' => 'why-coily', 'cat' => 'Hair science', 'img' => 0, 'read' => 6,
        'title' => 'Why coily hair needs a different kind of care',
        'ex' => 'The shape of each strand explains the dryness, shrinkage, strength and fragility.',
        'body' => <<<'HTML'
<p class="jn-lede">Coily hair is the most versatile hair type in the world, and the most misunderstood. Once you see how it's built, caring for it starts to make sense.</p>
<h3>It starts at the follicle</h3><p>Straight hair grows from round follicles. Coily hair grows from flat, oval, curved follicles, so the strand twists and bends as it grows. Under a microscope, a 4C strand can change direction many times in a single centimetre.</p>
<h3>Why it feels dry</h3><p>Your scalp makes its own oil (sebum). On straight hair it slides down easily. On coily hair, every bend slows it down, so the ends rarely get any. That's why coils need moisture added from the outside, through water-based products sealed in with oils or butters.</p>
<h3>Strong, but fragile</h3><p>Coily strands are often thick, but every bend is a weak point where the cuticle is thinner. Combing dry hair, tight styles and heat put stress on those points, which leads to breakage.</p>
<div class="jn-tip"><b>Key point:</b> moisturise often, detangle only with conditioner in, handle your hair gently and protect your ends at night.</div>
<h3>And the shrinkage?</h3><p>Shrinkage is a sign of healthy, elastic hair. Your coils spring up as they dry. Stretch them with twists, braids or banding when you want to show length.</p>
HTML
    ],
    [
        'id' => 'harmattan', 'cat' => 'Climate', 'img' => 13, 'read' => 4,
        'title' => 'Caring for your hair during the harmattan',
        'ex' => 'How dry, dusty air affects your hair and how to adjust your routine.',
        'body' => <<<'HTML'
<p class="jn-lede">From around November to March, the harmattan brings dry, dusty winds across West Africa. Your skin feels it, and so does your hair.</p>
<h3>What happens to your hair</h3><p>Very dry air pulls water out of your strands, so hair feels rough, tangles more and breaks more easily. Dust also settles on the scalp and in your hair, which can cause itching and buildup.</p>
<h3>How to adjust</h3><ul><li>Switch to a richer leave-in and seal with a butter instead of a light oil.</li><li>Use glycerin-heavy products carefully. In very dry air, glycerin can pull water out of hair instead of in.</li><li>Keep your hair covered outdoors with a scarf or a protective style.</li><li>Deep condition every week instead of every two.</li><li>Rinse dust out with a gentle cleanser or co-wash if your scalp gets itchy.</li></ul>
<div class="jn-tip"><b>Tip:</b> keep a small spray bottle of water mixed with a little leave-in for a midday refresh.</div>
HTML
    ],
    [
        'id' => 'night', 'cat' => 'Routines', 'img' => 9, 'read' => 3,
        'title' => 'A night-time routine to protect your hair',
        'ex' => 'How sleep habits affect moisture and breakage.',
        'body' => <<<'HTML'
<p class="jn-lede">You spend a third of your life asleep, and most of it with your hair rubbing against a pillow. That friction adds up.</p>
<h3>Why cotton is the problem</h3><p>Cotton is absorbent and rough. It soaks up the moisture you put in and catches on your ends, causing frizz and breakage.</p>
<h3>Your five-minute routine</h3><ul><li>Lightly moisturise any dry areas, especially the ends.</li><li>Gather your hair loosely: a high "pineapple" for curls, or loose twists for longer hair.</li><li>Cover with a satin bonnet or scarf, or sleep on a satin or silk pillowcase.</li><li>Keep edges free of tight ties.</li></ul>
<div class="jn-tip"><b>Tip:</b> a satin-lined bonnet also keeps your style looking fresh for days longer.</div>
HTML
    ],
    [
        'id' => 'washday', 'cat' => 'Routines', 'img' => 5, 'read' => 5,
        'title' => 'A step-by-step wash day guide',
        'ex' => 'A simple wash day routine for beginners.',
        'body' => <<<'HTML'
<p class="jn-lede">Wash day doesn't need to take all day. With a simple order of steps, most people finish in about an hour.</p>
<h3>1. Pre-poo (optional)</h3><p>Coat dry ends with oil or conditioner 15–30 minutes before washing to protect them.</p>
<h3>2. Section</h3><p>Split your hair into 4–6 sections. Small sections mean less tangling and less breakage.</p>
<h3>3. Cleanse the scalp</h3><p>Focus shampoo on your scalp and let it run down the lengths. Massage with your fingertips.</p>
<h3>4. Condition and detangle</h3><p>Apply conditioner generously, then detangle with your fingers or a wide-tooth comb, from ends to roots.</p>
<h3>5. Deep condition</h3><p>Every one to two weeks, leave a mask on for 15–30 minutes, ideally under a shower cap.</p>
<h3>6. Moisturise and seal</h3><p>On damp hair, apply a leave-in, then a cream, then a little oil on the ends. Style as usual.</p>
HTML
    ],
    [
        'id' => 'porosity', 'cat' => 'Hair science', 'img' => 12, 'read' => 4,
        'title' => 'Understanding hair porosity',
        'ex' => 'Why some products sit on top of your hair while others vanish.',
        'body' => <<<'HTML'
<p class="jn-lede">Porosity describes how easily your hair lets water in and how well it holds on to it. It explains a lot of product frustration.</p>
<h3>Low porosity</h3><p>The cuticle is tightly closed. Hair takes a long time to get wet and products tend to sit on top. Use lighter products, apply them to damp hair, and add gentle warmth when deep conditioning.</p>
<h3>High porosity</h3><p>The cuticle is more open, often from heat, colour or rough handling. Hair absorbs water fast and loses it fast. Use richer creams, seal well, and add occasional protein.</p>
<h3>How to tell</h3><p>Watch your hair on wash day. If water beads up and it takes ages to get fully wet, you likely have low porosity. If it soaks through instantly and dries very quickly, you likely have high porosity. The popular "float test" isn't very reliable.</p>
HTML
    ],
    [
        'id' => 'edges', 'cat' => 'Scalp & edges', 'img' => 15, 'read' => 4,
        'title' => 'Protecting your edges and hairline',
        'ex' => 'Why the hairline is the most fragile area and how to care for it.',
        'body' => <<<'HTML'
<p class="jn-lede">The hair around your hairline is finer than the rest, and it carries the most tension from styles, gels and brushing.</p>
<h3>Traction alopecia</h3><p>Repeated pulling from tight braids, ponytails or wigs can damage follicles over time. Early signs include soreness, small bumps and thinning at the temples. Caught early, it often improves once the tension stops.</p>
<h3>Daily edge care</h3><ul><li>Ask your stylist for looser edges and don't sit through pain.</li><li>Use a soft brush and a light edge control, and wash it out regularly.</li><li>Alternate styles so the same spots aren't always under strain.</li><li>Massage gently with a few drops of oil.</li></ul>
<div class="jn-tip"><b>See a professional</b> if thinning is sudden, patchy or doesn't improve after you loosen your styles.</div>
HTML
    ],
    [
        'id' => 'transition', 'cat' => 'Journeys', 'img' => 2, 'read' => 5,
        'title' => 'Transitioning from relaxed to natural',
        'ex' => 'Big chop or long transition? How to choose, and how to handle two textures.',
        'body' => <<<'HTML'
<p class="jn-lede">Going natural is personal. Some people cut everything off in one day, while others grow out slowly over a year or more.</p>
<h3>The big chop</h3><p>Cutting off all the relaxed hair gives you a fresh start with one texture to learn. It's quick and freeing, but it means starting short.</p>
<h3>Long-term transitioning</h3><p>You keep your length and trim the relaxed ends bit by bit. The challenge is the line where the two textures meet, which is weak and breaks easily.</p>
<h3>Tips for transitioners</h3><ul><li>Choose styles that blend both textures: twist-outs, braid-outs, flexi-rods and buns.</li><li>Detangle extra gently at the line of demarcation.</li><li>Deep condition every week, adding protein now and then.</li><li>Skip heat to avoid damaging your new growth.</li></ul>
HTML
    ],
    [
        'id' => 'styling', 'cat' => 'Styling', 'img' => 7, 'read' => 3,
        'title' => 'Wash-and-go or twist-out: choosing a style',
        'ex' => 'Two of the most popular natural styles, compared.',
        'body' => <<<'HTML'
<p class="jn-lede">Both styles show off your natural texture. They just do it differently.</p>
<h3>Wash-and-go</h3><p>Apply a leave-in and gel to soaking-wet hair and let your curls clump on their own. It's best for defined coils and quick mornings, but expect shrinkage.</p>
<h3>Twist-out</h3><p>Two-strand twist damp hair with a cream, let it dry fully, then unravel. You get stretched, defined curls with more visible length, and it lasts several days.</p>
<div class="jn-tip"><b>In summary:</b> want definition and speed? Wash-and-go. Want length and a softer look? Twist-out.</div>
HTML
    ],
    [
        'id' => 'growth', 'cat' => 'Hair science', 'img' => 6, 'read' => 4,
        'title' => 'Length retention and healthy hair growth',
        'ex' => 'Why hair breaks and how to keep the length you grow.',
        'body' => <<<'HTML'
<p class="jn-lede">If your hair seems stuck at the same length, it's almost never because it isn't growing. It's because the ends are breaking as fast as the roots grow.</p>
<h3>Where length is lost</h3><ul><li>Rough detangling, especially on dry hair.</li><li>Dry, brittle ends that snap.</li><li>Friction from cotton, collars and scarves.</li><li>Heat damage and tight styles.</li></ul>
<h3>How to keep it</h3><ul><li>Moisturise and seal your ends a few times a week.</li><li>Detangle only with conditioner in.</li><li>Wear low-manipulation styles and sleep on satin.</li><li>Trim split ends every few months.</li></ul>
<div class="jn-tip"><b>Tip:</b> take a photo of a stretched section on the first day of each month. Progress is easier to see over time.</div>
HTML
    ],
];

$pageTitle = 'The Crown Journal';
$pageDescription = 'Articles on hair science, routines, scalp care and styling for natural and African hair.';
require_once __DIR__ . '/layout/header.php';
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/journal.css?v=20261118">

<main class="cms-journal">

    <header class="jn-mast jn-wrap" id="top">
        <h1 class="jn-title">The Crown <i>Journal</i></h1>
        <p>Articles, routines and seasonal advice for natural hair.</p>
    </header>

    <section class="jn-wrap jn-feature">
        <div class="jn-hero-card" data-read="why-coily" role="button" tabindex="0">
            <img src="<?php echo $img(0, 1100, 1200); ?>" alt="Woman with natural coily hair">
            <div class="jn-shade"></div>
            <div class="jn-txt">
                <span class="jn-pill">Featured article</span>
                <h3>Why coily hair needs a different kind of care</h3>
                <p>How the shape of each strand affects dryness, shrinkage, strength and breakage.</p>
            </div>
        </div>
        <div class="jn-side">
            <div class="jn-mini" data-read="harmattan" role="button" tabindex="0">
                <img src="<?php echo $img(13, 260, 260); ?>" alt="" loading="lazy">
                <div><span class="jn-pill">Climate</span><h4>Caring for your hair during the harmattan</h4><span>4 min read</span></div>
            </div>
            <div class="jn-mini" data-read="night" role="button" tabindex="0">
                <img src="<?php echo $img(9, 260, 260); ?>" alt="" loading="lazy">
                <div><span class="jn-pill">Routines</span><h4>A night-time routine to protect your hair</h4><span>3 min read</span></div>
            </div>
            <div class="jn-quote">
                <p>"Healthy hair isn't about the perfect product. It's about understanding what your hair is asking for."</p>
                <small>The CrownCare team</small>
            </div>
        </div>
    </section>

    <section class="jn-sec jn-wrap" id="blog">
        <div class="jn-sec-head">
            <div><div class="jn-kicker">All articles</div><h2>Latest articles</h2></div>
            <div class="jn-filters" id="jnFilters"></div>
        </div>
        <div class="jn-grid" id="jnGrid"></div>
        <article class="jn-reader" id="jnReader"></article>
    </section>

    <p class="jn-note jn-wrap">
        The Crown Journal shares general hair-care information, not medical advice.
        For ongoing scalp pain, sores, bald patches or sudden hair loss, please see a dermatologist.
    </p>

</main>

<!-- Articles and image sources handed to the script that filters and opens them. -->
<script type="application/json" id="jnData"><?php echo json_encode(['posts' => $posts, 'images' => $imageSources], JSON_HEX_TAG | JSON_HEX_AMP); ?></script>
<script>
(function () {
    var data = JSON.parse(document.getElementById('jnData').textContent);
    var POSTS = data.posts;
    var IMAGES = data.images;

    function src(i, w, h) {
        var s = IMAGES[i];
        if (!s) return '';
        if (s.local) return s.local;
        return 'https://static.wixstatic.com/media/' + s.wix + '/v1/fill/w_' + w + ',h_' + h + ',al_c,q_82/img.jpg';
    }

    function esc(s) {
        return String(s).replace(/[&<>"]/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
        });
    }

    function goTo(el) {
        if (!el) return;
        el.setAttribute('tabindex', '-1');
        el.style.outline = 'none';
        el.style.scrollMarginTop = '120px';
        try { el.focus({ preventScroll: true }); } catch (e) {}
        el.scrollIntoView({ block: 'start' });
    }

    var cats = ['All'];
    POSTS.forEach(function (p) { if (cats.indexOf(p.cat) === -1) cats.push(p.cat); });
    var cat = 'All';

    var grid = document.getElementById('jnGrid');
    var reader = document.getElementById('jnReader');
    var filters = document.getElementById('jnFilters');

    function renderFilters() {
        filters.innerHTML = cats.map(function (c) {
            return '<button type="button" class="jn-tab" aria-selected="' + (c === cat) + '" data-c="' + esc(c) + '">' + esc(c) + '</button>';
        }).join('');
    }

    function renderGrid() {
        var list = POSTS.filter(function (p) { return cat === 'All' || p.cat === cat; });
        grid.innerHTML = list.map(function (p, i) {
            var wide = i === 0 && cat === 'All';
            return '<button type="button" class="jn-post' + (wide ? ' jn-wide' : '') + '" data-read="' + p.id + '">' +
                '<div class="jn-img"><img src="' + src(p.img, wide ? 1200 : 700, wide ? 675 : 525) + '" alt="" loading="lazy"></div>' +
                '<span class="jn-pill">' + esc(p.cat) + '</span>' +
                '<h3>' + esc(p.title) + '</h3>' +
                '<p>' + esc(p.ex) + '</p>' +
                '<span class="jn-meta">' + p.read + ' min read</span>' +
                '</button>';
        }).join('');
    }

    function openPost(id) {
        var p = POSTS.filter(function (x) { return x.id === id; })[0];
        if (!p) return;
        var next = POSTS[(POSTS.indexOf(p) + 1) % POSTS.length];
        reader.innerHTML =
            '<button type="button" class="jn-back-btn" data-back>Back to all articles</button>' +
            '<span class="jn-pill">' + esc(p.cat) + '</span>' +
            '<h1>' + esc(p.title) + '</h1>' +
            '<div class="jn-meta">' + p.read + ' min read</div>' +
            '<div class="jn-cover"><img src="' + src(p.img, 1400, 700) + '" alt=""></div>' +
            p.body +
            '<div class="jn-next">' +
            '<button type="button" class="jn-btn" data-read="' + next.id + '">Next article</button>' +
            '<button type="button" class="jn-btn jn-ghost" data-back>Back to all articles</button>' +
            '</div>';
        if (innerWidth > 900) reader.style.minHeight = grid.offsetHeight + 'px';
        grid.style.display = 'none';
        filters.style.visibility = 'hidden';
        reader.classList.add('jn-on');
        goTo(document.getElementById('blog'));
    }

    function closePost() {
        reader.classList.remove('jn-on');
        grid.style.display = '';
        filters.style.visibility = '';
        goTo(document.getElementById('blog'));
    }

    filters.addEventListener('click', function (e) {
        var b = e.target.closest('[data-c]');
        if (!b) return;
        cat = b.dataset.c;
        renderFilters();
        renderGrid();
    });

    var main = document.querySelector('.cms-journal');
    main.addEventListener('click', function (e) {
        var r = e.target.closest('[data-read]');
        if (r) { openPost(r.dataset.read); return; }
        if (e.target.closest('[data-back]')) closePost();
    });
    main.addEventListener('keydown', function (e) {
        var r = e.target.closest('[role="button"][data-read]');
        if (r && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); openPost(r.dataset.read); }
    });

    renderFilters();
    renderGrid();
    requestAnimationFrame(function () { if (innerWidth > 900) grid.style.minHeight = grid.offsetHeight + 'px'; });
})();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
