@extends('layouts.admin')

@section('title', 'Organizations & Partner Institutions')
@section('breadcrumb', 'Organizations')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Partner Organizations & Universities</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Provision multi-tenant institutions, assign annual quotas, and manage Dean / Principal access.
            </p>
        </div>
        <a href="{{ route('organizations.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);">
            <i data-lucide="building-2" style="width: 16px; height: 16px;"></i>
            <span>Register New Organization</span>
        </a>
    </div>
</div>

<!-- Search Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('organizations.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: var(--text-muted);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search organization name, code, type, email..." value="{{ request('search') }}">
        </div>

        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if(request('search'))
            <a href="{{ route('organizations.index') }}" class="quick-action-btn secondary" style="color: #dc2626;">Clear</a>
        @endif
    </form>
</div>

<!-- Organizations Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
    @forelse($organizations as $org)
        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; background: rgba(255, 255, 255, 0.9);">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 12px;">
                    <div>
                        <span class="mono" style="font-size: 11.5px; font-weight: 800; color: #4f46e5; background: rgba(79, 70, 229, 0.08); padding: 3px 8px; border-radius: 4px;">{{ $org->code }}</span>
                        <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 8px;">{{ $org->name }}</h3>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Type: {{ $org->type }}</div>
                    </div>
                    <span class="status-pill {{ $org->status === 'ACTIVE' ? 'active' : 'danger' }}">{{ $org->status }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin: 16px 0; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); padding: 12px; border-radius: 10px; text-align: center;">
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Deans</div>
                        <div style="font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $org->principals_count }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Faculty</div>
                        <div style="font-size: 17px; font-weight: 800; color: #4f46e5; margin-top: 2px;">{{ $org->teachers_count }} / {{ $org->max_teachers_allowed }}</div>
                    </div>
                    <div>
                        <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Exams/Yr</div>
                        <div style="font-size: 17px; font-weight: 800; color: #059669; margin-top: 2px;">{{ $org->exams_count }} / {{ $org->max_exams_allowed }}</div>
                    </div>
                </div>

                <div style="font-size: 12.5px; color: var(--text-secondary); margin-bottom: 12px;">
                    <div>Email: <strong style="color: #1e293b;">{{ $org->email ?? 'N/A' }}</strong></div>
                    <div>Location: {{ $org->address ?? 'India' }}</div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('organizations.show', $org->id) }}" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="settings" style="width: 14px; height: 14px;"></i>
                    <span>Manage Org & Quotas</span>
                </a>
                <a href="{{ route('organizations.edit', $org->id) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 8px 12px;">
                    <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                </a>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--text-muted);">
            <i data-lucide="building" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
            <p style="font-size: 15px; font-weight: 700; color: #0f172a;">No organizations registered</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Register New Organization" to add colleges.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $organizations->links() }}
</div>
@endsection
