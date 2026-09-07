@extends('layouts.admin')

@section('title', 'Schedule New Examination')
@section('breadcrumb', 'Schedule Exam')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('exams.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Exams Directory</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Schedule New Examination</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Configure examination parameters, duration, total marks, and schedule.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('exams.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Exam Code (Unique Identifier) *</label>
                    <input type="text" name="exam_code" class="form-control mono" placeholder="NAT-2026-EXAM" value="{{ old('exam_code') }}" required>
                    <small style="color: var(--text-muted); font-size: 11px;">Unique alphanumeric exam identifier (e.g. CS-FINAL-2026).</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Category / Stream *</label>
                    <input type="text" name="category" class="form-control" placeholder="Aptitude & Coding" value="{{ old('category', 'Aptitude & Coding') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Examination Title *</label>
                <input type="text" name="title" class="form-control" placeholder="National Aptitude & Programming Assessment Test" value="{{ old('title') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Candidate Instructions</label>
                <textarea name="description" class="form-control" placeholder="Official proctored examination covering MCQs, Data Structures, and System Architecture...">{{ old('description') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes', 120) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Marks *</label>
                    <input type="number" name="total_marks" class="form-control" min="1" value="{{ old('total_marks', 100) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Initial Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="ACTIVE">ACTIVE (Live Now)</option>
                        <option value="UPCOMING" selected>UPCOMING (Scheduled)</option>
                        <option value="COMPLETED">COMPLETED (Archived)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Exam Date</label>
                    <input type="text" name="exam_date" class="form-control" placeholder="15 Oct 2026" value="{{ old('exam_date', date('d M Y')) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Exam Time Window</label>
                    <input type="text" name="exam_time" class="form-control" placeholder="10:00 AM - 12:00 PM" value="{{ old('exam_time', '10:00 AM - 12:00 PM') }}">
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_results_published" value="1" {{ old('is_results_published') ? 'checked' : '' }}>
                    <span style="font-size: 14px; font-weight: 600; color: #fff;">Publish Results to Candidates Immediately upon evaluation</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('exams.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Create & Open Question Bank</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
