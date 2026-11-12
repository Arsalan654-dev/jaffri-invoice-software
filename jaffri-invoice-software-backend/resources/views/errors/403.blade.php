<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Forbidden</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <style>
        .forbidden-overlay {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(4px);
            padding: 24px;
        }
        .forbidden-modal {
            background: #fff;
            border-radius: 16px;
            padding: 40px;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .3);
            animation: forbiddenPop .25s ease;
        }
        @keyframes forbiddenPop {
            from { transform: scale(.95) translateY(8px); opacity: 0; }
            to { transform: scale(1) translateY(0); opacity: 1; }
        }
        .forbidden-code {
            font-size: 3rem;
            font-weight: 800;
            color: var(--danger, #ef4444);
            margin-bottom: 8px;
        }
    </style>
</head>
<body>
    <div class="forbidden-overlay" id="forbiddenOverlay">
        <div class="forbidden-modal" role="dialog" aria-modal="true" aria-labelledby="forbiddenTitle">
            <div class="forbidden-code">403</div>
            <h1 id="forbiddenTitle" style="font-size:1.25rem;font-weight:700;margin-bottom:12px">Forbidden</h1>
            <p style="color:#64748b;font-size:1rem;margin-bottom:24px">You cannot allowed by admin to access this</p>
            <div style="display:flex;gap:12px;justify-content:center">
                <button type="button" class="btn btn-secondary" onclick="history.back()">Go Back</button>
                <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('forbiddenOverlay').addEventListener('click', function (e) {
            if (e.target === this) {
                history.back();
            }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                history.back();
            }
        });
    </script>
</body>
</html>
