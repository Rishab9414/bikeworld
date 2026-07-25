<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta http-equiv="refresh" content="30">
    <title>We'll be back soon — {{ config('app.name') }}</title>
    <style>
        :root {
            --ink: #0a0a0a;
            --red: #e31e24;
            --cream: #f7f4ef;
            --muted: #8a857c;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            font-family: "Segoe UI", system-ui, -apple-system, sans-serif;
            color: var(--cream);
            background:
                radial-gradient(ellipse 80% 50% at 20% 10%, rgba(227, 30, 36, 0.28), transparent 55%),
                radial-gradient(ellipse 60% 40% at 90% 80%, rgba(227, 30, 36, 0.12), transparent 50%),
                linear-gradient(165deg, #0a0a0a 0%, #16120f 45%, #0d0d0d 100%);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .scene { position: relative; width: 100%; max-width: 640px; text-align: center; z-index: 1; }
        .brand {
            display: inline-flex; align-items: center; gap: 10px;
            margin-bottom: 28px; opacity: 0; animation: rise 0.8s ease forwards;
        }
        .brand-mark {
            width: 42px; height: 42px; border-radius: 12px;
            background: var(--red); color: #fff; font-weight: 900;
            display: grid; place-items: center; letter-spacing: -1px;
            box-shadow: 0 10px 30px rgba(227, 30, 36, 0.35);
        }
        .brand-name { font-size: 1.25rem; font-weight: 800; letter-spacing: -0.03em; }
        .gear-wrap {
            position: relative; width: 160px; height: 160px; margin: 0 auto 28px;
            opacity: 0; animation: rise 0.8s ease 0.1s forwards;
        }
        .gear {
            position: absolute; border-radius: 50%;
            border: 4px dashed rgba(247, 244, 239, 0.2);
        }
        .gear-a {
            inset: 18px; border-color: rgba(227, 30, 36, 0.55);
            animation: spin 12s linear infinite;
        }
        .gear-b {
            inset: 42px; border-style: solid; border-color: rgba(247, 244, 239, 0.15);
            animation: spin-rev 8s linear infinite;
        }
        .gear-core {
            position: absolute; inset: 58px; border-radius: 50%;
            background: linear-gradient(145deg, var(--red), #9f1217);
            box-shadow: 0 0 40px rgba(227, 30, 36, 0.45), inset 0 0 20px rgba(0,0,0,0.25);
            animation: pulse 2.4s ease-in-out infinite;
        }
        .gear-core::after {
            content: ""; position: absolute; inset: 12px; border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.25);
        }
        h1 {
            font-size: clamp(1.75rem, 5vw, 2.6rem); font-weight: 900;
            letter-spacing: -0.04em; line-height: 1.15; margin-bottom: 14px;
            opacity: 0; animation: rise 0.8s ease 0.2s forwards;
        }
        .message {
            color: rgba(247, 244, 239, 0.78); font-size: 1.05rem; line-height: 1.7;
            max-width: 32rem; margin: 0 auto 28px;
            opacity: 0; animation: rise 0.8s ease 0.3s forwards;
        }
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 8px 14px; border-radius: 999px;
            background: rgba(227, 30, 36, 0.15); border: 1px solid rgba(227, 30, 36, 0.35);
            color: #ffb4b6; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em;
            text-transform: uppercase; margin-bottom: 18px;
            opacity: 0; animation: rise 0.8s ease 0.15s forwards;
        }
        .badge-dot {
            width: 8px; height: 8px; border-radius: 50%; background: var(--red);
            animation: blink 1.2s ease-in-out infinite;
        }
        .countdown {
            display: none; gap: 10px; justify-content: center; margin-bottom: 28px;
            opacity: 0; animation: rise 0.8s ease 0.35s forwards;
        }
        .countdown.show { display: flex; }
        .unit {
            min-width: 70px; padding: 12px 10px; border-radius: 14px;
            background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }
        .unit strong { display: block; font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; }
        .unit span { font-size: 0.7rem; color: var(--muted); text-transform: uppercase; letter-spacing: 0.08em; }
        .hint {
            font-size: 0.85rem; color: var(--muted);
            opacity: 0; animation: rise 0.8s ease 0.45s forwards;
        }
        .road {
            position: absolute; left: 0; right: 0; bottom: 12%; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(247,244,239,0.2), transparent);
            overflow: hidden;
        }
        .bike {
            position: absolute; bottom: -6px; left: -80px;
            width: 48px; height: 24px; color: var(--red);
            animation: ride 9s linear infinite;
        }
        .particles span {
            position: absolute; width: 4px; height: 4px; border-radius: 50%;
            background: rgba(227, 30, 36, 0.45); pointer-events: none;
            animation: float var(--d) ease-in-out infinite;
            left: var(--x); top: var(--y); opacity: 0.5;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes spin-rev { to { transform: rotate(-360deg); } }
        @keyframes pulse {
            0%, 100% { transform: scale(1); box-shadow: 0 0 40px rgba(227, 30, 36, 0.45); }
            50% { transform: scale(1.06); box-shadow: 0 0 60px rgba(227, 30, 36, 0.65); }
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.35; } }
        @keyframes rise {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes ride {
            0% { left: -80px; }
            100% { left: calc(100% + 40px); }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); opacity: 0.35; }
            50% { transform: translateY(-18px); opacity: 0.8; }
        }
    </style>
</head>
<body>
    <div class="particles" aria-hidden="true">
        <span style="--x:8%;--y:18%;--d:5s"></span>
        <span style="--x:22%;--y:72%;--d:7s"></span>
        <span style="--x:78%;--y:24%;--d:6s"></span>
        <span style="--x:88%;--y:68%;--d:8s"></span>
        <span style="--x:48%;--y:12%;--d:5.5s"></span>
    </div>

    <div class="scene">
        <div class="brand">
            <div class="brand-mark">BW</div>
            <div class="brand-name">{{ config('app.name', 'BikeWorld') }}</div>
        </div>

        <div class="badge"><span class="badge-dot"></span> Under maintenance</div>

        <div class="gear-wrap" aria-hidden="true">
            <div class="gear gear-a"></div>
            <div class="gear gear-b"></div>
            <div class="gear-core"></div>
        </div>

        <h1>We'll be back on the road soon</h1>
        <p class="message">{{ \App\Models\Setting::maintenanceMessage() }}</p>

        @php
            $eta = \App\Models\Setting::maintenanceEta();
            $etaIso = $eta ? \Illuminate\Support\Carbon::parse($eta)->toIso8601String() : null;
        @endphp

        <div class="countdown {{ $etaIso ? 'show' : '' }}" id="countdown" @if($etaIso) data-eta="{{ $etaIso }}" @endif>
            <div class="unit"><strong id="d">0</strong><span>Days</span></div>
            <div class="unit"><strong id="h">0</strong><span>Hours</span></div>
            <div class="unit"><strong id="m">0</strong><span>Mins</span></div>
            <div class="unit"><strong id="s">0</strong><span>Secs</span></div>
        </div>

        <p class="hint">This page refreshes automatically. Thanks for your patience.</p>
    </div>

    <div class="road" aria-hidden="true">
        <svg class="bike" viewBox="0 0 64 32" fill="currentColor">
            <circle cx="12" cy="22" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
            <circle cx="52" cy="22" r="8" fill="none" stroke="currentColor" stroke-width="2"/>
            <path d="M12 22 L28 22 L36 10 H48 L52 22 M28 22 L36 10 M20 10 H32" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    @if($etaIso)
    <script>
        (function () {
            const el = document.getElementById('countdown');
            const target = new Date(el.dataset.eta).getTime();
            const d = document.getElementById('d');
            const h = document.getElementById('h');
            const m = document.getElementById('m');
            const s = document.getElementById('s');
            function tick() {
                const diff = Math.max(0, target - Date.now());
                const days = Math.floor(diff / 86400000);
                const hours = Math.floor((diff % 86400000) / 3600000);
                const mins = Math.floor((diff % 3600000) / 60000);
                const secs = Math.floor((diff % 60000) / 1000);
                d.textContent = days;
                h.textContent = hours;
                m.textContent = mins;
                s.textContent = secs;
            }
            tick();
            setInterval(tick, 1000);
        })();
    </script>
    @endif
</body>
</html>
