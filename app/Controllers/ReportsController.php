<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Services\ReportService;
use App\Models\AuditLog;

final class ReportsController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $userId = (int) $this->auth->id();
        $from = $this->query('from', date('Y-m-01'));
        $to = $this->query('to', date('Y-m-d'));
        $report = $this->query('report', 'monthly_summary');
        $svc = new ReportService($userId);

        $data = [
            'activePage' => 'reports',
            'from' => $from,
            'to' => $to,
            'report' => $report,
        ];

        switch ($report) {
            case 'income':
                $data['rows'] = $svc->monthlyIncomeReport($from, $to);
                $data['view'] = 'reports/partials/income';
                break;
            case 'expense':
                $data['rows'] = $svc->monthlyExpenseReport($from, $to);
                $data['view'] = 'reports/partials/expense';
                break;
            case 'category':
                $data['rows'] = $svc->expenseByCategoryReport($from, $to);
                $data['view'] = 'reports/partials/category';
                break;
            case 'transactions':
                $data['rows'] = $svc->transactionHistoryReport($from, $to);
                $data['view'] = 'reports/partials/transactions';
                break;
            case 'indicators':
                $data['indicators'] = $svc->financialIndicators($from, $to);
                $data['view'] = 'reports/partials/indicators';
                break;
            case 'budget_actual':
                $data['budgetData'] = $svc->budgetVsActualReport(substr($from, 0, 7));
                $data['view'] = 'reports/partials/budget_actual';
                break;
            case 'monthly_summary':
            default:
                $data['indicators'] = $svc->financialIndicators($from, $to);
                $data['incomeRows'] = $svc->monthlyIncomeReport($from, $to);
                $data['expenseRows'] = $svc->monthlyExpenseReport($from, $to);
                $data['view'] = 'reports/partials/summary';
                break;
        }

        $this->view('reports/index', $data);
    }

    public function exportCsv(): void
    {
        $this->auth->requireAuth();
        $userId = (int) $this->auth->id();
        $from = $this->query('from', date('Y-m-01'));
        $to = $this->query('to', date('Y-m-d'));
        $report = $this->query('report', 'transactions');
        $svc = new ReportService($userId);

        // Build CSV
        $rows = [];
        $filename = 'pesaplan_' . $report . '_' . $from . '_to_' . $to . '.csv';
        switch ($report) {
            case 'income':
                $rows[] = ReportService::csvRow(['Mwezi', 'Mshahara (TZS)', 'Mapato mengine (TZS)', 'Jumla (TZS)']);
                foreach ($svc->monthlyIncomeReport($from, $to) as $r) {
                    $rows[] = ReportService::csvRow([
                        ReportService::csvCell($r['month']),
                        ReportService::csvCell((string) (float) $r['salary']),
                        ReportService::csvCell((string) (float) $r['other']),
                        ReportService::csvCell((string) (float) $r['total']),
                    ]);
                }
                break;
            case 'expense':
                $rows[] = ReportService::csvRow(['Mwezi', 'Jumla (TZS)']);
                foreach ($svc->monthlyExpenseReport($from, $to) as $r) {
                    $rows[] = ReportService::csvRow([
                        ReportService::csvCell($r['month']),
                        ReportService::csvCell((string) (float) $r['total']),
                    ]);
                }
                break;
            case 'category':
                $rows[] = ReportService::csvRow(['Kategoria', 'Aina', 'Idadi ya miamala', 'Jumla (TZS)']);
                foreach ($svc->expenseByCategoryReport($from, $to) as $r) {
                    $rows[] = ReportService::csvRow([
                        ReportService::csvCell($r['name']),
                        ReportService::csvCell($r['kind']),
                        ReportService::csvCell((string) (int) $r['tx_count']),
                        ReportService::csvCell((string) (float) $r['total']),
                    ]);
                }
                break;
            case 'transactions':
            default:
                $rows[] = ReportService::csvRow(['Tarehe', 'Aina', 'Maelezo', 'Kategoria', 'Njia', 'Kiasi (TZS)']);
                foreach ($svc->transactionHistoryReport($from, $to) as $r) {
                    $rows[] = ReportService::csvRow([
                        ReportService::csvCell($r['transaction_date']),
                        ReportService::csvCell($r['type']),
                        ReportService::csvCell($r['description']),
                        ReportService::csvCell($r['category_name'] ?? ''),
                        ReportService::csvCell($r['payment_method']),
                        ReportService::csvCell((string) (float) $r['amount']),
                    ]);
                }
                break;
        }

        AuditLog::write($userId, 'csv_export', 'report', $report, ['from' => $from, 'to' => $to]);

        // Stream download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel
        echo implode('', $rows);
        exit;
    }
}
