<div class="flex items-center gap-2" {{ $attributes }}>
    <!-- EcoDrop Logo SVG -->
    <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
        <!-- Leaf shape -->
        <defs>
            <linearGradient id="leafGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#4ade80;stop-opacity:1" />
                <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
            </linearGradient>
        </defs>
        
        <!-- Main leaf -->
        <path d="M50 10 Q70 20 75 45 Q78 65 50 90 Q25 65 22 45 Q30 20 50 10 Z" fill="url(#leafGradient)" stroke="#047857" stroke-width="1.5"/>
        
        <!-- Leaf vein -->
        <path d="M50 15 Q50 30 50 90" stroke="#ecfdf5" stroke-width="1.5" fill="none" opacity="0.8"/>
        
        <!-- Droplet inside leaf -->
        <circle cx="50" cy="50" r="6" fill="#3b82f6" opacity="0.9"/>
        <circle cx="50" cy="50" r="4" fill="#60a5fa" opacity="0.6"/>
    </svg>
</div>