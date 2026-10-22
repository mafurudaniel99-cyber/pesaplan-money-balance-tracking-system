<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Review;

final class ReviewsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $today = date('Y-m-d');
        $weekLabel = date('o-\WW');
        $monthLabel = date('Y-m');

        $dailyChecklist = Review::dailyChecklist();
        $weeklyChecklist = Review::weeklyChecklist();
        $monthlyChecklist = Review::monthlyChecklist();

        $todayReview = Review::findForPeriod($userId, Review::TYPE_DAILY, $today);
        $weekReview = Review::findForPeriod($userId, Review::TYPE_WEEKLY, $weekLabel);
        $monthReview = Review::findForPeriod($userId, Review::TYPE_MONTHLY, $monthLabel);

        $past = Review::allForUser($userId, 30);

        $this->view('reviews/index', [
            'activePage' => 'reviews',
            'today' => $today,
            'weekLabel' => $weekLabel,
            'monthLabel' => $monthLabel,
            'dailyChecklist' => $dailyChecklist,
            'weeklyChecklist' => $weeklyChecklist,
            'monthlyChecklist' => $monthlyChecklist,
            'todayReview' => $todayReview,
            'weekReview' => $weekReview,
            'monthReview' => $monthReview,
            'past' => $past,
        ]);
    }

    public function store(): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        $type = $this->input('review_type', 'daily');
        if (!in_array($type, ['daily','weekly','monthly'], true)) {
            $this->back('Aina ya ukaguzi si sahihi.');
        }
        $items = $this->input('items', []);
        $notes = $this->input('notes', '');
        $date = $this->input('review_date', date('Y-m-d'));
        $periodLabel = $this->input('period_label', $date);
        // Convert items into checklist with done=true
        $checklist = [];
        $all = $type === 'daily' ? Review::dailyChecklist()
             : ($type === 'weekly' ? Review::weeklyChecklist() : Review::monthlyChecklist());
        foreach ($all as $i => $label) {
            $checklist[] = [
                'label' => $label,
                'done'  => isset($items[$i]),
            ];
        }
        Review::create($userId, $type, $date, $periodLabel, $checklist, $notes);
        $this->redirect('/reviews', 'Ukaguzi umehifadhiwa.');
    }

    public function complete(string $id): void
    {
        $this->auth->requireAuth();
        $this->requireCsrf();
        $userId = (int) $this->auth->id();
        Review::complete((int) $id, $userId);
        $this->redirect('/reviews', 'Ukaguzi umewekwa kama umekamilika.');
    }
}
