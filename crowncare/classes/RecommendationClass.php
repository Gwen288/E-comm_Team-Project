<?php

require_once __DIR__ . '/../core/db_class.php';

/*
 * Hair quiz product recommender, ported from the CMS (Wix) quiz so both sites
 * recommend the same products for the same answers.
 *
 * Each product has tags (hair types, concerns, goals, scalp types, preferences)
 * and a routine step. Every product is scored against the answers, the best
 * product is picked for each routine step, optional steps are dropped to fit the
 * budget, and a kit is suggested when it matches well.
 *
 * Products are matched to the database by title, so prices and photos stay live.
 */
class RecommendationClass extends Database
{
    /* Product tags, the same as the CMS quiz. */
    private const PRODUCTS = [
        ['id' => 'cleanser', 'name' => 'Moisture Rich Cleanser', 'step' => 'cleanse',
         'types' => ['4A', '4B', '4C'], 'concerns' => ['dryness'], 'goals' => ['moisture'], 'scalp' => ['dry', 'balanced'], 'prefs' => ['sulphate-free']],
        ['id' => 'cowash', 'name' => 'Gentle Co-Wash', 'step' => 'cleanse',
         'types' => ['3C', '4A', '4B'], 'concerns' => ['dryness', 'frizz'], 'goals' => ['moisture', 'definition'], 'scalp' => ['dry', 'balanced'], 'prefs' => ['sulphate-free', 'silicone-free'], 'frequent' => true],
        ['id' => 'blacksoap', 'name' => 'Black Soap Clarifying Shampoo', 'step' => 'cleanse',
         'types' => ['4A', '4B', '4C'], 'concerns' => ['buildup'], 'goals' => ['scalp'], 'scalp' => ['oily'], 'prefs' => ['vegan']],
        ['id' => 'soothing', 'name' => 'Scalp Soothing Shampoo', 'step' => 'cleanse',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['itchy'], 'goals' => ['scalp'], 'scalp' => ['sensitive'], 'prefs' => ['fragrance-free']],
        ['id' => 'detangle', 'name' => 'Detangling Conditioner', 'step' => 'condition',
         'types' => ['4A', '4B', '4C'], 'concerns' => ['breakage', 'tangles'], 'goals' => ['length', 'repair'], 'scalp' => [], 'prefs' => ['silicone-free']],
        ['id' => 'hibiscus', 'name' => 'Hibiscus Conditioner', 'step' => 'condition',
         'types' => ['3C', '4A'], 'concerns' => ['frizz'], 'goals' => ['definition'], 'scalp' => [], 'prefs' => ['vegan']],
        ['id' => 'masque', 'name' => 'Deep Moisture Masque', 'step' => 'treat',
         'types' => ['4B', '4C'], 'concerns' => ['dryness'], 'goals' => ['moisture'], 'scalp' => [], 'prefs' => ['vegan']],
        ['id' => 'protein', 'name' => 'Protein Repair Treatment', 'step' => 'treat',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['breakage'], 'goals' => ['repair', 'length'], 'scalp' => [], 'prefs' => ['fragrance-free']],
        ['id' => 'milk', 'name' => 'Leave-In Milk', 'step' => 'moisturise',
         'types' => ['3C', '4A', '4B'], 'concerns' => ['dryness'], 'goals' => ['moisture'], 'scalp' => [], 'prefs' => ['silicone-free']],
        ['id' => 'aloe', 'name' => 'Aloe Daily Hydrating Spray', 'step' => 'moisturise',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['dryness'], 'goals' => ['moisture'], 'scalp' => ['oily', 'balanced'], 'prefs' => ['fragrance-free', 'vegan']],
        ['id' => 'whipped', 'name' => 'Whipped Butter Moisturiser', 'step' => 'moisturise',
         'types' => ['4B', '4C'], 'concerns' => ['dryness', 'breakage'], 'goals' => ['moisture', 'length'], 'scalp' => ['dry'], 'prefs' => ['vegan']],
        ['id' => 'butter', 'name' => 'Nourishing Hair Butter', 'step' => 'moisturise',
         'types' => ['4A', '4B', '4C'], 'concerns' => ['dryness'], 'goals' => ['moisture'], 'scalp' => ['dry'], 'prefs' => ['fragrance-free', 'vegan']],
        ['id' => 'strength', 'name' => 'Scalp and Strength Oil', 'step' => 'oil',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['thinning'], 'goals' => ['growth'], 'scalp' => ['balanced', 'dry'], 'prefs' => ['vegan']],
        ['id' => 'baobab', 'name' => 'Baobab and Coconut Hair Oil', 'step' => 'oil',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['breakage', 'dryness'], 'goals' => ['length', 'moisture'], 'scalp' => ['dry'], 'prefs' => ['vegan']],
        ['id' => 'castor', 'name' => 'Castor Oil Scalp Serum', 'step' => 'oil',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['thinning', 'itchy'], 'goals' => ['growth', 'scalp'], 'scalp' => ['sensitive', 'dry'], 'prefs' => ['fragrance-free']],
        ['id' => 'custard', 'name' => 'Curl Defining Custard', 'step' => 'style',
         'types' => ['3C', '4A', '4B'], 'concerns' => ['frizz'], 'goals' => ['definition'], 'scalp' => [], 'prefs' => ['silicone-free']],
        ['id' => 'flax', 'name' => 'Flaxseed Styling Gel', 'step' => 'style',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['frizz'], 'goals' => ['definition'], 'scalp' => [], 'prefs' => ['vegan']],
        ['id' => 'edge', 'name' => 'Sleek Edge Control', 'step' => 'style',
         'types' => ['3C', '4A', '4B', '4C'], 'concerns' => ['frizz'], 'goals' => ['definition'], 'scalp' => [], 'prefs' => ['vegan']],
    ];

    private const KITS = [
        ['id' => 'kit4c', 'name' => '4C Moisture Starter Kit',
         'types' => ['4B', '4C'], 'concerns' => ['dryness'], 'goals' => ['moisture'],
         'contains' => 'Moisture Rich Cleanser, Deep Moisture Masque and Whipped Butter Moisturiser', 'saves' => 50],
        ['id' => 'kitscalp', 'name' => 'Scalp Care Kit',
         'types' => ['4A', '4B', '4C'], 'concerns' => ['thinning', 'itchy'], 'goals' => ['growth', 'scalp'],
         'contains' => 'Scalp Soothing Shampoo, Scalp and Strength Oil and Castor Oil Scalp Serum', 'saves' => 45],
    ];

    private const STEPS = [
        ['key' => 'cleanse', 'label' => 'Step 1 · Cleanse', 'core' => true],
        ['key' => 'condition', 'label' => 'Step 2 · Condition', 'core' => true],
        ['key' => 'moisturise', 'label' => 'Step 3 · Moisturise', 'core' => true],
        ['key' => 'oil', 'label' => 'Step 4 · Seal & scalp', 'core' => false],
        ['key' => 'treat', 'label' => 'Weekly · Deep treat', 'core' => false],
        ['key' => 'style', 'label' => 'Style', 'core' => false],
    ];

    /* Quiz answers as saved by actions/submit_hair_quiz_action.php, mapped to the CMS codes. */
    private const CONCERN_CODES = [
        'Dryness' => 'dryness', 'Breakage' => 'breakage', 'Frizz' => 'frizz',
        'Tangles and knots' => 'tangles', 'Itchy or flaky scalp' => 'itchy',
        'Thinning or weak edges' => 'thinning', 'Product buildup' => 'buildup',
    ];
    private const GOAL_CODES = [
        'Lasting moisture' => 'moisture', 'Length retention' => 'length', 'Growth' => 'growth',
        'Curl definition' => 'definition', 'Repair' => 'repair', 'Healthy scalp' => 'scalp',
    ];
    private const WASH_CODES = [
        '2–3 times a week or more' => 'often', 'Once a week' => 'weekly', 'Every 2 weeks or less' => 'rarely',
    ];
    private const BUDGETS = [
        'essentials' => 250, '250-500' => 500, '500-750' => 750, '750-plus' => 99999,
    ];

    /** Turns the saved quiz answers into the codes the scoring uses. */
    private function answers(array $quiz)
    {
        $type = $quiz['hair_type'] ?? 'Not sure';
        $scalp = $quiz['scalp_type'] ?? 'Not sure';

        return [
            'type' => $type === 'Not sure' ? 'unsure' : $type,
            'concerns' => array_values(array_filter(array_map(function ($c) {
                return self::CONCERN_CODES[$c] ?? null;
            }, (array) ($quiz['concerns'] ?? [])))),
            'concernLabels' => array_values((array) ($quiz['concerns'] ?? [])),
            'goal' => self::GOAL_CODES[$quiz['hair_goal'] ?? ''] ?? '',
            'goalLabel' => (string) ($quiz['hair_goal'] ?? ''),
            'scalp' => $scalp === 'Not sure' ? 'unsure' : strtolower($scalp),
            'wash' => self::WASH_CODES[$quiz['wash_frequency'] ?? ''] ?? 'weekly',
            'prefs' => array_values(array_map('strtolower', (array) ($quiz['preferences'] ?? []))),
            'budget' => self::BUDGETS[$quiz['budget'] ?? ''] ?? 250,
        ];
    }

    /** Live product rows (id, price of the smallest size, image) keyed by title. */
    private function liveProducts()
    {
        $rows = $this->conn->query(
            "SELECT p.product_id, p.product_title, p.product_image,
                    COALESCE(MIN(o.price), p.product_price) AS from_price
             FROM cc_products p
             LEFT JOIN cc_product_size_options o ON o.product_id = p.product_id
             GROUP BY p.product_id, p.product_title, p.product_image, p.product_price"
        );

        $byTitle = [];
        foreach ($rows ? $rows->fetch_all(MYSQLI_ASSOC) : [] as $row) {
            $byTitle[strtolower(trim($row['product_title']))] = $row;
        }

        return $byTitle;
    }

    /** Score one product against the answers, with the reasons shown to the shopper. */
    private function scoreProduct(array $p, array $a)
    {
        $s = 0;
        $why = [];

        if ($a['type'] === 'unsure') {
            $s += 1;
        } elseif (in_array($a['type'], $p['types'], true)) {
            $s += 3;
            $why[] = 'is made for ' . $a['type'] . ' hair';
        } else {
            $s -= 4;
        }

        foreach ($a['concerns'] as $i => $c) {
            if (in_array($c, $p['concerns'], true)) {
                $s += 4;
                $why[] = 'targets ' . strtolower($a['concernLabels'][$i] ?? $c);
            }
        }

        if ($a['goal'] !== '' && in_array($a['goal'], $p['goals'], true)) {
            $s += 5;
            $why[] = 'supports ' . strtolower($a['goalLabel']);
        }

        if ($a['scalp'] !== 'unsure' && in_array($a['scalp'], $p['scalp'], true)) {
            $s += 2;
            $why[] = 'suits a ' . $a['scalp'] . ' scalp';
        }

        if ($p['step'] === 'cleanse') {
            if ($a['wash'] === 'often' && !empty($p['frequent'])) {
                $s += 3;
                $why[] = 'is gentle enough for frequent washing';
            }
            if ($a['wash'] === 'rarely' && $p['id'] === 'blacksoap') {
                $s += 2;
            }
            if ($a['scalp'] === 'oily' && $p['id'] === 'blacksoap') {
                $s += 3;
            }
            if ($a['scalp'] === 'sensitive' && $p['id'] === 'soothing') {
                $s += 3;
            }
        }

        $prefHits = array_values(array_intersect($a['prefs'], $p['prefs']));
        $s += count($prefHits) * 2 - (count($a['prefs']) - count($prefHits)) * 3;
        if ($prefHits) {
            $why[] = 'is ' . implode(' and ', $prefHits);
        }

        // Fragrance-free is treated as a must-have (sensitivity); other preferences are strong nudges.
        $prefOk = !in_array('fragrance-free', $a['prefs'], true) || in_array('fragrance-free', $p['prefs'], true);

        return ['p' => $p, 's' => $s, 'why' => $why, 'prefOk' => $prefOk];
    }

    /**
     * Returns:
     *  routine  list of ['product' => db row, 'step' => label, 'why' => sentence, 'from' => price]
     *  total    sum of the "from" prices
     *  dropped  product names left out to fit the budget
     *  kit      null or ['product' => db row, 'contains' => text, 'saves' => amount, 'from' => price]
     */
    public function recommend(array $quiz)
    {
        $a = $this->answers($quiz);
        $live = $this->liveProducts();

        // Only products that exist in this database take part, with their live price.
        $scored = [];
        foreach (self::PRODUCTS as $p) {
            $row = $live[strtolower('CrownCare ' . $p['name'])] ?? null;
            if ($row) {
                $p['from'] = (float) $row['from_price'];
                $p['row'] = $row;
                $scored[] = $this->scoreProduct($p, $a);
            }
        }

        $hasAny = function (array $list) use ($a) {
            return (bool) array_intersect($a['concerns'], $list);
        };

        $routine = [];
        foreach (self::STEPS as $st) {
            $pool = array_values(array_filter($scored, function ($x) use ($st) {
                return $x['p']['step'] === $st['key'];
            }));
            $strict = array_values(array_filter($pool, function ($x) {
                return $x['prefOk'];
            }));
            if ($strict) {
                $pool = $strict; // respect preferences when possible
            }
            if (!$pool) {
                continue;
            }

            usort($pool, function ($x, $y) {
                return ($y['s'] <=> $x['s']) ?: ($x['p']['from'] <=> $y['p']['from']);
            });
            $best = $pool[0];

            $include = $st['core'];
            if ($st['key'] === 'oil') {
                $include = in_array($a['goal'], ['growth', 'length', 'scalp'], true)
                    || $hasAny(['thinning', 'itchy', 'breakage'])
                    || $best['s'] >= 9;
            }
            if ($st['key'] === 'treat') {
                $include = in_array($a['goal'], ['repair', 'moisture', 'length'], true)
                    || $hasAny(['breakage', 'dryness']);
            }
            if ($st['key'] === 'style') {
                $include = $a['goal'] === 'definition' || in_array('frizz', $a['concerns'], true);
            }

            if ($include) {
                $best['stepLabel'] = $st['label'];
                $routine[] = $best;
            }
        }

        // Budget: drop optional steps (style, treat, oil) until the routine fits.
        $total = function () use (&$routine) {
            return array_sum(array_map(function ($x) {
                return $x['p']['from'];
            }, $routine));
        };
        $dropped = [];
        foreach (['style', 'treat', 'oil'] as $k) {
            if ($total() <= $a['budget']) {
                break;
            }
            foreach ($routine as $i => $x) {
                if ($x['p']['step'] === $k) {
                    $dropped[] = $x['p']['name'];
                    array_splice($routine, $i, 1);
                    break;
                }
            }
        }

        // Kit suggestion.
        $kit = null;
        $kitScore = 0;
        foreach (self::KITS as $k) {
            $row = $live[strtolower('CrownCare ' . $k['name'])] ?? null;
            if (!$row) {
                continue;
            }
            $s = 0;
            if ($a['type'] === 'unsure' || in_array($a['type'], $k['types'], true)) {
                $s += 2;
            }
            if (in_array($a['goal'], $k['goals'], true)) {
                $s += 4;
            }
            foreach ($a['concerns'] as $c) {
                if (in_array($c, $k['concerns'], true)) {
                    $s += 3;
                }
            }
            if ($s >= 7 && (float) $row['from_price'] <= $a['budget'] && (!$kit || $s > $kitScore)) {
                $kit = [
                    'product' => $row,
                    'contains' => $k['contains'],
                    'saves' => $k['saves'],
                    'from' => (float) $row['from_price'],
                ];
                $kitScore = $s;
            }
        }

        return [
            'routine' => array_map(function ($x) {
                return [
                    'product' => $x['p']['row'],
                    'step' => $x['stepLabel'],
                    'why' => $x['why']
                        ? 'Recommended for you because it ' . implode(' and ', array_slice($x['why'], 0, 2)) . '.'
                        : 'A gentle, all-round recommendation for this step.',
                    'from' => $x['p']['from'],
                ];
            }, $routine),
            'total' => $total(),
            'dropped' => $dropped,
            'kit' => $kit,
        ];
    }
}
