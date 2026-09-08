@extends('layouts.admin')

@section('title', 'Edit Course: ' . $course->title)
@section('breadcrumb', 'Edit Course')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('courses.show', $course->course_id) }}" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Course Overview</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: var(--text-main);">Edit Course Details</h1>
    </div>

    <div class="glass-card">
        <form action="{{ route('courses.update', $course->course_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Course ID (Readonly)</label>
                    <input type="text" class="form-control mono" value="{{ $course->course_id }}" readonly disabled style="opacity: 0.7; background: #f1f5f9;">
                </div>

                <div class="form-group">
                    <label class="form-label">Course Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $course->title) }}" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Course Description *</label>
                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $course->description) }}</textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Difficulty Level</label>
                    <select name="level" class="form-control">
                        <option value="Beginner" {{ $course->level === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ $course->level === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ $course->level === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Estimated Duration Text</label>
                    <input type="text" name="duration_text" class="form-control" value="{{ old('duration_text', $course->duration_text) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Icon Emoji</label>
                    <input type="text" name="icon" class="form-control" value="{{ old('icon', $course->icon) }}">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('courses.show', $course->course_id) }}" class="btn-secondary" style="text-decoration: none; padding: 10px 20px; font-weight: 600; font-size: 14px;">Cancel</a>
                <button type="submit" class="btn-primary" style="padding: 10px 24px; font-weight: 600; font-size: 14px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
