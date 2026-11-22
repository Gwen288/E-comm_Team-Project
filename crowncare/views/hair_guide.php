<?php
require_once __DIR__ . '/../core/core.php';

/*
 * Hair guide for visitors and shoppers: articles on hair types and care,
 * with topic tabs and a short routine builder. Links out to the quiz and shop.
 */

$quizUrl = BASE_URL . 'views/hair_quiz.php';
$shopUrl = BASE_URL . 'views/shop.php';

$pageTitle = 'Hair guide';
$pageDescription = 'Learn about hair types, porosity and routines for natural and African hair.';
require_once __DIR__ . '/layout/header.php';

/* ---------- Topic list and previous/next helper ----------
   The ids double as anchors, so hair_guide.php#hg-porosity opens that topic. */

/* The topics in the hair care library, in the order they are shown. */
$hgTopics = [
    'hg-texture'  => 'Hair texture',
    'hg-porosity' => 'Porosity',
    'hg-moisture' => 'Moisture',
    'hg-scalp'    => 'Scalp care',
    'hg-breakage' => 'Length retention',
    'hg-styling'  => 'Protective styling',
    'hg-myths'    => 'Myths and facts',
];
$hgIds = array_keys($hgTopics);

/* Previous and next links at the bottom of each topic. */
function hg_topic_nav(string $id, array $topics, array $ids): void
{
    $i = array_search($id, $ids, true);
    $prev = $i > 0 ? $ids[$i - 1] : null;
    $next = $i < count($ids) - 1 ? $ids[$i + 1] : null;

    echo '<nav class="hg-topic-pager" aria-label="Topic navigation">';
    if ($prev) {
        echo '<a href="#' . e($prev) . '" class="hg-pager-link" data-topic="' . e($prev) . '">'
           . '<small>Previous topic</small>' . e($topics[$prev]) . '</a>';
    } else {
        echo '<span></span>';
    }
    if ($next) {
        echo '<a href="#' . e($next) . '" class="hg-pager-link hg-pager-next" data-topic="' . e($next) . '">'
           . '<small>Next topic</small>' . e($topics[$next]) . '</a>';
    } else {
        echo '<a href="#hg-routine" class="hg-pager-link hg-pager-next">'
           . '<small>Next step</small>Build your routine</a>';
    }
    echo '</nav>';
}
?>

<main class="hair-guide-page" id="main-content">

    <!-- HERO -->
    <section class="hg-hero">
        <div class="hg-hero-inner">

            <div class="hg-hero-content">
                <span class="hg-eyebrow">THE CROWNCARE HAIR GUIDE</span>

                <h1>
                    Your natural hair deserves
                    <span>understanding and care.</span>
                </h1>

                <p>
                    Discover how your hair works, understand what it needs
                    and build a routine that makes sense for you.
                </p>

                <div class="hg-hero-actions">
                    <a href="#hg-topics" class="hg-btn hg-btn-primary">Explore the topics</a>
                    <a href="<?php echo e($quizUrl); ?>" class="hg-btn hg-btn-outline">Take the hair quiz</a>
                </div>
            </div>

            <div class="hg-hero-visual">
                <div class="hg-visual-circle"></div>

                <div class="hg-visual-content">
                    <span class="hg-visual-small">A LITTLE REMINDER</span>
                    <h2>Your hair.<br>Your needs.<br>Your routine.</h2>
                    <span class="hg-visual-line"></span>
                    <p>Healthy hair care is not one-size-fits-all.</p>
                </div>
            </div>

        </div>
    </section>


    <!-- THE BASICS -->
    <section class="hg-intro" id="hg-basics">

        <div class="hg-intro-heading">
            <span class="hg-eyebrow">START WITH THE BASICS</span>
            <h2>Get to know your hair.</h2>
            <p>
                Before choosing products or following a routine, it helps
                to understand the characteristics of your hair. Texture,
                porosity, moisture, scalp condition and styling habits all
                influence how your hair responds to care.
            </p>
        </div>

        <dl class="hg-facts">
            <div>
                <dt>1.25 cm</dt>
                <dd>Average hair growth in a month</dd>
            </div>
            <div>
                <dt>50 to 100</dt>
                <dd>Hairs shed in a day, which is normal</dd>
            </div>
            <div>
                <dt>100,000</dt>
                <dd>Approximate number of follicles on the scalp</dd>
            </div>
            <div>
                <dt>2 to 7 years</dt>
                <dd>Length of the active growth phase</dd>
            </div>
        </dl>

    </section>


    <!-- HAIR CARE TOPICS: one topic shows at a time -->
    <section class="hg-topics" id="hg-topics">

        <div class="hg-topics-header">
            <span class="hg-eyebrow">THE HAIR CARE LIBRARY</span>
            <h2>Learn at your own pace.</h2>
            <p>Choose a topic below. Each one opens on its own, so you can read one at a time.</p>
        </div>

        <div class="hg-tabs" role="tablist" aria-label="Hair care topics" hidden>
            <?php foreach ($hgTopics as $id => $label): ?>
                <button type="button" role="tab" class="hg-tab"
                        id="<?php echo e($id); ?>-tab"
                        aria-controls="<?php echo e($id); ?>"
                        aria-selected="false" tabindex="-1">
                    <?php echo e($label); ?>
                </button>
            <?php endforeach; ?>
        </div>


        <!-- TOPIC: HAIR TEXTURE -->
        <article class="hg-panel" id="hg-texture" role="tabpanel" aria-labelledby="hg-texture-tab">
            <span class="hg-card-label">KNOW YOUR HAIR</span>
            <h3>Hair textures and curl patterns</h3>
            <p class="hg-panel-lead">
                Curl patterns describe the shape of individual hair strands.
                Your pattern is only one part of understanding your hair.
            </p>

            <div class="hg-panel-body">
                <div>
                    <h4>The four hair types</h4>
                    <ul>
                        <li><strong>Type 1:</strong> Straight hair that lies flat from root to tip.</li>
                        <li><strong>Type 2:</strong> Loose, S-shaped waves.</li>
                        <li><strong>Type 3:</strong> Defined curls with visible loops or spirals.</li>
                        <li><strong>Type 4:</strong> Tight coils or zigzag patterns with a lot of shrinkage.</li>
                    </ul>
                    <p>
                        Each type has subtypes from A to C, where A is the
                        loosest and C the tightest. Type 4C hair can shrink
                        to as little as a quarter of its stretched length.
                    </p>
                </div>

                <div>
                    <h4>What to remember</h4>
                    <p>
                        You can have different curl patterns on different
                        parts of your head. Curl pattern alone does not
                        determine how strong, healthy or moisturised your hair is.
                    </p>
                    <div class="hg-tip">
                        <strong>Care tip</strong>
                        Observe your hair when it is clean and product-free.
                        Pay attention to how it behaves rather than relying
                        entirely on a type label.
                    </div>
                </div>
            </div>

            <?php hg_topic_nav('hg-texture', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: POROSITY -->
        <article class="hg-panel" id="hg-porosity" role="tabpanel" aria-labelledby="hg-porosity-tab">
            <span class="hg-card-label">MOISTURE BEHAVIOUR</span>
            <h3>Understanding hair porosity</h3>
            <p class="hg-panel-lead">
                Porosity describes how easily moisture enters and leaves
                the hair. It depends on the hair cuticle and can vary across
                your head. Heat, colouring and chemical treatments can change it.
            </p>

            <div class="hg-porosity-levels">
                <div>
                    <strong>Low porosity</strong>
                    <p>Hair takes longer to absorb water and products.</p>
                    <p class="hg-level-care">Use lightweight products and apply them to damp, warm hair.</p>
                </div>
                <div>
                    <strong>Medium porosity</strong>
                    <p>Hair absorbs and holds moisture relatively well.</p>
                    <p class="hg-level-care">Keep a balanced routine and protect it from excess heat.</p>
                </div>
                <div>
                    <strong>High porosity</strong>
                    <p>Hair absorbs moisture quickly but loses it just as fast.</p>
                    <p class="hg-level-care">Use richer creams and seal moisture in with an oil or butter.</p>
                </div>
            </div>

            <div class="hg-tip">
                <strong>How to check</strong>
                Watch your hair on wash day. If water beads on the surface
                and your hair takes a long time to get wet, it is likely low
                porosity. If it soaks up water and also dries very quickly,
                it is likely high porosity. The strand-in-a-glass float test
                is not a reliable measure.
            </div>

            <?php hg_topic_nav('hg-porosity', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: MOISTURE -->
        <article class="hg-panel" id="hg-moisture" role="tabpanel" aria-labelledby="hg-moisture-tab">
            <span class="hg-card-label">HYDRATION AND SOFTNESS</span>
            <h3>Moisture and conditioning</h3>
            <p class="hg-panel-lead">
                Hair that is dry or difficult to manage is more likely to
                break during combing and styling. Conditioning improves
                softness, slip and manageability.
            </p>

            <div class="hg-panel-body">
                <div>
                    <h4>Know your products</h4>
                    <ul>
                        <li><strong>Rinse-out conditioner:</strong> Softens hair and helps detangling after shampooing.</li>
                        <li><strong>Leave-in conditioner:</strong> Adds conditioning after washing.</li>
                        <li><strong>Hair creams:</strong> Help with softness and manageability.</li>
                        <li><strong>Oils:</strong> Reduce friction and seal in moisture, but do not replace water or conditioner.</li>
                    </ul>
                </div>

                <div>
                    <h4>Ingredients you may see</h4>
                    <ul>
                        <li><strong>Shea butter:</strong> Softens hair and locks in moisture, especially on the ends.</li>
                        <li><strong>Aloe vera:</strong> Adds lightweight moisture and soothes the scalp.</li>
                        <li><strong>Glycerin:</strong> Draws water into the hair. It works best in humid weather.</li>
                        <li><strong>Fatty alcohols:</strong> Such as cetyl alcohol. These moisturise rather than dry the hair.</li>
                    </ul>
                </div>
            </div>

            <?php hg_topic_nav('hg-moisture', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: SCALP CARE -->
        <article class="hg-panel" id="hg-scalp" role="tabpanel" aria-labelledby="hg-scalp-tab">
            <span class="hg-card-label">CLEANLINESS AND COMFORT</span>
            <h3>Washing and scalp care</h3>
            <p class="hg-panel-lead">
                Your scalp is where your hair grows from. Regular cleansing
                removes sweat, oil and product build-up.
            </p>

            <div class="hg-panel-body">
                <div>
                    <h4>Practical washing habits</h4>
                    <ul>
                        <li>Choose a shampoo suited to your scalp and hair needs.</li>
                        <li>Concentrate shampoo on the scalp and let the lather rinse through your hair.</li>
                        <li>Massage with your fingertips, not your nails.</li>
                        <li>Use oils sparingly, since heavy greases can trap build-up on the scalp.</li>
                        <li>Most scalps do well with a wash every 7 to 10 days. Adjust this to your scalp, activity and products.</li>
                    </ul>
                </div>

                <div>
                    <div class="hg-tip">
                        <strong>When to get help</strong>
                        Persistent itching, pain, sores, thick flakes or unusual
                        hair loss should be discussed with a qualified health
                        professional.
                    </div>
                </div>
            </div>

            <?php hg_topic_nav('hg-scalp', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: LENGTH RETENTION -->
        <article class="hg-panel" id="hg-breakage" role="tabpanel" aria-labelledby="hg-breakage-tab">
            <span class="hg-card-label">HEALTHY HAIR HABITS</span>
            <h3>Length retention and breakage</h3>
            <p class="hg-panel-lead">
                Hair grows from follicles in the scalp, but the length you
                keep depends on how much of each strand stays intact rather
                than breaking off.
            </p>

            <div class="hg-panel-body">
                <div>
                    <h4>Habits that reduce breakage</h4>
                    <ul>
                        <li>Detangle gently in sections, using conditioner for slip.</li>
                        <li>Reduce heat styling and use heat protection when you do.</li>
                        <li>Avoid excessive pulling and rough handling.</li>
                        <li>Trim damaged ends when necessary.</li>
                    </ul>
                </div>

                <div>
                    <h4>Every night</h4>
                    <p>
                        Cover your hair with a satin bonnet or scarf, or sleep
                        on a satin pillowcase. Cotton absorbs moisture and
                        rubs against your ends.
                    </p>
                    <div class="hg-tip">
                        <strong>Care tip</strong>
                        Trimming removes split ends, but it does not make hair
                        grow faster at the scalp.
                    </div>
                </div>
            </div>

            <?php hg_topic_nav('hg-breakage', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: PROTECTIVE STYLING -->
        <article class="hg-panel" id="hg-styling" role="tabpanel" aria-labelledby="hg-styling-tab">
            <span class="hg-card-label">STYLING WITH CARE</span>
            <h3>Protective styling</h3>
            <p class="hg-panel-lead">
                Protective styles reduce daily handling of the hair. A style
                is not automatically protective simply because it is popular.
            </p>

            <div class="hg-panel-body">
                <div>
                    <h4>Usual tension on the hairline</h4>
                    <table class="hg-tension">
                        <tbody>
                            <tr><th scope="row">Twists</th><td>Low</td></tr>
                            <tr><th scope="row">Bantu knots</th><td>Medium</td></tr>
                            <tr><th scope="row">Wigs and units</th><td>Medium</td></tr>
                            <tr><th scope="row">Box braids</th><td>Higher</td></tr>
                            <tr><th scope="row">Cornrows</th><td>Higher</td></tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <h4>Styling considerations</h4>
                    <ul>
                        <li>Styles should not cause pain or excessive tension.</li>
                        <li>Pay attention to the hairline and edges.</li>
                        <li>Keep caring for your scalp and hair while styled.</li>
                        <li>Give your hair breaks between styles.</li>
                    </ul>
                    <div class="hg-tip">
                        <strong>Care tip</strong>
                        Pain is not a sign that a style is working. Persistent
                        pulling can lead to traction-related hair loss.
                    </div>
                </div>
            </div>

            <?php hg_topic_nav('hg-styling', $hgTopics, $hgIds); ?>
        </article>


        <!-- TOPIC: MYTHS AND FACTS -->
        <article class="hg-panel" id="hg-myths" role="tabpanel" aria-labelledby="hg-myths-tab">
            <span class="hg-card-label">CLEARING THINGS UP</span>
            <h3>Common hair myths</h3>
            <p class="hg-panel-lead">Three beliefs we often hear, and what is actually true.</p>

            <div class="hg-myths">
                <div>
                    <span class="hg-myth-label">Myth</span>
                    <strong>Grease feeds the scalp.</strong>
                    <span class="hg-myth-label hg-fact-label">Fact</span>
                    <p>Hair and scalp are not fed from the outside. Heavy grease can block and irritate the scalp.</p>
                </div>
                <div>
                    <span class="hg-myth-label">Myth</span>
                    <strong>4C hair cannot grow long.</strong>
                    <span class="hg-myth-label hg-fact-label">Fact</span>
                    <p>All hair grows. Coily hair looks shorter because of shrinkage, so keeping length is the real challenge.</p>
                </div>
                <div>
                    <span class="hg-myth-label">Myth</span>
                    <strong>Natural hair does not need conditioner.</strong>
                    <span class="hg-myth-label hg-fact-label">Fact</span>
                    <p>Coily hair needs it most. Conditioner adds slip for detangling and replaces moisture lost in washing.</p>
                </div>
            </div>

            <?php hg_topic_nav('hg-myths', $hgTopics, $hgIds); ?>
        </article>

    </section>


    <!-- INTERACTIVE ROUTINE BUILDER -->
    <section class="hg-routine-section" id="hg-routine">

        <div class="hg-routine-intro">
            <span class="hg-eyebrow">PUT YOUR KNOWLEDGE INTO PRACTICE</span>
            <h2>Build a simple hair-care routine.</h2>

            <p>
                Your routine should reflect your needs and habits.
                Answer these three questions to get a simple starting
                point you can adjust over time.
            </p>
        </div>

        <div class="hg-routine-builder">

            <div class="hg-routine-form">

                <div class="hg-routine-question">
                    <label for="hg-concern">What is your main hair concern?</label>
                    <select id="hg-concern">
                        <option value="breakage">Breakage and length retention</option>
                        <option value="dryness">Dryness and moisture</option>
                        <option value="scalp">Scalp cleanliness and comfort</option>
                        <option value="damage">Heat or styling damage</option>
                        <option value="maintenance">General hair maintenance</option>
                    </select>
                </div>

                <div class="hg-routine-question">
                    <label for="hg-wash">How often do you usually wash your hair?</label>
                    <select id="hg-wash">
                        <option value="weekly">Every week</option>
                        <option value="fortnightly">Every two weeks</option>
                        <option value="monthly">About once a month</option>
                        <option value="varies">It varies</option>
                    </select>
                </div>

                <div class="hg-routine-question">
                    <label for="hg-style">How do you usually wear your hair?</label>
                    <select id="hg-style">
                        <option value="natural">Mostly natural or loose</option>
                        <option value="protective">Braids, twists or cornrows</option>
                        <option value="heat">Frequently heat styled</option>
                        <option value="mixed">A mixture of styles</option>
                    </select>
                </div>

                <button type="button" id="hg-build-routine"
                        class="hg-btn hg-btn-primary hg-build-button">
                    Build my routine
                </button>

            </div>

            <div class="hg-routine-result" id="hg-routine-result" aria-live="polite">
                <div class="hg-result-placeholder">
                    <span class="hg-eyebrow">YOUR ROUTINE STARTS HERE</span>
                    <h3>A routine that makes sense for your hair.</h3>
                    <p>
                        Answer the questions and select Build my routine
                        to see your suggested starting points.
                    </p>
                </div>
            </div>

        </div>

        <p class="hg-routine-disclaimer">
            These are general educational suggestions, not a diagnosis
            or personalised medical recommendation. Adjust your routine
            according to how your hair and scalp respond.
        </p>

    </section>


    <!-- PRODUCT DISCOVERY -->
    <section class="hg-product-section">

        <div class="hg-product-copy">
            <span class="hg-eyebrow">CHOOSE WITH KNOWLEDGE</span>
            <h2>Now that you understand your hair, choose your products.</h2>

            <p>
                Explore the CrownCare collection and look for products
                that serve a purpose in your routine. You do not need
                a shelf full of products to care for your hair.
            </p>

            <a href="<?php echo e($shopUrl); ?>" class="hg-btn hg-btn-primary">Explore the shop</a>
        </div>

        <div class="hg-product-points">
            <div>
                <p><strong>Understand the need.</strong> Identify what you want a product to help with.</p>
            </div>
            <div>
                <p><strong>Read the product details.</strong> Check its purpose and directions.</p>
            </div>
            <div>
                <p><strong>Observe the results.</strong> Give your routine time and adjust when necessary.</p>
            </div>
        </div>

    </section>


    <!-- HAIR QUIZ -->
    <section class="hg-quiz-section">

        <div class="hg-quiz-content">
            <span class="hg-eyebrow">READY TO MAKE IT PERSONAL?</span>

            <h2>Let's learn more about your hair.</h2>

            <p>
                Not sure which products or routine may suit your needs?
                Take the CrownCare Hair Quiz and receive general
                suggestions based on your answers.
            </p>

            <a href="<?php echo e($quizUrl); ?>" class="hg-btn hg-btn-light">Take the hair quiz</a>
        </div>

        <div class="hg-quiz-decoration">
            <span>YOUR</span>
            <strong>CROWN</strong>
            <span>YOUR CARE</span>
        </div>

    </section>

    <p class="hg-disclaimer">
        The Hair Guide shares general hair-care information, not medical advice.
        For ongoing scalp pain, sores, bald patches or sudden hair loss, please
        see a dermatologist.
    </p>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ---------- Interactive parts: topic tabs, then the routine builder ---------- */

    // TOPIC TABS: show one topic at a time

    var tabList = document.querySelector('.hg-tabs');
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.hg-tab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.hg-panel'));
    var topicsSection = document.getElementById('hg-topics');

    function showTopic(id, moveFocus, keepHash) {
        var found = false;

        panels.forEach(function (panel) {
            var active = panel.id === id;
            panel.hidden = !active;
            if (active) { found = true; }
        });

        if (!found) { return showTopic(panels[0].id, moveFocus, keepHash); }

        tabs.forEach(function (tab) {
            var active = tab.getAttribute('aria-controls') === id;
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
            tab.tabIndex = active ? 0 : -1;
            if (active && moveFocus) { tab.focus(); }
        });

        if (!keepHash && window.history && history.replaceState) {
            history.replaceState(null, '', '#' + id);
        }
    }

    if (tabList && panels.length) {
        tabList.hidden = false;

        var startId = window.location.hash.slice(1);
        var startsOnTopic = panels.some(function (p) { return p.id === startId; });
        showTopic(startsOnTopic ? startId : panels[0].id, false, true);

        if (startsOnTopic) {
            topicsSection.scrollIntoView({ block: 'start' });
        }

        // A link to a topic, for example hair_guide.php#hg-porosity, opens that topic
        window.addEventListener('hashchange', function () {
            var id = window.location.hash.slice(1);
            if (panels.some(function (p) { return p.id === id; })) {
                showTopic(id, false, true);
                topicsSection.scrollIntoView({ block: 'start' });
            }
        });

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () {
                showTopic(tab.getAttribute('aria-controls'), false);
            });

            // Left and right arrow keys move between tabs
            tab.addEventListener('keydown', function (event) {
                var next = null;
                if (event.key === 'ArrowRight') { next = tabs[(index + 1) % tabs.length]; }
                if (event.key === 'ArrowLeft') { next = tabs[(index - 1 + tabs.length) % tabs.length]; }
                if (event.key === 'Home') { next = tabs[0]; }
                if (event.key === 'End') { next = tabs[tabs.length - 1]; }
                if (next) {
                    event.preventDefault();
                    showTopic(next.getAttribute('aria-controls'), true);
                }
            });
        });

        // Previous and next topic links
        document.querySelectorAll('.hg-pager-link[data-topic]').forEach(function (link) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                showTopic(link.dataset.topic, false);
                topicsSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }


    // ROUTINE BUILDER

    var buildButton = document.getElementById('hg-build-routine');
    var resultContainer = document.getElementById('hg-routine-result');

    var concernAdvice = {
        breakage: [
            'Detangle gently in sections and use a conditioner that provides good slip.',
            'Reduce unnecessary pulling and rough handling during styling.',
            'Cover your hair with a satin bonnet or scarf at night.'
        ],
        dryness: [
            'Use a suitable rinse-out conditioner after washing.',
            'Consider a leave-in conditioner if your hair responds well to it.',
            'Avoid relying on heavy oils alone to address dryness.'
        ],
        scalp: [
            'Keep your scalp clean with a shampoo suited to your needs.',
            'Pay attention to product build-up and how your scalp feels between washes.',
            'Seek professional advice if discomfort or irritation persists.'
        ],
        damage: [
            'Reduce frequent high-heat styling.',
            'Use heat protection when heat styling is necessary.',
            'Handle fragile areas gently and avoid tight styles.'
        ],
        maintenance: [
            'Keep your routine simple with cleansing, conditioning and appropriate styling.',
            'Observe how your hair responds to your current products.',
            'Introduce new products one at a time.'
        ]
    };

    var washAdvice = {
        weekly: 'You currently wash weekly. Continue if this works for your scalp and hair, and adjust when your needs change.',
        fortnightly: 'You currently wash every two weeks. Pay attention to scalp comfort and product build-up between wash days.',
        monthly: 'You currently wash about once a month. Consider whether your scalp and styling products need more frequent cleansing.',
        varies: 'Try to settle on a manageable washing pattern based on scalp condition, activity and product build-up rather than a rigid schedule.'
    };

    var styleAdvice = {
        natural: 'When wearing your hair loose, detangle gently, condition regularly and avoid unnecessary handling.',
        protective: 'While wearing protective styles, avoid excessive tension, pay attention to your hairline and keep caring for your scalp.',
        heat: 'If you frequently heat style, reduce unnecessary heat exposure and use a heat protectant.',
        mixed: 'Adjust your care to the style you are wearing. Pay attention to tension, heat exposure and detangling needs.'
    };

    if (buildButton && resultContainer) {
        buildButton.addEventListener('click', function () {
            var concern = document.getElementById('hg-concern').value;
            var wash = document.getElementById('hg-wash').value;
            var style = document.getElementById('hg-style').value;

            var items = (concernAdvice[concern] || []).map(function (item) {
                return '<li>' + item + '</li>';
            }).join('');

            resultContainer.innerHTML =
                '<div class="hg-generated-result">' +
                    '<span class="hg-eyebrow">YOUR SUGGESTED STARTING ROUTINE</span>' +
                    '<h3>Your hair-care starting point</h3>' +
                    '<div class="hg-result-group"><span>FOCUS ON YOUR MAIN CONCERN</span><ul>' + items + '</ul></div>' +
                    '<div class="hg-result-group"><span>YOUR WASHING HABIT</span><p>' + (washAdvice[wash] || '') + '</p></div>' +
                    '<div class="hg-result-group"><span>YOUR STYLING HABIT</span><p>' + (styleAdvice[style] || '') + '</p></div>' +
                    '<div class="hg-result-footer">Consistency and watching how your hair responds matter more than using many products.</div>' +
                '</div>';

            resultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }

});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
