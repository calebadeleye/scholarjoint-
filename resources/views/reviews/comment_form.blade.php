<!DOCTYPE html>
<html>
<head>
    <title>Review Decision</title>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h2>Decision Taken: {{ ucfirst($decision) }}</h2>
    <p>Please tell us why you chose to {{ $decision }} this review request.</p>

    {{-- Display Success Message --}}
    @if (session('success'))
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Display Error Message --}}
    @if (session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('review.comment', $review->token) }}">
        @csrf
        <textarea name="comment" rows="5" cols="50" placeholder="Your comments..." required></textarea>
        <br><br>
        <button type="submit">Submit Comment</button>
    </form>
</body>
</html>
