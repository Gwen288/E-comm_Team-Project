
<?php
require_once __DIR__ . '/../core/core.php';

$quizUrl = BASE_URL . 'views/hair_quiz.php';
$shopUrl = BASE_URL . 'views/shop.php';
?>

<?php require_once __DIR__ . '/layout/header.php'; ?>

<main class="hair-guide-page">

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
                    <a href="#hg-explore" class="hg-btn hg-btn-primary">
                        Explore hair care <span>→</span>
                    </a>

                    <a href="<?php echo htmlspecialchars($quizUrl); ?>"
                       class="hg-btn hg-btn-outline">
                        Discover your hair
                    </a>
                </div>

                <div class="hg-hero-note">
                    <span class="hg-note-dot"></span>
                    Practical knowledge for your natural hair journey
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

                <div class="hg-floating-card hg-floating-top">
                    <span>01</span>
                    <strong>Understand</strong>
                </div>

                <div class="hg-floating-card hg-floating-bottom">
                    <span>02</span>
                    <strong>Care consistently</strong>
                </div>
            </div>

        </div>

        <div class="hg-hero-bottom">
            <span>LEARN</span>
            <span class="hg-hero-bottom-line"></span>
            <span>UNDERSTAND</span>
            <span class="hg-hero-bottom-line"></span>
            <span>CARE</span>
        </div>
    </section>


    <!-- INTRODUCTION -->
    <section class="hg-intro" id="hg-explore">

        <div class="hg-intro-heading">
            <span class="hg-eyebrow">START WITH THE BASICS</span>
            <h2>Get to know your hair.</h2>
        </div>

        <p>
            Before choosing products or following a routine, it helps
            to understand the characteristics of your hair. Texture,
            porosity, moisture, scalp condition and styling habits all
            influence how your hair responds to care.
        </p>

    </section>


    <!-- QUICK TOPIC NAVIGATION -->
    <section class="hg-quick-nav">

        <div class="hg-section-heading">
            <div>
                <span class="hg-eyebrow">YOUR LEARNING GUIDE</span>
                <h2>Explore the topics</h2>
            </div>

            <p>Choose an area you want to understand better.</p>
        </div>

        <div class="hg-topic-shortcuts">

            <a href="#hg-texture">
                <span>01</span>
                <strong>Hair texture</strong>
                <small>Understand curl patterns</small>
            </a>

            <a href="#hg-porosity">
                <span>02</span>
                <strong>Porosity</strong>
                <small>How your hair handles moisture</small>
            </a>

            <a href="#hg-moisture">
                <span>03</span>
                <strong>Moisture</strong>
                <small>Conditioning and hydration</small>
            </a>

            <a href="#hg-scalp">
                <span>04</span>
                <strong>Scalp care</strong>
                <small>Washing and cleansing</small>
            </a>

            <a href="#hg-breakage">
                <span>05</span>
                <strong>Length retention</strong>
                <small>Reducing breakage</small>
            </a>

            <a href="#hg-styling">
                <span>06</span>
                <strong>Protective styling</strong>
                <small>Styling without excess tension</small>
            </a>

        </div>

    </section>


    <!-- SEARCHABLE HAIR TOPICS -->
    <section class="hg-learning-section">

        <div class="hg-learning-header">
            <div>
                <span class="hg-eyebrow">THE HAIR CARE LIBRARY</span>
                <h2>Learn at your own pace.</h2>
                <p>
                    Open a topic to explore practical information
                    and useful hair-care tips.
                </p>
            </div>

            <div class="hg-search">
                <span>⌕</span>
                <input
                    type="search"
                    id="hg-topic-search"
                    placeholder="Search hair topics..."
                    aria-label="Search hair topics"
                >
            </div>
        </div>


        <div class="hg-learning-grid" id="hg-learning-grid">

            <!-- TOPIC 1 -->
            <article class="hg-learning-card" id="hg-texture"
                     data-search="texture curl patterns type 3 type 4 waves coils">

                <div class="hg-card-top">
                    <span class="hg-card-number">01</span>
                    <span class="hg-card-symbol">〰</span>
                </div>

                <span class="hg-card-label">KNOW YOUR HAIR</span>
                <h3>Hair textures and curl patterns</h3>

                <p>
                    Learn about different natural hair patterns and
                    why your curl pattern is only one part of
                    understanding your hair.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>What are curl patterns?</h4>

                        <p>
                            Curl patterns describe the shape of individual
                            hair strands. Natural hair is commonly grouped
                            into types 2, 3 and 4, with subcategories
                            such as A, B and C.
                        </p>

                        <ul>
                            <li><strong>Type 2:</strong> Wavy patterns.</li>
                            <li><strong>Type 3:</strong> Curly patterns with visible loops or spirals.</li>
                            <li><strong>Type 4:</strong> Coily and tightly curled patterns.</li>
                        </ul>

                        <h4>What should you remember?</h4>

                        <p>
                            You can have different curl patterns on
                            different parts of your head. Curl pattern
                            alone does not determine how strong, healthy
                            or moisturised your hair is.
                        </p>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Observe your hair when it is clean and
                            product-free. Pay attention to how it behaves
                            rather than relying entirely on a type label.
                        </div>
                    </div>
                </details>
            </article>


            <!-- TOPIC 2 -->
            <article class="hg-learning-card" id="hg-porosity"
                     data-search="porosity low medium high moisture cuticle absorption">

                <div class="hg-card-top">
                    <span class="hg-card-number">02</span>
                    <span class="hg-card-symbol">◉</span>
                </div>

                <span class="hg-card-label">MOISTURE BEHAVIOUR</span>
                <h3>Understanding hair porosity</h3>

                <p>
                    Discover how readily your hair absorbs and releases
                    moisture and what that can mean for your routine.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>What is hair porosity?</h4>

                        <p>
                            Porosity describes how easily moisture enters
                            and leaves the hair. It is influenced by the
                            structure of the hair cuticle and can vary
                            across your head.
                        </p>

                        <div class="hg-porosity-levels">
                            <div>
                                <strong>Low porosity</strong>
                                <p>
                                    Hair may take longer to absorb products
                                    and moisture.
                                </p>
                            </div>

                            <div>
                                <strong>Medium porosity</strong>
                                <p>
                                    Hair often absorbs and retains moisture
                                    relatively well.
                                </p>
                            </div>

                            <div>
                                <strong>High porosity</strong>
                                <p>
                                    Hair may absorb moisture quickly but
                                    lose it more easily.
                                </p>
                            </div>
                        </div>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Do not rely on the water-float test alone.
                            Product build-up, hair condition and other
                            factors can affect the result.
                        </div>
                    </div>
                </details>
            </article>


            <!-- TOPIC 3 -->
            <article class="hg-learning-card" id="hg-moisture"
                     data-search="moisture hydration conditioner leave in creams oils hydration">

                <div class="hg-card-top">
                    <span class="hg-card-number">03</span>
                    <span class="hg-card-symbol">✳</span>
                </div>

                <span class="hg-card-label">HYDRATION AND SOFTNESS</span>
                <h3>Moisture and conditioning</h3>

                <p>
                    Understand the role of water, conditioners and
                    moisturising products in maintaining manageable hair.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>Why does moisture matter?</h4>

                        <p>
                            Hair that is dry or difficult to manage may
                            become more vulnerable to breakage during
                            combing and styling. Conditioning helps
                            improve softness, slip and manageability.
                        </p>

                        <h4>Know your products</h4>

                        <ul>
                            <li><strong>Rinse-out conditioner:</strong> Helps soften hair and improve detangling after shampooing.</li>
                            <li><strong>Leave-in conditioner:</strong> Provides additional conditioning after washing.</li>
                            <li><strong>Hair creams:</strong> Can help with softness and manageability.</li>
                            <li><strong>Oils:</strong> May help reduce friction and seal in moisture, but do not replace water or conditioner.</li>
                        </ul>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Pay attention to how your hair feels after
                            conditioning. You do not need to use every
                            product category.
                        </div>
                    </div>
                </details>
            </article>


            <!-- TOPIC 4 -->
            <article class="hg-learning-card" id="hg-scalp"
                     data-search="scalp washing shampoo cleansing dandruff itching wash day">

                <div class="hg-card-top">
                    <span class="hg-card-number">04</span>
                    <span class="hg-card-symbol">✺</span>
                </div>

                <span class="hg-card-label">CLEANLINESS AND COMFORT</span>
                <h3>Washing and scalp care</h3>

                <p>
                    Learn how to maintain a clean, comfortable scalp
                    and choose a washing routine that suits your needs.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>Why is scalp care important?</h4>

                        <p>
                            Your scalp is the environment from which
                            your hair grows. Regular cleansing helps
                            remove sweat, oil and product build-up.
                        </p>

                        <h4>Practical washing habits</h4>

                        <ul>
                            <li>Choose a shampoo suited to your scalp and hair needs.</li>
                            <li>Concentrate shampoo on the scalp and allow the lather to rinse through your hair.</li>
                            <li>Use conditioner to help with softness and detangling.</li>
                            <li>Adjust washing frequency based on your scalp, activity and styling products.</li>
                        </ul>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Persistent itching, pain, sores or unusual
                            hair loss should be discussed with a qualified
                            health professional.
                        </div>
                    </div>
                </details>
            </article>


            <!-- TOPIC 5 -->
            <article class="hg-learning-card" id="hg-breakage"
                     data-search="length retention breakage hair growth trimming heat damage detangling">

                <div class="hg-card-top">
                    <span class="hg-card-number">05</span>
                    <span class="hg-card-symbol">↗</span>
                </div>

                <span class="hg-card-label">HEALTHY HAIR HABITS</span>
                <h3>Length retention and breakage</h3>

                <p>
                    Understand why retaining length involves protecting
                    the hair you already have from unnecessary damage.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>Growth versus length retention</h4>

                        <p>
                            Hair grows from follicles in the scalp.
                            However, the length you retain also depends
                            on how much of the hair strand remains intact
                            rather than breaking off.
                        </p>

                        <h4>Habits that help reduce breakage</h4>

                        <ul>
                            <li>Detangle gently in sections using adequate slip.</li>
                            <li>Reduce excessive heat styling.</li>
                            <li>Use heat protection when using heat tools.</li>
                            <li>Avoid excessive pulling and rough handling.</li>
                            <li>Manage damaged ends when necessary.</li>
                        </ul>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Trimming damaged ends can help manage split
                            ends, but trimming does not make hair grow
                            faster at the scalp.
                        </div>
                    </div>
                </details>
            </article>


            <!-- TOPIC 6 -->
            <article class="hg-learning-card" id="hg-styling"
                     data-search="protective styling braids cornrows wigs tension hairline">

                <div class="hg-card-top">
                    <span class="hg-card-number">06</span>
                    <span class="hg-card-symbol">❋</span>
                </div>

                <span class="hg-card-label">STYLING WITH CARE</span>
                <h3>Protective styling</h3>

                <p>
                    Explore how protective styles can reduce daily
                    manipulation without placing unnecessary tension
                    on your hair.
                </p>

                <details class="hg-topic-details">
                    <summary>Explore this topic <span>＋</span></summary>

                    <div class="hg-details-content">
                        <h4>What makes a style protective?</h4>

                        <p>
                            Protective styles can reduce daily handling
                            of the hair. Examples include braids, twists,
                            cornrows and certain tucked-away styles.
                            However, a style is not automatically
                            protective simply because it is popular.
                        </p>

                        <h4>Important styling considerations</h4>

                        <ul>
                            <li>Styles should not cause pain or excessive tension.</li>
                            <li>Pay particular attention to the hairline and edges.</li>
                            <li>Avoid repeatedly installing tight styles.</li>
                            <li>Maintain appropriate scalp and hair care while styled.</li>
                            <li>Give your hair breaks between styles when needed.</li>
                        </ul>

                        <div class="hg-tip">
                            <strong>Care tip</strong>
                            Pain is not a sign that a protective style
                            is working. Persistent pulling can contribute
                            to hair damage and traction-related hair loss.
                        </div>
                    </div>
                </details>
            </article>

        </div>

        <div class="hg-no-results" id="hg-no-results" hidden>
            <h3>No matching topic found.</h3>
            <p>Try another search term.</p>
        </div>

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
                    <span>01</span>
                    <div>
                        <label for="hg-concern">What is your main hair concern?</label>

                        <select id="hg-concern">
                            <option value="breakage">Breakage and length retention</option>
                            <option value="dryness">Dryness and moisture</option>
                            <option value="scalp">Scalp cleanliness and comfort</option>
                            <option value="damage">Heat or styling damage</option>
                            <option value="maintenance">General hair maintenance</option>
                        </select>
                    </div>
                </div>

                <div class="hg-routine-question">
                    <span>02</span>
                    <div>
                        <label for="hg-wash">How often do you usually wash your hair?</label>

                        <select id="hg-wash">
                            <option value="weekly">Every week</option>
                            <option value="fortnightly">Every two weeks</option>
                            <option value="monthly">About once a month</option>
                            <option value="varies">It varies</option>
                        </select>
                    </div>
                </div>

                <div class="hg-routine-question">
                    <span>03</span>
                    <div>
                        <label for="hg-style">How do you usually wear your hair?</label>

                        <select id="hg-style">
                            <option value="natural">Mostly natural / loose</option>
                            <option value="protective">Braids, twists or cornrows</option>
                            <option value="heat">Frequently heat styled</option>
                            <option value="mixed">A mixture of styles</option>
                        </select>
                    </div>
                </div>

                <button type="button" id="hg-build-routine"
                        class="hg-btn hg-btn-primary hg-build-button">
                    Build my routine <span>→</span>
                </button>

            </div>

            <div class="hg-routine-result" id="hg-routine-result"
                 aria-live="polite">

                <div class="hg-result-placeholder">
                    <span class="hg-result-icon">✳</span>
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

            <a href="<?php echo htmlspecialchars($shopUrl); ?>"
               class="hg-btn hg-btn-primary">
                Explore the Shop <span>→</span>
            </a>
        </div>

        <div class="hg-product-points">
            <div>
                <span>01</span>
                <p><strong>Understand the need.</strong> Identify what you want a product to help with.</p>
            </div>

            <div>
                <span>02</span>
                <p><strong>Read the product details.</strong> Check its purpose and directions.</p>
            </div>

            <div>
                <span>03</span>
                <p><strong>Observe the results.</strong> Give your routine time and adjust when necessary.</p>
            </div>
        </div>

    </section>


    <!-- HAIR QUIZ CTA -->
    <section class="hg-quiz-section">

        <div class="hg-quiz-content">
            <span class="hg-eyebrow">READY TO MAKE IT PERSONAL?</span>

            <h2>Let's learn more about your hair.</h2>

            <p>
                Not sure which products or routine may suit your needs?
                Take the CrownCare Hair Quiz and receive general
                suggestions based on your answers.
            </p>

            <a href="<?php echo htmlspecialchars($quizUrl); ?>"
               class="hg-btn hg-btn-light">
                Take the Hair Quiz <span>→</span>
            </a>
        </div>

        <div class="hg-quiz-decoration">
            <span>YOUR</span>
            <strong>CROWN</strong>
            <span>YOUR CARE</span>
        </div>

    </section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // SEARCH HAIR TOPICS

    var searchInput = document.getElementById('hg-topic-search');
    var topicCards = document.querySelectorAll('.hg-learning-card');
    var noResults = document.getElementById('hg-no-results');

    if (searchInput) {
        searchInput.addEventListener('input', function () {

            var searchTerm = searchInput.value.toLowerCase().trim();
            var visibleCount = 0;

            topicCards.forEach(function (card) {

                var searchableText = (
                    card.innerText + ' ' + (card.dataset.search || '')
                ).toLowerCase();

                var matches = searchableText.includes(searchTerm);

                card.hidden = !matches;

                if (matches) {
                    visibleCount++;
                }

            });

            noResults.hidden = visibleCount > 0;

        });
    }


    // ROUTINE BUILDER

    var buildButton = document.getElementById('hg-build-routine');
    var resultContainer = document.getElementById('hg-routine-result');

    if (buildButton && resultContainer) {

        buildButton.addEventListener('click', function () {

            var concern = document.getElementById('hg-concern').value;
            var wash = document.getElementById('hg-wash').value;
            var style = document.getElementById('hg-style').value;

            var suggestions = [];

            // Main concern suggestions

            if (concern === 'breakage') {
                suggestions.push(
                    'Detangle gently in sections and use a conditioner that provides good slip.',
                    'Reduce unnecessary pulling and rough handling during styling.',
                    'Pay attention to areas that repeatedly experience breakage.'
                );
            }

            if (concern === 'dryness') {
                suggestions.push(
                    'Use a suitable rinse-out conditioner after washing.',
                    'Consider a leave-in conditioner if your hair responds well to it.',
                    'Avoid relying on heavy oils alone to address dryness.'
                );
            }

            if (concern === 'scalp') {
                suggestions.push(
                    'Keep your scalp clean with a shampoo suited to your needs.',
                    'Pay attention to product build-up and how your scalp feels between washes.',
                    'Seek professional advice if discomfort or irritation persists.'
                );
            }

            if (concern === 'damage') {
                suggestions.push(
                    'Reduce frequent high-heat styling.',
                    'Use heat protection when heat styling is necessary.',
                    'Handle fragile areas gently and avoid tight styles.'
                );
            }

            if (concern === 'maintenance') {
                suggestions.push(
                    'Keep your routine simple with cleansing, conditioning and appropriate styling.',
                    'Observe how your hair responds to your current products.',
                    'Introduce new products one at a time.'
                );
            }


            // Washing suggestions

            var washingAdvice = '';

            if (wash === 'weekly') {
                washingAdvice =
                    'You currently wash weekly. Continue if this works for your scalp and hair, and adjust when your needs change.';
            }

            if (wash === 'fortnightly') {
                washingAdvice =
                    'You currently wash every two weeks. Pay attention to scalp comfort and product build-up between wash days.';
            }

            if (wash === 'monthly') {
                washingAdvice =
                    'You currently wash about once a month. Consider whether your scalp and styling products require more frequent cleansing.';
            }

            if (wash === 'varies') {
                washingAdvice =
                    'Try to establish a manageable washing pattern based on scalp condition, activity and product build-up rather than following a rigid schedule.';
            }


            // Styling suggestions

            var stylingAdvice = '';

            if (style === 'natural') {
                stylingAdvice =
                    'When wearing your hair loose, detangle gently, condition regularly and avoid unnecessary manipulation.';
            }

            if (style === 'protective') {
                stylingAdvice =
                    'While wearing protective styles, avoid excessive tension, pay attention to your hairline and maintain appropriate scalp care.';
            }

            if (style === 'heat') {
                stylingAdvice =
                    'If you frequently heat style, reduce unnecessary heat exposure and use an appropriate heat protectant.';
            }

            if (style === 'mixed') {
                stylingAdvice =
                    'Adjust your care according to the style you are wearing. Pay attention to tension, heat exposure and detangling needs.';
            }


            // Display the routine

            var resultHTML = `
                <div class="hg-generated-result">

                    <span class="hg-eyebrow">YOUR SUGGESTED STARTING ROUTINE</span>

                    <h3>Your hair-care starting point</h3>

                    <div class="hg-result-group">
                        <span>01 · FOCUS ON YOUR MAIN CONCERN</span>
                        <ul>
                            ${suggestions.map(function (item) {
                                return '<li>' + item + '</li>';
                            }).join('')}
                        </ul>
                    </div>

                    <div class="hg-result-group">
                        <span>02 · YOUR WASHING HABIT</span>
                        <p>${washingAdvice}</p>
                    </div>

                    <div class="hg-result-group">
                        <span>03 · YOUR STYLING HABIT</span>
                        <p>${stylingAdvice}</p>
                    </div>

                    <div class="hg-result-footer">
                        Remember: consistency and observing how your
                        hair responds are more important than using
                        many products.
                    </div>

                </div>
            `;

            resultContainer.innerHTML = resultHTML;

            resultContainer.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });

        });

    }

});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
