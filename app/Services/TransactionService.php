<?php
declare(strict_types=1);
namespace App\Services;

use App\Core\Database;
use App\Models\{Transaction, Income, SavingsGoal, Debt};
use App\Models\AuditLog;

/**
 * Service that wraps multi-step transaction operations in DB transactions
 * and keeps savings/debt/obligation tables in sync.
 */
final class TransactionService
{
    /**
     * Record an expense and (optionally) link to a savings contribution
     * or debt payment. Uses a SQL transaction so everything rolls back
     * together if any step fails.
     */
    public static function recordExpense(int $userId, array $data): int
    {
        return Database::transaction(function () use ($userId, $data) {
            return Transaction::create($userId, [
                'category_id' => $data['category_id'] ?? null,
                'type' => Transaction::TYPE_EXPENSE,
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public static function recordSalaryReceipt(int $userId, int $incomeId, float $amount, string $date, string $description): int
    {
        return Database::transaction(function () use ($userId, $incomeId, $amount, $date, $description) {
            Income::markReceived($incomeId, $userId, $amount, $date);
            $txId = Transaction::create($userId, [
                'income_id' => $incomeId,
                'category_id' => null,
                'type' => Transaction::TYPE_SALARY,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description ?: 'Mshahara umepokelewa',
                'payment_method' => 'bank',
                'notes' => null,
            ]);
            AuditLog::write($userId, 'salary_received', 'income', (string) $incomeId, ['amount' => $amount, 'tx' => $txId]);
            return $txId;
        });
    }

    public static function recordOtherIncome(int $userId, array $data): int
    {
        return Database::transaction(function () use ($userId, $data) {
            return Transaction::create($userId, [
                'category_id' => $data['category_id'] ?? null,
                'type' => Transaction::TYPE_OTHER_INCOME,
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    /**
     * Contribute to a savings goal. Creates a savings_in transaction
     * (NOT an expense — money is still owned) and updates the goal balance.
     */
    public static function contributeToSavings(int $userId, int $goalId, float $amount, string $date, string $description, string $method = 'mobile_money'): int
    {
        return Database::transaction(function () use ($userId, $goalId, $amount, $date, $description, $method) {
            $txId = Transaction::create($userId, [
                'category_id' => null,
                'type' => Transaction::TYPE_SAVINGS_IN,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description ?: 'Mchango wa akiba',
                'payment_method' => $method,
                'notes' => null,
            ]);
            SavingsGoal::adjustBalance($goalId, $userId, $amount);
            SavingsGoal::addContribution($goalId, $txId, $amount, 'in', $date, null);
            return $txId;
        });
    }

    public static function withdrawFromSavings(int $userId, int $goalId, float $amount, string $date, string $description, string $method = 'mobile_money'): int
    {
        return Database::transaction(function () use ($userId, $goalId, $amount, $date, $description, $method) {
            $txId = Transaction::create($userId, [
                'category_id' => null,
                'type' => Transaction::TYPE_SAVINGS_OUT,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => $description ?: 'Kutoa akiba',
                'payment_method' => $method,
                'notes' => null,
            ]);
            SavingsGoal::adjustBalance($goalId, $userId, -$amount);
            SavingsGoal::addContribution($goalId, $txId, $amount, 'out', $date, null);
            return $txId;
        });
    }

    /**
     * Record a debt payment. Splits the payment into principal and interest
     * (if interest_rate_pct > 0). The transaction is type=debt_repay so it
     * reduces recorded cash but is NOT counted as consumption expense.
     */
    public static function payDebt(int $userId, int $debtId, float $amount, string $date, ?float $interestPortion = null, string $method = 'bank'): int
    {
        return Database::transaction(function () use ($userId, $debtId, $amount, $date, $interestPortion, $method) {
            $debt = Debt::findForUser($debtId, $userId);
            if (!$debt) {
                throw new \RuntimeException('Deni halipatikani.');
            }
            // If interest portion not given and rate > 0, estimate monthly interest.
            if ($interestPortion === null) {
                $monthlyRate = (float) $debt['interest_rate_pct'] / 100.0 / 12.0;
                $interestPortion = round((float) $debt['outstanding_balance'] * $monthlyRate, 2);
                if ($interestPortion > $amount) {
                    $interestPortion = $amount;
                }
            }
            $principal = max(0.0, $amount - $interestPortion);

            $txId = Transaction::create($userId, [
                'category_id' => null,
                'type' => Transaction::TYPE_DEBT_REPAY,
                'amount' => $amount,
                'transaction_date' => $date,
                'description' => 'Malipo ya deni: ' . $debt['name'],
                'payment_method' => $method,
                'notes' => null,
            ]);
            Debt::addPayment($debtId, $txId, $amount, $interestPortion, $principal, $date, null);
            Debt::applyPayment($debtId, $userId, $principal, $interestPortion);
            return $txId;
        });
    }

    /**
     * Record a refund. Refunds increase cash but are NOT income — they recover
     * a previously-paid expense.
     */
    public static function recordRefund(int $userId, array $data): int
    {
        return Database::transaction(function () use ($userId, $data) {
            return Transaction::create($userId, [
                'category_id' => $data['category_id'] ?? null,
                'type' => Transaction::TYPE_REFUND,
                'amount' => $data['amount'],
                'transaction_date' => $data['transaction_date'],
                'description' => $data['description'] ?: 'Rudisha pesa',
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
