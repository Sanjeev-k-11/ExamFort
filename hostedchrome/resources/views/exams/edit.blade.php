@extends('layouts.admin')

@section('title', 'Edit Exam: ' . $exam->exam_code)
@section('breadcrumb', 'Edit Exam')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('exams.show', $exam->exam_code) }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Exam Overview</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Edit Examination Parameters</h1>
        <p style="color: #64748b; font-size: 14px; margin-top: 4px;">Update assessment schedule, marks distribution, and status configuration</p>
    </div>

    <div class="glass-card">
        <form action="{{ route('exams.update', $exam->exam_code) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Exam Code (Readonly)</label>
                    <input type="text" class="form-control mono" value="{{ $exam->exam_code }}" readonly disabled style="background: #f1f5f9; color: #64748b;">
                </div>

                <div class="form-group">
                    <label class="form-label">Category / Stream *</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category', $exam->category) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Examination Title *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $exam->title) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description & Instructions</label>
                <textarea name="description" class="form-control">{{ old('description', $exam->description) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" class="form-control" min="1" max="600" value="{{ old('duration_minutes', $exam->duration_minutes) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Total Marks *</label>
                    <input type="number" name="total_marks" class="form-control" min="1" value="{{ old('total_marks', $exam->total_marks) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="ACTIVE" {{ $exam->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE (Live Now)</option>
                        <option value="UPCOMING" {{ $exam->status === 'UPCOMING' ? 'selected' : '' }}>UPCOMING (Scheduled)</option>
                        <option value="COMPLETED" {{ $exam->status === 'COMPLETED' ? 'selected' : '' }}>COMPLETED (Archived)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Exam Date</label>
                    <input type="text" name="exam_date" class="form-control" value="{{ old('exam_date', $exam->exam_date) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Exam Time Window</label>
                    <input type="text" name="exam_time" class="form-control" value="{{ old('exam_time', $exam->exam_time) }}">
                </div>
            </div>

            <div class="form-group" style="margin-top: 10px; background: rgba(248, 250, 252, 0.8); padding: 12px 16px; border-radius: 10px; border: 1px solid #e2e8f0;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; margin: 0;">
                    <input type="checkbox" name="is_results_published" value="1" {{ $exam->is_results_published ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #4f46e5;">
                    <span style="font-size: 13.5px; font-weight: 600; color: #0f172a;">Publish Results to Candidates</span>
                </label>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('exams.show', $exam->exam_code) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
