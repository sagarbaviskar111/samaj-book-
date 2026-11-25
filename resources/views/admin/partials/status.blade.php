@php
    $map = ['active' => 'badge-ok', 'approved' => 'badge-ok', 'resolved' => 'badge-ok', 'pending' => 'badge-warn', 'open' => 'badge-warn', 'paused' => 'badge-ink', 'married' => 'badge-ink', 'draft' => 'badge-ink', 'dismissed' => 'badge-ink', 'rejected' => 'badge-bad', 'blocked' => 'badge-ink adm-badge-blocked'];
@endphp
<span class="{{ $map[$s] ?? 'badge-ink' }}"><span class="h-1.5 w-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ __(ucfirst($s)) }}</span>
