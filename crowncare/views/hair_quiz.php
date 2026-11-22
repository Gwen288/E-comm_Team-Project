<?php
require_once __DIR__ . '/../core/core.php';

/*
 * Seven-question hair quiz for shoppers. The question list below builds the
 * form, and the JavaScript walks through one question at a time before posting
 * to actions/submit_hair_quiz_action.php.
 */

$pageTitle = 'Hair quiz';
$pageDescription = 'Answer seven questions about your hair and get CrownCare product recommendations for each step of your routine.';

// Answers from the last time, so a returning shopper can change one answer quickly.
$old = $_SESSION['hair_quiz'] ?? [];
$oldGoals = $old['hair_goals'] ?? (isset($old['hair_goal']) ? [$old['hair_goal']] : []);

// Each entry drives one step: name, choices, whether several can be picked,
// any limit, and the answers to tick. 'budget' shows descriptions as labels.
$questions = [
    [
        'name' => 'hair_type',
        'title' => 'What is your hair type?',
        'hint' => 'Look at a strand when it is clean and free of product.',
        'multiple' => false,
        'options' => [
            '3C' => 'Tight, springy corkscrew curls',
            '4A' => 'Small, defined coils',
            '4B' => 'Z-shaped coils with sharp bends',
            '4C' => 'Very tight coils with a lot of shrinkage',
            'Not sure' => 'We will recommend products that suit every curl and coil type',
        ],
        'selected' => [$old['hair_type'] ?? ''],
        'unsure' => 'hair-type',
    ],
    [
        'name' => 'concerns',
        'title' => 'What troubles your hair?',
        'hint' => 'Choose up to three.',
        'multiple' => true,
        'max' => 3,
        'options' => [
            'Dryness' => 'Hair feels rough or thirsty',
            'Breakage' => 'Short broken pieces and weak ends',
            'Frizz' => 'Curls lose their shape quickly',
            'Tangles and knots' => 'Hard to detangle on wash day',
            'Itchy or flaky scalp' => 'Irritation, flakes or tenderness',
            'Thinning or weak edges' => 'Thin patches, edges or slow growth',
            'Product buildup' => 'Hair feels coated or heavy',
        ],
        'selected' => $old['concerns'] ?? [],
    ],
    [
        'name' => 'hair_goals',
        'title' => 'What are your hair goals?',
        'hint' => 'Choose up to two.',
        'multiple' => true,
        'max' => 2,
        'options' => [
            'Lasting moisture' => 'Soft hair that stays hydrated',
            'Length retention' => 'Keep the length you grow',
            'Growth' => 'Fuller edges and a stimulated scalp',
            'Curl definition' => 'Defined coils without frizz',
            'Repair' => 'Stronger, less damaged hair',
            'Healthy scalp' => 'A clean, calm, balanced scalp',
        ],
        'selected' => $oldGoals,
    ],
    [
        'name' => 'scalp_type',
        'title' => 'How would you describe your scalp?',
        'hint' => 'Think about how it feels two or three days after washing.',
        'multiple' => false,
        'options' => [
            'Dry' => 'Tight or flaky between washes',
            'Oily' => 'Gets greasy quickly',
            'Balanced' => 'No real problems',
            'Sensitive' => 'Itches or reacts easily',
            'Not sure' => 'We will choose gentle products that suit every scalp',
        ],
        'selected' => [$old['scalp_type'] ?? ''],
        'unsure' => 'scalp',
    ],
    [
        'name' => 'wash_frequency',
        'title' => 'How often do you wash your hair?',
        'hint' => 'This helps us choose how gentle your cleanser should be.',
        'multiple' => false,
        'options' => [
            '2–3 times a week or more' => 'Frequent washing',
            'Once a week' => 'A weekly wash day',
            'Every 2 weeks or less' => 'Longer between washes',
        ],
        'selected' => [$old['wash_frequency'] ?? ''],
    ],
    [
        'name' => 'preferences',
        'title' => 'Any product preferences?',
        'hint' => 'Optional. Choose any that matter to you.',
        'multiple' => true,
        'optional' => true,
        'options' => [
            'Fragrance-free' => 'No added scent',
            'Vegan' => 'No animal ingredients',
            'Sulphate-free' => 'No harsh cleansing agents',
            'Silicone-free' => 'No silicones',
        ],
        'selected' => $old['preferences'] ?? [],
    ],
    [
        'name' => 'budget',
        'title' => 'How much would you like to spend?',
        'hint' => 'For your whole routine.',
        'multiple' => false,
        'options' => [
            'essentials' => 'Under GH₵250',
            '250-500' => 'GH₵250 to GH₵500',
            '500-750' => 'GH₵500 to GH₵750',
            '750-plus' => 'GH₵750 or more',
        ],
        'labels_are_values' => false,
        'selected' => [$old['budget'] ?? ''],
    ],
];

$totalSteps = count($questions);

require_once __DIR__ . '/layout/header.php';
?>

<main class="cc-flow cc-quiz">
    <div class="cc-quiz-shell">
        <div class="cc-quiz-head">
            <a class="cc-back" href="<?php echo BASE_URL; ?>index.php" data-back>Back</a>
            <h1>Hair quiz</h1>
            <p>Answer seven questions and we will recommend CrownCare products for each step of your routine.</p>
        </div>

        <section class="cc-quiz-card" aria-live="polite">
            <div class="cc-quiz-progress">
                <span id="quiz-step-label">Question 1 of <?php echo $totalSteps; ?></span>
                <div class="cc-quiz-track" aria-hidden="true"><div id="quiz-progress-fill"></div></div>
            </div>

            <form
                id="hairQuizForm"
                action="<?php echo BASE_URL; ?>actions/submit_hair_quiz_action.php"
                method="POST"
                novalidate
            >
                <?php echo cc_csrf_field(); ?>

                <!-- All steps are in the form; JS hides all but the current one. -->
                <?php foreach ($questions as $index => $q): ?>
                    <fieldset
                        class="cc-quiz-step<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        data-step="<?php echo $index; ?>"
                        data-name="<?php echo e($q['name']); ?>"
                        data-max="<?php echo (int) ($q['max'] ?? 0); ?>"
                        data-optional="<?php echo !empty($q['optional']) ? '1' : '0'; ?>"
                        <?php echo $index === 0 ? '' : 'hidden'; ?>
                    >
                        <legend><?php echo e($q['title']); ?></legend>
                        <p class="cc-quiz-hint"><?php echo e($q['hint']); ?></p>

                        <div class="cc-quiz-options">
                            <?php foreach ($q['options'] as $value => $description): ?>
                                <?php
                                $isBudget = $q['name'] === 'budget';
                                $label = $isBudget ? $description : $value;
                                $sub = $isBudget ? '' : $description;
                                $checked = in_array($value, (array) $q['selected'], true);
                                ?>
                                <label class="cc-quiz-option<?php echo $value === 'Not sure' ? ' is-unsure' : ''; ?>">
                                    <input
                                        type="<?php echo $q['multiple'] ? 'checkbox' : 'radio'; ?>"
                                        name="<?php echo e($q['name']) . ($q['multiple'] ? '[]' : ''); ?>"
                                        value="<?php echo e($value); ?>"
                                        <?php echo $checked ? 'checked' : ''; ?>
                                    >
                                    <span>
                                        <strong><?php echo e($label); ?></strong>
                                        <?php if ($sub !== ''): ?><small><?php echo e($sub); ?></small><?php endif; ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <?php if (($q['unsure'] ?? '') === 'hair-type'): ?>
                            <div class="cc-quiz-unsure" data-unsure-for="hair_type" hidden>
                                <strong>Not sure of your hair type?</strong>
                                You can still continue: we will recommend products that suit all curl and coil types.
                                To find your type, read the
                                <a class="cc-link" href="<?php echo BASE_URL; ?>views/hair_guide.php" target="_blank" rel="noopener">Hair Guide</a>,
                                or let a specialist tell you at a
                                <a class="cc-link" href="<?php echo BASE_URL; ?>views/book_consultation.php" target="_blank" rel="noopener">Hair and Scalp Assessment</a>.
                            </div>
                        <?php elseif (($q['unsure'] ?? '') === 'scalp'): ?>
                            <div class="cc-quiz-unsure" data-unsure-for="scalp_type" hidden>
                                <strong>Not sure about your scalp?</strong>
                                We will recommend gentle products that suit every scalp. A
                                <a class="cc-link" href="<?php echo BASE_URL; ?>views/book_consultation.php" target="_blank" rel="noopener">Hair and Scalp Assessment</a>
                                can tell you more.
                            </div>
                        <?php endif; ?>

                        <p class="cc-quiz-message" role="alert" hidden></p>

                        <div class="cc-quiz-actions">
                            <?php if ($index > 0): ?>
                                <button type="button" class="cc-button cc-button--outline" data-quiz-back>Back</button>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>

                            <?php if ($index < $totalSteps - 1): ?>
                                <button type="button" class="cc-button" data-quiz-next>Continue</button>
                            <?php else: ?>
                                <button type="submit" class="cc-button">See my products</button>
                            <?php endif; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </form>
        </section>

        <p class="cc-quiz-note">The quiz recommends CrownCare products. It is general guidance, not medical advice.</p>
    </div>
</main>

<script>
(function () {
    var form = document.getElementById('hairQuizForm');
    var steps = Array.prototype.slice.call(form.querySelectorAll('.cc-quiz-step'));
    var label = document.getElementById('quiz-step-label');
    var fill = document.getElementById('quiz-progress-fill');
    var current = 0;

    function show(index) {
        steps[current].hidden = true;
        steps[current].classList.remove('is-active');
        current = index;
        steps[current].hidden = false;
        steps[current].classList.add('is-active');
        label.textContent = 'Question ' + (current + 1) + ' of ' + steps.length;
        fill.style.width = Math.round(((current + 1) / steps.length) * 100) + '%';
        var first = steps[current].querySelector('input:checked') || steps[current].querySelector('input');
        if (first) first.focus({ preventScroll: true });
        var card = document.querySelector('.cc-quiz-card');
        if (card.getBoundingClientRect().top < 0) card.scrollIntoView({ behavior: 'smooth' });
    }

    function message(step, text) {
        var box = step.querySelector('.cc-quiz-message');
        box.textContent = text;
        box.hidden = text === '';
    }

    function valid(step) {
        var chosen = step.querySelectorAll('input:checked').length;
        var max = parseInt(step.dataset.max, 10) || 0;
        if (step.dataset.optional !== '1' && chosen === 0) {
            message(step, max > 0 ? 'Choose at least one answer to continue.' : 'Choose an answer to continue.');
            return false;
        }
        if (max > 0 && chosen > max) {
            message(step, 'Choose up to ' + (max === 2 ? 'two' : 'three') + '.');
            return false;
        }
        message(step, '');
        return true;
    }

    // Stop people choosing more than the limit.
    steps.forEach(function (step) {
        var max = parseInt(step.dataset.max, 10) || 0;
        step.addEventListener('change', function (event) {
            if (max > 0 && event.target.checked && step.querySelectorAll('input:checked').length > max) {
                event.target.checked = false;
                message(step, 'You can choose up to ' + (max === 2 ? 'two' : 'three') + '. Untick one to choose another.');
                return;
            }
            message(step, '');
            updateUnsure();
        });
    });

    // The "Not sure" help appears when Not sure is chosen.
    function updateUnsure() {
        form.querySelectorAll('[data-unsure-for]').forEach(function (box) {
            var chosen = form.querySelector('input[name="' + box.dataset.unsureFor + '"]:checked');
            box.hidden = !(chosen && chosen.value === 'Not sure');
        });
    }
    updateUnsure();

    form.addEventListener('click', function (event) {
        if (event.target.closest('[data-quiz-next]')) {
            if (valid(steps[current])) show(current + 1);
        } else if (event.target.closest('[data-quiz-back]')) {
            show(current - 1);
        }
    });

    // Enter moves to the next question instead of sending the form early.
    form.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' && current < steps.length - 1) {
            event.preventDefault();
            if (valid(steps[current])) show(current + 1);
        }
    });

    form.addEventListener('submit', function (event) {
        if (!valid(steps[current])) event.preventDefault();
    });
})();
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
