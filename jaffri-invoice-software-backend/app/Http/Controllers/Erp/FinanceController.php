<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\CashEntry;
use App\Models\LedgerEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function cashBook(Request $request)
    {
        $query = CashEntry::with('bank', 'creator');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $cashEntries = $query->latest('entry_date')->paginate(15)->withQueryString();

        $totalIncome = CashEntry::where('type', 'income')->sum('amount');
        $totalExpense = CashEntry::where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $banks = Bank::where('is_active', true)->orderBy('name')->get();

        return view('erp.finance.cash-book', compact('cashEntries', 'totalIncome', 'totalExpense', 'netBalance', 'banks'));
    }

    public function storeCashEntry(Request $request)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'entry_date' => ['required', 'date'],
            'bank_id' => ['nullable', 'exists:banks,id'],
        ]);

        $validated['created_by'] = auth()->id();

        CashEntry::create($validated);

        return back()->with('success', 'Cash entry created successfully.');
    }

    public function banks(Request $request)
    {
        $query = Bank::withSum('cashEntries', 'amount');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('account_number', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        $banks = $query->latest()->paginate(15)->withQueryString();

        return view('erp.finance.banks', compact('banks'));
    }

    public function storeBank(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'balance' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Bank::create($validated);

        return back()->with('success', 'Bank account created successfully.');
    }

    public function ledger(Request $request)
    {
        $query = LedgerEntry::with('creator');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $ledgerEntries = $query->latest('entry_date')->paginate(15)->withQueryString();

        $totalDebit = LedgerEntry::where('type', 'debit')->sum('amount');
        $totalCredit = LedgerEntry::where('type', 'credit')->sum('amount');
        $accounts = LedgerEntry::distinct()->pluck('account_name')->filter()->values();

        return view('erp.finance.ledger', compact('ledgerEntries', 'totalDebit', 'totalCredit', 'accounts'));
    }

    public function storeLedgerEntry(Request $request)
    {
        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:debit,credit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
            'entry_date' => ['required', 'date'],
        ]);

        $validated['created_by'] = auth()->id();

        LedgerEntry::create($validated);

        return back()->with('success', 'Ledger entry created successfully.');
    }

    public function destroyCashEntry(CashEntry $cashEntry)
    {
        $cashEntry->delete();

        return back()->with('success', 'Cash entry deleted successfully.');
    }

    public function updateCashEntry(Request $request, CashEntry $cashEntry)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:income,expense'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reference_number' => ['nullable', 'string', 'max:50'],
            'entry_date' => ['required', 'date'],
            'bank_id' => ['nullable', 'exists:banks,id'],
        ]);

        $cashEntry->update($validated);

        return back()->with('success', 'Cash entry updated successfully.');
    }

    public function updateLedgerEntry(Request $request, LedgerEntry $ledgerEntry)
    {
        $validated = $request->validate([
            'account_name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:debit,credit'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'entry_date' => ['required', 'date'],
        ]);

        $ledgerEntry->update($validated);

        return back()->with('success', 'Ledger entry updated successfully.');
    }

    public function destroyLedgerEntry(LedgerEntry $ledgerEntry)
    {
        $ledgerEntry->delete();

        return back()->with('success', 'Ledger entry deleted successfully.');
    }

    public function downloadCashEntryPdf(CashEntry $cashEntry)
    {
        $cashEntry->load(['bank', 'creator']);

        $fields = [
            'Date' => $cashEntry->entry_date->format('M d, Y'),
            'Type' => ucfirst($cashEntry->type),
            'Category' => e($cashEntry->category ?? '—'),
            'Amount' => 'Rs. '.number_format($cashEntry->amount, 2),
            'Reference' => e($cashEntry->reference_number ?? '—'),
            'Bank' => e($cashEntry->bank->name ?? '—'),
            'Created By' => e($cashEntry->creator->name ?? '—'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Cash Entry',
            'reference' => $cashEntry->reference_number ?: ('CASH-'.$cashEntry->id),
            'status' => ucfirst($cashEntry->type),
            'fields' => $fields,
            'notes' => $cashEntry->description,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('cash-entry-'.$cashEntry->id.'.pdf');
    }

    public function downloadLedgerPdf(LedgerEntry $ledgerEntry)
    {
        $ledgerEntry->load('creator');

        $fields = [
            'Date' => $ledgerEntry->entry_date->format('M d, Y'),
            'Account' => e($ledgerEntry->account_name),
            'Type' => ucfirst($ledgerEntry->type),
            'Amount' => 'Rs. '.number_format($ledgerEntry->amount, 2),
            'Reference Type' => e($ledgerEntry->reference_type ?? '—'),
            'Reference ID' => e($ledgerEntry->reference_id ?? '—'),
            'Created By' => e($ledgerEntry->creator->name ?? '—'),
        ];

        $pdf = Pdf::loadView('erp.pdfs.document', [
            'title' => 'Ledger Entry',
            'reference' => 'GL-'.str_pad($ledgerEntry->id, 6, '0', STR_PAD_LEFT),
            'status' => ucfirst($ledgerEntry->type),
            'fields' => $fields,
            'notes' => $ledgerEntry->description,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('ledger-entry-'.$ledgerEntry->id.'.pdf');
    }
}
