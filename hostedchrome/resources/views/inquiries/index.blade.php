@extends('layouts.admin')

@section('title', 'Institutional Consultation Leads & Inquiries')
@section('breadcrumb', 'Consultation Inquiries')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Institutional Consultation Leads &amp; Inquiries</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Incoming campus demo requests, partnership inquiries, and licensing requests submitted from the public landing website.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('public.contact') }}" target="_blank" class="quick-action-btn secondary">
                <i data-lucide="external-link" style="width: 15px; height: 15px;"></i>
                <span>Open Public Form</span>
            </a>
        </div>
    </div>
</div>

<!-- Metrics summary chips -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="glass-card" style="padding: 18px 22px;">
        <div style="font-size: 11.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Total Inquiries</div>
        <div style="font-size: 28px; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif; margin-top: 4px;">{{ $totalCount }}</div>
    </div>
    <div class="glass-card" style="padding: 18px 22px; border-color: rgba(239, 68, 68, 0.3);">
        <div style="font-size: 11.5px; font-weight: 700; color: #f87171; text-transform: uppercase;">Pending Review</div>
        <div style="font-size: 28px; font-weight: 800; color: #f87171; font-family: 'Outfit', sans-serif; margin-top: 4px;">{{ $pendingCount }}</div>
    </div>
    <div class="glass-card" style="padding: 18px 22px; border-color: rgba(16, 185, 129, 0.3);">
        <div style="font-size: 11.5px; font-weight: 700; color: #34d399; text-transform: uppercase;">Contacted / Resolved</div>
        <div style="font-size: 28px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif; margin-top: 4px;">{{ $totalCount - $pendingCount }}</div>
    </div>
</div>

<!-- Filter Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 18px 22px;">
    <form method="GET" action="{{ route('inquiries.index') }}" style="display: flex; gap: 14px; flex-wrap: wrap; align-items: center;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, college or subject..." class="form-input" style="flex: 1; min-width: 260px;">
        
        <select name="status" class="form-input" style="width: auto; min-width: 160px;">
            <option value="">-- All Statuses --</option>
            <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
            <option value="CONTACTED" {{ request('status') === 'CONTACTED' ? 'selected' : '' }}>Contacted</option>
            <option value="RESOLVED" {{ request('status') === 'RESOLVED' ? 'selected' : '' }}>Resolved</option>
            <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
        </select>

        <button type="submit" class="quick-action-btn">
            <i data-lucide="filter" style="width: 15px; height: 15px;"></i>
            <span>Filter</span>
        </button>

        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('inquiries.index') }}" class="quick-action-btn secondary">
                <span>Reset</span>
            </a>
        @endif
    </form>
</div>

<!-- Inquiries Table -->
<div class="glass-card">
    <table class="custom-table">
        <thead>
            <tr>
                <th>Applicant / Organization</th>
                <th>Contact Details</th>
                <th>Subject &amp; Requirements</th>
                <th>Date Received</th>
                <th>Status</th>
                <th style="text-align: right;">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inquiries as $inq)
                <tr>
                    <td>
                        <strong style="color: #fff; font-size: 14px; display: block;">{{ $inq->full_name }}</strong>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $inq->organization_name ?: 'Institution not specified' }}</span>
                    </td>
                    <td>
                        <div style="font-size: 13px; color: #818cf8;">
                            <a href="mailto:{{ $inq->email }}" style="color: inherit; text-decoration: none;">{{ $inq->email }}</a>
                        </div>
                        @if($inq->phone)
                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                                <a href="tel:{{ $inq->phone }}" style="color: inherit; text-decoration: none;">📞 {{ $inq->phone }}</a>
                            </div>
                        @endif
                    </td>
                    <td style="max-width: 380px;">
                        <strong style="color: #f8fafc; font-size: 13px; display: block;">{{ $inq->subject }}</strong>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5; white-space: pre-wrap;">{{ $inq->message }}</div>
                    </td>
                    <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                        {{ \Carbon\Carbon::parse($inq->created_at)->format('d M Y, h:i A') }}<br>
                        <span style="font-size: 11px; color: #818cf8;">{{ \Carbon\Carbon::parse($inq->created_at)->diffForHumans() }}</span>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('inquiries.updateStatus', $inq->id) }}">
                            @csrf
                            <select name="status" onchange="this.form.submit()" class="form-input" style="padding: 4px 8px; font-size: 11.5px; font-weight: 700; border-radius: 6px; width: auto; background: {{ $inq->status === 'PENDING' ? 'rgba(239, 68, 68, 0.2)' : ($inq->status === 'RESOLVED' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(59, 130, 246, 0.2)') }}; color: {{ $inq->status === 'PENDING' ? '#f87171' : ($inq->status === 'RESOLVED' ? '#34d399' : '#38bdf8') }}; border: 1px solid currentColor;">
                                <option value="PENDING" {{ $inq->status === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                <option value="CONTACTED" {{ $inq->status === 'CONTACTED' ? 'selected' : '' }}>CONTACTED</option>
                                <option value="RESOLVED" {{ $inq->status === 'RESOLVED' ? 'selected' : '' }}>RESOLVED</option>
                                <option value="REJECTED" {{ $inq->status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                            </select>
                        </form>
                    </td>
                    <td style="text-align: right;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="mailto:{{ $inq->email }}?subject=Re:%20{{ urlencode($inq->subject) }}" class="quick-action-btn" style="padding: 6px 10px; font-size: 11.5px;" title="Reply via Email">
                                <i data-lucide="mail" style="width: 14px; height: 14px;"></i>
                                <span>Reply</span>
                            </a>
                            @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN']))
                            <form method="POST" action="{{ route('inquiries.destroy', $inq->id) }}" onsubmit="return confirm('Delete this consultation inquiry permanently?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="quick-action-btn danger" style="padding: 6px 8px; font-size: 11.5px;" title="Delete Record">
                                    <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 48px; color: var(--text-muted);">
                        <i data-lucide="inbox" style="width: 36px; height: 36px; margin-bottom: 8px; color: #818cf8;"></i>
                        <p style="font-size: 14px;">No consultation inquiries found.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $inquiries->links() }}
    </div>
</div>
@endsection
