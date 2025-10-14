@component('mail::message')
# New Review Submitted

A reviewer has completed a review for one of your conference papers.

**Paper Title:** {{ $submission->title }}

**Reviewer Name:** {{ $review->reviewer_name }}  
**Reviewer Email:** {{ $review->reviewer_email }}

**Decision:** {{ ucfirst($review->decision) }}

@if($review->comment)
**Comment:**  
{{ $review->comment }}
@endif

---

Thanks,  
{{ config('app.name') }}
@endcomponent
