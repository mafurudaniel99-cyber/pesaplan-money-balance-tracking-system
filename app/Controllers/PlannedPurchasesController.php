<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Validator;
use App\Models\{PlannedPurchase, Category, User};
use App\Services\FinancialSummaryService;

/**
 * Handles the "Kagua Kabla ya Kutumia" (Pre-Spending Decision) feature.
 */
final class PlannedPurchasesController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $this->view('planned_purchases/index', [
            'activePage' => 'planned_purchases',
            'purchases' => PlannedPurchase::allForUser($userId),
            'categories' => Category::allForUser($userId, 'expense'),
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $settings = User::settings($userId);
        $name = Validator::sanitize($this->input('item_name', ''));
        if ($name === '') $this->back('Jina la bidhaa/huduma linahitajika.');
        $cost = Validator::amount($this->input('proposed_cost', '0'));
        if ($cost === null || $cost <= 0) $this->back('Bei lazima iwe kikubwa kuliko 0.');
        PlannedPurchase::create($userId, [
            'item_name' => $name,
            'proposed_cost' => $cost,
            'reason' => Validator::sanitize($this->input('reason', '')),
            'category_id' => $this->input('category_id') ? (int) $this->input('category_id') : null,
            'classification' => $this->input('classification', 'discretionary'),
            'can_postpone' => $this->input('can_postpone', '1') === '1',
            'proposed_date' => $this->input('proposed_date', date('Y-m-d')),
            'notes' => Validator::sanitize($this->input('notes', '') ?: ''),
        ], (int) ($settings['cooling_off_hours'] ?? 24));
        $this->redirect('/planned-purchases', 'Manunuzi yamehifadhiwa. Subiri kipindi cha kufikiri kabla ya kuamua.');
    }

    public function approve(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $pp = PlannedPurchase::findForUser((int) $id, $userId);
        if (!$pp) $this->back('Manunuzi hayapatikani.');
        [$result, $explanation] = $this->evaluate($userId, (float) $pp['proposed_cost'], (int) ($pp['category_id'] ?? 0));
        PlannedPurchase::setStatus((int) $id, $userId, 'approved', $result, $explanation);
        $this->redirect('/planned-purchases', 'Manunuzi yameidhinishwa.');
    }

    public function reject(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        PlannedPurchase::setStatus((int) $id, $userId, 'rejected', 'Imekataliwa', 'Umeamua kutokeleza manunuzi haya.');
        $this->redirect('/planned-purchases', 'Manunuzi yamekataliwa.', 'info');
    }

    public function complete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        PlannedPurchase::setStatus((int) $id, $userId, 'completed', 'Imekamilika', null);
        $this->redirect('/planned-purchases', 'Manunuzi yamewekwa kama yamekamilika.');
    }

    public function delete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        PlannedPurchase::delete((int) $id, $userId);
        $this->redirect('/planned-purchases', 'Manunuzi yamefutwa.', 'info');
    }

    /**
     * Evaluate a planned purchase against current financial position.
     * Returns [decision_result, explanation]. Never makes unsupported claims.
     */
    private function evaluate(int $userId, float $cost, int $categoryId): array
    {
        $summary = new FinancialSummaryService($userId);
        $cash = $summary->recordedCashBalance();
        $obligations = $summary->unpaidObligations();
        $projected = $cash - $obligations;

        $categoryRemaining = null;
        if ($categoryId > 0) {
            foreach ($summary->budgetItemsWithActuals() as $it) {
                if ((int) $it['category_id'] === $categoryId) {
                    $categoryRemaining = (float) $it['remaining'];
                    break;
                }
            }
        }

        if ($cash <= 0) {
            return ['Taarifa zisizotosha', 'Rekodi yako inaonyesha hakuna fedha zilizosajiliwa. Hakikisha umeweka mapato halisi kabla ya kuamua.'];
        }
        if ($cost > $cash) {
            return ['Fedha hazitoshi', 'Bei inazidi fedha ulizorekodi (' . money($cash) . '). Huwezi kulipia bila kukopa.'];
        }
        if ($cost > $projected) {
            return ['Kinakabiliwa na majukumu', 'Baada ya kutoa majukumu yasiyolipwa (' . money($obligations) . '), fedha zinazopatikana ni ' . money($projected) . '. Bei inazidi kiasi hichi.'];
        }
        if ($categoryRemaining !== null && $cost > $categoryRemaining) {
            return ['Inahitaji marekebisho ya bajeti', 'Bei inazidi bajeti iliyobaki kwenye kategoria hii (' . money($categoryRemaining) . '). Unaweza kubadilisha bajeti au kupangaisha.'];
        }
        if ($categoryRemaining !== null && $cost > $categoryRemaining * 0.8 && $categoryRemaining > 0) {
            return ['Fikiria kusitisha', 'Bei inakaribia kufunga bajeti ya kategoria hii. Linganisha umuhimu wake na mahitaji mengine.'];
        }
        return ['Ndani ya bajeti', 'Bei inalingana na fedha ulizonazo na bajeti ya kategoria.'];
    }
}
