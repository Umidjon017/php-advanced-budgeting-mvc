<?php declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\FileNotFoundException;
use App\Helpers\NumberFormatHelper;
use App\View;
use JetBrains\PhpStorm\NoReturn;

class TransactionController
{
    /**
     * @throws FileNotFoundException
     */
    public function index(): View
    {
        $files = $this->getTransactionFiles(STORAGE_PATH);
        $format = new NumberFormatHelper();

        $transactions = [];
        foreach ($files as $file) {
            $transactions = array_merge($transactions, $this->getTransactions($file));
        }

        return View::make('transactions', [
            'transactions'  => $transactions,
            'totals'        => $this->calculateTotals($transactions),
            'format'        => $format
        ]);
    }

    #[NoReturn] public function upload(): void
    {
        $filePath = STORAGE_PATH . '/' . $_FILES['receipt']['name'];
        move_uploaded_file($_FILES['receipt']['tmp_name'], $filePath);

        header('Location: /');
        exit();
    }

    public function getTransactionFiles(string $dirPath): array
    {
        $files = [];

        foreach (scandir($dirPath) as $dir) {
            if (is_dir($dir) || $dir == '.gitignore') {
                continue;
            }
            $files[] = $dirPath . $dir;
        }

        return $files;
    }

    /**
     * @throws FileNotFoundException
     */
    public function getTransactions(string $fileName): array
    {
        if (! file_exists($fileName)) {
            throw new FileNotFoundException();
        }

        $files = fopen($fileName, 'r');
        fgetcsv($files);
        $transactions = [];

        while(($transaction = fgetcsv($files)) !== false) {
//            if ($transactionHandler !== null) {
//                $transaction = $transactionHandler($transaction);
//            }
            $transactions[] = $this->extractTransactions($transaction);
        }

        return $transactions;
    }

    public function extractTransactions(array $transactionRows): array
    {
        [$date, $checkNumber, $description, $amount] = $transactionRows;

        $amount = (float) str_replace(['$', ','], '', $amount);

        return [
            'date'          => $date,
            'checkNumber'   => $checkNumber,
            'description'   => $description,
            'amount'        => $amount
        ];
    }

    public function calculateTotals(array $transactions): array
    {
        $totals = ['netTotal' => 0, 'totalIncome' => 0, 'totalExpense' => 0];

        foreach ($transactions as $transaction) {
            $totals['netTotal'] += $transaction['amount'];

            if ($transaction['amount'] >= 0) {
                $totals['totalIncome'] += $transaction['amount'];
            } else {
                $totals['totalExpense'] += $transaction['amount'];
            }
        }
        return $totals;
    }
}
