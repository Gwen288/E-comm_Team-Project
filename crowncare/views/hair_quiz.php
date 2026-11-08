<?php
require_once __DIR__ . '/../core/core.php';

require_once __DIR__ . '/layout/header.php';

$oldAnswers = $_SESSION['hair_quiz'] ?? [];
?>

<main class="hair-quiz-page">

    <section class="hair-quiz-intro">
        <span class="hair-quiz-kicker">YOUR CROWN, YOUR ROUTINE</span>
        <h1>Let's find what works for your hair.</h1>
        <p>
            Answer a few simple questions and we'll help you discover
            products that suit your hair, your goals and your budget.
        </p>
    </section>

    <section class="hair-quiz-card">

        <div class="hair-quiz-progress">
            <div class="hair-quiz-progress-top">
                <span id="quiz-step-label">QUESTION 1 OF 7</span>
                <span id="quiz-progress-percent">14%</span>
            </div>

            <div class="hair-quiz-progress-track">
                <div id="quiz-progress-fill"></div>
            </div>
        </div>

        <form
            id="hairQuizForm"
            action="<?php echo BASE_URL; ?>actions/submit_hair_quiz_action.php"
            method="POST"
        >

            <!-- QUESTION 1 -->

            <section class="hair-quiz-step active" data-step="0">

                <span class="hair-question-number">01 / 07</span>

                <h2>What is your hair type?</h2>

                <p class="hair-question-description">
                    Look at a strand when it is clean and free of product.
                </p>

                <div class="hair-option-grid">

                    <?php
                    $hairTypes = [
                        '3C' => 'Tight, springy corkscrew curls',
                        '4A' => 'Small, defined coils',
                        '4B' => 'Z-shaped coils with sharp bends',
                        '4C' => 'Very tight coils, lots of shrinkage',
                        'Not sure' => "We'll keep the picks broad"
                    ];
                    ?>

                    <?php foreach ($hairTypes as $value => $description): ?>

                        <label class="hair-option">
                            <input
                                type="radio"
                                name="hair_type"
                                value="<?php echo htmlspecialchars($value); ?>"
                                required
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                                <small><?php echo htmlspecialchars($description); ?></small>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <span></span>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 2 -->

            <section class="hair-quiz-step" data-step="1">

                <span class="hair-question-number">02 / 07</span>

                <h2>What troubles your hair most?</h2>

                <p class="hair-question-description">
                    Choose up to two.
                </p>

                <div class="hair-option-grid">

                    <?php
                    $concerns = [
                        'Dryness' => 'Hair feels rough or thirsty',
                        'Breakage' => 'Short broken pieces, weak ends',
                        'Frizz' => 'Curls lose shape quickly',
                        'Tangles and knots' => 'Hard to detangle on wash day',
                        'Itchy or flaky scalp' => 'Irritation, flakes or tenderness',
                        'Thinning or weak edges' => 'Thin patches, edges or slow growth',
                        'Product buildup' => 'Hair feels coated or heavy'
                    ];
                    ?>

                    <?php foreach ($concerns as $value => $description): ?>

                        <label class="hair-option">
                            <input
                                type="checkbox"
                                name="concerns[]"
                                value="<?php echo htmlspecialchars($value); ?>"
                                class="hair-concern-checkbox"
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                                <small><?php echo htmlspecialchars($description); ?></small>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <p id="concern-limit-message" class="hair-validation-message">
                    You can select a maximum of two concerns.
                </p>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 3 -->

            <section class="hair-quiz-step" data-step="2">

                <span class="hair-question-number">03 / 07</span>

                <h2>What is your main hair goal?</h2>

                <p class="hair-question-description">
                    Choose the result you'd most like to work towards.
                </p>

                <div class="hair-option-grid">

                    <?php
                    $goals = [
                        'Lasting moisture' => 'Soft hair that stays hydrated',
                        'Length retention' => 'Keep the length you grow',
                        'Growth' => 'Fuller edges and a stimulated scalp',
                        'Curl definition' => 'Defined, frizz-free coils',
                        'Repair' => 'Strengthen damaged hair',
                        'Healthy scalp' => 'A clean, calm, balanced scalp'
                    ];
                    ?>

                    <?php foreach ($goals as $value => $description): ?>

                        <label class="hair-option">
                            <input
                                type="radio"
                                name="hair_goal"
                                value="<?php echo htmlspecialchars($value); ?>"
                                required
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                                <small><?php echo htmlspecialchars($description); ?></small>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 4 -->

            <section class="hair-quiz-step" data-step="3">

                <span class="hair-question-number">04 / 07</span>

                <h2>How would you describe your scalp?</h2>

                <div class="hair-option-grid">

                    <?php
                    $scalpTypes = [
                        'Dry' => 'Tight or flaky between washes',
                        'Oily' => 'Gets greasy quickly',
                        'Balanced' => 'No real problems',
                        'Sensitive' => 'Itches or reacts easily',
                        'Not sure' => 'I am not quite sure'
                    ];
                    ?>

                    <?php foreach ($scalpTypes as $value => $description): ?>

                        <label class="hair-option">
                            <input
                                type="radio"
                                name="scalp_type"
                                value="<?php echo htmlspecialchars($value); ?>"
                                required
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                                <small><?php echo htmlspecialchars($description); ?></small>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 5 -->

            <section class="hair-quiz-step" data-step="4">

                <span class="hair-question-number">05 / 07</span>

                <h2>How often do you wash your hair?</h2>

                <div class="hair-option-grid">

                    <?php
                    $washFrequencies = [
                        '2–3 times a week or more',
                        'Once a week',
                        'Every 2 weeks or less'
                    ];
                    ?>

                    <?php foreach ($washFrequencies as $value): ?>

                        <label class="hair-option">
                            <input
                                type="radio"
                                name="wash_frequency"
                                value="<?php echo htmlspecialchars($value); ?>"
                                required
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 6 -->

            <section class="hair-quiz-step" data-step="5">

                <span class="hair-question-number">06 / 07</span>

                <h2>Any product preferences?</h2>

                <p class="hair-question-description">
                    Optional. Choose all that apply, or skip.
                </p>

                <div class="hair-option-grid">

                    <?php
                    $preferences = [
                        'Fragrance-free',
                        'Vegan',
                        'Sulphate-free',
                        'Silicone-free'
                    ];
                    ?>

                    <?php foreach ($preferences as $value): ?>

                        <label class="hair-option">
                            <input
                                type="checkbox"
                                name="preferences[]"
                                value="<?php echo htmlspecialchars($value); ?>"
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="button" class="hair-next-btn">Continue →</button>
                </div>

            </section>


            <!-- QUESTION 7 -->

            <section class="hair-quiz-step" data-step="6">

                <span class="hair-question-number">07 / 07</span>

                <h2>How much would you like to spend?</h2>

                <p class="hair-question-description">
                    We'll use your budget to keep the recommendations practical.
                </p>

                <div class="hair-option-grid">

                    <?php
                    $budgets = [
                        'Under GH₵250' => 'essentials',
                        'GH₵250–500' => '250-500',
                        'GH₵500–750' => '500-750',
                        'GH₵750+' => '750-plus'
                    ];
                    ?>

                    <?php foreach ($budgets as $value => $budgetCode): ?>

                        <label class="hair-option">
                            <input
                                type="radio"
                                name="budget"
                                value="<?php echo htmlspecialchars($budgetCode); ?>"
                                required
                            >

                            <span class="hair-option-content">
                                <strong><?php echo htmlspecialchars($value); ?></strong>

                                <?php if ($budgetCode === 'essentials'): ?>
                                    <small>We'll recommend the essentials</small>
                                <?php elseif ($budgetCode === '750-plus'): ?>
                                    <small>We'll recommend the full routine</small>
                                <?php endif; ?>
                            </span>

                            <span class="hair-option-check">✓</span>
                        </label>

                    <?php endforeach; ?>

                </div>

                <div class="hair-quiz-actions">
                    <button type="button" class="hair-back-btn">← Back</button>
                    <button type="submit" class="hair-submit-btn">
                        Get my recommendations →
                    </button>
                </div>

            </section>

        </form>

    </section>

    <p class="hair-quiz-footnote">
        Your answers help us suggest products based on your preferences.
        Recommendations are general product suggestions, not medical advice.
    </p>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var form = document.getElementById('hairQuizForm');
    var steps = document.querySelectorAll('.hair-quiz-step');
    var currentStep = 0;
    var totalSteps = steps.length;

    var progressFill = document.getElementById('quiz-progress-fill');
    var stepLabel = document.getElementById('quiz-step-label');
    var progressPercent = document.getElementById('quiz-progress-percent');

    var concernCheckboxes = document.querySelectorAll('.hair-concern-checkbox');
    var concernMessage = document.getElementById('concern-limit-message');

    function showStep(index) {

        steps.forEach(function (step) {
            step.classList.remove('active');
        });

        steps[index].classList.add('active');

        currentStep = index;

        var percentage = Math.round(((index + 1) / totalSteps) * 100);

        progressFill.style.width = percentage + '%';
        stepLabel.textContent = 'QUESTION ' + (index + 1) + ' OF ' + totalSteps;
        progressPercent.textContent = percentage + '%';

        window.scrollTo({
            top: document.querySelector('.hair-quiz-card').offsetTop - 100,
            behavior: 'smooth'
        });
    }

    function validateStep() {

var currentSection = steps[currentStep];

// Question 2: Maximum of two concerns
if (currentStep === 1) {

    var selectedConcerns = document.querySelectorAll(
        '.hair-concern-checkbox:checked'
    );

    if (selectedConcerns.length > 2) {
        concernMessage.style.display = 'block';
        return false;
    }

    concernMessage.style.display = 'none';
    return true;
}

// Question 6: Product preferences are optional
if (currentStep === 5) {
    return true;
}

// All other questions require at least one answer
var requiredInputs = currentSection.querySelectorAll(
    'input[required]'
);

var hasAnswer = false;

requiredInputs.forEach(function (input) {
    if (input.checked) {
        hasAnswer = true;
    }
});

if (!hasAnswer) {
    alert('Please select an answer before continuing.');
    return false;
}

return true;
}
    document.querySelectorAll('.hair-next-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            if (!validateStep()) {
                return;
            }

            if (currentStep < totalSteps - 1) {
                showStep(currentStep + 1);
            }

        });

    });

    document.querySelectorAll('.hair-back-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            if (currentStep > 0) {
                showStep(currentStep - 1);
            }

        });

    });

    concernCheckboxes.forEach(function (checkbox) {

        checkbox.addEventListener('change', function () {

            var selected = document.querySelectorAll(
                '.hair-concern-checkbox:checked'
            );

            if (selected.length > 2) {
                checkbox.checked = false;
                concernMessage.style.display = 'block';
            } else {
                concernMessage.style.display = 'none';
            }

        });

    });

    form.addEventListener('submit', function (event) {

        if (!validateStep()) {
            event.preventDefault();
        }

    });

    showStep(0);

});
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>