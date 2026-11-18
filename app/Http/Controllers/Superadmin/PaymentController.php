<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\User;
use App\Support\CsvExport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    private function filtered(Request $r)
    {
        $q = trim((string) $r->query('q', ''));

        return Payment::query()->with('user:id,name,phone')
            ->when(in_array($r->query('status'), Payment::STATUSES, true), fn ($x) => $x->where('status', $r->query('status')))
            ->when($r->filled('gateway'), fn ($x) => $x->where('gateway', $r->query('gateway')))
            ->when($r->filled('purpose'), fn ($x) => $x->where('purpose', $r->query('purpose')))
            ->when($r->query('from'), fn ($x, $d) => $x->whereDate('created_at', '>=', $d))
            ->when($r->query('to'), fn ($x, $d) => $x->whereDate('created_at', '<=', $d))
            ->when($q !== '', fn ($x) => $x->where(fn ($w) => $w
                ->where('gateway_order_id', 'like', "%{$q}%")->orWhere('gateway_payment_id', 'like', "%{$q}%")
                ->orWhereIn('user_id', User::query()->where('name', 'like', "%{$q}%")->orWhere('phone', 'like', "%{$q}%")->select('id'))))
            ->latest('id');
    }

    public function index(Request $request)
    {
        $payments = $this->filtered($request)->paginate(25)->withQueryString();
        $monthStart = now()->startOfMonth();
        $totals = [
            'paid_month' => (int) Payment::where('status', 'paid')->where('paid_at', '>=', $monthStart)->sum('amount_paise'),
            'pending' => (int) Payment::where('status', 'pending')->sum('amount_paise'),
            'refunded' => (int) Payment::where('status', 'refunded')->sum('amount_paise'),
        ];

        return view('superadmin.payments.index', compact('payments', 'totals'));
    }

    public function export(Request $request)
    {
        $rows = (function () use ($request) {
            foreach ($this->filtered($request)->limit(20000)->cursor() as $p) {
                yield [$p->id, $p->created_at, $p->paid_at, $p->user?->name, $p->user?->phone, number_format($p->amount_paise / 100, 2, '.', ''), $p->currency,
                    $p->purpose, $p->gateway, $p->method, $p->status, $p->gateway_order_id, $p->gateway_payment_id, $p->note];
            }
        })();

        return CsvExport::stream('payments-'.now()->format('Ymd-His').'.csv',
            ['id', 'created_at', 'paid_at', 'user', 'phone', 'amount', 'currency', 'purpose', 'gateway', 'method', 'status', 'order_id', 'payment_id', 'note'], $rows);
    }

    public function create()
    {
        return view('superadmin.payments.form');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_phone' => ['nullable', 'digits:10', Rule::exists('users', 'phone')],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
            'purpose' => ['required', Rule::in(Payment::PURPOSES)],
            'method' => ['nullable', Rule::in(['cash', 'upi', 'bank', 'cheque', 'other'])],
            'note' => ['nullable', 'string', 'max:1000'],
            'paid_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $p = Payment::create([
            'user_id' => filled($data['user_phone'] ?? null) ? User::where('phone', $data['user_phone'])->value('id') : null,
            'amount_paise' => (int) round($data['amount'] * 100),
            'currency' => 'INR',
            'purpose' => $data['purpose'],
            'gateway' => 'manual',
            'status' => 'paid',
            'method' => $data['method'] ?? null,
            'note' => $data['note'] ?? null,
            'paid_at' => $data['paid_at'] ?? now(),
            'created_by' => $request->user()->id,
        ]);
        AuditLog::recordSa('payment.create', 'payment', $p->id, $p->amountFormatted().' · '.$p->purpose.' · manual');

        return redirect()->route('superadmin.payments.show', $p->id)->with('status', __('Payment recorded.'));
    }

    public function show(int $id)
    {
        $payment = Payment::with(['user:id,name,phone', 'creator:id,name'])->findOrFail($id);

        return view('superadmin.payments.show', compact('payment'));
    }

    public function refund(int $id)
    {
        $p = Payment::findOrFail($id);
        abort_unless($p->status === 'paid', 422);
        $p->update(['status' => 'refunded']);
        AuditLog::recordSa('payment.refund', 'payment', $p->id, $p->amountFormatted());

        return back()->with('status', __('Marked as refunded.'));
    }

    public function markPaid(int $id)
    {
        $p = Payment::findOrFail($id);
        abort_unless($p->status === 'pending', 422);
        $p->update(['status' => 'paid', 'paid_at' => now()]);
        AuditLog::recordSa('payment.mark-paid', 'payment', $p->id, $p->amountFormatted());

        return back()->with('status', __('Marked as paid.'));
    }
}
