<?php
require_once __DIR__ . '/../core/core.php';

$articles = [
    [
        'id' => 1,
        'title' => 'Why coily hair needs a different kind of care',
        'category' => 'Hair science',
        'read_time' => '6 min read',
        'image' => BASE_URL . 'images/home/hair-guide.jpg',
        'excerpt' => 'The shape of each strand affects dryness, shrinkage, strength and breakage.',
        'body' => [
            'Coily hair has a naturally curved structure. The bends and twists along each strand can make it harder for natural scalp oils to travel from the roots to the ends.',
            'This is one reason coily hair may feel dry, even when you are following a regular hair-care routine. It does not mean your hair is unhealthy. It means your routine should take its structure into account.',
            'Gentle cleansing, conditioning, careful detangling and reducing unnecessary friction can all help. Try working in sections and avoid pulling through knots.',
            'Shrinkage is also a normal characteristic of coily hair. It is not a measure of hair length or health. Learn what your hair responds to and build your routine around its actual needs.'
        ]
    ],
    [
        'id' => 2,
        'title' => 'Caring for your hair during the harmattan',
        'category' => 'Climate',
        'read_time' => '4 min read',
        'image' => BASE_URL . 'images/home/hair-quiz.jpg',
        'excerpt' => 'How dry, dusty air affects your hair and how to adjust your routine.',
        'body' => [
            'Harmattan weather can bring dry air, dust and changes in humidity. These conditions may leave some people noticing more dryness, tangling or breakage.',
            'Consider using a conditioner after washing and a leave-in product if your hair responds well to one. Protective styles can also reduce daily manipulation, provided they are not installed too tightly.',
            'Pay attention to your ends, since they are the oldest parts of your hair and may need extra care.',
            'Your routine does not need to become complicated. Small, consistent adjustments can make a difference.'
        ]
    ],
    [
        'id' => 3,
        'title' => 'A night-time routine to protect your hair',
        'category' => 'Routines',
        'read_time' => '3 min read',
        'image' => BASE_URL . 'images/home/hair-guide.jpg',
        'excerpt' => 'A few simple habits before bed can help reduce friction and tangling.',
        'body' => [
            'Your hair-care routine does not end when you leave the bathroom. The way you protect your hair at night can influence how it looks and feels the next morning.',
            'If your hair is long enough, gently section or loosely gather it before sleeping. Avoid tight bands that pull on your edges.',
            'A satin or silk bonnet, scarf or pillowcase can help reduce friction compared with some rougher fabrics.',
            'Choose a method that feels comfortable and does not create tension. Consistency matters more than having an elaborate night-time routine.'
        ]
    ],
    [
        'id' => 4,
        'title' => 'Getting to know your scalp and edges',
        'category' => 'Scalp & edges',
        'read_time' => '5 min read',
        'image' => BASE_URL . 'images/home/hair-quiz.jpg',
        'excerpt' => 'Why scalp comfort and gentle styling deserve a place in your hair routine.',
        'body' => [
            'Your scalp is part of your hair-care routine. Regular cleansing helps remove sweat, excess oil and product buildup.',
            'Your edges can be especially vulnerable to repeated tension from tight hairstyles, extensions and frequent pulling.',
            'Choose comfortable styles and avoid ignoring pain or persistent irritation. Give your hairline breaks from styles that place tension on it.',
            'If you notice persistent thinning, irritation or scalp discomfort, consider seeking advice from a qualified healthcare professional.'
        ]
    ],
    [
        'id' => 5,
        'title' => 'Learning to appreciate your natural hair journey',
        'category' => 'Journeys',
        'read_time' => '4 min read',
        'image' => BASE_URL . 'images/home/hair-guide.jpg',
        'excerpt' => 'Your hair journey is personal. Give yourself room to learn what works for you.',
        'body' => [
            'Natural hair journeys are rarely identical. Your texture, lifestyle, preferences and the time you have available all influence your routine.',
            'It can be tempting to compare your progress with someone else’s. But hair growth, shrinkage, density and styling preferences vary from person to person.',
            'Take note of what works, be patient when trying new routines and remember that healthy hair care is not about achieving one particular look.',
            'The goal is to understand your own hair and care for it in a way that works for you.'
        ]
    ],
    [
        'id' => 6,
        'title' => 'Protective styling without the unnecessary tension',
        'category' => 'Styling',
        'read_time' => '5 min read',
        'image' => BASE_URL . 'images/home/hair-quiz.jpg',
        'excerpt' => 'Braids and twists can be useful styles, but comfort should always come first.',
        'body' => [
            'Protective styles such as braids and twists can reduce daily manipulation. However, a style should not hurt to be considered protective.',
            'Excessive tension can put stress on the hairline and scalp. Choose a stylist who listens to your comfort needs and avoid styles that feel painfully tight.',
            'Keep your scalp clean and pay attention to how your hair feels while it is styled.',
            'Give your hair time to rest between installations. Protective styling should support your hair-care routine, not work against it.'
        ]
    ]
];

$featured = $articles[0];
$sideArticles = [$articles[1], $articles[2]];
$latestArticles = array_slice($articles, 1);
?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="crown-journal">

    <!-- MAGAZINE MASTHEAD -->
    <section class="cj-masthead">
        <div class="cj-masthead-rule">
            <span>THE CROWNCARE EDITION</span>
            <span>HAIR • CARE • CULTURE</span>
        </div>

        <h1>The Crown <em>Journal</em></h1>

        <p>Articles, routines and seasonal advice for natural hair.</p>
    </section>


    <!-- TOP EDITORIAL STORIES -->
    <section class="cj-top-stories">

        <article class="cj-cover-story">
            <img
                src="<?php echo htmlspecialchars($featured['image']); ?>"
                alt="<?php echo htmlspecialchars($featured['title']); ?>"
            >

            <div class="cj-cover-overlay">
                <span class="cj-cover-label">FEATURED ARTICLE</span>

                <span class="cj-cover-category">
                    <?php echo htmlspecialchars($featured['category']); ?>
                </span>

                <h2><?php echo htmlspecialchars($featured['title']); ?></h2>

                <p><?php echo htmlspecialchars($featured['excerpt']); ?></p>

                <button
                    type="button"
                    class="cj-read-button cj-light-button"
                    data-article="<?php echo $featured['id']; ?>"
                >
                    Read the story <span>↗</span>
                </button>
            </div>
        </article>


        <div class="cj-side-stories">

            <?php foreach ($sideArticles as $article): ?>

                <article class="cj-side-story">

                    <img
                        src="<?php echo htmlspecialchars($article['image']); ?>"
                        alt="<?php echo htmlspecialchars($article['title']); ?>"
                    >

                    <div class="cj-side-story-content">

                        <span class="cj-category-tag">
                            <?php echo htmlspecialchars($article['category']); ?>
                        </span>

                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>

                        <span class="cj-read-time">
                            <?php echo htmlspecialchars($article['read_time']); ?>
                        </span>

                        <button
                            type="button"
                            class="cj-text-link"
                            data-article="<?php echo $article['id']; ?>"
                        >
                            Read article ↗
                        </button>

                    </div>
                </article>

            <?php endforeach; ?>


            <div class="cj-editorial-quote">
                <span class="cj-quote-mark">“</span>

                <blockquote>
                    Healthy hair isn't about the perfect product.
                    It's about understanding what your hair is asking for.
                </blockquote>

                <span class="cj-quote-credit">THE CROWNCARE TEAM</span>
            </div>

        </div>
    </section>


    <!-- LATEST ARTICLES -->
    <section class="cj-latest" id="latest-articles">

        <div class="cj-latest-heading">
            <div>
                <span class="cj-eyebrow">THE READING ROOM</span>
                <h2>Latest articles</h2>
            </div>

            <div class="cj-search">
                <span>⌕</span>
                <input
                    type="search"
                    id="cjSearch"
                    placeholder="Find something to read..."
                    aria-label="Search articles"
                >
            </div>
        </div>


        <!-- CATEGORY FILTERS -->
        <div class="cj-filter-row">

            <div class="cj-filters">
                <?php
                $categories = [
                    'All',
                    'Hair science',
                    'Climate',
                    'Routines',
                    'Scalp & edges',
                    'Journeys',
                    'Styling'
                ];
                ?>

                <?php foreach ($categories as $index => $category): ?>
                    <button
                        type="button"
                        class="cj-filter <?php echo $index === 0 ? 'active' : ''; ?>"
                        data-category="<?php echo htmlspecialchars($category); ?>"
                    >
                        <?php echo htmlspecialchars($category); ?>
                    </button>
                <?php endforeach; ?>
            </div>

            <span class="cj-count" id="cjCount">
                <?php echo count($latestArticles); ?> stories
            </span>

        </div>


        <!-- ARTICLE GRID -->
        <div class="cj-article-grid" id="cjArticleGrid">

            <?php foreach ($latestArticles as $index => $article): ?>

                <article
                    class="cj-article-card <?php echo $index === 0 ? 'cj-large-card' : ''; ?>"
                    data-category="<?php echo htmlspecialchars($article['category'], ENT_QUOTES); ?>"
                    data-search="<?php echo htmlspecialchars(strtolower($article['title'] . ' ' . $article['excerpt'] . ' ' . $article['category']), ENT_QUOTES); ?>"
                >

                    <div class="cj-article-image">
                        <img
                            src="<?php echo htmlspecialchars($article['image']); ?>"
                            alt="<?php echo htmlspecialchars($article['title']); ?>"
                            loading="lazy"
                        >

                        <span class="cj-image-category">
                            <?php echo htmlspecialchars($article['category']); ?>
                        </span>
                    </div>

                    <div class="cj-article-info">

                        <span class="cj-read-time">
                            <?php echo htmlspecialchars($article['read_time']); ?>
                        </span>

                        <h3><?php echo htmlspecialchars($article['title']); ?></h3>

                        <p><?php echo htmlspecialchars($article['excerpt']); ?></p>

                        <button
                            type="button"
                            class="cj-text-link"
                            data-article="<?php echo $article['id']; ?>"
                        >
                            Continue reading <span>↗</span>
                        </button>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>


        <div class="cj-no-results" id="cjNoResults" hidden>
            <span>✦</span>
            <h3>No stories found</h3>
            <p>Try another search or choose a different category.</p>
            <button type="button" id="cjReset">Show all articles</button>
        </div>

    </section>


    <!-- CLOSING EDITORIAL BANNER -->
    <section class="cj-closing">
        <div>
            <span class="cj-eyebrow">A LITTLE MORE ABOUT YOUR CROWN</span>
            <h2>Your hair has a story.<br>Let's help you understand it.</h2>
        </div>

        <a href="<?php echo BASE_URL; ?>views/hair_guide.php">
            Explore the Hair Guide <span>↗</span>
        </a>
    </section>


    <!-- ARTICLE READER -->
    <div class="cj-reader" id="cjReader" hidden>

        <div class="cj-reader-backdrop" id="cjReaderBackdrop"></div>

        <article class="cj-reader-panel" role="dialog" aria-modal="true" aria-labelledby="cjReaderTitle">

            <button type="button" class="cj-reader-close" id="cjReaderClose" aria-label="Close article">
                &times;
            </button>

            <span class="cj-eyebrow" id="cjReaderCategory"></span>

            <h2 id="cjReaderTitle"></h2>

            <span class="cj-read-time" id="cjReaderTime"></span>

            <div class="cj-reader-divider"></div>

            <div id="cjReaderBody" class="cj-reader-body"></div>

            <div class="cj-reader-signoff">
                THE CROWN JOURNAL · CROWNCARE
            </div>

        </article>
    </div>

</main>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const articles = <?php echo json_encode($articles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

    const search = document.getElementById('cjSearch');
    const filters = document.querySelectorAll('.cj-filter');
    const cards = document.querySelectorAll('.cj-article-card');
    const count = document.getElementById('cjCount');
    const noResults = document.getElementById('cjNoResults');
    const reset = document.getElementById('cjReset');

    const reader = document.getElementById('cjReader');
    const readerBackdrop = document.getElementById('cjReaderBackdrop');
    const readerClose = document.getElementById('cjReaderClose');

    const readerCategory = document.getElementById('cjReaderCategory');
    const readerTitle = document.getElementById('cjReaderTitle');
    const readerTime = document.getElementById('cjReaderTime');
    const readerBody = document.getElementById('cjReaderBody');

    let activeCategory = 'All';


    function filterArticles() {

        const term = search.value.toLowerCase().trim();
        let visible = 0;

        cards.forEach(function (card) {

            const categoryMatch =
                activeCategory === 'All' ||
                card.dataset.category === activeCategory;

            const searchMatch = card.dataset.search.includes(term);

            const show = categoryMatch && searchMatch;

            card.hidden = !show;

            if (show) {
                visible++;
            }

        });

        count.textContent = visible + (visible === 1 ? ' story' : ' stories');
        noResults.hidden = visible !== 0;
    }


    search.addEventListener('input', filterArticles);


    filters.forEach(function (filter) {

        filter.addEventListener('click', function () {

            filters.forEach(function (item) {
                item.classList.remove('active');
            });

            filter.classList.add('active');
            activeCategory = filter.dataset.category;

            filterArticles();

        });

    });


    reset.addEventListener('click', function () {

        search.value = '';
        activeCategory = 'All';

        filters.forEach(function (filter) {
            filter.classList.toggle('active', filter.dataset.category === 'All');
        });

        filterArticles();

    });


    function openArticle(articleId) {

        const article = articles.find(function (item) {
            return Number(item.id) === Number(articleId);
        });

        if (!article) {
            return;
        }

        readerCategory.textContent = article.category;
        readerTitle.textContent = article.title;
        readerTime.textContent = article.read_time;
        readerBody.innerHTML = '';

        article.body.forEach(function (paragraph) {

            const p = document.createElement('p');
            p.textContent = paragraph;
            readerBody.appendChild(p);

        });

        reader.hidden = false;
        document.body.classList.add('cj-reader-open');
        readerClose.focus();

    }


    document.querySelectorAll('[data-article]').forEach(function (button) {

        button.addEventListener('click', function () {
            openArticle(button.dataset.article);
        });

    });


    function closeArticle() {
        reader.hidden = true;
        document.body.classList.remove('cj-reader-open');
    }

    readerClose.addEventListener('click', closeArticle);
    readerBackdrop.addEventListener('click', closeArticle);

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape' && !reader.hidden) {
            closeArticle();
        }

    });

});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>