@extends('layouts.admin')

@section('title', 'Create Course')
@section('breadcrumb', 'Create Course')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('courses.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Courses</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Create New Course</h1>
    </div>

    <div class="glass-card">
        <form action="{{ route('courses.store') }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Course ID (Unique Key) *</label>
                    <input type="text" name="course_id" class="form-control mono" placeholder="course-python" value="{{ old('course_id') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Course Title *</label>
                    <input type="text" name="title" class="form-control" placeholder="Programming in Python" value="{{ old('title') }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Course Description *</label>
                <textarea name="description" class="form-control" placeholder="Master Python programming from basics to advanced data structures..." required>{{ old('description') }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select name="level" class="form-control">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate" selected>Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estimated Duration Text</label>
                    <input type="text" name="duration_text" class="form-control" placeholder="6h 30m" value="{{ old('duration_text', '6h 00m') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Icon Emoji</label>
                    <input type="text" name="icon" class="form-control" placeholder="🐍" value="{{ old('icon', '🐍') }}">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('courses.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Create Course</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
